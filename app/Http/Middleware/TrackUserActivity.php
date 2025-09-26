<?php

namespace App\Http\Middleware;

use App\Services\OnlineStatus\PresenceStatusManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track activity for authenticated users
        if (Auth::check()) {
            $user = Auth::user();

            // With presence channels, we don't need complex activity tracking
            // The presence channel automatically handles join/leave events
            // We only need to ensure the user is marked as online when they're active
            if ($this->isRealUserInteraction($request) && $user->online_status !== 'invisible') {
                // Only set to online if user is currently away (auto-away)
                // Don't override manual status changes (dnd, away, etc.)
                if ($user->online_status === 'away' && ! $user->last_status_change) {
                    // This is an auto-away user, set them back to online
                    PresenceStatusManager::setOnline($user);
                }
            }
        }

        return $next($request);
    }

    /**
     * Check if this is a real user interaction (not background/AJAX request)
     */
    private function isRealUserInteraction(Request $request): bool
    {
        // Don't record activity for AJAX requests unless they're user interactions
        if ($request->ajax()) {
            // Only record activity for specific user interaction routes
            $userInteractionRoutes = [
                'profile.update-online-status',
                'profile.track-activity',
            ];

            return in_array($request->route()?->getName(), $userInteractionRoutes);
        }

        // Record activity for regular page requests
        return true;
    }
}
