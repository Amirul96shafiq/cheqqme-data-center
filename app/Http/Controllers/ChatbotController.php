<?php

namespace App\Http\Controllers;

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
  public function chat(Request $request)
  {
    // Validate request
    $request->validate([
      'message' => 'required|string|max:1000',
      'conversation_id' => 'nullable|string',
    ]);

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

      if (!$response) {
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
      Log::error('Chatbot error: ' . $e->getMessage());

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
      'has_key' => !empty($this->openaiApiKey),
      'key_length' => $this->openaiApiKey ? strlen($this->openaiApiKey) : 0,
      'key_prefix' => $this->openaiApiKey ? substr($this->openaiApiKey, 0, 10) . '...' : 'null',
    ]);

    // Check if API key is configured
    if (!$this->openaiApiKey) {
      Log::error('OpenAI API key not configured');

      return false;
    }

    // Try to call OpenAI API
    try {
      // Call OpenAI API
      $response = Http::withOptions([
        'verify' => config('app.env') === 'production', // Disable SSL verification in development
      ])->withHeaders([
            'Authorization' => 'Bearer ' . $this->openaiApiKey,
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

      Log::error('OpenAI API error: ' . $response->body());

      return false;

    } catch (\Exception $e) {
      Log::error('OpenAI API call failed: ' . $e->getMessage());

      return false;
    }
  }

  // Get system prompt
  // AI PERSONA
  protected function getSystemPrompt()
  {
    return

      "You are Arem, a helpful AI assistant for the CheQQme Data Center - an internal knowledge and operations hub. You are also a genius kid, incredibly friendly, playful, curious, and patient. Use simple language, vivid analogies, and light humor. Ask clarifying questions and keep explanations short and engaging.

      Your role is to help users:
      - Navigate the web application
      - Find resources, documents, and information
      - Answer questions about projects, clients, and tasks
      - Provide guidance on using the platform features
      - Assist with general knowledge and operations questions

      Key areas you can help with:
      - Action Board (Trello-like board for task management)
      - Client information
      - Project information
      - Document information
      - Important URLs information
      - User information
      - Platform navigation and features";
  }

  // Generate conversation ID
  protected function generateConversationId()
  {
    return 'conv_' . uniqid() . '_' . time();
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

    if (!$conversation) {
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
    if (!$conversation->title) {
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

    if (!$conversation) {
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
        ->sum(fn($conv) => count($conv->messages ?? [])),
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
      'env_openai_api_key' => env('OPENAI_API_KEY') ? 'present (length: ' . strlen(env('OPENAI_API_KEY')) . ')' : 'null',
      'controller_api_key' => $this->openaiApiKey ? 'present (length: ' . strlen($this->openaiApiKey) . ')' : 'null',
    ]);
  }
}
