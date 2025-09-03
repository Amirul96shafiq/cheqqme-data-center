<?php

namespace App\Console\Commands;

use App\Services\TaskCountService;
use Illuminate\Console\Command;

class TestWebSocket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:websocket {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test WebSocket broadcasting for task count updates';

    /**
     * Execute the console command.
     */
    public function handle(TaskCountService $taskCountService)
    {
        $userId = (int) $this->argument('user_id');

        $this->info("Testing WebSocket broadcast for user ID: {$userId}");

        // Get current count
        $count = $taskCountService->getActiveTaskCount($userId);
        $this->info("Current active task count: {$count}");

        // Broadcast update
        $this->info('Broadcasting task count update...');
        $taskCountService->broadcastTaskCountUpdate($userId);

        $this->info('WebSocket broadcast sent successfully!');
        $this->info('Check the browser console for WebSocket connection status.');

        return Command::SUCCESS;
    }
}
