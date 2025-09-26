<?php

namespace App\Services\OnlineStatus;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

/**
 * Handles user activity tracking and status management
 */
class ActivityTracker
{
    // Configuration constants
    public const AWAY_TIMEOUT_MINUTES = 5;
    public const ACTIVITY_CACHE_PREFIX = 'user_activity_';
    public const ACTIVITY_LOG_NAME = 'user_activity';
    public const CACHE_TTL_HOURS = 1;

    /**
     * Update user activity timestamp
     */
    public static function recordActivity(User $user): void
    {
        // Update cache for fast access
        $cacheKey = self::ACTIVITY_CACHE_PREFIX . $user->id;
        Cache::put($cacheKey, now(), now()->addHours(self::CACHE_TTL_HOURS));

        // Log activity for persistence and audit trail
        activity(self::ACTIVITY_LOG_NAME)
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties([
                'action' => 'user_activity',
                'timestamp' => now()->toISOString(),
                'status' => $user->online_status,
            ])
            ->log('User activity recorded');
    }

    /**
     * Clear user activity data
     */
    public static function clearActivity(User $user): void
    {
        $cacheKey = self::ACTIVITY_CACHE_PREFIX . $user->id;
        Cache::forget($cacheKey);
    }

    /**
     * Get user's last activity timestamp
     */
    public static function getLastActivity(User $user): ?Carbon
    {
        // First try cache for fast access
        $cacheKey = self::ACTIVITY_CACHE_PREFIX . $user->id;
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
            Cache::put($cacheKey, $activityTime, now()->addHours(self::CACHE_TTL_HOURS));
            return $activityTime;
        }

        return null;
    }

    /**
     * Check if user is currently active (within timeout period)
     */
    public static function isActive(User $user): bool
    {
        $lastActivity = self::getLastActivity($user);

        if (!$lastActivity) {
            return false;
        }

        $minutesSinceActivity = floor((now()->timestamp - $lastActivity->timestamp) / 60);
        return $minutesSinceActivity < self::AWAY_TIMEOUT_MINUTES;
    }

    /**
     * Get minutes since last activity
     */
    public static function getMinutesSinceActivity(User $user): ?int
    {
        $lastActivity = self::getLastActivity($user);

        if (!$lastActivity) {
            return null;
        }

        return floor((now()->timestamp - $lastActivity->timestamp) / 60);
    }

    /**
     * Check if user should be considered away
     */
    public static function shouldBeAway(User $user): bool
    {
        $minutesSinceActivity = self::getMinutesSinceActivity($user);
        
        if ($minutesSinceActivity === null) {
            return true; // No activity recorded
        }

        return $minutesSinceActivity >= self::AWAY_TIMEOUT_MINUTES;
    }
}
