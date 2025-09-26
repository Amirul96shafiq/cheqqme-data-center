<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class OnlineStatusTracker
{
    /**
     * Timeout for away status (in minutes)
     */
    public const AWAY_TIMEOUT_MINUTES = 5;

    /**
     * Cache key prefix for user activity
     */
    public const ACTIVITY_CACHE_PREFIX = 'user_activity_';

    /**
     * Activity log name for user activity tracking
     */
    public const ACTIVITY_LOG_NAME = 'user_activity';

    /**
     * Set user as online when they login
     */
    public static function setUserOnline(User $user): void
    {
        // Always set to online when user logs in, regardless of previous status
        $user->update(['online_status' => OnlineStatusManager::STATUS_ONLINE]);
        self::updateUserActivity($user);
        Log::info("User {$user->id} set to online status on login");
    }

    /**
     * Set user as invisible when they logout
     */
    public static function setUserInvisible(User $user): void
    {
        $user->update(['online_status' => OnlineStatusManager::STATUS_INVISIBLE]);
        self::clearUserActivity($user);
        Log::info("User {$user->id} set to invisible status");
    }

    /**
     * Update user activity timestamp
     */
    public static function updateUserActivity(User $user): void
    {
        // Update cache for fast access
        $cacheKey = self::ACTIVITY_CACHE_PREFIX.$user->id;
        Cache::put($cacheKey, now(), now()->addHours(1));

        // Log activity for persistence and audit trail
        activity(self::ACTIVITY_LOG_NAME)
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'action' => 'user_activity',
                'timestamp' => now()->toISOString(),
            ])
            ->log('User activity recorded');
    }

    /**
     * Clear user activity data
     */
    public static function clearUserActivity(User $user): void
    {
        $cacheKey = self::ACTIVITY_CACHE_PREFIX.$user->id;
        Cache::forget($cacheKey);
    }

    /**
     * Check if user should be set to away status
     */
    public static function checkAndUpdateUserStatus(User $user): void
    {
        // Don't auto-update if user manually set to invisible, do not disturb, or away
        if (in_array($user->online_status, [
            OnlineStatusManager::STATUS_INVISIBLE,
            OnlineStatusManager::STATUS_DND,
            OnlineStatusManager::STATUS_AWAY,
        ])) {
            return;
        }

        $lastActivity = self::getUserLastActivity($user);

        if (! $lastActivity) {
            // No activity recorded, set to away
            if ($user->online_status !== OnlineStatusManager::STATUS_AWAY_AUTO) {
                $user->update(['online_status' => OnlineStatusManager::STATUS_AWAY_AUTO]);
                Log::info("User {$user->id} auto-set to away status (no activity)");
            }

            return;
        }

        $minutesSinceActivity = floor((now()->timestamp - $lastActivity->timestamp) / 60);

        if ($minutesSinceActivity >= self::AWAY_TIMEOUT_MINUTES) {
            // User has been inactive for more than 5 minutes
            if ($user->online_status !== OnlineStatusManager::STATUS_AWAY_AUTO) {
                $user->update(['online_status' => OnlineStatusManager::STATUS_AWAY_AUTO]);
                Log::info("User {$user->id} auto-set to away status (inactive for {$minutesSinceActivity} minutes)");
            }
        } else {
            // User is active, set to online
            if ($user->online_status !== OnlineStatusManager::STATUS_ONLINE) {
                $user->update(['online_status' => OnlineStatusManager::STATUS_ONLINE]);
                Log::info("User {$user->id} auto-set to online status (active)");
            }
        }
    }

    /**
     * Get user's last activity timestamp
     */
    public static function getUserLastActivity(User $user): ?Carbon
    {
        // First try cache for fast access
        $cacheKey = self::ACTIVITY_CACHE_PREFIX.$user->id;
        $cachedActivity = Cache::get($cacheKey);

        if ($cachedActivity) {
            return $cachedActivity;
        }

        // Fallback to activity log for persistence
        $lastActivity = Activity::where('log_name', self::ACTIVITY_LOG_NAME)
            ->where('subject_type', User::class)
            ->where('subject_id', $user->id)
            ->latest()
            ->first();

        if ($lastActivity) {
            $activityTime = $lastActivity->created_at;
            // Update cache for future requests
            Cache::put($cacheKey, $activityTime, now()->addHours(1));

            return $activityTime;
        }

        return null;
    }

    /**
     * Check if user is currently active (within timeout period)
     */
    public static function isUserActive(User $user): bool
    {
        $lastActivity = self::getUserLastActivity($user);

        if (! $lastActivity) {
            return false;
        }

        return floor((now()->timestamp - $lastActivity->timestamp) / 60) < self::AWAY_TIMEOUT_MINUTES;
    }

    /**
     * Handle manual status change
     */
    public static function handleManualStatusChange(User $user, string $newStatus): void
    {
        $user->update(['online_status' => $newStatus]);

        if ($newStatus === OnlineStatusManager::STATUS_INVISIBLE) {
            // Clear activity when user manually goes invisible
            self::clearUserActivity($user);
            Log::info("User {$user->id} manually set to invisible status - will not auto-update");
        } else {
            // Update activity for other status changes
            self::updateUserActivity($user);
            Log::info("User {$user->id} manually changed status to {$newStatus}");
        }
    }

    /**
     * Get users who should be checked for status updates
     */
    public static function getUsersToCheck(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereIn('online_status', [
            OnlineStatusManager::STATUS_ONLINE,
            OnlineStatusManager::STATUS_AWAY_AUTO,
        ])->get();
    }

    /**
     * Process status updates for all users
     */
    public static function processAllUserStatusUpdates(): void
    {
        $users = self::getUsersToCheck();

        foreach ($users as $user) {
            self::checkAndUpdateUserStatus($user);
        }

        Log::info("Processed status updates for {$users->count()} users");
    }

    /**
     * Handle page refresh - set auto-away users back to online
     */
    public static function handlePageRefresh(User $user): void
    {
        if ($user->online_status === OnlineStatusManager::STATUS_AWAY_AUTO) {
            $user->update(['online_status' => OnlineStatusManager::STATUS_ONLINE]);
            self::updateUserActivity($user);
            Log::info("User {$user->id} set to online status on page refresh (was auto-away)");
        }
    }

    /**
     * Get status update rules for frontend
     */
    public static function getStatusUpdateRules(): array
    {
        return [
            'away_timeout_minutes' => self::AWAY_TIMEOUT_MINUTES,
            'auto_update_statuses' => [
                OnlineStatusManager::STATUS_ONLINE,
                OnlineStatusManager::STATUS_AWAY,
            ],
            'manual_only_statuses' => [
                OnlineStatusManager::STATUS_DND,
                OnlineStatusManager::STATUS_INVISIBLE,
            ],
        ];
    }
}
