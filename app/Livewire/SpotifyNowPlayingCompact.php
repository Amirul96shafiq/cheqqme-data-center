<?php

namespace App\Livewire;

use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SpotifyNowPlayingCompact extends Component
{
    public $track = null;

    public $isLoading = true;

    public $hasError = false;

    public function mount()
    {
        $this->loadCurrentTrack();
    }

    public function loadCurrentTrack()
    {
        $this->isLoading = true;
        $this->hasError = false;

        try {
            $user = Auth::user();

            if (! $user || ! $user->hasSpotifyAuth()) {
                $this->hasError = true;
                $this->isLoading = false;
                $this->dispatch('track-updated');

                return;
            }

            $spotifyService = app(SpotifyService::class);
            $track = $spotifyService->getCurrentlyPlaying($user);

            if ($track) {
                $this->track = $track;
            } else {
                $this->track = null;
            }

        } catch (\Exception $e) {
            \Log::error('Spotify Now Playing Compact Error: '.$e->getMessage());
            $this->hasError = true;
            $this->track = null;
        }

        $this->isLoading = false;

        // Dispatch event to reschedule the next update based on new track data
        $this->dispatch('track-updated');
    }

    public function refresh()
    {
        $this->loadCurrentTrack();
    }

    public function render()
    {
        return view('livewire.spotify-now-playing-compact');
    }
}
