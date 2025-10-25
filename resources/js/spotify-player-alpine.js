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
            pollingInterval: null,
            progressInterval: null,
            pollingTimeoutId: null,

            // Tracking
            lastSyncTime: null,
            isPlaying: false,
            isModalVisible: isModalVisible,
            lastTrackId: null,
            lastIsPlaying: null,
            changeDetectionEnabled: true,

            // Initialize the player
            async initPlayer() {
                // console.log('🎵 Alpine Spotify Player: Initializing...', { context, userId, isModalVisible });

                // Listen to Spotify Web Playback SDK events for real-time updates
                this.setupSDKEventListeners();

                // Only start polling if modal is visible or context is not modal
                if (this.isModalVisible || context !== "modal") {
                    // Initial fetch
                    await this.fetchTrack();

                    // Start minimal polling as fallback (only when modal is closed)
                    if (!this.isModalVisible && context === "modal") {
                        this.startMinimalPolling();
                    }

                    // Start progress tracking
                    this.startProgressTracking();
                } else {
                    // If modal is not visible, just set loading to false
                    this.isLoading = false;
                }
            },

            // Setup Spotify Web Playback SDK event listeners for real-time updates
            setupSDKEventListeners() {
                // Listen for track updates from the Web Playback SDK
                window.addEventListener("spotify-track-updated", (event) => {
                    const trackData = event.detail.track;
                    // console.log('🎵 Real-time track update from SDK:', trackData);

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
                    // console.log('🎵 Track loaded from SDK:', trackData);

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
                    // console.log('💤 No track playing (from SDK)');
                    this.track = null;
                    this.trackPosition = 0;
                    this.progressPercentage = 0;
                    this.lastTrackId = null;
                    this.lastIsPlaying = null;
                    this.isLoading = false;
                });
            },

            // Fetch current track from API
            async fetchTrack() {
                try {
                    const response = await fetch("/api/spotify/current-track", {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });

                    if (!response.ok) {
                        this.hasError = true;
                        this.isLoading = false;
                        return;
                    }

                    const data = await response.json();

                    if (data.connected && data.track) {
                        const wasPlaying = this.track?.is_playing;
                        const wasTrackId = this.track?.track_id;

                        this.track = data.track;
                        this.trackPosition = data.track.progress_ms || 0;
                        this.isPlaying = data.track.is_playing || false;
                        this.lastSyncTime = Date.now();
                        this.hasError = false;

                        // Update change tracking
                        this.lastTrackId = this.track.track_id;
                        this.lastIsPlaying = this.track.is_playing;

                        // Calculate initial progress percentage
                        if (this.track.duration_ms > 0) {
                            this.progressPercentage =
                                (this.trackPosition / this.track.duration_ms) *
                                100;
                        }
                    } else {
                        this.track = null;
                        this.trackPosition = 0;
                        this.progressPercentage = 0;
                        this.lastTrackId = null;
                        this.lastIsPlaying = null;
                    }
                } catch (error) {
                    console.error("🎵 Alpine Spotify fetch error:", error);
                    this.hasError = true;
                    this.track = null;
                } finally {
                    this.isLoading = false;
                }
            },

            // Start minimal polling as fallback (only when SDK events are not available)
            startMinimalPolling() {
                if (this.pollingTimeoutId) {
                    clearTimeout(this.pollingTimeoutId);
                }

                // Only poll when modal is closed (SDK handles real-time when modal is open)
                if (this.isModalVisible && context === "modal") {
                    return; // SDK events handle real-time updates when modal is open
                }

                // Minimal polling every 10 seconds as fallback
                const poll = () => {
                    if (!this.isModalVisible && context === "modal") {
                        this.fetchTrack();
                        this.pollingTimeoutId = setTimeout(poll, 10000); // 10 seconds
                    }
                };

                poll();
            },

            // Start smooth progress tracking
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

                console.log(`🎵 Attempt ${attempt + 1}:`, {
                    hasSpan: !!trackNameSpan,
                    textContent: trackNameSpan?.textContent,
                    innerText: trackNameSpan?.innerText,
                    scrollWidth: trackNameSpan?.scrollWidth,
                    offsetWidth: trackNameSpan?.offsetWidth,
                    clientWidth: trackNameSpan?.clientWidth,
                    computedDisplay: trackNameSpan
                        ? window.getComputedStyle(trackNameSpan).display
                        : null,
                    computedVisibility: trackNameSpan
                        ? window.getComputedStyle(trackNameSpan).visibility
                        : null,
                });

                if (
                    container &&
                    trackNameSpan &&
                    trackNameSpan.scrollWidth > 0
                ) {
                    // Text is rendered, now we can check
                    this.isLongTrackName =
                        trackNameSpan.scrollWidth > container.offsetWidth;
                    console.log("🎵 Marquee check SUCCESS:", {
                        scrollWidth: trackNameSpan.scrollWidth,
                        containerWidth: container.offsetWidth,
                        isLong: this.isLongTrackName,
                        trackName: this.track?.track_name,
                        attempt: attempt + 1,
                    });
                } else if (attempt < maxAttempts) {
                    // Retry after a short delay
                    setTimeout(() => {
                        this.checkTrackNameLengthWithRetry(attempt + 1);
                    }, 50);
                } else {
                    console.log("🎵 Marquee check FAILED after max attempts");
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

                // Fetch current track immediately for instant response
                this.fetchTrack().then(() => {
                    // Start progress tracking (SDK events handle track updates)
                    this.startProgressTracking();
                });
            },

            onModalHide() {
                this.isModalVisible = false;
                // console.log('🎵 Modal hidden - switching to minimal polling');

                // Stop progress tracking
                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }

                // Start minimal polling as fallback
                this.startMinimalPolling();
            },

            // Handle immediate polling when changes are detected
            pollImmediately() {
                if (this.pollingTimeoutId) {
                    clearTimeout(this.pollingTimeoutId);
                    this.pollingTimeoutId = null;
                }

                // Poll immediately
                this.fetchTrack();
            },

            // Adjust polling interval dynamically based on track state
            adjustPollingInterval() {
                if (
                    this.pollingInterval &&
                    (this.isModalVisible || context !== "modal")
                ) {
                    // Restart polling with new interval
                    this.startPolling();
                }
            },

            // Cleanup on component destroy
            destroy() {
                // console.log('🧹 Cleaning up Alpine Spotify player...');

                if (this.pollingInterval) {
                    clearInterval(this.pollingInterval);
                    this.pollingInterval = null;
                }

                if (this.pollingTimeoutId) {
                    clearTimeout(this.pollingTimeoutId);
                    this.pollingTimeoutId = null;
                }

                if (this.progressInterval) {
                    clearInterval(this.progressInterval);
                    this.progressInterval = null;
                }
            },
        })
    );
});
