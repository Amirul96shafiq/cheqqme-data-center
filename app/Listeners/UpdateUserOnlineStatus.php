<?php

namespace App\Listeners;

use App\Services\OnlineStatusTracker;
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
            OnlineStatusTracker::setUserOnline($event->user);
        } elseif ($event instanceof Logout) {
            // User logged out - set to invisible
            OnlineStatusTracker::setUserInvisible($event->user);
        }
    }
}
