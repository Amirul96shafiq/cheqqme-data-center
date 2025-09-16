<?php

namespace App\Services;

use App\Models\ChatbotBackup;
use App\Models\ChatbotConversation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ChatbotBackupService
{
    /**
     * Create a backup of user's chatbot conversations
     */
    public function createBackup(User $user, string $backupType = 'weekly'): ChatbotBackup
    {
        // Get all conversations for the user
        $conversations = ChatbotConversation::where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($conversations->isEmpty()) {
            throw new \Exception('No conversations found to backup');
        }

        // Prepare backup data
        $backupData = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'backup_created_at' => now()->toISOString(),
            'conversations' => $conversations->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'conversation_id' => $conversation->conversation_id,
                    'role' => $conversation->role,
                    'content' => $conversation->content,
                    'created_at' => $conversation->created_at->toISOString(),
                    'last_activity' => $conversation->last_activity->toISOString(),
                ];
            })->toArray(),
        ];

        // Generate backup name
        $backupName = $this->generateBackupName($backupType, $conversations);

        // Create backup record
        $backup = ChatbotBackup::create([
            'user_id' => $user->id,
            'backup_name' => $backupName,
            'backup_data' => $backupData,
            'backup_type' => $backupType,
            'message_count' => $conversations->count(),
            'backup_date' => now(),
            'conversation_start_date' => $conversations->first()->created_at,
            'conversation_end_date' => $conversations->last()->created_at,
        ]);

        Log::info('Chatbot backup created', [
            'user_id' => $user->id,
            'backup_id' => $backup->id,
            'message_count' => $conversations->count(),
            'backup_type' => $backupType,
        ]);

        return $backup;
    }

    /**
     * Generate backup name based on type and date range
     */
    private function generateBackupName(string $backupType, $conversations): string
    {
        $startDate = $conversations->first()->created_at->format('M d');
        $endDate = $conversations->last()->created_at->format('M d, Y');

        switch ($backupType) {
            case 'weekly':
                return "Weekly Backup ({$startDate} - {$endDate})";
            case 'manual':
                return "Manual Backup ({$endDate})";
            case 'import':
                return "Imported Backup ({$endDate})";
            default:
                return "Backup ({$endDate})";
        }
    }

    /**
     * Check if weekly cleanup should run
     */
    public function shouldRunWeeklyCleanup(): bool
    {
        $lastCleanup = Cache::get('last_weekly_cleanup');
        $isSunday = now()->isSunday();
        $isMidnight = now()->hour === 0 && now()->minute < 30; // Within first 30 minutes of Sunday

        // Run if it's Sunday midnight and we haven't run this week
        if ($isSunday && $isMidnight && (! $lastCleanup || $lastCleanup < now()->startOfWeek())) {
            return true;
        }

        return false;
    }

    /**
     * Perform weekly cleanup for all users
     */
    public function performWeeklyCleanup(): void
    {
        if (! $this->shouldRunWeeklyCleanup()) {
            return;
        }

        Log::info('Starting weekly chatbot cleanup');

        // Get all users who have conversations
        $users = User::whereHas('chatbotConversations')->get();

        foreach ($users as $user) {
            try {
                // Create backup before cleanup
                $this->createBackup($user, 'weekly');

                // Clean up old conversations (keep only last 7 days)
                ChatbotConversation::where('user_id', $user->id)
                    ->where('last_activity', '<', now()->subDays(7))
                    ->delete();

                Log::info('Weekly cleanup completed for user', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('Weekly cleanup failed for user', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Mark cleanup as completed
        Cache::put('last_weekly_cleanup', now(), now()->addWeek());

        Log::info('Weekly chatbot cleanup completed');
    }

    /**
     * Get user's backup history
     */
    public function getUserBackups(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return ChatbotBackup::where('user_id', $user->id)
            ->orderBy('backup_date', 'desc')
            ->get();
    }

    /**
     * Download backup as JSON file
     */
    public function downloadBackup(ChatbotBackup $backup): string
    {
        $fileName = "chatbot_backup_{$backup->id}_{$backup->user->name}_".now()->format('Y-m-d_H-i-s').'.json';

        // Update backup record with file name
        $backup->update(['file_name' => $fileName]);

        return $fileName;
    }

    /**
     * Restore conversations from backup
     */
    public function restoreFromBackup(ChatbotBackup $backup, ?string $conversationId = null): int
    {
        $restoredCount = 0;

        // Use database transaction to ensure all operations complete before refresh
        \DB::transaction(function () use ($backup, $conversationId, &$restoredCount) {
            // Use provided conversation ID or get the current session's conversation ID for the user
            $currentConversationId = $conversationId ?: $this->getCurrentConversationId($backup->user_id);

            foreach ($backup->backup_data['conversations'] as $conversationData) {
                // Check if conversation already exists (by content and role to avoid duplicates)
                $existing = ChatbotConversation::where('user_id', $backup->user_id)
                    ->where('conversation_id', $currentConversationId)
                    ->where('content', $conversationData['content'])
                    ->where('role', $conversationData['role'])
                    ->first();

                if (! $existing) {
                    ChatbotConversation::create([
                        'user_id' => $backup->user_id,
                        'conversation_id' => $currentConversationId, // Use current session's conversation ID
                        'role' => $conversationData['role'],
                        'content' => $conversationData['content'],
                        'created_at' => Carbon::parse($conversationData['created_at']),
                        'last_activity' => now(), // Set to current time so restored conversations are visible
                    ]);
                    $restoredCount++;
                }
            }

            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'restored_count' => $restoredCount,
                'conversation_id' => $currentConversationId,
            ]);
        });

        // Clear any relevant caches to ensure restored data is immediately available
        \Cache::forget("chatbot_conversations_{$backup->user_id}");

        return $restoredCount;
    }

    /**
     * Get the current conversation ID for a user
     */
    private function getCurrentConversationId($userId): string
    {
        // Find the most recent active conversation for the user within 7 days
        $lastConversation = \App\Models\ChatbotConversation::where('user_id', $userId)
            ->where('last_activity', '>', now()->subDays(7))
            ->orderBy('last_activity', 'desc')
            ->first();

        // If a recent conversation exists, use it, otherwise create a new one
        if ($lastConversation) {
            return $lastConversation->conversation_id;
        } else {
            // Create a new conversation ID if no recent conversation exists
            return 'conv_'.uniqid().'_'.time();
        }
    }
}
