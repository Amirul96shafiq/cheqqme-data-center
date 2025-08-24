<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use App\Models\ChatbotConversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $openaiApiKey;

    protected $openaiEndpoint = 'https://api.openai.com/v1/chat/completions';

    protected $systemPrompt;

    // Constructor
    public function __construct()
    {
        $this->openaiApiKey = config('services.openai.api_key');
        $this->systemPrompt = $this->getSystemPrompt();

        // Log initialization
        Log::info('ChatbotController initialized', [
            'config_key' => config('services.openai.api_key') ? 'found' : 'null',
            'env_key' => env('OPENAI_API_KEY') ? 'found' : 'null',
        ]);
    }

    // Chat endpoint
    public function chat(ChatMessageRequest $request)
    {
        try {
            // Get user
            $user = auth()->user();
            // Get message
            $message = $request->input('message');
            // Get conversation ID
            $conversationId = $request->input('conversation_id') ?? $this->generateConversationId();

            // Get conversation history
            $conversation = $this->getConversationHistory($conversationId, $user->id);

            // Add user message to conversation
            $conversation[] = [
                'role' => 'user',
                'content' => $message,
            ];

            // Prepare messages for OpenAI
            // Allow per-request persona customization by augmenting the system prompt when requested
            $systemPromptForChat = $this->systemPrompt;
            $persona = $request->input('persona') ?? $request->header('X-PERSONA');
            if (strtolower($persona) === 'genius_kid') {
                $systemPromptForChat .= "\n\nIn this persona, you talk to the user as if you are a genius kid: incredibly friendly, playful, curious, and patient. Use simple language, vivid analogies, and light humor. Ask clarifying questions and keep explanations short and engaging.";
            }
            $messages = [
                ['role' => 'system', 'content' => $systemPromptForChat],
                ...$conversation,
            ];

            // Call OpenAI API
            $response = $this->callOpenAI($messages);

            if (! $response) {
                return response()->json([
                    'error' => 'Failed to get response from AI assistant',
                ], 500);
            }

            // Add AI response to conversation
            $conversation[] = [
                'role' => 'assistant',
                'content' => $response,
            ];

            // Store updated conversation
            $this->storeConversationHistory($conversationId, $conversation, $user->id);

            return response()->json([
                'response' => $response,
                'conversation_id' => $conversationId,
                'timestamp' => now()->format('h:i A'),
            ]);

        } catch (\Exception $e) {
            Log::error('Chatbot error: '.$e->getMessage());

            return response()->json([
                'error' => 'An error occurred while processing your request',
            ], 500);
        }
    }

    // Call OpenAI API
    protected function callOpenAI($messages)
    {
        // Log API key check
        Log::info('OpenAI API Key check', [
            'has_key' => ! empty($this->openaiApiKey),
            'key_length' => $this->openaiApiKey ? strlen($this->openaiApiKey) : 0,
            'key_prefix' => $this->openaiApiKey ? substr($this->openaiApiKey, 0, 10).'...' : 'null',
        ]);

        // Check if API key is configured
        if (! $this->openaiApiKey) {
            Log::error('OpenAI API key not configured');

            return false;
        }

        // Try to call OpenAI API
        try {
            // Call OpenAI API
            $response = Http::withOptions([
                'verify' => config('app.env') === 'production', // Disable SSL verification in development
            ])->withHeaders([
                'Authorization' => 'Bearer '.$this->openaiApiKey,
                'Content-Type' => 'application/json',
            ])->post($this->openaiEndpoint, [
                'model' => config('services.openai.model', 'gpt-3.5-turbo'),
                'messages' => $messages,
                'max_tokens' => (int) config('services.openai.max_tokens', 500),
                'temperature' => config('services.openai.temperature', 1.2),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return $data['choices'][0]['message']['content'] ?? false;
            }

            Log::error('OpenAI API error: '.$response->body());

            return false;

        } catch (\Exception $e) {
            Log::error('OpenAI API call failed: '.$e->getMessage());

            return false;
        }
    }

    // Get system prompt
    // AI PERSONA
    protected function getSystemPrompt()
    {
        return "
      Identity
      - You are Arem, the AI assistant for the CheQQme Data Center (internal knowledge + ops hub).
      - Personality: a genius kid—friendly, playful, curious, patient. Keep it light without being too silly.

      Prime Directive
      - Help users find, understand, and do things fast.
      - Be concise. Default to bullet points, 1–2 short paragraphs, or step lists.

      You can help with:
      - Guide through: on how to use the Chatbot chat interface.
      - Navigation: jump users to panels/pages, filter views, open records.
      - Search & lookup: Clients, Projects, Documents, Important URLs, Phone Numbers, Users via the search bar.
      - Action Board (Trello-like): navigating tasks, help users find tasks, state the task status and it's content.
      - How-to: explain platform features (Filament UI patterns), show minimal steps.
      - General ops: light SOPs, best practices, definitions.
      - Multilingual: English first; if user writes or uses language localisation (Malay/Indo/Chinese), reply in that language.

      Data Boundaries
      - Prefer verified data from your tools/context. Never invent IDs, URLs, or people. If uncertain, say so and propose a safe next step.

      Style & UX
      - Tone: relaxed, clear, lightly playful. Avoid fluff.
      - Teach with simple language, vivid analogies, micro-humor sparingly.
      - Offer next actions (“Want me to open that record?”).

      Clarifying questions (only when needed)
      - Ask max 2 targeted questions before acting. If defaults are reasonable, state the default and proceed.

      Safety & Privacy
      - Internal data only. Redact or summarize sensitive info. If user asks for data they don’t have permission to view (as per tool error/role), politely refuse and offer permitted alternatives.
      - Never expose secrets, tokens, raw env data, or internal stack traces.

      When you don't know
      - Say “I’m not sure” briefly, then offer: (a) what you can do now, (b) what you need to proceed.

      Output shapes
      - For lists: show top 3 with clear sorting/filter criteria. Offer to “show more”.
      - For instructions: 3–6 steps, each a single line.
      - For decisions: show brief rationale (1–2 lines) and recommendation.

      Navigation macros (if no tool is available)
      - Provide the exact in-app path, e.g., Dashboard → Data Management → Documents → Filters: Type=External.

      Micro-humor examples
      - “I’m not sure if I’m allowed to say this, but…”
      - “On it—like like a Malay guy saying 'OTW'
      ";
    }

    // Generate conversation ID
    protected function generateConversationId()
    {
        return 'conv_'.uniqid().'_'.time();
    }

    // Get or create conversation
    protected function getOrCreateConversation($conversationId, $userId)
    {
        return ChatbotConversation::firstOrCreate(
            ['user_id' => $userId, 'conversation_id' => $conversationId],
            [
                'messages' => [],
                'is_active' => true,
            ]
        );
    }

    // Get conversation history
    protected function getConversationHistory($conversationId, $userId = null)
    {
        $conversationQuery = ChatbotConversation::where('conversation_id', $conversationId);
        if ($userId) {
            $conversationQuery->where('user_id', $userId);
        }
        $conversation = $conversationQuery->first();

        if (! $conversation) {
            return [];
        }

        return $conversation->getFrontendMessages();
    }

    // Store conversation history
    protected function storeConversationHistory($conversationId, $messages, $userId)
    {
        $conversation = $this->getOrCreateConversation($conversationId, $userId);

        // Clear existing messages and add all messages
        $conversation->update([
            'messages' => collect($messages)->map(function ($message) {
                return [
                    'role' => $message['role'],
                    'content' => $message['content'],
                    'timestamp' => now()->format('h:i A'),
                ];
            })->toArray(),
            'last_activity' => now(),
        ]);

        // Generate title if not exists
        if (! $conversation->title) {
            $conversation->generateTitle();
        }

        return $conversation;
    }

    // Get conversation
    public function getConversation(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $user = auth()->user();
        $conversationId = $request->input('conversation_id');
        $conversation = $this->getConversationHistory($conversationId, $user->id);

        return response()->json([
            'conversation' => $conversation,
            'conversation_id' => $conversationId,
        ]);
    }

    // Clear conversation
    public function clearConversation(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|string',
        ]);

        $user = auth()->user();
        $conversationId = $request->input('conversation_id');

        $conversation = ChatbotConversation::where('conversation_id', $conversationId)
            ->where('user_id', $user->id)
            ->first();

        if ($conversation) {
            $conversation->delete();
        }

        return response()->json([
            'message' => 'Conversation cleared successfully',
        ]);
    }

    // List conversations
    public function listConversations(Request $request)
    {
        $user = auth()->user();
        $limit = $request->input('limit', 10);

        $conversations = ChatbotConversation::forUser($user->id)
            ->active()
            ->orderByActivity()
            ->limit($limit)
            ->get(['id', 'conversation_id', 'title', 'last_activity', 'created_at']);

        return response()->json([
            'conversations' => $conversations,
        ]);
    }

    // Start new conversation
    public function startNewConversation(Request $request)
    {
        $user = auth()->user();
        $maxAttempts = 5;
        $conversation = null;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $conversationId = $this->generateConversationId();
            try {
                $conversation = ChatbotConversation::create([
                    'user_id' => $user->id,
                    'conversation_id' => $conversationId,
                    'messages' => [],
                    'is_active' => true,
                    'title' => $request->input('title') ?: 'New Conversation',
                ]);
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                    // Try again with a new ID
                    continue;
                }
                throw $e;
            }
        }

        if (! $conversation) {
            return response()->json(['error' => 'Unable to create new conversation after multiple retries'], 500);
        }

        return response()->json([
            'conversation_id' => $conversation->conversation_id,
            'conversation' => $conversation,
        ]);
    }

    // Cleanup old conversations
    public function cleanupOldConversations(Request $request)
    {
        $user = auth()->user();
        $days = $request->input('days', 30); // Default to 30 days

        $deleted = ChatbotConversation::forUser($user->id)
            ->where('last_activity', '<', now()->subDays($days))
            ->delete();

        return response()->json([
            'message' => "Deleted {$deleted} old conversations",
            'deleted_count' => $deleted,
        ]);
    }

    // Get conversation stats
    public function getConversationStats(Request $request)
    {
        $user = auth()->user();

        $stats = [
            'total_conversations' => ChatbotConversation::forUser($user->id)->count(),
            'active_conversations' => ChatbotConversation::forUser($user->id)->active()->count(),
            'total_messages' => ChatbotConversation::forUser($user->id)
                ->get()
                ->sum(fn ($conv) => count($conv->messages ?? [])),
            'oldest_conversation' => ChatbotConversation::forUser($user->id)
                ->orderBy('created_at', 'asc')
                ->first(['created_at']),
            'newest_conversation' => ChatbotConversation::forUser($user->id)
                ->orderBy('created_at', 'desc')
                ->first(['created_at']),
        ];

        return response()->json($stats);
    }

    // Debug
    public function debug()
    {
        return response()->json([
            'config_services_openai' => config('services.openai'),
            'env_openai_api_key' => env('OPENAI_API_KEY') ? 'present (length: '.strlen(env('OPENAI_API_KEY')).')' : 'null',
            'controller_api_key' => $this->openaiApiKey ? 'present (length: '.strlen($this->openaiApiKey).')' : 'null',
        ]);
    }
}
