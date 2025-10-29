/**
 * Pure Alpine.js Spotify Player Component
 * No Livewire - avoids snapshot conflicts with Filament resource pages
 */

document.addEventListener("alpine:init", () => {
    // Global tracking to prevent duplicate SDK connections
    window.spotifyPlayerConnections =
        window.spotifyPlayerConnections || new Set();

    // Global flag to track if SDK event listeners have been set up
    window.spotifySDKListenersSetup = window.spotifySDKListenersSetup || false;

    // Global tracking for playback polling to prevent multiple intervals
    window.spotifyPlaybackPolling = window.spotifyPlaybackPolling || new Map();

    Alpine.data(
        "spotifyPlayerAlpine",
        (context, userId, isModalVisible = false) => ({
            // State
            track: null,
            isLoading: false,
            hasError: false,
            trackPosition: 0,
            progressPercentage: 0,

            // Intervals
            progressInterval: null,
            userPollInterval: null,
            playbackPollInterval: null,

            // Tracking
            lastSyncTime: null,
            isPlaying: false,
            isModalVisible: isModalVisible,
            lastTrackId: null,
            lastIsPlaying: null,
            changeDetectionEnabled: true,
            initialized: false, // Track if already initialized

            // Initialize the player - 100% SDK dependent
            async initPlayer() {
                // Prevent multiple initializations
                if (this.initialized) {
                    // console.log("🎵 Already initialized, skipping");
                    return;
                }

                // console.log("🎵 Alpine Spotify Player: Initializing...", {
                //     context,
                //     userId,
                //     isModalVisible,
                //     currentUserId: window.currentUserId,
                //     userIdType: typeof userId,
                //     currentUserIdType: typeof window.currentUserId,
                // });

                // Check if viewing another user's Spotify
                const isCurrentUser = userId == window.currentUserId; // Using == for loose comparison

                // console.log("🎵 Is current user?", isCurrentUser);
                // console.log(
                //     "🎵 About to:",
                //     isCurrentUser ? "Initialize SDK" : "Start polling"
                // );

                if (isCurrentUser) {
                    // Check if SDK connection already exists
                    const connectionKey = `sdk-${userId}-${context}`;
                    if (window.spotifyPlayerConnections.has(connectionKey)) {
                        // console.log(
                        //     "🎵 SDK connection already exists for this user/context"
                        // );
                        // Just set up event listeners, don't reconnect
                        this.setupSDKEventListeners();
                        this.isLoading = false;
                        this.initialized = true;
                        return;
                    }

                    // Mark as connecting immediately to prevent race conditions
                    window.spotifyPlayerConnections.add(connectionKey);
                    // console.log("🎵 Added to connections set");

                    // Initialize Spotify Web Playback SDK for current user
                    await this.initializeSpotifySDK();

                    // Set up global SDK event listeners (only once)
                    this.setupSDKEventListeners();

                    // Set up local event listeners for this component instance
                    this.setupLocalEventListeners();
                } else {
                    // For other users, use API polling only
                    this.startPollingForUser(userId);
                }

                // Start progress tracking (SDK events handle track updates)
                this.startProgressTracking();

                // Start polling for playback state changes if modal is visible
                if (isCurrentUser && isModalVisible) {
                    this.startPlaybackPolling();
                }

                // Set loading to false - SDK will provide track data via events
                this.isLoading = false;
                this.initialized = true;
            },

            // Initialize Spotify Web Playback SDK
            async initializeSpotifySDK() {
                // Verify this is for the current user
                const isCurrentUser = userId == window.currentUserId;

                // console.log("🎵 SDK initialize check:", {
                //     userId,
                //     currentUserId: window.currentUserId,
                //     isCurrentUser,
                // });

                if (!isCurrentUser || !window.currentUserId) {
                    console.warn(
                        "⚠️ Cannot initialize SDK for another user's Spotify or currentUserId not set"
                    );
                    this.startPollingForUser(userId);
                    return;
                }

                // console.log("🎵 Initializing Spotify Web Playback SDK...");

                // Load SDK script if not already loaded
                if (!document.querySelector('script[src*="spotify-player"]')) {
                    await this.loadSpotifySDK();
                }

                // Check if SDK is already available
                if (window.Spotify) {
                    // console.log("🎵 Spotify SDK already available");
                    await this.createSpotifyPlayer();
                    return;
                }

                // Wait for SDK to load
                return new Promise((resolve) => {
                    // Set up the callback
                    if (!window.onSpotifyWebPlaybackSDKReady) {
                        window.onSpotifyWebPlaybackSDKReady = async () => {
                            // console.log("🎵 Spotify Web Playback SDK: Ready");
                            await this.createSpotifyPlayer();
                            resolve();
                        };
                    }

                    // Timeout after 5 seconds
                    setTimeout(() => {
                        if (!window.Spotify) {
                            console.warn(
                                "⚠️ Spotify SDK failed to load, using API polling only"
                            );
                            resolve(); // Don't reject, just fall back to polling
                        }
                    }, 5000);
                });
            },

            // Load Spotify Web Playback SDK
            loadSpotifySDK() {
                return new Promise((resolve) => {
                    const script = document.createElement("script");
                    script.src = "https://sdk.scdn.co/spotify-player.js";
                    script.async = true;
                    script.onload = () => {
                        resolve();
                    };
                    script.onerror = () => {
                        console.error("Failed to load Spotify SDK");
                        resolve(); // Don't reject, just continue without SDK
                    };
                    document.body.appendChild(script);
                });
            },

            // Create Spotify player instance
            async createSpotifyPlayer() {
                try {
                    // console.log("🎵 Creating Spotify player instance...");

                    // Get access token
                    const tokenResponse = await fetch("/api/spotify/token", {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });

                    // console.log(
                    //     "🎵 Token response status:",
                    //     tokenResponse.status
                    // );

                    if (!tokenResponse.ok) {
                        console.error(
                            "❌ Failed to get Spotify token:",
                            tokenResponse.status
                        );
                        this.hasError = true;
                        this.isLoading = false;
                        return;
                    }

                    const { access_token } = await tokenResponse.json();

                    // console.log("🎵 Got access token, creating player...");

                    // Create player
                    const player = new Spotify.Player({
                        name: "CheQQme Alpine Player",
                        getOAuthToken: (cb) => {
                            cb(access_token);
                        },
                        volume: 0.5,
                    });

                    // Setup event listeners
                    this.setupPlayerEventListeners(player);

                    // Connect to player
                    // console.log("🎵 Attempting to connect to Spotify SDK...");
                    const connected = await player.connect();
                    if (connected) {
                        // console.log(
                        //     "✅ Spotify Web Playback SDK: Connected successfully"
                        // );
                        this.hasError = false;
                    } else {
                        console.error("❌ Failed to connect to Spotify");
                        this.hasError = true;
                        this.isLoading = false;
                    }
                } catch (error) {
                    console.error(
                        "❌ Spotify SDK initialization error:",
                        error
                    );
                    this.hasError = true;
                    this.isLoading = false;
                }
            },

            // Setup player event listeners
            setupPlayerEventListeners(player) {
                // Ready event
                player.addListener("ready", async ({ device_id }) => {
                    // console.log(
                    //     "✅ Spotify Player Ready with Device ID:",
                    //     device_id
                    // );

                    // Fetch current state immediately
                    const state = await player.getCurrentState();
                    // console.log("🎵 Current player state:", state);

                    if (state) {
                        this.handlePlayerState(state);
                    } else {
                        // console.log(
                        //     "⚠️ No current state - trying to fetch via API"
                        // );
                        // If no state from SDK, try to fetch via API
                        try {
                            const trackResponse = await fetch(
                                "/api/spotify/current-track",
                                {
                                    headers: {
                                        Accept: "application/json",
                                        "X-Requested-With": "XMLHttpRequest",
                                    },
                                }
                            );

                            // console.log(
                            //     "🎵 API currently-playing response:",
                            //     trackResponse.status
                            // );

                            if (
                                trackResponse.ok &&
                                trackResponse.status !== 204
                            ) {
                                const data = await trackResponse.json();
                                // console.log("🎵 API returned track:", data);

                                if (data && data.track) {
                                    // console.log(
                                    //     "🎵 Dispatching spotify-track-loaded event"
                                    // );
                                    window.dispatchEvent(
                                        new CustomEvent(
                                            "spotify-track-loaded",
                                            {
                                                detail: { track: data.track },
                                            }
                                        )
                                    );
                                }
                            } else {
                                // console.log(
                                //     "🎵 No track currently playing via API"
                                // );
                            }
                        } catch (error) {
                            console.error(
                                "❌ Error fetching current track:",
                                error
                            );
                        }
                    }
                });

                // Not Ready
                player.addListener("not_ready", ({ device_id }) => {
                    console.warn("⚠️ Device ID has gone offline:", device_id);
                });

                // Player state changed - REAL-TIME UPDATES!
                player.addListener("player_state_changed", (state) => {
                    // console.log("🎵 Player state changed:", state);
                    this.handlePlayerState(state);
                });

                // Initialization error
                player.addListener("initialization_error", ({ message }) => {
                    console.error("❌ Initialization Error:", message);
                });

                // Authentication error
                player.addListener("authentication_error", ({ message }) => {
                    console.error("❌ Authentication Error:", message);
                    this.hasError = true;
                    this.isLoading = false;
                    // Fall back to polling
                    this.startPollingForUser(userId);
                });

                // Account error
                player.addListener("account_error", ({ message }) => {
                    console.error("❌ Account Error:", message);
                    this.hasError = true;
                    this.isLoading = false;
                    // Fall back to polling
                    this.startPollingForUser(userId);
                });

                // Playback error
                player.addListener("playback_error", ({ message }) => {
                    console.error("❌ Playback Error:", message);
                });
            },

            // Extract artist names from array
            extractArtistNames(artists) {
                return artists.map((artist) => artist.name).join(", ");
            },

            // Handle player state changes
            handlePlayerState(state) {
                if (!state) {
                    // console.log("🔇 No playback detected");
                    window.dispatchEvent(new CustomEvent("spotify-no-track"));
                    return;
                }

                // Extract track info from SDK state
                const track = state["track_window"]["current_track"] ?? null;
                if (!track) {
                    // console.log("🔇 No track in state");
                    window.dispatchEvent(new CustomEvent("spotify-no-track"));
                    return;
                }

                const trackData = {
                    track_name: track["name"] ?? "Unknown Track",
                    artist_name: this.extractArtistNames(
                        track["artists"] ?? []
                    ),
                    album_name: track["album"]["name"] ?? "Unknown Album",
                    album_art: track["album"]["images"][0]["url"] ?? null,
                    progress_ms: state["position"] ?? 0,
                    duration_ms: state["duration"] ?? 0,
                    is_playing: !state["paused"],
                    spotify_url: track["uri"] ?? null,
                    track_id: track["id"] ?? null,
                };

                // console.log("🎵 Track data extracted:", trackData);

                // Dispatch event for Alpine component to listen to
                window.dispatchEvent(
                    new CustomEvent("spotify-track-updated", {
                        detail: { track: trackData },
                    })
                );
            },

            // Setup Spotify Web Playback SDK event listeners for real-time updates
            setupSDKEventListeners() {
                // Set up global listeners only once
                if (!window.spotifySDKListenersSetup) {
                    // console.log(
                    //     "🎵 Setting up SDK event listeners (global)..."
                    // );
                    window.spotifySDKListenersSetup = true;

                    // Listen for track updates from the Web Playback SDK - only add listener once globally
                    if (!window._spotifyTrackUpdatedHandler) {
                        window._spotifyTrackUpdatedHandler = (event) => {
                            const trackData = event.detail.track;
                            // console.log(
                            //     "🎵 Real-time track update from SDK:",
                            //     trackData
                            // );

                            // Dispatch to all Alpine components via custom event
                            window.dispatchEvent(
                                new CustomEvent("spotify-track-updated-local", {
                                    detail: { track: trackData },
                                })
                            );
                        };

                        window.addEventListener(
                            "spotify-track-updated",
                            window._spotifyTrackUpdatedHandler
                        );
                    }

                    // Listen for track loaded events - only add listener once globally
                    if (!window._spotifyTrackLoadedHandler) {
                        window._spotifyTrackLoadedHandler = (event) => {
                            const trackData = event.detail.track;
                            // console.log("🎵 Track loaded from SDK:", trackData);

                            // Dispatch to all Alpine components via custom event
                            window.dispatchEvent(
                                new CustomEvent("spotify-track-loaded-local", {
                                    detail: { track: trackData },
                                })
                            );
                        };

                        window.addEventListener(
                            "spotify-track-loaded",
                            window._spotifyTrackLoadedHandler
                        );
                    }

                    // Listen for no track events - only add listener once globally
                    if (!window._spotifyNoTrackHandler) {
                        window._spotifyNoTrackHandler = () => {
                            // console.log("💤 No track playing (from SDK)");

                            // Dispatch to all Alpine components via custom event
                            window.dispatchEvent(
                                new CustomEvent("spotify-no-track-local")
                            );
                        };

                        window.addEventListener(
                            "spotify-no-track",
                            window._spotifyNoTrackHandler
                        );
                    }
                }
            },

            // Set up local event listeners for THIS component
            setupLocalEventListeners() {
                if (this._localListenersSetup) {
                    return;
                }

                // console.log(
                //     "🎵 Setting up local listeners for component",
                //     userId
                // );
                this._localListenersSetup = true;

                this._handleTrackUpdated = (event) => {
                    const trackData = event.detail.track;
                    // console.log("🎵 Local track updated", trackData);

                    if (trackData && userId == window.currentUserId) {
                        this.track = trackData;
                        this.trackPosition = trackData.progress_ms || 0;
                        this.isPlaying = trackData.is_playing || false;
                        this.lastSyncTime = Date.now();
                        this.hasError = false;
                        this.isLoading = false;

                        this.lastTrackId = trackData.track_id;
                        this.lastIsPlaying = trackData.is_playing;

                        if (trackData.duration_ms > 0) {
                            this.progressPercentage =
                                (trackData.progress_ms /
                                    trackData.duration_ms) *
                                100;
                        }
                    }
                };

                this._handleTrackLoaded = (event) => {
                    const trackData = event.detail.track;
                    // console.log("🎵 Local track loaded", trackData);

                    if (trackData && userId == window.currentUserId) {
                        this.track = trackData;
                        this.trackPosition = trackData.progress_ms || 0;
                        this.isPlaying = trackData.is_playing || false;
                        this.lastSyncTime = Date.now();
                        this.hasError = false;
                        this.isLoading = false;

                        this.lastTrackId = trackData.track_id;
                        this.lastIsPlaying = trackData.is_playing;

                        if (trackData.duration_ms > 0) {
                            this.progressPercentage =
                                (trackData.progress_ms /
                                    trackData.duration_ms) *
                                100;
                        }
                    }
                };

                this._handleNoTrack = () => {
                    // console.log("🎵 Local no-track");
                    this.track = null;
                    this.trackPosition = 0;
                    this.progressPercentage = 0;
                    this.lastTrackId = null;
                    this.lastIsPlaying = null;
                    this.isLoading = false;
                };

                window.addEventListener(
                    "spotify-track-updated-local",
                    this._handleTrackUpdated
                );
                window.addEventListener(
                    "spotify-track-loaded-local",
                    this._handleTrackLoaded
                );
                window.addEventListener(
                    "spotify-no-track-local",
                    this._handleNoTrack
                );
            },

            // Start smooth progress tracking with instant updates
            startProgressTracking() {
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                }

                // More responsive progress tracking when modal is visible
                const progressInterval =
                    this.isModalVisible && context === "modal" ? 50 : 100; // 50ms for modal, 100ms for others

                // Update progress every 50-100ms for smooth animation
                this.progressInterval = setInterval(() => {
                    if (
                        this.track &&
                        this.isPlaying &&
                        this.track.duration_ms > 0
                    ) {
                        const elapsed = Date.now() - this.lastSyncTime;
                        this.trackPosition =
                            (this.track.progress_ms || 0) + elapsed;

                        // Prevent going over duration
                        if (this.trackPosition >= this.track.duration_ms) {
                            this.trackPosition = this.track.duration_ms;
                        }

                        // Update percentage
                        this.progressPercentage =
                            (this.trackPosition / this.track.duration_ms) * 100;
                    }
                }, progressInterval);
            },

            // Retry checking track name length until text is rendered
            checkTrackNameLengthWithRetry(attempt = 0) {
                const maxAttempts = 10;

                const container = this.$el?.querySelector(
                    ".track-name-container"
                );
                const trackNameDiv = container?.querySelector("div");
                const trackNameSpan = trackNameDiv?.querySelector("span");

                // console.log(`🎵 Attempt ${attempt + 1}:`, {
                //     hasSpan: !!trackNameSpan,
                //     textContent: trackNameSpan?.textContent,
                //     innerText: trackNameSpan?.innerText,
                //     scrollWidth: trackNameSpan?.scrollWidth,
                //     offsetWidth: trackNameSpan?.offsetWidth,
                //     clientWidth: trackNameSpan?.clientWidth,
                //     computedDisplay: trackNameSpan
                //         ? window.getComputedStyle(trackNameSpan).display
                //         : null,
                //     computedVisibility: trackNameSpan
                //         ? window.getComputedStyle(trackNameSpan).visibility
                //         : null,
                // });

                if (
                    container &&
                    trackNameSpan &&
                    trackNameSpan.scrollWidth > 0
                ) {
                    // Text is rendered, now we can check
                    this.isLongTrackName =
                        trackNameSpan.scrollWidth > container.offsetWidth;
                    // console.log("🎵 Marquee check SUCCESS:", {
                    //     scrollWidth: trackNameSpan.scrollWidth,
                    //     containerWidth: container.offsetWidth,
                    //     isLong: this.isLongTrackName,
                    //     trackName: this.track?.track_name,
                    //     attempt: attempt + 1,
                    // });
                } else if (attempt < maxAttempts) {
                    // Retry after a short delay
                    setTimeout(() => {
                        this.checkTrackNameLengthWithRetry(attempt + 1);
                    }, 50);
                } else {
                    // console.log("🎵 Marquee check FAILED after max attempts");
                }
            },

            // Check if track name is too long and needs marquee
            checkTrackNameLength() {
                const container = this.$el?.querySelector(
                    ".track-name-container"
                );
                const trackNameDiv = container?.querySelector("div");
                const trackNameSpan = trackNameDiv?.querySelector("span");

                if (
                    container &&
                    trackNameSpan &&
                    trackNameSpan.scrollWidth > 0
                ) {
                    // Compare span's content width to container's width
                    this.isLongTrackName =
                        trackNameSpan.scrollWidth > container.offsetWidth;
                }
            },

            // Format milliseconds to MM:SS
            formatTime(ms) {
                if (!ms || ms < 0) return "00:00";

                const minutes = Math.floor(ms / 60000);
                const seconds = Math.floor((ms % 60000) / 1000);

                return (
                    minutes.toString().padStart(2, "0") +
                    ":" +
                    seconds.toString().padStart(2, "0")
                );
            },

            // Handle modal visibility changes
            onModalShow() {
                this.isModalVisible = true;
                // console.log("🎵 Modal shown - starting playback polling");

                // Start progress tracking (SDK events handle track updates)
                this.startProgressTracking();

                // Start polling for playback state changes
                if (userId === window.currentUserId) {
                    this.startPlaybackPolling();
                }
            },

            onModalHide() {
                this.isModalVisible = false;
                // console.log("🎵 Modal hidden - stopping polling");

                // Stop progress tracking
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }

                // Stop playback polling only if it's our interval
                if (
                    this.playbackPollInterval &&
                    window.spotifyPlaybackPolling.get(userId) ===
                        this.playbackPollInterval
                ) {
                    clearInterval(this.playbackPollInterval);
                    window.spotifyPlaybackPolling.delete(userId);
                    // console.log("🎵 Stopped playback polling for user", userId);
                }
                this.playbackPollInterval = null;
            },

            // Start polling for another user's Spotify track
            startPollingForUser(userId) {
                // console.log("🎵 Starting polling for user:", userId);

                // Initial fetch
                this.fetchUserTrack(userId);

                // Set up polling interval (poll every 3 seconds for other users)
                this.userPollInterval = setInterval(() => {
                    this.fetchUserTrack(userId);
                }, 3000);
            },

            // Fetch track for a specific user
            async fetchUserTrack(userId) {
                try {
                    // console.log("🎵 Fetching track for user:", userId);

                    const response = await fetch(
                        `/api/spotify/user/${userId}/current-track`,
                        {
                            headers: {
                                Accept: "application/json",
                                "X-Requested-With": "XMLHttpRequest",
                            },
                        }
                    );

                    // console.log(
                    //     "🎵 Track fetch response status:",
                    //     response.status
                    // );

                    if (!response.ok) {
                        this.hasError = true;
                        this.isLoading = false;
                        return;
                    }

                    const data = await response.json();

                    if (data.connected && data.track) {
                        this.track = data.track;
                        this.trackPosition = data.track.progress_ms || 0;
                        this.isPlaying = data.track.is_playing || false;
                        this.lastSyncTime = Date.now();
                        this.hasError = false;
                        this.isLoading = false;

                        // Calculate progress percentage
                        if (data.track.duration_ms > 0) {
                            this.progressPercentage =
                                (data.track.progress_ms /
                                    data.track.duration_ms) *
                                100;
                        }
                    } else {
                        this.track = null;
                        this.trackPosition = 0;
                        this.progressPercentage = 0;
                        this.isLoading = false;
                    }
                } catch (error) {
                    console.error("Error fetching user track:", error);
                    this.hasError = true;
                    this.isLoading = false;
                }
            },

            // Start polling for playback state changes (for detecting play/pause/next/previous in user's Spotify client)
            startPlaybackPolling() {
                // Check if polling already exists for this user
                if (window.spotifyPlaybackPolling.has(userId)) {
                    const existingInterval =
                        window.spotifyPlaybackPolling.get(userId);
                    this.playbackPollInterval = existingInterval;
                    // console.log("🎵 Using existing polling for user", userId);
                    return;
                }

                // Initial fetch
                this.fetchCurrentPlayback();

                // Poll every 2 seconds to detect state changes
                const intervalId = setInterval(() => {
                    this.fetchCurrentPlayback();
                }, 2000);

                // Store interval ID globally
                window.spotifyPlaybackPolling.set(userId, intervalId);
                this.playbackPollInterval = intervalId;

                // console.log("🎵 Started playback polling for user", userId);
            },

            // Fetch current playback state from Spotify API
            async fetchCurrentPlayback() {
                try {
                    const response = await fetch("/api/spotify/current-track", {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });

                    if (response.ok && response.status !== 204) {
                        const data = await response.json();

                        if (data && data.track) {
                            const trackData = data.track;

                            // Check if track changed
                            const trackChanged =
                                !this.track ||
                                this.track.track_id !== trackData.track_id;

                            // Update track data
                            this.track = trackData;
                            this.trackPosition = trackData.progress_ms || 0;
                            this.isPlaying = trackData.is_playing || false;
                            this.lastSyncTime = Date.now();
                            this.hasError = false;
                            this.isLoading = false;

                            if (trackData.duration_ms > 0) {
                                this.progressPercentage =
                                    (trackData.progress_ms /
                                        trackData.duration_ms) *
                                    100;
                            }

                            if (trackChanged) {
                                // console.log(
                                //     "🎵 Track changed:",
                                //     trackData.track_name
                                // );
                            }
                        } else if (!data.track) {
                            // No track playing
                            this.track = null;
                            this.trackPosition = 0;
                            this.progressPercentage = 0;
                        }
                    }
                } catch (error) {
                    console.error("Error fetching current playback:", error);
                }
            },

            // Cleanup on component destroy
            destroy() {
                // console.log('🧹 Cleaning up Alpine Spotify player...');

                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }

                if (this.userPollInterval) {
                    clearInterval(this.userPollInterval);
                    this.userPollInterval = null;
                }

                // Only clear playback polling if we own it
                if (
                    this.playbackPollInterval &&
                    window.spotifyPlaybackPolling.get(userId) ===
                        this.playbackPollInterval
                ) {
                    clearInterval(this.playbackPollInterval);
                    window.spotifyPlaybackPolling.delete(userId);
                    // console.log("🧹 Cleaned up playback polling for user", userId);
                }
                this.playbackPollInterval = null;
            },
        })
    );
});
