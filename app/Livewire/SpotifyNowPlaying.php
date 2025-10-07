<?php

namespace App\Livewire;

use App\Services\SpotifyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SpotifyNowPlaying extends Component
{
    public $track = null;

    public $isLoading = true;

    public $hasError = false;

    public $autoRefresh = true;

    protected $spotifyService;

    public function boot(SpotifyService $spotifyService)
    {
        $this->spotifyService = $spotifyService;
    }

    public function mount()
    {
        $this->loadCurrentlyPlaying();

        // Set up auto-refresh every 30 seconds if auto-refresh is enabled
        if ($this->autoRefresh) {
            $this->dispatch('start-spotify-refresh');
        }
    }

    public function loadCurrentlyPlaying()
    {
        $this->isLoading = true;
        $this->hasError = false;

        try {
            $user = Auth::user();

            if (! $user || ! $user->hasSpotifyAuth()) {
                $this->track = null;
                $this->isLoading = false;

                return;
            }

            $track = $this->spotifyService->getCurrentlyPlaying($user);
            $this->track = $track;
            $this->isLoading = false;

        } catch (\Exception $e) {
            $this->hasError = true;
            $this->isLoading = false;
            $this->track = null;
        }
    }

    public function refresh()
    {
        $this->loadCurrentlyPlaying();
    }

    // public function toggleAutoRefresh()
    // {
    //     $this->autoRefresh = ! $this->autoRefresh;

    //     if ($this->autoRefresh) {
    //         $this->dispatch('start-spotify-refresh');
    //     } else {
    //         $this->dispatch('stop-spotify-refresh');
    //     }
    // }

    public function render()
    {
        return view('livewire.spotify-now-playing');
    }
}
