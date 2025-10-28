<?php

namespace App\Livewire;

use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SpotifyNowPlaying extends Component
{
    public $track = null;

    public $isLoading = false;

    public $hasError = false;

    public $notConnected = false;

    public $useWebPlaybackSdk = true; // Enable Web Playback SDK by default

    public $context = 'dropdown'; // 'dropdown' or 'modal'

    public $shouldLoad = false; // Track if component should load

    public function mount($context = 'dropdown')
    {
        $this->context = $context;

        // Don't load immediately - wait for lazy trigger
        // Loading is triggered when dropdown opens via Alpine.js
    }

    /**
     * Method to initialize and load the track data
     * Called via Alpine.js when dropdown panel opens
     */
    public function lazyLoad()
    {
        if ($this->shouldLoad) {
            return; // Already loading or loaded
        }

        $this->shouldLoad = true;
        $this->loadCurrentTrack();
    }

    /**
     * Update playback state from Web Playback SDK
     * Called via JavaScript when SDK detects state changes
     */
    public function updatePlaybackState($state)
    {
        $this->isLoading = false;
        $this->hasError = false;
        $this->notConnected = false;

        // If state is null or empty, nothing is playing
        if (! $state || empty($state)) {
            $this->track = null;

            return;
        }

        try {
            // Extract track info from SDK state
            $track = $state['track_window']['current_track'] ?? null;

            if (! $track) {
                $this->track = null;

                return;
            }

            $this->track = [
                'track_name' => $track['name'] ?? 'Unknown Track',
                'artist_name' => $this->extractArtistNames($track['artists'] ?? []),
                'album_name' => $track['album']['name'] ?? 'Unknown Album',
                'album_art' => $track['album']['images'][0]['url'] ?? null,
                'progress_ms' => $state['position'] ?? 0,
                'duration_ms' => $state['duration'] ?? 0,
                'progress_percentage' => $state['duration'] > 0 ? ($state['position'] / $state['duration']) * 100 : 0,
                'is_playing' => ! $state['paused'],
                'spotify_url' => $track['uri'] ?? null,
            ];

            // Dispatch event with track data for Alpine to sync (for wire:ignore contexts)
            $this->dispatch('spotify-track-updated', track: $this->track);

        } catch (\Exception $e) {
            \Log::error('Spotify Web Playback SDK state update error: '.$e->getMessage(), [
                'state' => $state,
            ]);
            $this->hasError = true;
            $this->track = null;
        }
    }

    /**
     * Fallback method for API polling (when SDK is not available)
     */
    public function loadCurrentTrack()
    {
        $this->isLoading = true;
        $this->hasError = false;

        try {
            $user = Auth::user();

            if (! $user || ! $user->hasSpotifyAuth()) {
                $this->notConnected = true;
                $this->hasError = false;
                $this->isLoading = false;
                $this->dispatch('track-updated');

                return;
            }

            $spotifyService = app(SpotifyService::class);
            $track = $spotifyService->getCurrentlyPlaying($user);

            // \Log::info('Spotify API Polling: Response received', [
            //     'has_track' => ! empty($track),
            //     'track_data' => $track,
            // ]);

            if ($track) {
                $this->track = $track;

                // \Log::info('Spotify API Polling: Track loaded successfully', [
                //     'track' => $track['track_name'],
                //     'artist' => $track['artist_name'],
                // ]);

                // Dispatch full track data to JavaScript for Alpine to sync
                $this->dispatch('spotify-track-loaded', track: $track);
            } else {
                $this->track = null;

                // \Log::info('Spotify API Polling: No track currently playing');

                // Dispatch to JavaScript for console logging
                $this->dispatch('spotify-no-track');
            }

        } catch (\Exception $e) {
            \Log::error('Spotify Now Playing Compact Error: '.$e->getMessage());
            $this->hasError = true;
            $this->track = null;
        }

        $this->isLoading = false;

        // Dispatch event to reschedule the next update
        $this->dispatch('track-updated');
    }

    public function refresh()
    {
        if ($this->useWebPlaybackSdk) {
            // When using SDK, dispatch event to JavaScript to refresh
            $this->dispatch('spotify-refresh-requested');
        } else {
            // Fallback to API polling
            $this->loadCurrentTrack();
        }
    }

    /**
     * Extract artist names from SDK artists array
     */
    private function extractArtistNames(array $artists): string
    {
        if (empty($artists)) {
            return 'Unknown Artist';
        }

        $names = array_map(fn ($artist) => $artist['name'] ?? 'Unknown', $artists);

        return implode(', ', $names);
    }

    public function render()
    {
        return view('livewire.spotify-now-playing');
    }
}
