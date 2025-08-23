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

  public function __construct()
  {
    $this->openaiApiKey = config('services.openai.api_key');
    $this->systemPrompt = $this->getSystemPrompt();

    Log::info('ChatbotController initialized', [
      'config_key' => config('services.openai.api_key') ? 'found' : 'null',
      'env_key' => env('OPENAI_API_KEY') ? 'found' : 'null',
    ]);
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
        $systemPromptForChat .= "\n\nIn this persona, you talk to the user as if they are a genius kid: incredibly friendly, playful, curious, and patient. Use simple language, vivid analogies, and light humor. Ask clarifying questions and keep explanations short and engaging.";
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

  protected function callOpenAI($messages)
  {
    Log::info('OpenAI API Key check', [
      'has_key' => !empty($this->openaiApiKey),
      'key_length' => $this->openaiApiKey ? strlen($this->openaiApiKey) : 0,
      'key_prefix' => $this->openaiApiKey ? substr($this->openaiApiKey, 0, 10) . '...' : 'null',
    ]);

    if (!$this->openaiApiKey) {
      Log::error('OpenAI API key not configured');

      return false;
    }

    try {
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

  protected function getSystemPrompt()
  {
    return

      "You are Arem, a helpful AI assistant for the CheQQme Data Center - an internal knowledge and operations hub. 

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
      - Platform navigation and features

      You are also a genius kid, incredibly friendly, playful, curious, and patient. Use simple language, vivid analogies, and light humor. Ask clarifying questions and keep explanations short and engaging.";
  }

  protected function generateConversationId()
  {
    return 'conv_' . uniqid() . '_' . time();
  }

  protected function getOrCreateConversation($conversationId, $userId)
  {
    return ChatbotConversation::firstOrCreate(
      ['conversation_id' => $conversationId],
      [
        'user_id' => $userId,
        'messages' => [],
        'is_active' => true,
      ]
    );
  }

  protected function getConversationHistory($conversationId, $userId = null)
  {
    $conversation = ChatbotConversation::where('conversation_id', $conversationId)
      ->when($userId, fn($query) => $query->where('user_id', $userId))
      ->first();

    if (!$conversation) {
      return [];
    }

    return $conversation->getFrontendMessages();
  }

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

  public function startNewConversation(Request $request)
  {
    $user = auth()->user();
    $conversationId = $this->generateConversationId();

    // Create a new conversation
    $conversation = ChatbotConversation::create([
      'user_id' => $user->id,
      'conversation_id' => $conversationId,
      'messages' => [],
      'is_active' => true,
      'title' => $request->input('title') ?: 'New Conversation',
    ]);

    return response()->json([
      'conversation_id' => $conversationId,
      'conversation' => $conversation,
    ]);
  }

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

  public function debug()
  {
    return response()->json([
      'config_services_openai' => config('services.openai'),
      'env_openai_api_key' => env('OPENAI_API_KEY') ? 'present (length: ' . strlen(env('OPENAI_API_KEY')) . ')' : 'null',
      'controller_api_key' => $this->openaiApiKey ? 'present (length: ' . strlen($this->openaiApiKey) . ')' : 'null',
    ]);
  }
}
