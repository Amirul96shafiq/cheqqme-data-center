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
    public function chat(Request $request)
    {
        try {
            $user = Auth::user();
            if (! $user) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }

            $message = $request->input('message');
            $conversationId = $request->input('conversation_id') ?? uniqid('conv_');

            $conversationHistory = ChatbotConversation::where('conversation_id', $conversationId)
                ->orderBy('created_at')
                ->get();

            $messages = $this->buildMessages($conversationHistory, $message);

            // Instantiate the service with the current user
            $chatbotService = new ChatbotService($user);
            $tools = $chatbotService->getAllToolDefinitions();

            // Initial request to OpenAI
            $response = $this->sendToOpenAI($messages, $tools);

            $responseChoice = $response['choices'][0]['message'];

            // Check if the model wants to call a tool
            if (isset($responseChoice['tool_calls'])) {
                $toolCall = $responseChoice['tool_calls'][0];
                $toolName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true);

                // Execute the tool
                $toolResult = $chatbotService->executeTool($toolName, $arguments);

                // Add the tool call and result to the message history
                // IMPORTANT: We must add the 'role' to the tool call message BEFORE appending it
                $toolCallMessage = $responseChoice;
                $toolCallMessage['role'] = 'assistant';

                $messages[] = $toolCallMessage;
                $messages[] = [
                    'tool_call_id' => $toolCall['id'],
                    'role' => 'tool',
                    'name' => $toolName,
                    'content' => $toolResult,
                ];

                // Send the tool result back to OpenAI to get the final natural language response
                $finalResponse = $this->sendToOpenAI($messages);
                $botReply = $finalResponse['choices'][0]['message']['content'];
            } else {
                // No tool call, just a regular response
                $botReply = $response['choices'][0]['message']['content'];
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

            return response()->json([
                'reply' => $botReply,
                'conversation_id' => $conversationId,
            ]);
        } catch (\Exception $e) {
            Log::error('ChatbotController@chat: An error occurred', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    protected function buildMessages($conversationHistory, string $newMessage): array
    {
        $messages = [
            ['role' => 'system', 'content' => 'You are a helpful assistant integrated into a project management tool. Your name is Cheqqbot. Be concise. If you are asked to do something that is not in your list of functions, respectfully decline.'],
        ];

        foreach ($conversationHistory as $entry) {
            $messages[] = ['role' => $entry->role, 'content' => $entry->content];
        }

        $messages[] = ['role' => 'user', 'content' => $newMessage];

        return $messages;
    }

    protected function sendToOpenAI(array $messages, ?array $tools = null): array
    {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        $apiKey = env('OPENAI_API_KEY');

        $payload = [
            'model' => 'gpt-4-turbo-preview',
            'messages' => $messages,
        ];

        if (! empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $startTime = microtime(true);

        $response = Http::withToken($apiKey)
            ->withoutVerifying()
            ->timeout(120)
            ->post($endpoint, $payload);

        $duration = (microtime(true) - $startTime) * 1000;

        OpenaiLog::create([
            'user_id' => Auth::id(),
            'conversation_id' => null, // Simplified for this example
            'model' => $payload['model'],
            'endpoint' => $endpoint,
            'request_payload' => json_encode($payload),
            'response_text' => $response->body(),
            'status_code' => $response->status(),
            'duration_ms' => $duration,
        ]);

        if ($response->failed()) {
            Log::error('OpenAI API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to communicate with OpenAI.');
        }

        return $response->json();
    }

    public function listConversations(Request $request)
    {
        $user = Auth::user();
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

        return response()->json([
            'conversations' => $conversationDetails,
        ]);
    }

    public function getSessionInfo(Request $request)
    {
        $user = Auth::user();

        // Find the most recent active conversation for the user within 24 hours
        $lastConversation = ChatbotConversation::where('user_id', $user->id)
            ->where('last_activity', '>', now()->subHours(24))
            ->orderBy('last_activity', 'desc')
            ->first();

        if ($lastConversation) {
            $conversationId = $lastConversation->conversation_id;
        } else {
            // Create a new conversation ID if no recent conversation exists
            $conversationId = 'conv_'.uniqid().'_'.time();
        }

        return response()->json([
            'conversation_id' => $conversationId,
            'user_id' => $user->id,
        ]);
    }

    public function getConversationHistory(Request $request)
    {
        $user = Auth::user();
        $conversationId = $request->input('conversation_id');

        // Clean up old conversations (older than 24 hours) before loading
        $this->cleanupOldConversations($user->id);

        $messages = ChatbotConversation::where('user_id', $user->id)
            ->where('conversation_id', $conversationId)
            ->where('last_activity', '>', now()->subHours(24)) // Only load recent messages
            ->orderBy('created_at', 'asc')
            ->get(['role', 'content', 'created_at']);

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
     * Clean up conversations older than 24 hours for a specific user
     */
    protected function cleanupOldConversations(int $userId): void
    {
        ChatbotConversation::where('user_id', $userId)
            ->where('last_activity', '<', now()->subHours(24))
            ->delete();
    }

    public function clearConversation(Request $request)
    {
        // This will now just generate a new conversation ID for the client to use.
        // The old messages remain in the database but will be associated with old IDs.
        $newConversationId = 'conv_'.uniqid();

        return response()->json([
            'message' => 'New conversation started.',
            'conversation_id' => $newConversationId,
        ]);
    }
}
