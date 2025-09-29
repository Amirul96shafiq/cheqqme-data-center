<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class DailyLogService
{
    /**
     * These methods are deprecated. Use standard Laravel Log facade instead.
     * Kept for backward compatibility.
     */

    /**
     * Log an info message to today's daily log
     */
    public static function info(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    /**
     * Log for a specific date - helper method
     */
    public static function getLogPathForDate(string $date): string
    {
        return storage_path("logs/laravel-{$date}.log");
    }

    /**
     * Get today's log file path
     */
    public static function getTodayLogPath(): string
    {
        return storage_path('logs/laravel-'.date('Y-m-d').'.log');
    }

    /**
     * Get yesterday's log file path
     */
    public static function getYesterdayLogPath(): string
    {
        return storage_path('logs/laravel-'.date('Y-m-d', strtotime('-1 day')).'.log');
    }
}
