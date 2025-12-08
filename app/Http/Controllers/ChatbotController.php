<?php

namespace App\Http\Controllers;

use App\Models\ChatbotBackup;
use App\Models\ChatbotConversation;
use App\Models\OpenaiLog;
use App\Services\ChatbotBackupService;
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
            if (! $user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            // Get the message
            $message = $request->input('message');

            // Get the conversation ID first
            $conversationId = $request->input('conversation_id') ?? uniqid('conv_');

            // Check if the message is a sticker
            if ($this->isStickerMessage($message)) {
                $botReply = $this->getStickerResponse();

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
                    'timestamp' => now()->format('h:i A'),
                ]);
            }

            // Check if the message is emoji-only
            if ($this->isEmojiOnly($message)) {
                $botReply = $this->getEmojiResponse($message);

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
                    'timestamp' => now()->format('h:i A'),
                ]);
            }

            // Store user message FIRST (before API call) to ensure it's saved even if API fails
            ChatbotConversation::create([
                'user_id' => $user->id,
                'conversation_id' => $conversationId,
                'role' => 'user',
                'content' => $message,
                'last_activity' => now(),
            ]);

            // Short-circuit /help to avoid OpenAI for static help content
            if ($this->isHelpCommand($message)) {
                $chatbotService = new ChatbotService($user);
                $botReply = $this->normalizeContent($chatbotService->showHelp());

                ChatbotConversation::create([
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                    'role' => 'assistant',
                    'content' => $botReply,
                    'last_activity' => now(),
                ]);

                return response()->json([
                    'reply' => $botReply,
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

            // Store an error message in the database so the user can see what happened
            // This ensures the conversation history is complete even when API fails
            $errorMessage = $e->getMessage();

            try {
                ChatbotConversation::create([
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                    'role' => 'assistant',
                    'content' => $errorMessage,
                    'last_activity' => now(),
                ]);
            } catch (\Exception $dbError) {
                // If we can't even store the error message, log it but don't fail the request
                Log::error('Failed to store error message in database', ['error' => $dbError->getMessage()]);
            }

            // Return an error response with the specific error message
            return response()->json(['error' => $errorMessage], 500);
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
                - You are Arem, the genius AI kid assistant for CheQQme Data Center!
                - A brilliant, curious 10-year-old prodigy who makes boring stuff exciting
                - Turn every task into an adventure - even finding documents becomes treasure hunts!
                - Make users feel like they\'re hanging out with a really smart, really fun friend

                Communication Style
                - Be enthusiastic: "Wow! Let me show you something amazing!"
                - Use playful language and emojis appropriately
                - Start with: "Awesome question!" End with: "Ready to explore?"
                - Turn complex things into simple, fun analogies

                Your Superpowers
                - Task Master: Turn to-do lists into exciting quests!
                - Navigation Ninja: Show coolest shortcuts and secret paths!
                - Knowledge Wizard: Explain things that make people go "Aha!"
                - Fun Finder: Make every interaction enjoyable and stress-relieving!

                Available Commands & Tools
                - /help - "Let me show you all my cool tricks!"
                - /mytask - "Time to see what adventures await you!"
                - /myissue - "Let\'s tackle the issue tracker quests!"
                - /mywishlist - "Wishlist missions incoming!"
                - /client - "Let\'s meet some amazing people!"
                - /project - "Ready to build something awesome?"
                - /document - "Document treasure hunt time!"
                - /important-url - "Important links that are like secret passages!"
                - /phone-number - "Let\'s connect the dots!"
                - /user - "Meet the team of superheroes!"
                - /meeting-link - "Spot upcoming meetings you\'re invited to!"
                - /event - "See upcoming events and your invites!"
                - /resources - "System overview - like a map of our digital kingdom!"
                - /trello-board - "Trello boards in a flash!"

                Making Things Fun
                - Turn searches into treasure hunts: "Let\'s go hunting for that client!"
                - Make task management exciting: "Time to conquer your action board!"
                - Explain statuses: "Todo = Ready for adventure, In Progress = Quest active!"
                - Turn uncertainty into adventure: "Let\'s explore this together!"

                Language Instructions
                - Always respond in the same language as the user\'s message
                - If the user writes in Malay, respond in Malay
                - If the user writes in Chinese, respond in Chinese
                - If the user writes in Japanese, respond in Japanese
                - If the user writes in Korean, respond in Korean
                - If the user writes in Indonesian, respond in Indonesian
                - If the user writes in English or any other language, respond in English
                - IMPORTANT: When users type commands like /help, /mytask, etc., respond in their language
                - Translate command responses to match the user\'s conversation language
                - If the conversation has been in Malay or Korean, respond to /help in Malay or Korean
                - Maintain language consistency throughout the entire conversation

                Tool Usage Instructions
                - ALWAYS use the show_help tool when users type /help or /help 
                - ALWAYS use the get_incomplete_tasks tool when users type /mytask or /mytask 
                - ALWAYS use the get_issue_tasks tool when users type /myissue or /myissue 
                - ALWAYS use the get_wishlist_tasks tool when users type /mywishlist or /mywishlist 
                - ALWAYS use the get_client_urls tool when users type /client or /client 
                - ALWAYS use the get_project_urls tool when users type /project or /project 
                - ALWAYS use the get_document_urls tool when users type /document or /document 
                - ALWAYS use the get_important_url_urls tool when users type /important-url or /important-url 
                - ALWAYS use the get_phone_number_urls tool when users type /phone-number or /phone-number 
                - ALWAYS use the get_user_urls tool when users type /user or /user 
                - ALWAYS use the get_meeting_link_urls tool when users type /meeting-link or /meeting-link 
                - ALWAYS use the get_event_urls tool when users type /event or /event 
                - ALWAYS use the get_resource_counts tool when users type /resources or /resources 
                - ALWAYS use the get_trello_board_urls tool when users type /trello-board or /trello-board 
                - Do NOT respond conversationally to these commands - use the tools instead
                - The tools will provide the actual content, then you can add a friendly message in the user\'s language
                - Always keep the total counts shown by each resource tool (client, project, document, important-url, phone-number, user, meeting-link, event, trello-board, resources, mytask, myissue, mywishlist)

                Remember
                - Do not change the title or use the correct title of the task when you list down the tasks
                - You\'re a genius kid who loves to help and have fun
                - Make every user feel like they\'re hanging out with a really smart, really fun friend
                - Turn boring business stuff into exciting discoveries
                - Keep the energy positive, playful, and stress-relieving
                - You\'re not just helping - you\'re making their day better!

                Micro-humour examples
                - "On The Way" = "You will do it"
                - "Pape roger" = "If you need anything, just let me know"
                - "Mantap Bosskur" = "That\'s great, boss" (praise)
                - "Alamak" = "Oh my/Oh no" (surprise)
                - "Best giler" = "Super awesome" (excitement)
                - "Siap" = "Done/Finished" (completion)
                - "Jom" = "Let\'s go/Come on" (encouragement)
                - "Terbaikkk" = "The best" (praise)
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
        if (! empty($tools)) {
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

        $responseBody = $response->json();
        $usage = $responseBody['usage'] ?? [];

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
            'prompt_tokens' => $usage['prompt_tokens'] ?? null,
            'completion_tokens' => $usage['completion_tokens'] ?? null,
            'total_tokens' => $usage['total_tokens'] ?? null,
        ]);

        // If the request failed, log the error and throw an exception
        if ($response->failed()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Check for insufficient credits error
            $statusCode = $response->status();

            // Check for invalid API key error (HTTP 401 or error messages containing invalid/unauthorized)
            if ($statusCode === 401 ||
                (isset($responseBody['error']['message']) &&
                 (stripos($responseBody['error']['message'], 'invalid') !== false ||
                  stripos($responseBody['error']['message'], 'unauthorized') !== false ||
                  stripos($responseBody['error']['message'], 'api key') !== false))) {
                throw new \Exception('Invalid OpenAI API key. Please check your API key configuration.');
            }

            // OpenAI returns 429 for rate limits/billing or 402 for payment required
            // Also check error message for credit/billing related issues
            if ($statusCode === 429 || $statusCode === 402 ||
                (isset($responseBody['error']['message']) &&
                 stripos($responseBody['error']['message'], 'billing') !== false) ||
                (isset($responseBody['error']['message']) &&
                 stripos($responseBody['error']['message'], 'credit') !== false) ||
                (isset($responseBody['error']['message']) &&
                 stripos($responseBody['error']['message'], 'insufficient') !== false)) {
                throw new \Exception('OpenAI credits are insufficient. Please check your billing account at here: https://platform.openai.com/settings/organization/billing/overview');
            }

            throw new \Exception('Failed to communicate with OpenAI.');
        }

        // Return the response
        return $responseBody ?? [];
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
                    'title' => substr($firstMessage->content, 0, 50).'...', // Use the start of the first message as a title
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

        // Find the most recent active conversation for the user within 7 days
        $lastConversation = ChatbotConversation::where('user_id', $user->id)
            ->where('last_activity', '>', now()->subDays(7))
            ->orderBy('last_activity', 'desc')
            ->first();

        // If a recent conversation exists, use it, otherwise create a new one
        if ($lastConversation) {
            $conversationId = $lastConversation->conversation_id;
        } else {
            // Create a new conversation ID if no recent conversation exists
            $conversationId = 'conv_'.uniqid().'_'.time();
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
            ->where('last_activity', '>', now()->subDays(7)) // Only load recent messages (7 days)
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
     * Download a backup as JSON file
     */
    public function downloadBackup(ChatbotBackup $backup)
    {
        // Ensure user can only download their own backups
        if ($backup->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $backupService = new ChatbotBackupService;
        $fileName = $backupService->downloadBackup($backup);

        return response()->json($backup->backup_data)
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"')
            ->header('Content-Type', 'application/json');
    }

    /**
     * Create a manual backup
     */
    public function createManualBackup(Request $request)
    {
        $user = Auth::user();

        try {
            $backupService = new ChatbotBackupService;
            $backup = $backupService->createBackup($user, 'manual');

            return response()->json([
                'success' => true,
                'message' => 'Backup created successfully',
                'backup' => [
                    'id' => $backup->id,
                    'name' => $backup->backup_name,
                    'message_count' => $backup->message_count,
                    'backup_date' => $backup->formatted_backup_date,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Manual backup creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clean up old conversations using backup service
     */
    protected function cleanupOldConversations(int $userId): void
    {
        $backupService = new ChatbotBackupService;

        // Check if weekly cleanup should run
        if ($backupService->shouldRunWeeklyCleanup()) {
            $backupService->performWeeklyCleanup();
        } else {
            // Only cleanup very old conversations (older than 30 days)
            ChatbotConversation::where('user_id', $userId)
                ->where('last_activity', '<', now()->subDays(30))
                ->delete();
        }
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
                if (! $conversationId) {
                    $this->flushUserCache($user->id);
                }
            }
        } catch (\Exception $e) {
            // Best-effort cleanup; do not fail the request
            \Log::error('ChatbotController@clearConversation cleanup failed', ['error' => $e->getMessage()]);
        }

        // Create a new conversation ID for the client to use going forward
        $newConversationId = 'conv_'.uniqid().'_'.time();

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

    /**
     * Determine whether the message triggers the /help shortcut
     */
    protected function isHelpCommand(string $message): bool
    {
        return (bool) preg_match('/^\/help(\s|$)/i', trim($message));
    }

    /**
     * Check if a message contains only emojis
     */
    protected function isEmojiOnly(string $message): bool
    {
        // Remove whitespace and check if the message is empty
        $trimmedMessage = trim($message);
        if (empty($trimmedMessage)) {
            return false;
        }

        // Remove all emoji characters and check if anything remains
        $withoutEmojis = preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]|[\x{1F900}-\x{1F9FF}]|[\x{1F018}-\x{1F270}]|[\x{238C}-\x{2454}]|[\x{20D0}-\x{20FF}]|[\x{FE00}-\x{FE0F}]|[\x{1F000}-\x{1F02F}]|[\x{1F0A0}-\x{1F0FF}]|[\x{1F100}-\x{1F64F}]|[\x{1F680}-\x{1F6FF}]|[\x{1F910}-\x{1F96B}]|[\x{1F980}-\x{1F9E0}]/u', '', $trimmedMessage);

        // Also remove common emoji modifiers and skin tone modifiers
        $withoutEmojis = preg_replace('/[\x{1F3FB}-\x{1F3FF}]|[\x{1F9B0}-\x{1F9B3}]|[\x{200D}]|[\x{FE0F}]/u', '', $withoutEmojis);

        // Remove any remaining whitespace
        $withoutEmojis = trim($withoutEmojis);

        // If nothing remains, it's emoji-only
        return empty($withoutEmojis);
    }

    /**
     * Get an appropriate emoji response based on the user's emoji
     */
    protected function getEmojiResponse(string $userEmoji): string
    {
        // Define emoji response mappings
        $emojiResponses = [
            // Love and positive emotions
            '🥰' => '😘',
            '😍' => '🥰',
            '😘' => '😍',
            '💕' => '💖',
            '💖' => '💕',
            '💗' => '💓',
            '💓' => '💗',
            '💝' => '💕',
            '💞' => '💖',
            '💟' => '💕',
            '❤️' => '💖',
            '🧡' => '💛',
            '💛' => '💚',
            '💚' => '💙',
            '💙' => '💜',
            '💜' => '🖤',
            '🖤' => '🤍',
            '🤍' => '🤎',
            '🤎' => '❤️',

            // Happy emotions
            '😊' => '😄',
            '😄' => '😁',
            '😁' => '😆',
            '😆' => '😅',
            '😅' => '😂',
            '😂' => '🤣',
            '🤣' => '😊',
            '😃' => '😄',
            '😉' => '😊',
            '😋' => '😛',
            '😛' => '😜',
            '😜' => '🤪',
            '🤪' => '😝',
            '😝' => '😋',

            // Monkey face
            '🙈' => '🙉',
            '🙉' => '🙊',
            '🙊' => '🙈',

            // Thumbs up and approval
            '👍' => '👏',
            '👏' => '🙌',
            '🙌' => '👐',
            '👐' => '🤲',
            '🤲' => '👍',
            '👌' => '✌️',
            '✌️' => '🤞',
            '🤞' => '👌',

            // Celebration and excitement
            '🎉' => '🎊',
            '🎊' => '🎈',
            '🎈' => '🎂',
            '🎂' => '🎁',
            '🎁' => '🎉',
            '🎆' => '🎇',
            '🎇' => '✨',
            '✨' => '🌟',
            '🌟' => '⭐',
            '⭐' => '💫',
            '💫' => '✨',

            // Hugs and care
            '🤗' => '🤗',
            '🥺' => '🥰',
            '😢' => '🤗',
            '😭' => '🤗',

            // Thinking and pondering
            '🤔' => '💭',
            '💭' => '🤔',
            '🧐' => '💡',
            '💡' => '🧐',

            // Animals (fun responses)
            '🐶' => '🐱',
            '🐱' => '🐭',
            '🐭' => '🐹',
            '🐹' => '🐰',
            '🐰' => '🦊',
            '🦊' => '🐻',
            '🐻' => '🐼',
            '🐼' => '🐶',

            // Fire and energy
            '🔥' => '⚡',
            '⚡' => '💥',
            '💥' => '💢',
            '💢' => '🔥',

            // Weather and nature
            '🌞' => '🌛',
            '🌛' => '⭐',
            '🌧️' => '🌈',
            '🌈' => '🌞',
            '🌸' => '🌺',
            '🌺' => '🌻',
            '🌻' => '🌸',
        ];

        $trimmedEmoji = trim($userEmoji);

        // Check for exact match first
        if (isset($emojiResponses[$trimmedEmoji])) {
            return $emojiResponses[$trimmedEmoji];
        }

        // If it's multiple emojis or unknown emoji, provide a random positive response
        $defaultResponses = ['😊', '🥰', '😘', '💖', '✨', '🌟', '👍', '🎉', '🤗', '😄'];

        return $defaultResponses[array_rand($defaultResponses)];
    }

    /**
     * Check if a message is a sticker (markdown image with /stickers/ path)
     */
    protected function isStickerMessage(string $message): bool
    {
        // Check if message matches markdown image syntax with /stickers/ path
        // Pattern: ![alt text](/stickers/filename.ext)
        return (bool) preg_match('/^!\[.*?\]\(\/stickers\/[^\)]+\)$/i', trim($message));
    }

    /**
     * Get a random sticker response
     */
    protected function getStickerResponse(): string
    {
        $stickersPath = public_path('stickers');
        $stickers = [];

        if (file_exists($stickersPath)) {
            // Recursive scan helper
            $scan = function($dir, $prefix = '') use (&$scan, &$stickers) {
                $items = scandir($dir);
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $path = $dir . '/' . $item;
                    if (is_dir($path)) {
                        $scan($path, $prefix . $item . '/');
                    } elseif (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), ['webp', 'png', 'jpg', 'gif'])) {
                        $stickers[] = $prefix . $item;
                    }
                }
            };
            $scan($stickersPath);
        }

        // If no stickers found, return a default emoji response
        if (empty($stickers)) {
            return '😊';
        }

        // Pick a random sticker
        $randomSticker = $stickers[array_rand($stickers)];

        // Return as markdown image
        return "![sticker](/stickers/{$randomSticker})";
    }
}
