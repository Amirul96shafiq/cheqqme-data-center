<div 
    x-data="{
        refreshInterval: null,
        init() {
            // Listen for refresh commands
            this.$wire.on('start-spotify-refresh', () => {
                this.startRefresh();
            });
            
            this.$wire.on('stop-spotify-refresh', () => {
                this.stopRefresh();
            });
            
            // Start refresh if auto-refresh is enabled
            if (this.$wire.autoRefresh) {
                this.startRefresh();
            }
        },
        startRefresh() {
            this.stopRefresh(); // Clear any existing interval
            this.refreshInterval = setInterval(() => {
                this.$wire.refresh();
            }, 30000); // Refresh every 30 seconds
        },
        stopRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        }
    }"
    class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg border border-green-200 dark:border-green-800 p-4"
>
    <!-- Header -->
    <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
            <img src="{{ asset('images/spotify-icon.svg') }}" alt="Spotify" class="w-5 h-5 text-green-600">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm">
                @if($track && $track['is_playing'])
                    Listening to Spotify
                @elseif($track && !$track['is_playing'])
                    Paused on Spotify
                @else
                    Spotify
                @endif
            </h3>
        </div>
        
        <div class="flex items-center gap-2">
            @if($track)
                <button 
                    wire:click="refresh" 
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    title="Refresh"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            @endif
            
            <button 
                wire:click="toggleAutoRefresh" 
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                :class="{ 'text-green-600 dark:text-green-400': autoRefresh }"
                :title="autoRefresh ? 'Auto-refresh enabled' : 'Auto-refresh disabled'"
                x-text="autoRefresh ? 'Auto-refresh enabled' : 'Auto-refresh disabled'"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Loading State -->
    @if($isLoading)
        <div class="flex items-center justify-center py-8">
            <div class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span class="text-sm">Loading...</span>
            </div>
        </div>
    @elseif($hasError)
        <!-- Error State -->
        <div class="text-center py-4">
            <div class="text-red-500 dark:text-red-400 text-sm mb-2">
                <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Failed to load Spotify data
            </div>
            <button 
                wire:click="refresh" 
                class="text-xs text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition-colors"
            >
                Try again
            </button>
        </div>
    @elseif($track)
        <!-- Currently Playing Track -->
        <div class="flex items-center gap-3">
            <!-- Album Art -->
            <div class="flex-shrink-0">
                @if($track['album_art'])
                    <img 
                        src="{{ $track['album_art'] }}" 
                        alt="{{ $track['album_name'] }}" 
                        class="w-12 h-12 rounded-md shadow-sm"
                    >
                @else
                    <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                    </div>
                @endif
            </div>
            
            <!-- Track Info -->
            <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate">
                    {{ $track['track_name'] }}
                </h4>
                <p class="text-xs text-gray-600 dark:text-gray-400 truncate">
                    {{ $track['artist_name'] }}
                </p>
                
                <!-- Progress Bar -->
                @if($track['duration_ms'] > 0)
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
                            <div 
                                class="bg-green-600 h-1 rounded-full transition-all duration-1000" 
                                style="width: {{ $track['progress_percentage'] }}%"
                            ></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-1">
                            <span>{{ gmdate('i:s', $track['progress_ms'] / 1000) }}</span>
                            <span>{{ gmdate('i:s', $track['duration_ms'] / 1000) }}</span>
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Play/Pause Icon -->
            <div class="flex-shrink-0">
                @if($track['is_playing'])
                    <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </div>
                @else
                    <div class="w-6 h-6 border-2 border-gray-400 dark:border-gray-500 rounded-full flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-400 dark:text-gray-500 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Spotify Link -->
        @if($track['spotify_url'])
            <div class="mt-3 pt-3 border-t border-green-200 dark:border-green-700">
                <a 
                    href="{{ $track['spotify_url'] }}" 
                    target="_blank" 
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-1 text-xs text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition-colors"
                >
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.42 1.56-.299.421-1.02.599-1.559.3z"/>
                    </svg>
                    Open in Spotify
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
            </div>
        @endif
    @else
        <!-- No Track Playing -->
        <div class="text-center py-6">
            <div class="text-gray-500 dark:text-gray-400 mb-2">
                <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                </svg>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                No music playing on Spotify
            </p>
            <button 
                wire:click="refresh" 
                class="text-xs text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 transition-colors"
            >
                Refresh
            </button>
        </div>
    @endif
</div>