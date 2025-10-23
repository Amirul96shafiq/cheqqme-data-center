<?php

namespace App\Console\Commands;

use App\Services\PublicHolidayService;
use Illuminate\Console\Command;

class SyncHolidays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holidays:sync {country=MY} {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync public holidays from API for a specific country and year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $country = $this->argument('country');
        $year = $this->argument('year') ?? now()->year;

        $this->info("Syncing holidays for {$country} in {$year}...");

        $service = app(PublicHolidayService::class);
        $success = $service->syncHolidaysFromAPI($country, $year);

        if ($success) {
            $this->info("✅ Successfully synced holidays for {$country} in {$year}");
        } else {
            $this->error("❌ Failed to sync holidays for {$country} in {$year}");
        }

        return $success ? 0 : 1;
    }
}
