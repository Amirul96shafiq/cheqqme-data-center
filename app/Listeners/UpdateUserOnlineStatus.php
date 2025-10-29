<?php

namespace App\Listeners;

use App\Services\OnlineStatus\PresenceStatusManager;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateUserOnlineStatus implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(Login|Logout $event): void
    {
        /** @var \App\Models\User $user */
        $user = $event->user;

        if ($event instanceof Login) {
            // User logged in - set to online (force override any previous manual status)
            PresenceStatusManager::setOnline($user, forceOverride: true);
        } elseif ($event instanceof Logout) {
            // User logged out - set to invisible
            PresenceStatusManager::setInvisible($user);

            // SECURITY: Clear remember_token on logout to ensure old remember sessions are invalidated
            // This is important for security - the user has explicitly logged out
            $userId = $user->getAuthIdentifier();
            $oldToken = $user->getRememberToken();

            Log::info('Logout: clearing remember_token', [
                'user_id' => $userId,
                'old_token' => $oldToken,
            ]);

            DB::table('users')
                ->where('id', $userId)
                ->update(['remember_token' => null]);

            Log::info('Logout: remember_token cleared', [
                'user_id' => $userId,
            ]);
        }
    }
}
