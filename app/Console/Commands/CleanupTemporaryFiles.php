<?php

namespace App\Console\Commands;

use App\Services\TemporaryFileService;
use Illuminate\Console\Command;

class CleanupTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-temporary-files {--hours=24 : Number of hours old files to delete}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old temporary files from the issue tracker upload system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        $minutes = $hours * 60;

        $this->info("Cleaning up temporary files older than {$hours} hours...");

        $tempService = new TemporaryFileService;
        $deletedCount = $tempService->cleanupOldFiles($minutes);

        $this->info("Cleaned up {$deletedCount} temporary files.");

        return Command::SUCCESS;
    }
}
