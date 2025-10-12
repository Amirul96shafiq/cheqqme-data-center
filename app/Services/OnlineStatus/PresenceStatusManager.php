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
     *
     * @param  User  $user  The user to set online
     * @param  bool  $forceOverride  If true, override manual status changes (used on fresh login)
     */
    public static function setOnline(User $user, bool $forceOverride = false): void
    {
        // If user has manually set their status and we're not forcing, don't override it
        if (! $forceOverride && $user->last_status_change !== null) {
            Log::info("User {$user->id} has manual status ({$user->online_status}), not setting to online");

            return;
        }

        $previousStatus = $user->online_status;

        // Clear last_status_change when setting to online (allows auto-away to work)
        $user->update([
            'online_status' => StatusConfig::ONLINE,
            'last_status_change' => null,
        ]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusConfig::ONLINE, $previousStatus));

        Log::info("User {$user->id} set to online status via presence channel".($forceOverride ? ' (forced)' : ''));
    }

    /**
     * Set user as invisible and broadcast status change
     */
    public static function setInvisible(User $user): void
    {
        $previousStatus = $user->online_status;

        $user->update(['online_status' => StatusConfig::INVISIBLE]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusConfig::INVISIBLE, $previousStatus));

        Log::info("User {$user->id} set to invisible status via presence channel");
    }

    /**
     * Handle manual status change and broadcast
     */
    public static function handleManualChange(User $user, string $newStatus): void
    {
        if (! StatusConfig::isValidStatus($newStatus)) {
            throw new \InvalidArgumentException("Invalid status: {$newStatus}");
        }

        $previousStatus = $user->online_status;

        // For manual changes, set timestamp to mark as manual
        // Only clear timestamp for online status (to allow auto-away)
        if ($newStatus === StatusConfig::ONLINE) {
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

        $user->update(['online_status' => StatusConfig::AWAY]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusConfig::AWAY, $previousStatus));

        Log::info("User {$user->id} set to away status via presence channel");
    }

    /**
     * Set user as do not disturb and broadcast status change
     */
    public static function setDoNotDisturb(User $user): void
    {
        $previousStatus = $user->online_status;

        $user->update(['online_status' => StatusConfig::DO_NOT_DISTURB]);

        // Broadcast the status change to all users
        broadcast(new UserOnlineStatusChanged($user, StatusConfig::DO_NOT_DISTURB, $previousStatus));

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
        return $user->online_status === StatusConfig::ONLINE;
    }

    /**
     * Set user as away due to inactivity (auto-away)
     */
    public static function setAwayDueToInactivity(User $user): void
    {
        $previousStatus = $user->online_status;

        // Only set to away if user is currently online and hasn't manually set a status
        if ($user->online_status === StatusConfig::ONLINE && ! $user->last_status_change) {
            $user->update([
                'online_status' => StatusConfig::AWAY,
                'last_status_change' => null, // Keep null to indicate this is auto-away
            ]);

            // Broadcast the status change to all users
            broadcast(new UserOnlineStatusChanged($user, StatusConfig::AWAY, $previousStatus));

            Log::info("User {$user->id} auto-set to away status due to inactivity");
        }
    }

    /**
     * Set user as invisible when tab loses focus (auto-invisible)
     */
    public static function setInvisibleDueToTabBlur(User $user): void
    {
        $previousStatus = $user->online_status;

        // Only set to invisible if user is currently online and hasn't manually set a status
        if ($user->online_status === StatusConfig::ONLINE && ! $user->last_status_change) {
            $user->update([
                'online_status' => StatusConfig::INVISIBLE,
                'last_status_change' => null, // Keep null to indicate this is auto-invisible
            ]);

            // Broadcast the status change to all users
            broadcast(new UserOnlineStatusChanged($user, StatusConfig::INVISIBLE, $previousStatus));

            Log::info("User {$user->id} auto-set to invisible status due to tab blur");
        }
    }

    /**
     * Restore user to online when they return (from auto-away or auto-invisible)
     */
    public static function restoreFromAutoStatus(User $user): void
    {
        $previousStatus = $user->online_status;

        // Only restore if user was in an auto-managed status (away or invisible with no manual change)
        if (in_array($user->online_status, [StatusConfig::AWAY, StatusConfig::INVISIBLE]) && ! $user->last_status_change) {
            $user->update([
                'online_status' => StatusConfig::ONLINE,
                'last_status_change' => null,
            ]);

            // Broadcast the status change to all users
            broadcast(new UserOnlineStatusChanged($user, StatusConfig::ONLINE, $previousStatus));

            Log::info("User {$user->id} restored to online status from auto-status: {$previousStatus}");
        }
    }
}
