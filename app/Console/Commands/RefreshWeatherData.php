<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshWeatherData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weather:refresh {--user= : Specific user ID to refresh weather for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh weather data for all users or a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $weatherService = new WeatherService;
        $userId = $this->option('user');

        if ($userId) {
            // Refresh for specific user
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");

                return 1;
            }

            $this->refreshWeatherForUser($weatherService, $user);
        } else {
            // Refresh for all users
            $users = User::all();
            $this->info("Refreshing weather data for {$users->count()} users...");

            foreach ($users as $user) {
                $this->refreshWeatherForUser($weatherService, $user);
            }
        }

        $this->info('Weather data refresh completed.');

        return 0;
    }

    /**
     * Refresh weather data for a specific user
     */
    private function refreshWeatherForUser(WeatherService $weatherService, User $user): void
    {
        try {
            // Clear existing cache
            $weatherService->clearCache($user);

            // Fetch fresh data
            $currentWeather = $weatherService->getCurrentWeather($user);
            $forecast = $weatherService->getForecast($user);

            $this->line("✓ Refreshed weather for {$user->name} ({$user->email})");
            Log::info("Weather data refreshed for user {$user->id}", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'location' => $currentWeather['location']['city'] . ', ' . $currentWeather['location']['country'],
                'temperature' => $currentWeather['current']['temperature'] . '°C',
            ]);
        } catch (\Exception $e) {
            $this->error("✗ Failed to refresh weather for {$user->name}: " . $e->getMessage());
            Log::error("Failed to refresh weather data for user {$user->id}", [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
