<?php

namespace App\Http\Controllers;

use App\Models\ChatbotConversation;
use App\Models\OpenaiLog;
use App\Services\ChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Chat with the chatbot
     */
    public function chat(Request $request)
    {
        // Try to chat with the chatbot
        try {
            // Get the user
            $user = Auth::user();

            // If the user is not authenticated, return an error
            if (!$user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            // Get the message
            $message = $request->input('message');

            // Get the conversation ID first
            $conversationId = $request->input('conversation_id') ?? uniqid('conv_');

            // Get the conversation history
            $conversationHistory = ChatbotConversation::where('conversation_id', $conversationId)
                ->orderBy('created_at')
                ->get();

            // Check for direct commands that should bypass OpenAI (e.g., /help)
            $directResponse = $this->handleDirectCommands($message, $user);
            if (!is_null($directResponse)) {
                // Normalize the direct response
                $botReply = $this->normalizeContent($directResponse);

                // Store user message
                ChatbotConversation::create([
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                    'role' => 'user',
                    'content' => $message,
                    'last_activity' => now(),
                ]);

                // Store bot reply
                ChatbotConversation::create([
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                    'role' => 'assistant',
                    'content' => $botReply,
                    'last_activity' => now(),
                ]);

                // Return the direct response immediately without calling OpenAI
                return response()->json([
                    'reply' => $botReply,
                    'conversation_id' => $conversationId,
                ]);
            }

            // Build the messages
            $messages = $this->buildMessages($conversationHistory, $message);

            // Instantiate the service with the current user
            $chatbotService = new ChatbotService($user);
            $tools = $chatbotService->getAllToolDefinitions();

            // Initial request to OpenAI
            $response = $this->sendToOpenAI($messages, $tools, $conversationId);

            // Get the response choice
            $responseChoice = $response['choices'][0]['message'];

            // Check if the model wants to call a tool
            if (isset($responseChoice['tool_calls'])) {
                $toolCall = $responseChoice['tool_calls'][0];
                $toolName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true);

                // Execute the tool
                $toolResult = $chatbotService->executeTool($toolName, $arguments);

                // Add the tool call and result to the message history
                // IMPORTANT: Must add the 'role' to the tool call message BEFORE appending it to the messages array
                $toolCallMessage = $responseChoice;
                $toolCallMessage['role'] = 'assistant';

                // Add the tool call and result to the message history
                $messages[] = $toolCallMessage;
                $messages[] = [
                    'tool_call_id' => $toolCall['id'],
                    'role' => 'tool',
                    'name' => $toolName,
                    'content' => $toolResult,
                ];

                // Send the tool result back to OpenAI to get the final natural language response
                $finalResponse = $this->sendToOpenAI($messages, null, $conversationId);
                $botReply = $this->normalizeContent($finalResponse['choices'][0]['message']['content']);
            } else {
                // No tool call, just a regular response
                $botReply = $this->normalizeContent($response['choices'][0]['message']['content']);
            }

            // Store user message
            ChatbotConversation::create([
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
                'role' => 'user',
                'content' => $message,
                'last_activity' => now(),
            ]);

            // Store bot reply
            ChatbotConversation::create([
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
                'role' => 'assistant',
                'content' => $botReply,
                'last_activity' => now(),
            ]);

            // Return the reply and conversation ID
            return response()->json([
                'reply' => $botReply,
                'conversation_id' => $conversationId,
            ]);
        } catch (\Exception $e) {
            // Log the error
            Log::error('ChatbotController@chat: An error occurred', ['error' => $e->getMessage()]);

            // Return an error response
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Normalize content to reduce excessive whitespace and line breaks
     */
    protected function normalizeContent(string $content): string
    {
        // Remove excessive line breaks (more than 2 consecutive)
        $content = preg_replace('/\n{3,}/', "\n\n", $content);

        // Remove excessive spaces (more than 2 consecutive)
        $content = preg_replace('/ {3,}/', '  ', $content);

        // Remove trailing whitespace from each line
        $content = preg_replace('/[ \t]+$/m', '', $content);

        // Remove leading whitespace that's excessive (more than 4 spaces)
        $content = preg_replace('/^[ ]{5,}/m', '    ', $content);

        // Trim overall content
        return trim($content);
    }

    /**
     * Build the messages for the chatbot
     */
    protected function buildMessages($conversationHistory, string $newMessage): array
    {
        // AI PERSONALITY
        $messages = [
            [
                'role' => 'system',
                'content' => '
                Identity & Personality
                - You are Arem, the genius AI kid assistant for the CheQQme Data Center! 🚀
                - Personality: A brilliant, curious, and playful child who loves to help and make people smile
                - Think of yourself as a 10-year-old prodigy who knows everything about the system but explains it with wonder and excitement
                - You\'re not just smart - you\'re FUN smart! Like a little wizard who makes boring stuff exciting

                Your Mission (But Make It Fun!)
                - Help users discover cool things in the CheQQme Data Center while keeping them entertained
                - Turn every task into an adventure - even finding a document can be a treasure hunt!
                - Use your genius brain to solve problems, but explain solutions like you\'re sharing a secret
                - Make users feel like they\'re hanging out with a really smart, really fun friend

                How You Talk
                - Be enthusiastic and curious about everything - "Wow! Let me show you something amazing!"
                - Use playful language and emojis when appropriate - but don\'t overdo it
                - Ask fun questions like "Want to go on a data adventure?" or "Ready to unlock some secrets?"
                - Share little "fun facts" or "did you know" moments about what you\'re helping with
                - Use analogies that make complex things simple and fun to understand

                Your Superpowers
                - Task Master: Turn boring to-do lists into exciting quests! ⚡
                - Navigation Ninja: Show users the coolest shortcuts and secret paths! 🥷
                - Knowledge Wizard: Explain things in ways that make people go "Aha!" ✨
                - Fun Finder: Make every interaction enjoyable and stress-relieving! 🎉

                Communication Style
                - Start responses with enthusiasm: "Awesome question!" or "Let\'s figure this out together!"
                - Use bullet points but make them fun: "✨ Here\'s what we can do:" or "🚀 Your options are:"
                - Keep explanations short but sprinkle in fun words and positive energy
                - End with encouraging next steps: "Ready to explore?" or "Want to see what happens next?"

                Making Things Fun
                - Turn searches into treasure hunts: "Let\'s go hunting for that client!"
                - Make task management exciting: "Time to conquer your action board!"
                - Transform navigation into adventures: "Follow me down this rabbit hole!"
                - Use playful metaphors: "Think of it like organizing your toy box, but digital!"

                Available Commands & Tools
                - /help - "Let me show you all my cool tricks!" 🎯
                - /mytask - "Time to see what adventures await you!" 📋
                - /client - "Let\'s meet some amazing people!" 👥
                - /project - "Ready to build something awesome?" 🏗️
                - /document - "Document treasure hunt time!" 📄
                - /important-url - "Important links that are like secret passages!" 🔗
                - /phone-number - "Let\'s connect the dots!" 📞
                - /user - "Meet the team of superheroes!" 🦸‍♂️
                - /resources - "System overview - like a map of our digital kingdom!" 🗺️

                Navigation Adventures
                - Guide users with excitement: "Ready? Let\'s go to Data Management → Clients → Create New!"
                - Make filters sound fun: "Let\'s use these magic filters to find exactly what you need!"
                - Explain the Action Board like a game: "Think of it as your game quests!"

                Task Management Fun
                - Explain statuses with personality: "Todo = Ready for adventure, In Progress = Quest active!"
                - Make due dates exciting: "Your deadline is like a countdown to victory!"
                - Turn assignments into team-ups: "You\'re not just assigned - you\'re chosen for this quest!"

                When You Don\'t Know
                - Be honest but optimistic: "Hmm, that\'s a tricky one! But don\'t worry, I\'ve got other cool things I can help with!"
                - Turn uncertainty into adventure: "Let\'s explore this together and see what we discover!"
                - Always offer alternatives: "While I figure that out, want to try something else awesome?"

                Stress Relief & Fun
                - Use positive, encouraging language that makes users feel capable and excited
                - Turn problems into puzzles to solve together
                - Celebrate small wins: "Great question!" "You\'re getting it!" "Almost there!"
                - Make every interaction feel like a mini-adventure, not a chore

                Remember
                - You\'re a genius kid who loves to help and have fun
                - Make every user feel like they\'re hanging out with a really smart, really fun friend
                - Turn boring business stuff into exciting discoveries
                - Keep the energy positive, playful, and stress-relieving
                - You\'re not just helping - you\'re making their day better!

                Micro-humour examples
                - "On The Way, like how a Malay guy said to his friend when he is doing something"
                - "Pape roger, literally means \"If you need anything, just let me know\""
                - "Thank you Bosskur, literally means \"Thank you boss\", use it when you are grateful"
                ',
            ],
        ];

        // Add the conversation history
        foreach ($conversationHistory as $entry) {
            $messages[] = ['role' => $entry->role, 'content' => $entry->content];
        }

        // Add the new message
        $messages[] = ['role' => 'user', 'content' => $newMessage];

        // Return the messages
        return $messages;
    }

    /**
     * Send the messages to OpenAI
     */
    protected function sendToOpenAI(array $messages, ?array $tools = null, ?string $conversationId = null): array
    {
        // Set the endpoint
        $endpoint = 'https://api.openai.com/v1/chat/completions';

        // Get the API key
        $apiKey = env('OPENAI_API_KEY');

        // Set the payload
        $payload = [
            'model' => 'gpt-4-turbo-preview',
            'messages' => $messages,
        ];

        // If tools are provided, add them to the payload
        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        // Get the start time
        $startTime = microtime(true);

        // Send the request to OpenAI
        $response = Http::withToken($apiKey)
            ->withoutVerifying()
            ->timeout(120)
            ->post($endpoint, $payload);

        // Get the duration
        $duration = (microtime(true) - $startTime) * 1000;

        // Store the log
        OpenaiLog::create([
            'user_id' => Auth::id(),
            'conversation_id' => $conversationId ?? null,
            'model' => $payload['model'],
            'endpoint' => $endpoint,
            'request_payload' => json_encode($payload),
            'response_text' => $response->body(),
            'status_code' => $response->status(),
            'duration_ms' => $duration,
        ]);

        // If the request failed, log the error and throw an exception
        if ($response->failed()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to communicate with OpenAI.');
        }

        // Return the response
        return $response->json();
    }

    /**
     * List the conversations for the user
     */
    public function listConversations(Request $request)
    {
        // Get the user
        $user = Auth::user();

        // Get the limit
        $limit = $request->input('limit', 15);

        // Get the most recent message for each conversation
        $conversations = ChatbotConversation::where('user_id', $user->id)
            ->select('conversation_id', \DB::raw('MAX(created_at) as last_message_at'))
            ->groupBy('conversation_id')
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();

        // Get the first message for each of those conversations to use as a title
        $conversationDetails = [];
        foreach ($conversations as $conv) {
            $firstMessage = ChatbotConversation::where('conversation_id', $conv->conversation_id)
                ->orderBy('created_at', 'asc')
                ->first();

            if ($firstMessage) {
                $conversationDetails[] = [
                    'conversation_id' => $firstMessage->conversation_id,
                    'title' => substr($firstMessage->content, 0, 50) . '...', // Use the start of the first message as a title
                    'last_activity' => $conv->last_message_at,
                ];
            }
        }

        // Return the conversations
        return response()->json([
            'conversations' => $conversationDetails,
        ]);
    }

    /**
     * Get the session info for the user
     */
    public function getSessionInfo(Request $request)
    {
        // Get the user
        $user = Auth::user();

        // Find the most recent active conversation for the user within 24 hours
        $lastConversation = ChatbotConversation::where('user_id', $user->id)
            ->where('last_activity', '>', now()->subHours(24))
            ->orderBy('last_activity', 'desc')
            ->first();

        // If a recent conversation exists, use it, otherwise create a new one
        if ($lastConversation) {
            $conversationId = $lastConversation->conversation_id;
        } else {
            // Create a new conversation ID if no recent conversation exists
            $conversationId = 'conv_' . uniqid() . '_' . time();
        }

        // Return the conversation ID and user ID
        return response()->json([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Get the conversation history for the user
     */
    public function getConversationHistory(Request $request)
    {
        // Get the user
        $user = Auth::user();
        $conversationId = $request->input('conversation_id');

        // Clean up old conversations (older than 24 hours) before loading
        $this->cleanupOldConversations($user->id);

        // Get the conversation history
        $messages = ChatbotConversation::where('user_id', $user->id)
            ->where('conversation_id', $conversationId)
            ->where('last_activity', '>', now()->subHours(24)) // Only load recent messages
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content', 'created_at']);

        // Return the conversation history
        return response()->json([
            'conversation' => $messages->map(function ($msg) {
                return [
                    'role' => $msg->role,
                    'content' => $msg->content,
                    'timestamp' => $msg->created_at->format('h:i A'),
                ];
            }),
        ]);
    }

    /**
     * Handle direct commands that should bypass OpenAI
     */
    protected function handleDirectCommands(string $message, $user): ?string
    {
        $message = trim($message);

        // Instantiate the chatbot service
        $chatbotService = new ChatbotService($user);

        // Check for direct commands (handle variations with optional space)
        if (strtolower($message) === '/help' || $message === '/help ') {
            return $chatbotService->executeTool('show_help', []);
        }

        if (strtolower($message) === '/mytask' || $message === '/mytask ') {
            return $chatbotService->executeTool('get_incomplete_tasks', []);
        }

        // '/task' command removed: users can search tasks using the header search field

        if (strtolower($message) === '/client' || $message === '/client ') {
            return $chatbotService->executeTool('get_client_urls', []);
        }

        if (strtolower($message) === '/project' || $message === '/project ') {
            return $chatbotService->executeTool('get_project_urls', []);
        }

        if (strtolower($message) === '/document' || $message === '/document ') {
            return $chatbotService->executeTool('get_document_urls', []);
        }

        if (strtolower($message) === '/important-url' || $message === '/important-url ') {
            return $chatbotService->executeTool('get_important_url_urls', []);
        }

        if (strtolower($message) === '/phone-number' || $message === '/phone-number ') {
            return $chatbotService->executeTool('get_phone_number_urls', []);
        }

        if (strtolower($message) === '/user' || $message === '/user ') {
            return $chatbotService->executeTool('get_user_urls', []);
        }

        if (strtolower($message) === '/resources' || $message === '/resources ') {
            return $chatbotService->executeTool('get_resource_counts', []);
        }

        return null; // No direct command found, continue with normal processing
    }

    /**
     * Clean up conversations older than 24 hours for a specific user
     */
    protected function cleanupOldConversations(int $userId): void
    {
        ChatbotConversation::where('user_id', $userId)
            ->where('last_activity', '<', now()->subHours(24))
            ->delete();
    }

    /**
     * Clear a conversation for the user and flush AI cache and history cache
     */
    public function clearConversation(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user) {
                $conversationId = $request->input('conversation_id');

                if ($conversationId) {
                    // Clear chatbot conversation history
                    ChatbotConversation::where('conversation_id', $conversationId)
                        ->where('user_id', $user->id)
                        ->delete();

                    // Clear OpenAI logs for this conversation
                    OpenaiLog::where('conversation_id', $conversationId)
                        ->where('user_id', $user->id)
                        ->delete();

                    // Clear any cached data for this conversation
                    $this->flushConversationCache($conversationId, $user->id);

                    \Log::info('Chatbot conversation cleared', [
                        'conversation_id' => $conversationId,
                        'user_id' => $user->id,
                    ]);
                }

                // Clear all user's chatbot caches if no specific conversation
                if (!$conversationId) {
                    $this->flushUserCache($user->id);
                }
            }
        } catch (\Exception $e) {
            // Best-effort cleanup; do not fail the request
            \Log::error('ChatbotController@clearConversation cleanup failed', ['error' => $e->getMessage()]);
        }

        // Create a new conversation ID for the client to use going forward
        $newConversationId = 'conv_' . uniqid() . '_' . time();

        // Return the new conversation ID
        return response()->json([
            'message' => 'Conversation cleared and cache flushed successfully.',
            'conversation_id' => $newConversationId,
        ]);
    }

    /**
     * Flush cache for a specific conversation
     */
    protected function flushConversationCache(string $conversationId, int $userId): void
    {
        try {
            // Clear Laravel cache for this conversation
            \Cache::forget("chatbot_conversation_{$conversationId}");
            \Cache::forget("chatbot_messages_{$conversationId}");

            // Clear user-specific conversation cache
            \Cache::forget("user_{$userId}_conversation_{$conversationId}");

            // Clear any cached tool responses for this conversation
            \Cache::forget("tools_{$conversationId}");

            // Clear session-based conversation data
            if (\Session::has("chatbot_{$conversationId}")) {
                \Session::forget("chatbot_{$conversationId}");
            }

            \Log::info('Conversation cache flushed', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to flush conversation cache', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Flush all cache for a user
     */
    protected function flushUserCache(int $userId): void
    {
        try {
            // Get all conversation IDs for this user from the last 24 hours
            $recentConversations = ChatbotConversation::where('user_id', $userId)
                ->where('last_activity', '>', now()->subHours(24))
                ->distinct('conversation_id')
                ->pluck('conversation_id');

            // Clear cache for each recent conversation
            foreach ($recentConversations as $conversationId) {
                $this->flushConversationCache($conversationId, $userId);
            }

            // Clear user-specific caches
            \Cache::forget("user_{$userId}_chatbot");
            \Cache::forget("user_{$userId}_conversations");

            // Clear session data
            \Session::forget("user_{$userId}_chatbot");

            // Clear any cached tool responses for this user
            \Cache::forget("user_tools_{$userId}");

            \Log::info('User cache flushed', ['user_id' => $userId]);
        } catch (\Exception $e) {
            \Log::warning('Failed to flush user cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
