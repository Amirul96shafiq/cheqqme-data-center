<?php

namespace App\Console\Commands;

use App\Services\PublicHolidayService;
use Illuminate\Console\Command;

class InitializeHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:initialize {country=MY}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize holidays for the current year and next year from Google Calendar API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $country = $this->argument('country');
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;

        $this->info("Initializing holidays for {$country}...");

        $service = app(PublicHolidayService::class);

        // Sync current year
        $this->info("Syncing holidays for {$currentYear}...");
        $success = $service->syncHolidaysFromAPI($country, $currentYear);
        if ($success) {
            $this->info("✅ Successfully synced holidays for {$country} in {$currentYear}");
        } else {
            $this->error("❌ Failed to sync holidays for {$country} in {$currentYear}");
        }

        // Sync next year
        $this->info("Syncing holidays for {$nextYear}...");
        $success = $service->syncHolidaysFromAPI($country, $nextYear);
        if ($success) {
            $this->info("✅ Successfully synced holidays for {$country} in {$nextYear}");
        } else {
            $this->error("❌ Failed to sync holidays for {$country} in {$nextYear}");
        }

        $this->info('Holiday initialization complete!');
    }
}
