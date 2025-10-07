<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SpotifyService
{
    private const SPOTIFY_API_BASE = 'https://api.spotify.com/v1';

    /**
     * Get currently playing track for a user
     */
    public function getCurrentlyPlaying(User $user): ?array
    {
        if (! $user->hasSpotifyAuth() || ! $user->spotify_access_token) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$user->spotify_access_token,
                'Content-Type' => 'application/json',
            ])->get(self::SPOTIFY_API_BASE.'/me/player/currently-playing');

            if ($response->successful()) {
                $data = $response->json();

                // If no track is currently playing, return null
                if (empty($data['item'])) {
                    return null;
                }

                $track = $data['item'];
                $progress = $data['progress_ms'] ?? 0;
                $duration = $track['duration_ms'] ?? 0;

                return [
                    'track_name' => $track['name'],
                    'artist_name' => $this->getArtistName($track['artists']),
                    'album_name' => $track['album']['name'],
                    'album_art' => $this->getAlbumArt($track['album']['images']),
                    'progress_ms' => $progress,
                    'duration_ms' => $duration,
                    'progress_percentage' => $duration > 0 ? ($progress / $duration) * 100 : 0,
                    'is_playing' => $data['is_playing'] ?? false,
                    'spotify_url' => $track['external_urls']['spotify'] ?? null,
                ];
            } elseif ($response->status() === 401) {
                // Token expired, try to refresh
                $this->refreshAccessToken($user);

                // Retry once with new token
                return $this->getCurrentlyPlaying($user);
            }

            Log::warning('Spotify API error', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Spotify API exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get user's playback state
     */
    public function getPlaybackState(User $user): ?array
    {
        if (! $user->hasSpotifyAuth() || ! $user->spotify_access_token) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$user->spotify_access_token,
                'Content-Type' => 'application/json',
            ])->get(self::SPOTIFY_API_BASE.'/me/player');

            if ($response->successful()) {
                return $response->json();
            } elseif ($response->status() === 401) {
                $this->refreshAccessToken($user);

                return $this->getPlaybackState($user);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Spotify playback state API exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Refresh the user's Spotify access token
     */
    public function refreshAccessToken(User $user): bool
    {
        if (! $user->spotify_refresh_token) {
            return false;
        }

        try {
            $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $user->spotify_refresh_token,
                'client_id' => config('services.spotify.client_id'),
                'client_secret' => config('services.spotify.client_secret'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $user->update([
                    'spotify_access_token' => $data['access_token'],
                ]);

                // Update refresh token if provided
                if (isset($data['refresh_token'])) {
                    $user->update([
                        'spotify_refresh_token' => $data['refresh_token'],
                    ]);
                }

                return true;
            }

            Log::warning('Failed to refresh Spotify token', [
                'user_id' => $user->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Spotify token refresh exception', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Extract artist name from artists array
     */
    private function getArtistName(array $artists): string
    {
        if (empty($artists)) {
            return 'Unknown Artist';
        }

        $names = array_map(fn ($artist) => $artist['name'], $artists);

        return implode(', ', $names);
    }

    /**
     * Get the best available album art URL
     */
    private function getAlbumArt(array $images): ?string
    {
        if (empty($images)) {
            return null;
        }

        // Sort by size (width) and get the medium-sized image (around 300px)
        usort($images, fn ($a, $b) => $a['width'] <=> $b['width']);

        // Find the closest to 300px or return the middle-sized image
        foreach ($images as $image) {
            if ($image['width'] >= 300) {
                return $image['url'];
            }
        }

        // Fallback to the largest image
        return end($images)['url'] ?? null;
    }
}
