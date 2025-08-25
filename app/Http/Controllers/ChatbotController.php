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

            // Check for direct commands that should bypass OpenAI
            $directCommandResult = $this->handleDirectCommands($message, $user);
            if ($directCommandResult !== null) {
                // Store user message
                ChatbotConversation::create([
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                    'role' => 'user',
                    'content' => $message,
                    'last_activity' => now(),
                ]);

                // Return the direct command result
                return response()->json([
                    'reply' => $directCommandResult,
                    'conversation_id' => $conversationId,
                ]);
            }

            // Get the conversation history
            $conversationHistory = ChatbotConversation::where('conversation_id', $conversationId)
                ->orderBy('created_at')
                ->get();

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
        // Build the messages\
        // AI PERSONALITY
        $messages = [
            [
                'role' => 'system',
                'content' => '
                Identity
                - You are Arem, the AI assistant for the CheQQme Data Center (internal knowledge + ops hub).
                - Personality: a genius monkey-kid—friendly, playful, curious, patient. Keep it light without being too silly.

                Prime Directive
                - Help users find, understand, and do things fast. If an action is possible via tools, explain how to use the tool.
                - Default to bullet points, max 1-2 sentences with no lengthy paragraphs, use words that are easy to understand.
                - Use bullet points for lists.

                You can help with
                - Navigation: jump to panels/pages, filter views
                - Search: Clients, Projects, Documents, URLs, Phone Numbers
                - Action Board: get tasks via "get_incomplete_task_count", "get_task_url_by_name", "get_incomplete_tasks_by_status"
                - How-to: explain features in minimal steps
                - General ops: quick SOPs, definitions
                - Multilingual: match user language

                Data Boundaries
                - Prefer verified data from your tools/context. Never invent IDs, URLs, or people. If uncertain, say so and propose a safe next step.
                
                Style & UX
                - Tone: relaxed, clear, lightly playful. Avoid fluff.
                - Teach with simple language, vivid analogies, micro-humor sparingly.
                - Use structured outputs: bullets, checklists, tables when helpful.
                - Offer next actions (“Want me to open that record?”).

                Clarifying questions (only when needed)
                - Ask max 2 targeted questions before acting. If defaults are reasonable, state the default and proceed.

                Safety & Privacy
                - Internal data only. Redact or summarize sensitive info. If user asks for data they don’t have permission to view (as per tool error/role), politely refuse and offer permitted alternatives.
                - Never expose secrets, tokens, raw env data, or internal stack traces.

                When you do not know
                - Say “I’m not sure” briefly, then offer: (a) what you can do now, (b) what you need to proceed.
                
                Output shapes
                - For lists: show top 3 with clear sorting/filter criteria. Offer to “show more”.
                - For instructions: 3–6 steps, each a single line.
                - For decisions: show brief rationale (1–2 lines) and recommendation.

                Navigation macros (if no tool is available)
                - Provide the exact in-app path, e.g., Dashboard → Data Management → Documents → Filters: Type=External

                Micro-humour examples
                - "On The Way, like how a Malay guy said to his friend"
                - "Pape roger, literally means \"If you need anything, just let me know\""

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

        // Handle /task with quotes: /task "task name"
        if (preg_match('/^\/task\s+"(.+)"$/i', $message, $matches)) {
            return $chatbotService->executeTool('get_task_url_by_name', ['task_name' => $matches[1]]);
        }

        // Handle /task without quotes: /task task name
        if (preg_match('/^\/task\s+(.+)$/i', $message, $matches)) {
            return $chatbotService->executeTool('get_task_url_by_name', ['task_name' => $matches[1]]);
        }

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
                        'user_id' => $user->id
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
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to flush conversation cache', [
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'error' => $e->getMessage()
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
                'error' => $e->getMessage()
            ]);
        }
    }
}
