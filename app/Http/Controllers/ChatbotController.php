<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;

class ChatbotController extends Controller
{
    protected $openaiApiKey;
    protected $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';
    protected $systemPrompt;

    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->systemPrompt = $this->getSystemPrompt();
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'conversation_id' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $message = $request->input('message');
            $conversationId = $request->input('conversation_id') ?? $this->generateConversationId();

            // Get conversation history
            $conversation = $this->getConversationHistory($conversationId);
            
            // Add user message to conversation
            $conversation[] = [
                'role' => 'user',
                'content' => $message
            ];

            // Prepare messages for OpenAI
            $messages = [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ...$conversation
            ];

            // Call OpenAI API
            $response = $this->callOpenAI($messages);
            
            if (!$response) {
                return response()->json([
                    'error' => 'Failed to get response from AI assistant'
                ], 500);
            }

            // Add AI response to conversation
            $conversation[] = [
                'role' => 'assistant',
                'content' => $response
            ];

            // Store updated conversation
            $this->storeConversationHistory($conversationId, $conversation);

            return response()->json([
                'response' => $response,
                'conversation_id' => $conversationId,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'An error occurred while processing your request'
            ], 500);
        }
    }

    protected function callOpenAI($messages)
    {
        if (!$this->openaiApiKey) {
            Log::error('OpenAI API key not configured');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->openaiEndpoint, [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => $messages,
                'max_tokens' => config('services.openai.max_tokens', 500),
                'temperature' => config('services.openai.temperature', 0.7),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? false;
            }

            Log::error('OpenAI API error: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('OpenAI API call failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function getSystemPrompt()
    {
        return "You are CheQQme, a helpful AI assistant for the CheQQme Data Center - an internal knowledge and operations hub. 

Your role is to help users:
- Navigate the web application
- Find resources, documents, and information
- Answer questions about projects, clients, and tasks
- Provide guidance on using the platform features
- Assist with general knowledge and operations questions

Key areas you can help with:
- Action Board (Kanban-style task management)
- Client and project information
- Document storage and retrieval
- Important URLs and resources
- User management and permissions
- Platform navigation and features

Be friendly, professional, and concise. If you don't know something specific about the platform, suggest where the user might find the information or ask for clarification. Always maintain a helpful and supportive tone.";
    }

    protected function generateConversationId()
    {
        return 'conv_' . uniqid() . '_' . time();
    }

    protected function getConversationHistory($conversationId)
    {
        $cacheKey = "chatbot_conversation_{$conversationId}";
        return Cache::get($cacheKey, []);
    }

    protected function storeConversationHistory($conversationId, $conversation)
    {
        $cacheKey = "chatbot_conversation_{$conversationId}";
        // Store conversation for 24 hours
        Cache::put($cacheKey, $conversation, now()->addHours(24));
    }

    public function getConversationHistory(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string'
        ]);

        $conversationId = $request->input('conversation_id');
        $conversation = $this->getConversationHistory($conversationId);

        return response()->json([
            'conversation' => $conversation,
            'conversation_id' => $conversationId
        ]);
    }

    public function clearConversation(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string'
        ]);

        $conversationId = $request->input('conversation_id');
        $cacheKey = "chatbot_conversation_{$conversationId}";
        
        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Conversation cleared successfully'
        ]);
    }
}