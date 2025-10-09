<div 
    class="spotify-now-playing"
    x-data="spotifyPlayer"
    x-init="initPlayer()"
    @spotify-refresh-requested.window="refreshPlayer()"
    @track-updated.window="scheduleNextPollingUpdate()"
    wire:ignore.self
>
    <!-- Loading State -->
    @if($isLoading)
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span class="text-xs">{{ __('spotify.status.loading') }}</span>
        </div>
    @elseif($notConnected)
        {{-- User not connected to Spotify - don't show anything --}}
    @elseif($hasError)

        <!-- Error State -->
        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
            <span class="text-xs">{{ __('spotify.status.spotify_unavailable') }}</span>
        </div>

    @elseif($track)

        <!-- Playing Track -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">

            <!-- Header with Spotify icon and status -->
            <div class="flex items-center justify-between mb-1.5">
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    @if($track['is_playing'])
                        <span class="text-[10px] font-normal text-green-600 dark:text-green-400">{{ __('spotify.play.now_playing') }}</span>
                    @else
                        <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">{{ __('spotify.play.paused') }}</span>
                    @endif  
                </div>
                
                <!-- Refresh button -->
                {{-- <button 
                    wire:click="refresh" 
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    title="Refresh"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button> --}}
            </div>

            <!-- Track Info with Album Cover -->
            <div class="flex items-center gap-3">

                <!-- Album Cover -->
                <div class="shrink-0">
                    <img 
                        src="{{ $track['album_art'] }}" 
                        alt="{{ $track['album_name'] }} cover" 
                        title="{{ __('spotify.tooltip.album_info', ['track' => $track['track_name'], 'artist' => $track['artist_name'], 'album' => $track['album_name']]) }}"
                        class="w-20 h-20 rounded-md object-cover"
                        onerror="this.style.display='none'"
                        draggable="false"
                    >
                </div>
                
                <!-- Track Details -->
                <div class="flex-1 min-w-0 text-left">
                    
                    <!-- Track Name -->
                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate text-left" title="{{ $track['track_name'] }}">
                        {{ $track['track_name'] }}
                    </div>
                    
                    <!-- Artist Name -->
                    <div class="text-xs text-gray-600 dark:text-gray-400 truncate text-left" title="{{ $track['artist_name'] }}">
                        {{ $track['artist_name'] }}
                    </div>

                     <!-- Progress Bar (Client-side Smooth Tracking) -->
                     @if(isset($track['progress_ms']) && isset($track['duration_ms']))
                         <div class="mt-4">
                           <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                                <div 
                                    class="spotify-progress-bar h-1 rounded-full transition-all duration-100 {{ $track['is_playing'] ? 'bg-green-500' : 'bg-yellow-500' }}" 
                                    style="width: {{ $track['progress_percentage'] }}%"
                                ></div>
                            </div>
                             <div class="flex justify-between text-[10px] text-gray-500/50 dark:text-gray-400/50 mt-1">
                                 <span class="spotify-current-time">{{ sprintf('%02d:%02d', floor($track['progress_ms'] / 60000), floor(($track['progress_ms'] % 60000) / 1000)) }}</span>
                                 <span class="spotify-duration-time">{{ sprintf('%02d:%02d', floor($track['duration_ms'] / 60000), floor(($track['duration_ms'] % 60000) / 1000)) }}</span>
                             </div>
                        </div>
                    @endif

                </div>

            </div>

            
        </div>
    @else

        <!-- No Track Playing -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">

            <!-- Header with Spotify icon -->
            <div class="flex items-center justify-center mb-1.5">
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    <span class="text-[10px] font-normal text-gray-500 dark:text-gray-400">Spotify</span>
                </div>
            </div>
            
            <!-- No Track Message -->
            <div class="flex items-center justify-center text-gray-400 dark:text-gray-500">
                <span class="text-sm">{{ __('spotify.status.nothing_playing') }}</span>
            </div>

</div>

    @endif

    <!-- Spotify Web Playback SDK Integration -->
    @vite('resources/js/spotify-player.js')
</div>