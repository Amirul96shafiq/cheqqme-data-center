<?php

namespace App\Listeners;

use App\Services\OnlineStatus\StatusController;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateUserOnlineStatus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(Login|Logout $event): void
    {
        if ($event instanceof Login) {
            // User logged in - set to online
            StatusController::setOnline($event->user);
        } elseif ($event instanceof Logout) {
            // User logged out - set to invisible
            StatusController::setInvisible($event->user);
        }
    }
}
