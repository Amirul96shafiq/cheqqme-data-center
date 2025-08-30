<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->bearerToken();

        // Check if API key is provided
        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key is required. Please provide it in the Authorization header as: Bearer YOUR_API_KEY',
            ], 401);
        }

        // Find user by API key
        $user = User::where('api_key', $apiKey)->first();

        // Check if user exists
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid API key provided.',
            ], 401);
        }

        // Authenticate the user for this request using Laravel's built-in authentication system
        auth()->login($user);

        return $next($request);
    }
}
