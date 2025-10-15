/**
 * Pure Alpine.js Spotify Player Component
 * No Livewire - avoids snapshot conflicts with Filament resource pages
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("spotifyPlayerAlpine", (context, userId) => ({
        // State
        track: null,
        isLoading: true,
        hasError: false,
        trackPosition: 0,
        progressPercentage: 0,

        // Intervals
        pollingInterval: null,
        progressInterval: null,

        // Tracking
        lastSyncTime: null,
        isPlaying: false,

        // Initialize the player
        async initPlayer() {
            // console.log('🎵 Alpine Spotify Player: Initializing...', { context, userId });

            // Initial fetch
            await this.fetchTrack();

            // Start polling
            this.startPolling();

            // Start progress tracking
            this.startProgressTracking();
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
                    this.track = data.track;
                    this.trackPosition = data.track.progress_ms || 0;
                    this.isPlaying = data.track.is_playing || false;
                    this.lastSyncTime = Date.now();
                    this.hasError = false;

                    // Calculate initial progress percentage
                    if (this.track.duration_ms > 0) {
                        this.progressPercentage =
                            (this.trackPosition / this.track.duration_ms) * 100;
                    }
                } else {
                    this.track = null;
                    this.trackPosition = 0;
                    this.progressPercentage = 0;
                }
            } catch (error) {
                console.error("🎵 Alpine Spotify fetch error:", error);
                this.hasError = true;
                this.track = null;
            } finally {
                this.isLoading = false;
            }
        },

        // Start polling for track updates
        startPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }

            // Poll every 3 seconds if track is playing, 10 seconds if not
            const pollInterval = () => (this.track ? 3000 : 10000);

            this.pollingInterval = setInterval(() => {
                this.fetchTrack();
            }, pollInterval());
        },

        // Start smooth progress tracking
        startProgressTracking() {
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
            }

            // Update progress every 100ms for smooth animation
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
            }, 100);
        },

        // Retry checking track name length until text is rendered
        checkTrackNameLengthWithRetry(attempt = 0) {
            const maxAttempts = 10;

            const container = this.$el?.querySelector(".track-name-container");
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

            if (container && trackNameSpan && trackNameSpan.scrollWidth > 0) {
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
            const container = this.$el?.querySelector(".track-name-container");
            const trackNameDiv = container?.querySelector("div");
            const trackNameSpan = trackNameDiv?.querySelector("span");

            if (container && trackNameSpan && trackNameSpan.scrollWidth > 0) {
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

        // Cleanup on component destroy
        destroy() {
            // console.log('🧹 Cleaning up Alpine Spotify player...');

            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }

            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
        },
    }));
});
