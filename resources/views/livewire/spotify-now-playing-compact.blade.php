<div 
    class="spotify-now-playing-compact"
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
    <!-- SDK script is preloaded in app head for faster initialization -->
    <script>
        // Alpine.js component for Spotify Web Playback SDK
        document.addEventListener('alpine:init', () => {
            Alpine.data('spotifyPlayer', () => ({
                player: null,
                deviceId: null,
                currentState: null,
                updateInterval: null,
                pollingTimeout: null,
                progressInterval: null,
                sdkCheckTimeout: null,
                sdkFallbackTimeout: null,
                isSDKReady: false,
                isUsingSDK: @js($useWebPlaybackSdk),
                
                // Client-side progress tracking for smooth updates
                trackStartTime: null,
                trackPosition: 0,
                trackDuration: 0,
                isPlaying: false,
                lastSyncTime: null,

                async initPlayer() {
                    console.log('🎵 Spotify Web Playback SDK: Initializing...');
                    
                    // Listen for Livewire events
                    window.addEventListener('spotify-track-loaded', (event) => {
                        console.log('🎵 Track loaded from API:', event.detail.track);
                        this.syncProgressFromServer();
                    });
                    
                    window.addEventListener('spotify-no-track', () => {
                        console.log('💤 API returned: No track playing');
                        this.stopProgressTracking();
                    });
                    
                    // Check if SDK is already available (preloaded in head)
                    if (window.Spotify) {
                        console.log('✅ Spotify SDK already loaded!');
                        this.isSDKReady = true;
                        this.initializePlayer();
                    } else {
                        // Wait for SDK to be ready
                        this.waitForSDK();
                    }
                },

                waitForSDK() {
                    // Check if SDK is already loaded
                    if (window.Spotify) {
                        console.log('🎵 Spotify SDK already available');
                        this.isSDKReady = true;
                        this.initializePlayer();
                        return;
                    }

                    // Set up the callback FIRST before any timeouts
                    if (!window.onSpotifyWebPlaybackSDKReady) {
                        window.onSpotifyWebPlaybackSDKReady = () => {
                            console.log('🎵 Spotify Web Playback SDK: Ready');
                            this.isSDKReady = true;
                            
                            // Clear any pending timeout checks
                            if (this.sdkCheckTimeout) clearTimeout(this.sdkCheckTimeout);
                            if (this.sdkFallbackTimeout) clearTimeout(this.sdkFallbackTimeout);
                            
                            this.initializePlayer();
                        };
                    }

                    // Wait for SDK to load (preloaded in head, should be quick)
                    this.sdkCheckTimeout = setTimeout(() => {
                        if (!this.isSDKReady) {
                            console.log('⏳ Spotify SDK still loading...');
                            
                            // Final check after additional time
                            this.sdkFallbackTimeout = setTimeout(() => {
                                if (!this.isSDKReady) {
                                    console.info('💡 SDK not available, continuing with API polling');
                                    this.isUsingSDK = false;
                                    @this.set('useWebPlaybackSdk', false);
                                    // Trigger polling to start
                                    this.scheduleNextPollingUpdate();
                                }
                            }, 3000); // Additional 3 seconds (reduced from 5s)
                        }
                    }, 1500); // Initial check at 1.5 seconds (reduced from 3s)
                },

                async initializePlayer() {
                    try {
                        // Get access token
                        const tokenResponse = await fetch('/api/spotify/token', {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (!tokenResponse.ok) {
                            console.error('❌ Failed to get Spotify token:', tokenResponse.status);
                            @this.set('hasError', true);
                            @this.set('isLoading', false);
                            return;
                        }

                        const { access_token } = await tokenResponse.json();

                        // Initialize player
                        this.player = new Spotify.Player({
                            name: 'CheQQme Web Player',
                            getOAuthToken: cb => { cb(access_token); },
                            volume: 0.5
                        });

                        // Setup event listeners
                        this.setupPlayerListeners();

                        // Connect to player
                        const connected = await this.player.connect();
                        
                        if (connected) {
                            console.log('✅ Spotify Web Playback SDK: Connected successfully');
                        } else {
                            console.error('❌ Failed to connect to Spotify');
                            @this.set('hasError', true);
                        }

                    } catch (error) {
                        console.error('❌ Spotify SDK initialization error:', error);
                        console.info('💡 Continuing with API polling mode...');
                        
                        // Fallback to API polling (already loaded on mount)
                        this.isUsingSDK = false;
                        @this.set('useWebPlaybackSdk', false);
                        // Trigger polling to start
                        this.scheduleNextPollingUpdate();
                    }
                },

                setupPlayerListeners() {
                    // Ready event - device ID is available
                    this.player.addListener('ready', ({ device_id }) => {
                        console.log('✅ Ready with Device ID:', device_id);
                        this.deviceId = device_id;
                        
                        // Start polling for state updates
                        this.startStatePolling();
                        
                        // Check for playback on web player after 2 seconds
                        setTimeout(async () => {
                            const state = await this.player.getCurrentState();
                            if (!state && this.isUsingSDK) {
                                console.warn('⚠️ No playback on web player');
                                console.info('ℹ️ SDK only tracks playback IN the browser');
                                console.info('📱 Using API polling to track playback from all devices...');
                                console.info('🎧 To use web player: Transfer playback to "CheQQme Web Player" in Spotify');
                                this.isUsingSDK = false;
                                @this.set('useWebPlaybackSdk', false);
                                // Trigger polling to start
                                this.scheduleNextPollingUpdate();
                            }
                        }, 2000);  // Reduced from 3000ms to 2000ms
                    });

                    // Not Ready
                    this.player.addListener('not_ready', ({ device_id }) => {
                        console.warn('⚠️ Device ID has gone offline:', device_id);
                    });

                    // Player state changed - REAL-TIME UPDATES!
                    this.player.addListener('player_state_changed', state => {
                        if (!state) {
                            console.log('🔇 No playback detected');
                            @this.call('updatePlaybackState', null);
                            return;
                        }

                        console.log('🎵 Player state changed:', state);
                        this.currentState = state;
                        
                        // Update Livewire component with new state
                        @this.call('updatePlaybackState', state);
                    });

                    // Initialization error
                    this.player.addListener('initialization_error', ({ message }) => {
                        console.error('❌ Initialization Error:', message);
                        @this.set('hasError', true);
                    });

                    // Authentication error
                    this.player.addListener('authentication_error', ({ message }) => {
                        console.error('❌ Authentication Error:', message);
                        
                        if (message.includes('token') || message.includes('scope')) {
                            console.error('🔑 Your Spotify account needs to be reconnected with new permissions!');
                            console.info('📋 Steps to fix:');
                            console.info('   1. Go to your Profile settings');
                            console.info('   2. Disconnect Spotify');
                            console.info('   3. Reconnect Spotify');
                            console.info('   4. Grant the "streaming" permission');
                            
                            // This is a real error - show error state
                            @this.set('hasError', true);
                        }
                        
                        console.info('🔄 Switching to API polling mode...');
                        
                        // Fallback to API polling (already loaded on mount)
                        this.isUsingSDK = false;
                        @this.set('useWebPlaybackSdk', false);
                        // Trigger polling to start
                        this.scheduleNextPollingUpdate();
                    });

                    // Account error (e.g., not Premium)
                    this.player.addListener('account_error', ({ message }) => {
                        console.error('❌ Account Error:', message);
                        if (message.includes('premium')) {
                            console.warn('💡 Spotify Web Playback SDK requires Premium');
                        }
                        console.info('🔄 Switching to API polling to track playback from all devices...');
                        
                        // Fallback to API polling (already loaded on mount)
                        // Don't set hasError - this is expected behavior
                        this.isUsingSDK = false;
                        @this.set('useWebPlaybackSdk', false);
                        // Trigger polling to start
                        this.scheduleNextPollingUpdate();
                    });

                    // Playback error
                    this.player.addListener('playback_error', ({ message }) => {
                        console.error('❌ Playback Error:', message);
                    });
                },

                startStatePolling() {
                    // Poll for state updates every 1 second for smooth progress bar
                    if (this.updateInterval) {
                        clearInterval(this.updateInterval);
                    }

                    this.updateInterval = setInterval(async () => {
                        const state = await this.player.getCurrentState();
                        if (state) {
                            @this.call('updatePlaybackState', state);
                        }
                    }, 1000);
                },

                async refreshPlayer() {
                    console.log('🔄 Refreshing player state...');
                    if (this.player) {
                        const state = await this.player.getCurrentState();
                        if (state) {
                            @this.call('updatePlaybackState', state);
                        } else {
                            @this.call('updatePlaybackState', null);
                        }
                    }
                },

                async transferPlayback() {
                    if (!this.deviceId) {
                        console.warn('⚠️ No device ID available for transfer');
                        return;
                    }

                    try {
                        const response = await fetch('/api/spotify/transfer-playback', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({
                                device_id: this.deviceId
                            })
                        });

                        if (response.ok) {
                            console.log('✅ Playback transferred to web player');
                        } else {
                            console.error('❌ Failed to transfer playback:', response.status);
                        }
                    } catch (error) {
                        console.error('❌ Transfer playback error:', error);
                    }
                },

                // Sync progress from server data (called after API response)
                syncProgressFromServer() {
                    const track = @this.track;
                    
                    if (!track || !track.progress_ms || !track.duration_ms) {
                        this.stopProgressTracking();
                        return;
                    }

                    // Update track data
                    this.trackPosition = track.progress_ms;
                    this.trackDuration = track.duration_ms;
                    this.isPlaying = track.is_playing;
                    this.lastSyncTime = Date.now();

                    console.log('⏱️ Synced position:', Math.floor(this.trackPosition / 1000) + 's / ' + Math.floor(this.trackDuration / 1000) + 's');

                    // Start smooth progress tracking
                    this.startProgressTracking();
                },

                // Start smooth client-side progress tracking
                startProgressTracking() {
                    // Clear any existing interval
                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                    }

                    // Update progress every 100ms for smooth animation
                    this.progressInterval = setInterval(() => {
                        if (this.isPlaying && this.trackPosition < this.trackDuration) {
                            // Calculate elapsed time since last sync
                            const now = Date.now();
                            const elapsed = now - this.lastSyncTime;
                            
                            // Interpolate position
                            this.trackPosition = @this.track.progress_ms + elapsed;
                            
                            // Prevent going over duration
                            if (this.trackPosition >= this.trackDuration) {
                                this.trackPosition = this.trackDuration;
                            }

                            // Update the UI (this will trigger Alpine reactivity)
                            this.$nextTick(() => {
                                this.updateProgressBar();
                            });
                        }
                    }, 100); // Update every 100ms for smooth animation
                },

                // Stop progress tracking
                stopProgressTracking() {
                    if (this.progressInterval) {
                        clearInterval(this.progressInterval);
                        this.progressInterval = null;
                    }
                    this.trackPosition = 0;
                    this.trackDuration = 0;
                    this.isPlaying = false;
                },

                // Update progress bar (will be called by Alpine reactivity)
                updateProgressBar() {
                    const percentage = (this.trackPosition / this.trackDuration) * 100;
                    const progressBar = this.$el.querySelector('.spotify-progress-bar');
                    const currentTime = this.$el.querySelector('.spotify-current-time');
                    
                    if (progressBar) {
                        progressBar.style.width = percentage + '%';
                    }
                    
                    if (currentTime) {
                        const minutes = Math.floor(this.trackPosition / 60000);
                        const seconds = Math.floor((this.trackPosition % 60000) / 1000);
                        
                        // Format as MM:SS with leading zeros
                        currentTime.textContent = 
                            minutes.toString().padStart(2, '0') + ':' + 
                            seconds.toString().padStart(2, '0');
                    }
                },

                // Schedule next API polling update (fallback mode)
                scheduleNextPollingUpdate() {
                    // Only schedule if not using SDK
                    if (this.isUsingSDK) {
                        return;
                    }

                    // Clear any existing timeout
                    if (this.pollingTimeout) {
                        clearTimeout(this.pollingTimeout);
                        this.pollingTimeout = null;
                    }

                    // Get the current track state from Livewire component
                    const hasTrack = @this.track !== null;

                    if (hasTrack) {
                        // Sync with server every 3 seconds (reduced from 1 second)
                        // Client-side interpolation handles smooth updates
                        console.log('🔄 Next API sync in 3 seconds...');
                        this.pollingTimeout = setTimeout(() => {
                            @this.call('loadCurrentTrack');
                        }, 3000);
                    } else {
                        // Check every 10 seconds if nothing is playing
                        console.log('💤 Nothing playing, checking again in 10 seconds...');
                        this.pollingTimeout = setTimeout(() => {
                            @this.call('loadCurrentTrack');
                        }, 10000);
                    }
                },

                // Cleanup on component destroy
                destroy() {
                    console.log('🧹 Cleaning up Spotify player...');
                    
                    if (this.updateInterval) clearInterval(this.updateInterval);
                    if (this.pollingTimeout) clearTimeout(this.pollingTimeout);
                    if (this.progressInterval) clearInterval(this.progressInterval);
                    if (this.sdkCheckTimeout) clearTimeout(this.sdkCheckTimeout);
                    if (this.sdkFallbackTimeout) clearTimeout(this.sdkFallbackTimeout);
                    
                    if (this.player) {
                        this.player.disconnect();
                    }
                }
            }));
        });
    </script>

</div>