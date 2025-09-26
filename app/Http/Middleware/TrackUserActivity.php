<?php

namespace App\Http\Middleware;

use App\Services\OnlineStatusTracker;
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
            
            // Update user activity
            OnlineStatusTracker::updateUserActivity($user);
            
            // Check and update user status if needed
            OnlineStatusTracker::checkAndUpdateUserStatus($user);
        }

        return $next($request);
    }
}
