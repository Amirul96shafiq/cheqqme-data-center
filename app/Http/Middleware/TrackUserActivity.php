<?php

namespace App\Http\Middleware;

use App\Services\OnlineStatus\StatusController;
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
            
            // Handle page refresh - set auto-away users back to online
            StatusController::handlePageRefresh($user);
            
            // Only record activity for real user interactions, not background requests
            if ($this->isRealUserInteraction($request)) {
                StatusController::checkAndUpdateStatus($user);
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
