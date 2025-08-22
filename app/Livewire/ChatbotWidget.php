<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotWidget extends Component
{
    public $isOpen = false;
    public $message = '';
    public $conversation = [];
    public $conversationId = null;
    public $isLoading = false;
    public $error = null;

    protected $listeners = ['toggleChatbot'];

    public function mount()
    {
        $this->conversationId = 'conv_' . uniqid() . '_' . time();
    }

    public function toggleChatbot()
    {
        $this->isOpen = !$this->isOpen;
        
        if ($this->isOpen && empty($this->conversation)) {
            $this->addWelcomeMessage();
        }
    }

    public function addWelcomeMessage()
    {
        $this->conversation[] = [
            'role' => 'assistant',
            'content' => "Hello! I'm CheQQme, your AI assistant for the CheQQme Data Center. I can help you navigate the platform, find resources, answer questions about projects and tasks, and much more. How can I help you today?",
            'timestamp' => now()->toISOString()
        ];
    }

    public function sendMessage()
    {
        if (empty(trim($this->message))) {
            return;
        }

        $userMessage = trim($this->message);
        
        // Add user message to conversation
        $this->conversation[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->toISOString()
        ];

        // Clear input
        $this->message = '';
        
        // Set loading state
        $this->isLoading = true;
        $this->error = null;

        try {
            // Call the chatbot API
            $response = Http::withHeaders([
                'X-CSRF-TOKEN' => csrf_token(),
            ])->post('/chatbot/chat', [
                'message' => $userMessage,
                'conversation_id' => $this->conversationId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Add AI response to conversation
                $this->conversation[] = [
                    'role' => 'assistant',
                    'content' => $data['response'],
                    'timestamp' => $data['timestamp'] ?? now()->toISOString()
                ];

                // Update conversation ID if provided
                if (isset($data['conversation_id'])) {
                    $this->conversationId = $data['conversation_id'];
                }
            } else {
                $this->error = 'Failed to get response from AI assistant. Please try again.';
                Log::error('Chatbot API error: ' . $response->body());
            }

        } catch (\Exception $e) {
            $this->error = 'An error occurred while processing your request. Please try again.';
            Log::error('Chatbot error: ' . $e->getMessage());
        }

        $this->isLoading = false;
        
        // Scroll to bottom after response
        $this->dispatch('scrollToBottom');
    }

    public function clearConversation()
    {
        $this->conversation = [];
        $this->conversationId = 'conv_' . uniqid() . '_' . time();
        $this->error = null;
        $this->addWelcomeMessage();
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}