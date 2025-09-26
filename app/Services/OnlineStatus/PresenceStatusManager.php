<?php

namespace App\Services\OnlineStatus;

use App\Events\UserOnlineStatusChanged;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Real-time online status management using Laravel Presence Channels
 *
 * This replaces the polling-based system with real-time presence detection
 */
class PresenceStatusManager
{
    /**
     * Set user as online and broadcast status change
     */
    public static function setOnline(User $user): void
    {
        $previousStatus = $user->online_status;

        // Clear last_status_change when setting to online (allows auto-away to work)
        $user->update([
            'online_status' => StatusManager::ONLINE,
            'last_status_change' => null,
        ]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusManager::ONLINE, $previousStatus));

        Log::info("User {$user->id} set to online status via presence channel");
    }

    /**
     * Set user as invisible and broadcast status change
     */
    public static function setInvisible(User $user): void
    {
        $previousStatus = $user->online_status;

        $user->update(['online_status' => StatusManager::INVISIBLE]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusManager::INVISIBLE, $previousStatus));

        Log::info("User {$user->id} set to invisible status via presence channel");
    }

    /**
     * Handle manual status change and broadcast
     */
    public static function handleManualChange(User $user, string $newStatus): void
    {
        if (! StatusManager::isValidStatus($newStatus)) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        $previousStatus = $user->online_status;

        // For manual changes, set timestamp to mark as manual
        // Only clear timestamp for online status (to allow auto-away)
        if ($newStatus === StatusManager::ONLINE) {
            $user->update([
                'online_status' => $newStatus,
                'last_status_change' => null,
            ]);
        } else {
            $user->update([
                'online_status' => $newStatus,
                'last_status_change' => now(),
            ]);
        }

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, $newStatus, $previousStatus));

        Log::info("User {$user->id} manually changed status to {$newStatus} via presence channel");
    }

    /**
     * Set user as away and broadcast status change
     */
    public static function setAway(User $user): void
    {
        $previousStatus = $user->online_status;

        $user->update(['online_status' => StatusManager::AWAY]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusManager::AWAY, $previousStatus));

        Log::info("User {$user->id} set to away status via presence channel");
    }

    /**
     * Set user as do not disturb and broadcast status change
     */
    public static function setDoNotDisturb(User $user): void
    {
        $previousStatus = $user->online_status;

        $user->update(['online_status' => StatusManager::DO_NOT_DISTURB]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusManager::DO_NOT_DISTURB, $previousStatus));

        Log::info("User {$user->id} set to do not disturb status via presence channel");
    }

    /**
     * Get online users from presence channel
     * This is handled automatically by the presence channel
     */
    public static function getOnlineUsers(): array
    {
        // This will be populated by the frontend JavaScript
        // The presence channel automatically tracks who's online
        return [];
    }

    /**
     * Check if user is online (presence-based)
     */
    public static function isUserOnline(User $user): bool
    {
        // With presence channels, we don't need to check activity timestamps
        // The presence channel automatically handles join/leave events
        return $user->online_status === StatusManager::ONLINE;
    }
}
