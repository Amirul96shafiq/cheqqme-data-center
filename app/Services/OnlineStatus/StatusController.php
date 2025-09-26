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
        if (! StatusManager::isValidStatus($newStatus)) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        // Record activity first (before status change) to ensure proper ordering
        if ($newStatus !== StatusManager::INVISIBLE) {
            ActivityTracker::recordActivity($user);
        }

        // Special logic for manual status changes
        if ($newStatus === StatusManager::ONLINE) {
            // When manually changing to online, set last_status_change to NULL
            // This allows auto-away to reset on page refresh
            $user->update([
                'online_status' => $newStatus,
                'last_status_change' => null,
            ]);
        } else {
            // For other statuses (away, dnd, invisible), set timestamp
            $user->update([
                'online_status' => $newStatus,
                'last_status_change' => now(),
            ]);
        }

        if ($newStatus === StatusManager::INVISIBLE) {
            // Clear activity when user manually goes invisible
            ActivityTracker::clearActivity($user);
            Log::info("User {$user->id} manually set to invisible status - will not auto-update");
        } else {
            Log::info("User {$user->id} manually changed status to {$newStatus}");
        }
    }

    /**
     * Handle page refresh/navigation - reset auto-away users to online
     * Following Microsoft Teams behavior: any activity resets auto-away to online
     */
    public static function handlePageRefresh(User $user): void
    {
        // If user is away, check if it's auto-away or manual away
        if ($user->online_status === StatusManager::AWAY) {
            $lastStatusChange = $user->last_status_change;

            // If no last_status_change, it's definitely an auto-away
            if (! $lastStatusChange) {
                $user->update(['online_status' => StatusManager::ONLINE]);
                // Record activity when user reopens tab (this is user activity)
                ActivityTracker::recordActivity($user);
                Log::info("User {$user->id} set to online status on page refresh (was auto-away - no manual change)");

                return;
            }

            // If last_status_change is older than 5 minutes, treat as auto-away
            // This handles cases where user was manually set to away but then became inactive
            $minutesSinceStatusChange = floor((now()->timestamp - strtotime($lastStatusChange)) / 60);
            if ($minutesSinceStatusChange >= 5) {
                $user->update(['online_status' => StatusManager::ONLINE]);
                // Record activity when user reopens tab (this is user activity)
                ActivityTracker::recordActivity($user);
                Log::info("User {$user->id} set to online status on page refresh (was auto-away - status change older than 5 minutes)");

                return;
            }

            // Recent manual away - don't reset
            Log::info("User {$user->id} is manually away (recent change) - not resetting on page refresh");
        }
    }

    /**
     * Check and update user status based on activity
     * Following Microsoft Teams behavior: only auto-managed statuses change automatically
     */
    public static function checkAndUpdateStatus(User $user): void
    {
        // Don't auto-update if status is not auto-managed
        if (! StatusManager::isAutoManaged($user->online_status)) {
            return;
        }

        $shouldBeAway = ActivityTracker::shouldBeAway($user);
        $isActive = ActivityTracker::isActive($user);

        // Record activity if user is active (tab reopened, page refreshed, etc.)
        if ($isActive) {
            ActivityTracker::recordActivity($user);
        }

        if ($shouldBeAway && $user->online_status !== StatusManager::AWAY) {
            // User should be away - this is an auto-away, don't set last_status_change
            $user->update(['online_status' => StatusManager::AWAY]);
            $minutesSinceActivity = ActivityTracker::getMinutesSinceActivity($user);
            Log::info("User {$user->id} auto-set to away status (inactive for {$minutesSinceActivity} minutes)");
        } elseif ($isActive && $user->online_status === StatusManager::AWAY) {
            // User is active and currently away - check if it's auto-away or manual away
            $lastStatusChange = $user->last_status_change;

            // If no last_status_change or it's older than 5 minutes, treat as auto-away
            if (! $lastStatusChange || floor((now()->timestamp - strtotime($lastStatusChange)) / 60) >= 5) {
                $user->update(['online_status' => StatusManager::ONLINE]);
                Log::info("User {$user->id} auto-set to online status (active after auto-away)");
            } else {
                // Recent manual away - don't auto-change
                Log::info("User {$user->id} is active but manually away (recent change) - not auto-changing");
            }
        } elseif ($isActive && $user->online_status !== StatusManager::ONLINE) {
            // User is active and not away - set to online
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
                fn ($status) => StatusManager::isAutoManaged($status)
            ),
            'manual_only_statuses' => array_filter(
                StatusManager::getAllStatuses(),
                fn ($status) => ! StatusManager::isAutoManaged($status)
            ),
            'refresh_reset_statuses' => array_filter(
                StatusManager::getAllStatuses(),
                fn ($status) => StatusManager::resetsOnRefresh($status)
            ),
        ];
    }
}
