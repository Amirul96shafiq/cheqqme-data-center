@props(['user', 'context' => 'modal', 'modalId' => null])

@if($user->hasSpotifyAuth())
<div 
    class="spotify-now-playing-alpine"
    x-data="spotifyPlayerAlpine('{{ $context }}', {{ $user->id }}, @if($context === 'modal') false @else true @endif, '{{ $modalId }}')"
    @if($context === 'modal')
        @modal-show.window="
            const eventModalId = $event.detail.modalId;
            const matchesModal = eventModalId === componentModalId;
            {{-- console.log('🎵 Modal show event check', { 
                userId: {{ $user->id }}, 
                eventUserId: $event.detail.userId,
                eventModalId,
                componentModalId,
                matchesModal
            }); --}}
            if ($event.detail.userId === {{ $user->id }} && matchesModal) {
                {{-- console.log('🎵 ✅ This is MY modal, initializing player'); --}}
                if (typeof initPlayer === 'function' && !initialized) {
                    initPlayer();
                    initialized = true;
                }
                onModalShow();
            }
        "
        @modal-hide.window="
            if ($event.detail.userId === {{ $user->id }} && $event.detail.modalId === componentModalId) {
                onModalHide();
            }
        "
    @else
        x-intersect="(function(){ if (typeof initPlayer==='function' && !initialized){ initPlayer(); initialized=true; } else if (typeof resumePolling==='function'){ resumePolling(); } })()"
        x-intersect.leave="(function(){ if (typeof pausePolling==='function'){ pausePolling(); } })()"
        @modal-show.window="
            if ($event.detail.userId === {{ $user->id }}) {
                pausePolling();
            }
        "
        @modal-hide.window="
            if ($event.detail.userId === {{ $user->id }}) {
                resumePolling();
            }
        "
    @endif
>
    <!-- Loading State -->
    <div x-show="isLoading" class="flex items-center justify-center gap-2 text-gray-500 dark:text-gray-400 h-32">
        <x-icons.custom-icon name="loading" class="w-4 h-4 animate-spin" />
        <span class="text-xs">{{ __('spotify.status.loading') }}</span>
    </div>

    <!-- Error State -->
    <div x-show="hasError && !isLoading" x-cloak class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
        </svg>
        <span class="text-xs">{{ __('spotify.status.spotify_unavailable') }}</span>
    </div>

    <!-- Playing Track -->
    <div x-show="track && !isLoading" x-cloak class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
        <!-- Header -->
        <div class="flex items-center justify-between mb-1.5">
            <div class="flex items-center gap-1">
                <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                </svg>
                <span 
                    x-text="track && track.is_playing ? '{{ $context === 'modal' ? __('spotify.play.currently_playing') : __('spotify.play.now_playing') }}' : '{{ __('spotify.play.paused') }}'" 
                    :class="track && track.is_playing ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'"
                    class="text-[10px] font-normal"
                ></span>
            </div>
        </div>

        <!-- Track Info -->
        <div class="flex items-center gap-3">
            <!-- Album Cover -->
            <div class="shrink-0">
                <img 
                    :src="track ? track.album_art : ''" 
                    :alt="track ? track.album_name + ' cover' : ''"
                    :title="track ? track.track_name + ' by ' + track.artist_name + ' on ' + track.album_name : ''"
                    class="w-20 h-20 rounded-md object-cover"
                    draggable="false"
                    {{-- onerror="this.style.display='none'" --}} {{-- this is causing the image to not load --}}
                >
            </div>
            
            <!-- Track Details -->
            <div class="flex-1 min-w-0 text-left">
                <!-- Track Name -->
                <div 
                    x-text="track ? track.track_name : ''" 
                    :title="track ? track.track_name : ''"
                    class="text-sm font-medium text-gray-900 dark:text-white truncate text-left"
                ></div>
                
                <!-- Artist -->
                <div 
                    x-text="track ? track.artist_name : ''" 
                    :title="track ? track.artist_name : ''"
                    class="text-xs text-gray-600 dark:text-gray-400 truncate text-left"
                ></div>

                <!-- Progress Bar -->
                <template x-if="track && track.duration_ms">
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                            <div 
                                class="h-1 rounded-full transition-all duration-100"
                                :class="track && track.is_playing ? 'bg-green-500' : 'bg-yellow-500'"
                                :style="'width: ' + progressPercentage + '%'"
                            ></div>
                        </div>
                        <div class="flex justify-between text-[10px] text-gray-500/50 dark:text-gray-400/50 mt-1">
                            <span x-text="formatTime(trackPosition)"></span>
                            <span x-text="track ? formatTime(track.duration_ms) : '00:00'"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- No Track Playing -->
    <div x-show="!track && !isLoading && !hasError" x-cloak class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
        <!-- Header -->
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

</div>
@endif

