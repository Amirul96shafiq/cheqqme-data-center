<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupDailyLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:setup-daily {--clean-old : Clean old log files}';

    /**
     * The console command description.
     */
    protected $description = 'Setup daily logging with standard Laravel daily rotation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oldLogPath = storage_path('logs/laravel.log');
        $todayLogPath = storage_path('logs/laravel-'.date('Y-m-d').'.log');

        $this->info('Setting up daily logging with standard Laravel daily rotation');

        // Clean old log files if requested
        if ($this->option('clean-old')) {
            $this->cleanOldLogs();
        }

        // Move any existing logs to archive file temporarily
        if (file_exists($oldLogPath) && filesize($oldLogPath) > 0) {
            $timestamp = time();
            $archivePath = storage_path("logs/laravel_archived_{$timestamp}.log");
            rename($oldLogPath, $archivePath);
            $this->info("Archived old log file to: {$archivePath}");
        }

        $this->info('Daily logging setup complete!');
        $this->info("Today's log file: {$todayLogPath}");
        $this->info('Laravel will automatically create daily files: laravel-YYYY-MM-DD.log');

        return Command::SUCCESS;
    }

    /**
     * Clean old log files
     */
    protected function cleanOldLogs()
    {
        $logDir = storage_path('logs');
        $files = glob($logDir.'/*');
        $deleted = 0;
        $today = date('Y-m-d');

        foreach ($files as $file) {
            if (is_file($file) &&
                in_array(pathinfo($file, PATHINFO_EXTENSION), ['log']) &&
                strpos(pathinfo($file, PATHINFO_FILENAME), 'laravel') !== false) {

                $filename = pathinfo($file, PATHINFO_FILENAME);

                // Skip today's files and non-standard files
                if (strpos($filename, $today) !== false || strpos($filename, 'archived') !== false) {
                    continue;
                }

                // Check if file is older than 30 days
                if (filemtime($file) < strtotime('-30 days')) {
                    unlink($file);
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            $this->info("Cleaned {$deleted} old log files");
        } else {
            $this->info('No old log files to clean');
        }
    }
}
