<?php

namespace App\Services\OnlineStatus;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Main controller for online status management
 * 
 * This class orchestrates all status-related operations and business logic
 */
class StatusController
{
    /**
     * Set user as online (typically on login)
     */
    public static function setOnline(User $user): void
    {
        $user->update(['online_status' => StatusManager::ONLINE]);
        ActivityTracker::recordActivity($user);
        Log::info("User {$user->id} set to online status");
    }

    /**
     * Set user as invisible (typically on logout)
     */
    public static function setInvisible(User $user): void
    {
        $user->update(['online_status' => StatusManager::INVISIBLE]);
        ActivityTracker::clearActivity($user);
        Log::info("User {$user->id} set to invisible status");
    }

    /**
     * Handle manual status change by user
     */
    public static function handleManualChange(User $user, string $newStatus): void
    {
        if (!StatusManager::isValidStatus($newStatus)) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        $user->update(['online_status' => $newStatus]);

        if ($newStatus === StatusManager::INVISIBLE) {
            // Clear activity when user manually goes invisible
            ActivityTracker::clearActivity($user);
            Log::info("User {$user->id} manually set to invisible status - will not auto-update");
        } else {
            // Record activity for other status changes
            ActivityTracker::recordActivity($user);
            Log::info("User {$user->id} manually changed status to {$newStatus}");
        }
    }

    /**
     * Handle page refresh - reset auto-away users to online
     */
    public static function handlePageRefresh(User $user): void
    {
        // Only reset if user is away and status resets on refresh
        if ($user->online_status === StatusManager::AWAY && StatusManager::resetsOnRefresh(StatusManager::AWAY)) {
            $user->update(['online_status' => StatusManager::ONLINE]);
            ActivityTracker::recordActivity($user);
            Log::info("User {$user->id} set to online status on page refresh (was away)");
        }
    }

    /**
     * Check and update user status based on activity
     */
    public static function checkAndUpdateStatus(User $user): void
    {
        // Don't auto-update if status is not auto-managed
        if (!StatusManager::isAutoManaged($user->online_status)) {
            return;
        }

        $shouldBeAway = ActivityTracker::shouldBeAway($user);
        $isActive = ActivityTracker::isActive($user);

        if ($shouldBeAway && $user->online_status !== StatusManager::AWAY) {
            // User should be away
            $user->update(['online_status' => StatusManager::AWAY]);
            $minutesSinceActivity = ActivityTracker::getMinutesSinceActivity($user);
            Log::info("User {$user->id} auto-set to away status (inactive for {$minutesSinceActivity} minutes)");
        } elseif ($isActive && $user->online_status !== StatusManager::ONLINE) {
            // User is active, set to online
            $user->update(['online_status' => StatusManager::ONLINE]);
            Log::info("User {$user->id} auto-set to online status (active)");
        }
    }

    /**
     * Get users who should be checked for status updates
     */
    public static function getUsersToCheck(): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereIn('online_status', [
            StatusManager::ONLINE,
            StatusManager::AWAY,
        ])->get();
    }

    /**
     * Process status updates for all users
     */
    public static function processAllStatusUpdates(): void
    {
        $users = self::getUsersToCheck();

        foreach ($users as $user) {
            self::checkAndUpdateStatus($user);
        }

        Log::info("Processed status updates for {$users->count()} users");
    }

    /**
     * Get status update rules for frontend
     */
    public static function getStatusUpdateRules(): array
    {
        return [
            'away_timeout_minutes' => ActivityTracker::AWAY_TIMEOUT_MINUTES,
            'auto_managed_statuses' => array_filter(
                StatusManager::getAllStatuses(),
                fn($status) => StatusManager::isAutoManaged($status)
            ),
            'manual_only_statuses' => array_filter(
                StatusManager::getAllStatuses(),
                fn($status) => !StatusManager::isAutoManaged($status)
            ),
            'refresh_reset_statuses' => array_filter(
                StatusManager::getAllStatuses(),
                fn($status) => StatusManager::resetsOnRefresh($status)
            ),
        ];
    }
}
