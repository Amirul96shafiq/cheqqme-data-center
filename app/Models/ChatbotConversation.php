<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'role',
        'content',
        'last_activity',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
    }

    protected $casts = [
        'messages' => 'array',
        'last_activity' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the conversation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get active conversations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get conversations for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to order conversations by last activity
     */
    public function scopeOrderByActivity($query)
    {
        return $query->orderBy('last_activity', 'desc');
    }

    /**
     * Add a message to the conversation
     */
    public function addMessage(string $role, string $content): void
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->format('h:i A'),
        ];

        $this->update([
            'messages' => $messages,
            'last_activity' => now(),
        ]);
    }

    /**
     * Get conversation messages formatted for OpenAI
     */
    public function getFormattedMessages(): array
    {
        $messages = [];
        foreach ($this->messages ?? [] as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        return $messages;
    }

    /**
     * Get conversation messages formatted for frontend display
     */
    public function getFrontendMessages(): array
    {
        $messages = [];
        foreach ($this->messages ?? [] as $message) {
            $messages[] = [
                'role' => $message['role'],
                'content' => $message['content'],
                'timestamp' => $message['timestamp'] ?? now()->format('h:i A'),
            ];
        }

        return $messages;
    }

    /**
     * Generate a title from the first user message
     */
    public function generateTitle(): void
    {
        if ($this->title) {
            return;
        }

        $firstUserMessage = collect($this->messages ?? [])
            ->where('role', 'user')
            ->first();

        if ($firstUserMessage) {
            $title = substr($firstUserMessage['content'], 0, 50);
            if (strlen($firstUserMessage['content']) > 50) {
                $title .= '...';
            }
            $this->update(['title' => $title]);
        }
    }
}
