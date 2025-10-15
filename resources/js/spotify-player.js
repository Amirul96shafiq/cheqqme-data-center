/**
 * Spotify Web Playback SDK Integration
 * Handles both SDK-based playback (Premium) and API polling fallback
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("spotifyPlayer", () => ({
        player: null,
        deviceId: null,
        currentState: null,
        updateInterval: null,
        pollingTimeout: null,
        progressInterval: null,
        sdkCheckTimeout: null,
        sdkFallbackTimeout: null,
        isSDKReady: false,
        isUsingSDK: true,

        // Client-side progress tracking for smooth updates
        trackStartTime: null,
        trackPosition: 0,
        trackDuration: 0,
        isPlaying: false,
        lastSyncTime: null,
        initialProgressMs: 0,
        currentTrackData: null,

        async initPlayer() {
            // console.log('🎵 Spotify Web Playback SDK: Initializing...');

            // Listen for Livewire events (works with wire:ignore)
            window.addEventListener("spotify-track-loaded", (event) => {
                const track = event.detail.track;
                // console.log('🎵 Track loaded from API:', track);

                // Update local track data from event
                this.updateTrackFromEvent(track);
            });

            window.addEventListener("spotify-track-updated", (event) => {
                const track = event.detail.track;
                // console.log('🔄 Track updated from SDK:', track);

                // Update local track data from event
                this.updateTrackFromEvent(track);
            });

            window.addEventListener("spotify-no-track", () => {
                // console.log('💤 API returned: No track playing');
                this.stopProgressTracking();
            });

            // Check if SDK is already available (preloaded in head)
            if (window.Spotify) {
                // console.log('✅ Spotify SDK already loaded!');
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
                // console.log('🎵 Spotify SDK already available');
                this.isSDKReady = true;
                this.initializePlayer();
                return;
            }

            // Set up the callback FIRST before any timeouts
            if (!window.onSpotifyWebPlaybackSDKReady) {
                window.onSpotifyWebPlaybackSDKReady = () => {
                    // console.log('🎵 Spotify Web Playback SDK: Ready');
                    this.isSDKReady = true;

                    // Clear any pending timeout checks
                    if (this.sdkCheckTimeout)
                        clearTimeout(this.sdkCheckTimeout);
                    if (this.sdkFallbackTimeout)
                        clearTimeout(this.sdkFallbackTimeout);

                    this.initializePlayer();
                };
            }

            // Wait for SDK to load (preloaded in head, should be quick)
            this.sdkCheckTimeout = setTimeout(() => {
                if (!this.isSDKReady) {
                    // console.log('⏳ Spotify SDK still loading...');

                    // Final check after additional time
                    this.sdkFallbackTimeout = setTimeout(() => {
                        if (!this.isSDKReady) {
                            // console.info('💡 SDK not available, continuing with API polling');
                            this.isUsingSDK = false;
                            this.$wire.set("useWebPlaybackSdk", false);
                            // Trigger polling to start
                            this.scheduleNextPollingUpdate();
                        }
                    }, 3000);
                }
            }, 1500);
        },

        async initializePlayer() {
            try {
                // Get access token
                const tokenResponse = await fetch("/api/spotify/token", {
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                if (!tokenResponse.ok) {
                    console.error(
                        "❌ Failed to get Spotify token:",
                        tokenResponse.status
                    );
                    this.$wire.set("hasError", true);
                    this.$wire.set("isLoading", false);
                    return;
                }

                const { access_token } = await tokenResponse.json();

                // Initialize player
                this.player = new Spotify.Player({
                    name: "CheQQme Web Player",
                    getOAuthToken: (cb) => {
                        cb(access_token);
                    },
                    volume: 0.5,
                });

                // Setup event listeners
                this.setupPlayerListeners();

                // Connect to player
                const connected = await this.player.connect();

                if (connected) {
                    // console.log('✅ Spotify Web Playback SDK: Connected successfully');
                } else {
                    console.error("❌ Failed to connect to Spotify");
                    this.$wire.set("hasError", true);
                }
            } catch (error) {
                console.error("❌ Spotify SDK initialization error:", error);
                // console.info('💡 Continuing with API polling mode...');

                // Fallback to API polling (already loaded on mount)
                this.isUsingSDK = false;
                this.$wire.set("useWebPlaybackSdk", false);
                // Trigger polling to start
                this.scheduleNextPollingUpdate();
            }
        },

        setupPlayerListeners() {
            // Ready event - device ID is available
            this.player.addListener("ready", ({ device_id }) => {
                // console.log('✅ Ready with Device ID:', device_id);
                this.deviceId = device_id;

                // Start polling for state updates
                this.startStatePolling();

                // Check for playback on web player after 2 seconds
                setTimeout(async () => {
                    const state = await this.player.getCurrentState();
                    if (!state && this.isUsingSDK) {
                        // console.warn('⚠️ No playback on web player');
                        // console.info('ℹ️ SDK only tracks playback IN the browser');
                        // console.info('📱 Using API polling to track playback from all devices...');
                        // console.info('🎧 To use web player: Transfer playback to "CheQQme Web Player" in Spotify');
                        this.isUsingSDK = false;
                        this.$wire.set("useWebPlaybackSdk", false);
                        // Trigger polling to start
                        this.scheduleNextPollingUpdate();
                    }
                }, 2000);
            });

            // Not Ready
            this.player.addListener("not_ready", ({ device_id }) => {
                console.warn("⚠️ Device ID has gone offline:", device_id);
            });

            // Player state changed - REAL-TIME UPDATES!
            this.player.addListener("player_state_changed", (state) => {
                if (!state) {
                    // console.log('🔇 No playback detected');
                    this.$wire.call("updatePlaybackState", null);
                    return;
                }

                // console.log('🎵 Player state changed:', state);
                this.currentState = state;

                // Update Livewire component with new state
                this.$wire.call("updatePlaybackState", state);
            });

            // Initialization error
            this.player.addListener("initialization_error", ({ message }) => {
                console.error("❌ Initialization Error:", message);
                this.$wire.set("hasError", true);
            });

            // Authentication error
            this.player.addListener("authentication_error", ({ message }) => {
                console.error("❌ Authentication Error:", message);

                if (message.includes("token") || message.includes("scope")) {
                    console.error(
                        "🔑 Your Spotify account needs to be reconnected with new permissions!"
                    );
                    console.info("📋 Steps to fix:");
                    console.info("   1. Go to your Profile settings");
                    console.info("   2. Disconnect Spotify");
                    console.info("   3. Reconnect Spotify");
                    console.info('   4. Grant the "streaming" permission');

                    // This is a real error - show error state
                    this.$wire.set("hasError", true);
                }

                // console.info('🔄 Switching to API polling mode...');

                // Fallback to API polling (already loaded on mount)
                this.isUsingSDK = false;
                this.$wire.set("useWebPlaybackSdk", false);
                // Trigger polling to start
                this.scheduleNextPollingUpdate();
            });

            // Account error (e.g., not Premium)
            this.player.addListener("account_error", ({ message }) => {
                console.error("❌ Account Error:", message);
                if (message.includes("premium")) {
                    console.warn(
                        "💡 Spotify Web Playback SDK requires Premium"
                    );
                }
                // console.info('🔄 Switching to API polling to track playback from all devices...');

                // Fallback to API polling (already loaded on mount)
                // Don't set hasError - this is expected behavior
                this.isUsingSDK = false;
                this.$wire.set("useWebPlaybackSdk", false);
                // Trigger polling to start
                this.scheduleNextPollingUpdate();
            });

            // Playback error
            this.player.addListener("playback_error", ({ message }) => {
                console.error("❌ Playback Error:", message);
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
                    this.$wire.call("updatePlaybackState", state);
                }
            }, 1000);
        },

        async refreshPlayer() {
            // console.log('🔄 Refreshing player state...');
            if (this.player) {
                const state = await this.player.getCurrentState();
                if (state) {
                    this.$wire.call("updatePlaybackState", state);
                } else {
                    this.$wire.call("updatePlaybackState", null);
                }
            }
        },

        async transferPlayback() {
            if (!this.deviceId) {
                console.warn("⚠️ No device ID available for transfer");
                return;
            }

            try {
                const response = await fetch("/api/spotify/transfer-playback", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: JSON.stringify({
                        device_id: this.deviceId,
                    }),
                });

                if (response.ok) {
                    console.log("✅ Playback transferred to web player");
                } else {
                    console.error(
                        "❌ Failed to transfer playback:",
                        response.status
                    );
                }
            } catch (error) {
                console.error("❌ Transfer playback error:", error);
            }
        },

        // Update track data from Livewire event (works with wire:ignore)
        updateTrackFromEvent(track) {
            if (
                !track ||
                track.progress_ms === undefined ||
                !track.duration_ms
            ) {
                this.stopProgressTracking();
                return;
            }

            // Store initial values for interpolation
            this.initialProgressMs = track.progress_ms;
            this.trackDuration = track.duration_ms;
            this.isPlaying = track.is_playing;
            this.lastSyncTime = Date.now();
            this.trackPosition = track.progress_ms;

            // Store track metadata for display updates
            this.currentTrackData = {
                track_name: track.track_name,
                artist_name: track.artist_name,
                album_name: track.album_name,
                album_art: track.album_art,
            };

            // console.log('⏱️ Synced position:', Math.floor(this.trackPosition / 1000) + 's / ' + Math.floor(this.trackDuration / 1000) + 's');

            // Start smooth progress tracking
            this.startProgressTracking();

            // Update DOM elements that might not re-render due to wire:ignore
            this.updateTrackDisplay();
        },

        // Sync progress from server data (fallback for non-event contexts)
        syncProgressFromServer() {
            const track = this.$wire.track;

            if (
                !track ||
                track.progress_ms === undefined ||
                !track.duration_ms
            ) {
                this.stopProgressTracking();
                return;
            }

            // Store initial values for interpolation
            this.initialProgressMs = track.progress_ms;
            this.trackDuration = track.duration_ms;
            this.isPlaying = track.is_playing;
            this.lastSyncTime = Date.now();
            this.trackPosition = track.progress_ms;

            // console.log('⏱️ Synced position:', Math.floor(this.trackPosition / 1000) + 's / ' + Math.floor(this.trackDuration / 1000) + 's');

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

                    // Interpolate position from the initial synced value
                    this.trackPosition = this.initialProgressMs + elapsed;

                    // Prevent going over duration
                    if (this.trackPosition >= this.trackDuration) {
                        this.trackPosition = this.trackDuration;
                    }

                    // Update the UI (this will trigger Alpine reactivity)
                    this.$nextTick(() => {
                        this.updateProgressBar();
                    });
                }
            }, 100);
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

        // Update progress bar (called by Alpine reactivity)
        updateProgressBar() {
            const percentage = (this.trackPosition / this.trackDuration) * 100;
            const progressBar = this.$el.querySelector(".spotify-progress-bar");
            const currentTime = this.$el.querySelector(".spotify-current-time");

            if (progressBar) {
                progressBar.style.width = percentage + "%";
            }

            if (currentTime) {
                const minutes = Math.floor(this.trackPosition / 60000);
                const seconds = Math.floor((this.trackPosition % 60000) / 1000);

                // Format as MM:SS with leading zeros
                currentTime.textContent =
                    minutes.toString().padStart(2, "0") +
                    ":" +
                    seconds.toString().padStart(2, "0");
            }
        },

        // Update track display elements (for wire:ignore contexts where DOM doesn't auto-update)
        updateTrackDisplay() {
            if (!this.currentTrackData) return;

            const trackNameEl = this.$el.querySelector(
                ".track-name-container span"
            );
            const artistNameEl = this.$el.querySelector(
                ".text-xs.text-gray-600"
            );
            const albumArtEl = this.$el.querySelector("img[alt*='cover']");

            if (trackNameEl && this.currentTrackData.track_name) {
                trackNameEl.textContent = this.currentTrackData.track_name;
            }

            if (artistNameEl && this.currentTrackData.artist_name) {
                artistNameEl.textContent = this.currentTrackData.artist_name;
            }

            if (albumArtEl && this.currentTrackData.album_art) {
                albumArtEl.src = this.currentTrackData.album_art;
                albumArtEl.alt = this.currentTrackData.album_name + " cover";
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
            const hasTrack = this.$wire.track !== null;

            if (hasTrack) {
                // Sync with server every 3 seconds
                // Client-side interpolation handles smooth updates
                // console.log('🔄 Next API sync in 3 seconds...');
                this.pollingTimeout = setTimeout(() => {
                    this.$wire.call("loadCurrentTrack");
                }, 3000);
            } else {
                // Check every 10 seconds if nothing is playing
                // console.log('💤 Nothing playing, checking again in 10 seconds...');
                this.pollingTimeout = setTimeout(() => {
                    this.$wire.call("loadCurrentTrack");
                }, 10000);
            }
        },

        // Cleanup on component destroy
        destroy() {
            // console.log('🧹 Cleaning up Spotify player...');

            if (this.updateInterval) clearInterval(this.updateInterval);
            if (this.pollingTimeout) clearTimeout(this.pollingTimeout);
            if (this.progressInterval) clearInterval(this.progressInterval);
            if (this.sdkCheckTimeout) clearTimeout(this.sdkCheckTimeout);
            if (this.sdkFallbackTimeout) clearTimeout(this.sdkFallbackTimeout);

            if (this.player) {
                this.player.disconnect();
            }
        },
    }));
});
