<?php

namespace App\Http\Controllers;

use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpotifyPlayerController extends Controller
{
    public function __construct(private SpotifyService $spotifyService) {}

    /**
     * Get OAuth token for Spotify Web Playback SDK
     * This endpoint provides the access token needed by the SDK
     */
    public function getToken(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->hasSpotifyAuth()) {
            return response()->json([
                'error' => 'Spotify account not connected',
            ], 401);
        }

        // Check if token needs refresh
        if (! $user->spotify_access_token) {
            $refreshed = $this->spotifyService->refreshAccessToken($user);
            if (! $refreshed) {
                return response()->json([
                    'error' => 'Failed to refresh Spotify token',
                ], 401);
            }
            $user->refresh();
        }

        return response()->json([
            'access_token' => $user->spotify_access_token,
        ]);
    }

    /**
     * Get currently playing track
     * Used by pure Alpine.js component to avoid Livewire snapshot conflicts
     */
    public function getCurrentTrack(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->hasSpotifyAuth()) {
            return response()->json([
                'connected' => false,
                'track' => null,
            ]);
        }

        try {
            $track = $this->spotifyService->getCurrentlyPlaying($user);

            return response()->json([
                'connected' => true,
                'track' => $track,
            ]);
        } catch (\Exception $e) {
            \Log::error('Spotify getCurrentTrack error: '.$e->getMessage());

            return response()->json([
                'connected' => true,
                'track' => null,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Transfer playback to the web player device
     */
    public function transferPlayback(Request $request)
    {
        $user = Auth::user();

        if (! $user || ! $user->hasSpotifyAuth()) {
            return response()->json([
                'error' => 'Spotify account not connected',
            ], 401);
        }

        $deviceId = $request->input('device_id');

        if (! $deviceId) {
            return response()->json([
                'error' => 'Device ID required',
            ], 400);
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Authorization' => 'Bearer '.$user->spotify_access_token,
                'Content-Type' => 'application/json',
            ])->put('https://api.spotify.com/v1/me/player', [
                'device_ids' => [$deviceId],
                'play' => false, // Don't auto-play, just transfer
            ]);

            if ($response->successful() || $response->status() === 204) {
                return response()->json([
                    'success' => true,
                    'message' => 'Playback transferred to web player',
                ]);
            }

            return response()->json([
                'error' => 'Failed to transfer playback',
                'status' => $response->status(),
            ], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
