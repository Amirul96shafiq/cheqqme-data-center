<?php

namespace App\Console\Commands;

use App\Services\ChatbotBackupService;
use Illuminate\Console\Command;

class WeeklyChatbotCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chatbot:weekly-cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform weekly cleanup of chatbot conversations with automatic backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting weekly chatbot cleanup...');

        $backupService = new ChatbotBackupService;

        try {
            $backupService->performWeeklyCleanup();
            $this->info('Weekly chatbot cleanup completed successfully!');
        } catch (\Exception $e) {
            $this->error('Weekly chatbot cleanup failed: '.$e->getMessage());

            return 1;
        }

        return 0;
    }
}
