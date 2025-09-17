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
        // Validate backup type
        if (! in_array($backupType, ['weekly', 'manual', 'import'])) {
            throw new \Exception('Invalid backup type');
        }

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
     * Generate backup name based on custom format: arem_chat_{user_id}_{backed_up_at}_0001
     */
    private function generateBackupName(string $backupType, $conversations): string
    {
        $userId = $conversations->first()->user_id;
        $backedUpAt = now()->format('Ymd_His');

        // Get the next sequence number for this user
        $sequenceNumber = $this->getNextSequenceNumber($userId);

        return "arem_chat_{$userId}_{$backedUpAt}_{$sequenceNumber}";
    }

    /**
     * Get the next sequence number for a user's backups
     */
    private function getNextSequenceNumber(int $userId): string
    {
        // Count existing backups for this user and add 1
        $existingCount = ChatbotBackup::where('user_id', $userId)->count();
        $nextNumber = $existingCount + 1;

        // Format as 4-digit zero-padded number
        return str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
        // Use backup name as filename, sanitized for file system
        $baseFileName = $this->sanitizeFileName($backup->backup_name);

        // Add random characters for security
        $randomSuffix = $this->generateRandomSuffix();
        $fileName = $baseFileName.'_'.$randomSuffix.'.json';

        // Update backup record with file name
        $backup->update(['file_name' => $fileName]);

        return $fileName;
    }

    /**
     * Sanitize filename to be safe for file systems
     */
    private function sanitizeFileName(string $fileName): string
    {
        // Remove or replace characters that are problematic in filenames
        $fileName = preg_replace('/[\/:*?"<>|\\\\]/', '_', $fileName);

        // Remove multiple consecutive underscores
        $fileName = preg_replace('/_+/', '_', $fileName);

        // Remove leading/trailing underscores and spaces
        $fileName = trim($fileName, '_ ');

        // Ensure filename is not empty
        if (empty($fileName)) {
            $fileName = 'backup';
        }

        return $fileName;
    }

    /**
     * Generate a random suffix for backup filenames (security enhancement)
     */
    private function generateRandomSuffix(int $length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $suffix = '';

        for ($i = 0; $i < $length; $i++) {
            $suffix .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $suffix;
    }

    /**
     * Restore conversations from backup
     */
    public function restoreFromBackup(ChatbotBackup $backup, ?string $conversationId = null): int
    {
        // Validate backup data integrity
        if (! $this->validateBackupData($backup->backup_data)) {
            throw new \Exception('Invalid backup data format');
        }

        if (empty($backup->backup_data['conversations'])) {
            throw new \Exception('No conversations found in backup data');
        }

        $restoredCount = 0;
        $currentConversationId = $conversationId ?: $this->getCurrentConversationId($backup->user_id);

        // Use database transaction to ensure all operations complete atomically
        \DB::transaction(function () use ($backup, $currentConversationId, &$restoredCount) {
            // Get existing conversation IDs to avoid duplicates
            $existingIds = ChatbotConversation::whereIn('id',
                array_column($backup->backup_data['conversations'], 'id')
            )->pluck('id')->toArray();

            foreach ($backup->backup_data['conversations'] as $conversationData) {
                // Skip if conversation already exists
                if (in_array($conversationData['id'], $existingIds)) {
                    continue;
                }

                try {
                    ChatbotConversation::create([
                        'user_id' => $backup->user_id,
                        'conversation_id' => $currentConversationId,
                        'role' => $conversationData['role'],
                        'content' => $conversationData['content'],
                        'created_at' => Carbon::parse($conversationData['created_at']),
                        'last_activity' => now(),
                    ]);
                    $restoredCount++;
                } catch (\Exception $e) {
                    Log::warning('Failed to restore conversation', [
                        'conversation_id' => $conversationData['id'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            Log::info('Backup restored', [
                'backup_id' => $backup->id,
                'restored_count' => $restoredCount,
                'total_in_backup' => count($backup->backup_data['conversations']),
                'conversation_id' => $currentConversationId,
            ]);
        });

        // Clear relevant caches to ensure restored data is immediately available
        \Cache::forget("chatbot_conversations_{$backup->user_id}");

        return $restoredCount;
    }

    /**
     * Validate backup data integrity
     */
    public function validateBackupData(array $backupData): bool
    {
        $requiredFields = ['user_id', 'user_name', 'backup_created_at', 'conversations'];

        foreach ($requiredFields as $field) {
            if (! isset($backupData[$field])) {
                return false;
            }
        }

        if (! is_array($backupData['conversations'])) {
            return false;
        }

        foreach ($backupData['conversations'] as $conversation) {
            $requiredConversationFields = ['id', 'conversation_id', 'role', 'content', 'created_at', 'last_activity'];
            foreach ($requiredConversationFields as $field) {
                if (! isset($conversation[$field])) {
                    return false;
                }
            }
        }

        return true;
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
