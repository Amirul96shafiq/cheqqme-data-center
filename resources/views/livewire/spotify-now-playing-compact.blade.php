<div class="spotify-now-playing-compact">
    <!-- Loading State -->
    @if($isLoading)
        <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            <span class="text-xs">Loading...</span>
        </div>
    @elseif($hasError)
        <!-- Error State -->
        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
            <span class="text-xs">Spotify unavailable</span>
        </div>
    @elseif($track)
        <!-- Playing Track -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-2 border border-gray-200 dark:border-gray-700">
            <!-- Header with Spotify icon and status -->
            <div class="flex items-center justify-between mb-1">
                <div class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    @if($track['is_playing'])
                        <span class="text-xs text-green-600 dark:text-green-400 font-medium">Now Playing</span>
                    @else
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Paused</span>
                    @endif
                </div>
                
                <!-- Refresh button -->
                <button 
                    wire:click="refresh" 
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    title="Refresh"
                >
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>

            <!-- Track Info -->
            <div class="space-y-1">
                <!-- Track Name -->
                <div class="text-xs font-medium text-gray-900 dark:text-white truncate" title="{{ $track['track_name'] }}">
                    {{ $track['track_name'] }}
                </div>
                
                <!-- Artist Name -->
                <div class="text-xs text-gray-600 dark:text-gray-400 truncate" title="{{ $track['artist_name'] }}">
                    {{ $track['artist_name'] }}
                </div>
            </div>

            <!-- Progress Bar (if playing) -->
            @if($track['is_playing'] && isset($track['progress_ms']) && isset($track['duration_ms']))
                <div class="mt-2">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                        <div 
                            class="bg-green-500 h-1 rounded-full transition-all duration-1000" 
                            style="width: {{ $track['progress_percentage'] }}%"
                        ></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span>{{ gmdate('i:s', floor($track['progress_ms'] / 1000)) }}</span>
                        <span>{{ gmdate('i:s', floor($track['duration_ms'] / 1000)) }}</span>
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- No Track Playing -->
        <div class="flex items-center gap-2 text-gray-400 dark:text-gray-500">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
            </svg>
            <span class="text-xs">Nothing playing</span>
        </div>
    @endif
</div>