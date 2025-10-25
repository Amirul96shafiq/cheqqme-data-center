/**
 * Pure Alpine.js Spotify Player Component
 * No Livewire - avoids snapshot conflicts with Filament resource pages
 */

document.addEventListener("alpine:init", () => {
    Alpine.data(
        "spotifyPlayerAlpine",
        (context, userId, isModalVisible = false) => ({
            // State
            track: null,
            isLoading: true,
            hasError: false,
            trackPosition: 0,
            progressPercentage: 0,

            // Intervals
            progressInterval: null,

            // Tracking
            lastSyncTime: null,
            isPlaying: false,
            isModalVisible: isModalVisible,
            lastTrackId: null,
            lastIsPlaying: null,
            changeDetectionEnabled: true,

            // Initialize the player - 100% SDK dependent
            async initPlayer() {
                // console.log("🎵 Alpine Spotify Player: Initializing...", {
                //     context,
                //     userId,
                //     isModalVisible,
                // });

                // Initialize Spotify Web Playback SDK
                await this.initializeSpotifySDK();

                // Listen to Spotify Web Playback SDK events for real-time updates
                this.setupSDKEventListeners();

                // Detect user actions for instant UI updates
                this.detectUserActions();

                // Start progress tracking (SDK events handle track updates)
                this.startProgressTracking();

                // Set loading to false - SDK will provide track data via events
                this.isLoading = false;
            },

            // Initialize Spotify Web Playback SDK
            async initializeSpotifySDK() {
                // console.log("🎵 Initializing Spotify Web Playback SDK...");

                // Check if SDK is already available
                if (window.Spotify) {
                    // console.log("🎵 Spotify SDK already available");
                    await this.createSpotifyPlayer();
                    return;
                }

                // Wait for SDK to load
                return new Promise((resolve, reject) => {
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

            // Create Spotify player instance
            async createSpotifyPlayer() {
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
                        return;
                    }

                    const { access_token } = await tokenResponse.json();

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
                    const connected = await player.connect();
                    if (connected) {
                        // console.log(
                        //     "✅ Spotify Web Playback SDK: Connected successfully"
                        // );
                    } else {
                        console.error("❌ Failed to connect to Spotify");
                    }
                } catch (error) {
                    console.error(
                        "❌ Spotify SDK initialization error:",
                        error
                    );
                }
            },

            // Setup player event listeners
            setupPlayerEventListeners(player) {
                // Ready event
                player.addListener("ready", ({ device_id }) => {
                    console.log(
                        "✅ Spotify Player Ready with Device ID:",
                        device_id
                    );
                });

                // Not Ready
                player.addListener("not_ready", ({ device_id }) => {
                    console.warn("⚠️ Device ID has gone offline:", device_id);
                });

                // Player state changed - REAL-TIME UPDATES!
                player.addListener("player_state_changed", (state) => {
                    console.log("🎵 Player state changed:", state);

                    if (!state) {
                        // console.log("🔇 No playback detected");
                        window.dispatchEvent(
                            new CustomEvent("spotify-no-track")
                        );
                        return;
                    }

                    // Extract track info from SDK state
                    const track =
                        state["track_window"]["current_track"] ?? null;
                    if (!track) {
                        window.dispatchEvent(
                            new CustomEvent("spotify-no-track")
                        );
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

                    // Dispatch event for Alpine component to listen to
                    window.dispatchEvent(
                        new CustomEvent("spotify-track-updated", {
                            detail: { track: trackData },
                        })
                    );
                });

                // Initialization error
                player.addListener("initialization_error", ({ message }) => {
                    console.error("❌ Initialization Error:", message);
                });

                // Authentication error
                player.addListener("authentication_error", ({ message }) => {
                    console.error("❌ Authentication Error:", message);
                });

                // Account error
                player.addListener("account_error", ({ message }) => {
                    console.error("❌ Account Error:", message);
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

            // Setup Spotify Web Playback SDK event listeners for real-time updates
            setupSDKEventListeners() {
                // console.log("🎵 Setting up SDK event listeners...");

                // Listen for track updates from the Web Playback SDK
                window.addEventListener("spotify-track-updated", (event) => {
                    const trackData = event.detail.track;
                    // console.log(
                    //     "🎵 Real-time track update from SDK:",
                    //     trackData
                    // );

                    // Update track data immediately
                    this.track = trackData;
                    this.trackPosition = trackData.progress_ms || 0;
                    this.isPlaying = trackData.is_playing || false;
                    this.lastSyncTime = Date.now();
                    this.hasError = false;
                    this.isLoading = false;

                    // Update change tracking
                    this.lastTrackId = trackData.track_id;
                    this.lastIsPlaying = trackData.is_playing;

                    // Calculate progress percentage
                    if (trackData.duration_ms > 0) {
                        this.progressPercentage =
                            (trackData.progress_ms / trackData.duration_ms) *
                            100;
                    }
                });

                // Listen for track loaded events
                window.addEventListener("spotify-track-loaded", (event) => {
                    const trackData = event.detail.track;
                    // console.log("🎵 Track loaded from SDK:", trackData);

                    this.track = trackData;
                    this.trackPosition = trackData.progress_ms || 0;
                    this.isPlaying = trackData.is_playing || false;
                    this.lastSyncTime = Date.now();
                    this.hasError = false;
                    this.isLoading = false;

                    // Update change tracking
                    this.lastTrackId = trackData.track_id;
                    this.lastIsPlaying = trackData.is_playing;

                    // Calculate progress percentage
                    if (trackData.duration_ms > 0) {
                        this.progressPercentage =
                            (trackData.progress_ms / trackData.duration_ms) *
                            100;
                    }
                });

                // Listen for no track events
                window.addEventListener("spotify-no-track", () => {
                    // console.log("💤 No track playing (from SDK)");
                    this.track = null;
                    this.trackPosition = 0;
                    this.progressPercentage = 0;
                    this.lastTrackId = null;
                    this.lastIsPlaying = null;
                    this.isLoading = false;
                });
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
                // console.log('🎵 Modal shown - SDK events will handle real-time updates');

                // Start progress tracking (SDK events handle track updates)
                this.startProgressTracking();
            },

            onModalHide() {
                this.isModalVisible = false;
                // console.log('🎵 Modal hidden - stopping progress tracking');

                // Stop progress tracking
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
            },

            // Cleanup on component destroy
            destroy() {
                // console.log('🧹 Cleaning up Alpine Spotify player...');

                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
            },
        })
    );
});
