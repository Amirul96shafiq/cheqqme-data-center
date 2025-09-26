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
            
            // Update user activity and check status
            StatusController::checkAndUpdateStatus($user);
        }

        return $next($request);
    }
}
