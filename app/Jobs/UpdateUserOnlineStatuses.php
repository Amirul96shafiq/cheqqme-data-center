<?php

namespace App\Jobs;

use App\Services\OnlineStatus\StatusController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class UpdateUserOnlineStatuses implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            StatusController::processAllStatusUpdates();
        } catch (\Exception $e) {
            Log::error('Failed to update user online statuses: ' . $e->getMessage());
            throw $e;
        }
    }
}
