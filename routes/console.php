<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule weather data refresh every 4 hours
Schedule::command('weather:refresh')
    ->everyFourHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/weather-refresh.log'));

// Resource lock plugin removed - no cleanup required

// Schedule weekly chatbot cleanup with automatic backup
Schedule::command('chatbot:weekly-cleanup')
    ->weeklyOn(0, '00:00') // Sunday at midnight
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chatbot-weekly-cleanup.log'));

// Schedule temporary file cleanup daily at 2 AM
Schedule::command('app:cleanup-temporary-files')
    ->dailyAt('02:00') // Run during low traffic hours
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/temp-file-cleanup.log'));

// User online status updates are now handled by presence channels in real-time
// No scheduled job needed
