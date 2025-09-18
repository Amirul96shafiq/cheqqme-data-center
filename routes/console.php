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

// Schedule resource lock cleanup every 5 minutes
Schedule::command('resource-lock:cleanup --force')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/resource-lock-cleanup.log'));

// Schedule weekly chatbot cleanup with automatic backup
Schedule::command('chatbot:weekly-cleanup')
    ->weeklyOn(0, '00:00') // Sunday at midnight
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/chatbot-weekly-cleanup.log'));
