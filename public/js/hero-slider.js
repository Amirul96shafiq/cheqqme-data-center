/**
 * Hero Slider for Login Page
 * Manages the hero image slider functionality with theme-aware images
 */
class HeroSlider {
    constructor() {
        this.slides = [
            {
                title: window.heroSliderLang?.title1,
                description: window.heroSliderLang?.description1,
                imageNumber: 1,
            },
            {
                title: window.heroSliderLang?.title2,
                description: window.heroSliderLang?.description2,
                imageNumber: 2,
            },
            {
                title: window.heroSliderLang?.title3,
                description: window.heroSliderLang?.description3,
                imageNumber: 3,
            },
            {
                title: window.heroSliderLang?.title4,
                description: window.heroSliderLang?.description4,
                imageNumber: 4,
            },
            {
                title: window.heroSliderLang?.title5,
                description: window.heroSliderLang?.description5,
                imageNumber: 5,
            },
            {
                title: window.heroSliderLang?.title6,
                description: window.heroSliderLang?.description6,
                imageNumber: 6,
            },
        ];

        this.currentSlide = 0;
        this.previousSlideIndex = 0;
        this.isAnimating = false;
        this.animationQueue = [];
        this.maxQueueSize = 1; // Maximum number of queued animations
        this.lastClickTime = Date.now(); // Initialize with current time
        this.clickDebounceDelay = 500; // Minimum time between clicks (ms)
        this.elements = {
            heroImage: document.getElementById("heroImage"),
            heroImageWrapper: document.getElementById("heroImageWrapper"),
            heroTitle: document.getElementById("heroTitle"),
            heroDescription: document.getElementById("heroDescription"),
            sliderButtons: document.querySelectorAll("#sliderNav button"),
            prevButton: document.getElementById("prevSlide"),
            nextButton: document.getElementById("nextSlide"),
            pausePlayButton: document.getElementById("pausePlaySlide"),
            playIcon: document.getElementById("playIcon"),
            pauseIcon: document.getElementById("pauseIcon"),
        };

        this.autoAdvanceInterval = null;
        this.progressInterval = null;
        this.autoAdvanceDuration = 10000; // 10 seconds in milliseconds
        this.progressUpdateInterval = 50; // Update progress every 50ms for smooth animation
        this.isPaused = false; // Track pause state
        this.init();
    }

    /**
     * Get current theme mode
     */
    getCurrentTheme() {
        const html = document.documentElement;
        if (html.classList.contains("dark")) {
            return "dark";
        } else if (html.classList.contains("light")) {
            return "light";
        } else {
            // Check system preference if no explicit theme is set
            return window.matchMedia("(prefers-color-scheme: dark)").matches
                ? "dark"
                : "light";
        }
    }

    /**
     * Get theme-specific image path
     */
    getThemeImagePath(imageNumber) {
        const theme = this.getCurrentTheme();
        return `${
            window.location.origin
        }/images/hero-images/${theme}/${imageNumber
            .toString()
            .padStart(2, "0")}.png`;
    }

    /**
     * Update slider content with coordinated exit and entrance animations
     */
    updateSlider(direction = null) {
        // Prevent overlapping animations
        if (this.isAnimating) {
            // console.log("Animation already in progress, ignoring request");
            return;
        }

        // Determine animation direction if not provided
        if (direction === null) {
            direction = this.getSlideDirection();
        }

        // Set animation state
        this.isAnimating = true;

        // Remove any existing animation classes
        this.elements.heroImageWrapper.classList.remove(
            "hero-image-slide-left",
            "hero-image-slide-right",
            "hero-image-exit-left",
            "hero-image-exit-right"
        );

        // Fade out text content
        this.elements.heroTitle.style.opacity = "0";
        this.elements.heroDescription.style.opacity = "0";

        // Play exit animation for current image
        if (direction === "left") {
            this.elements.heroImageWrapper.classList.add(
                "hero-image-exit-left"
            );
        } else if (direction === "right") {
            this.elements.heroImageWrapper.classList.add(
                "hero-image-exit-right"
            );
        }

        // Wait for exit animation to complete, then play entrance animation
        setTimeout(() => {
            // Update content with current theme
            const slide = this.slides[this.currentSlide];
            this.elements.heroImage.src = this.getThemeImagePath(
                slide.imageNumber
            );
            this.elements.heroTitle.textContent = slide.title;
            this.elements.heroDescription.innerHTML = slide.description;

            // Remove exit animation class
            this.elements.heroImageWrapper.classList.remove(
                "hero-image-exit-left",
                "hero-image-exit-right"
            );

            // Apply entrance animation to new hero image
            if (direction === "left") {
                this.elements.heroImageWrapper.classList.add(
                    "hero-image-slide-left"
                );
            } else if (direction === "right") {
                this.elements.heroImageWrapper.classList.add(
                    "hero-image-slide-right"
                );
            }

            // Fade in text content
            this.elements.heroTitle.style.opacity = "1";
            this.elements.heroDescription.style.opacity = "1";

            // Update button states
            this.updateSliderButtons();

            // Restore transition after entrance animation completes
            setTimeout(() => {
                this.elements.heroImageWrapper.classList.remove(
                    "hero-image-slide-left",
                    "hero-image-slide-right"
                );
                // Reset animation state to allow next animation
                this.isAnimating = false;
                // console.log("Animation completed, ready for next transition");

                // Reset and start progress bar for current slide immediately
                this.resetProgressBars();

                // Only start progress bar and auto-advance if not paused
                if (!this.isPaused) {
                    this.startProgressBar();
                    this.startAutoAdvance();
                }

                // Process any queued animations
                this.processQueue();
            }, 1200); // Match entrance animation duration
        }, 800); // Wait for exit animation (0.8s) to complete
    }

    /**
     * Determine slide direction based on previous and current slide indices
     */
    getSlideDirection() {
        const totalSlides = this.slides.length;
        const prev = this.previousSlideIndex;
        const current = this.currentSlide;

        // Handle wrap-around cases
        if (prev === totalSlides - 1 && current === 0) {
            // Going from last slide to first (right to left)
            return "right";
        } else if (prev === 0 && current === totalSlides - 1) {
            // Going from first slide to last (left to right)
            return "left";
        } else if (current > prev) {
            // Moving forward (left to right)
            return "left";
        } else if (current < prev) {
            // Moving backward (right to left)
            return "right";
        }

        // Default case (shouldn't happen in normal operation)
        return "left";
    }

    /**
     * Update slider navigation buttons
     */
    updateSliderButtons() {
        this.elements.sliderButtons.forEach((button, index) => {
            if (index === this.currentSlide) {
                button.classList.remove(
                    "w-4",
                    "bg-gray-400",
                    "dark:bg-gray-200"
                );
                button.classList.add("w-12", "bg-primary-400");
            } else {
                button.classList.remove("w-12", "bg-primary-400");
                button.classList.add("w-4", "bg-gray-400", "dark:bg-gray-200");
            }
        });
    }

    /**
     * Reset all progress bars to 0%
     */
    resetProgressBars() {
        for (let i = 0; i < this.slides.length; i++) {
            const progressBar = document.getElementById(`progressBar${i}`);
            if (progressBar) {
                progressBar.style.width = "0%";
            }
        }
    }

    /**
     * Start progress bar animation for current slide
     */
    startProgressBar() {
        // Clear any existing progress interval
        this.stopProgressBar();

        const progressBar = document.getElementById(
            `progressBar${this.currentSlide}`
        );
        if (!progressBar) return;

        let progress = 0;
        const increment =
            (this.progressUpdateInterval / this.autoAdvanceDuration) * 100;

        this.progressInterval = setInterval(() => {
            progress += increment;
            if (progress >= 100) {
                progress = 100;
                this.stopProgressBar();
            }
            progressBar.style.width = `${progress}%`;
        }, this.progressUpdateInterval);
    }

    /**
     * Stop progress bar animation
     */
    stopProgressBar() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }

    /**
     * Check if animation is allowed (not currently animating)
     */
    canAnimate() {
        return !this.isAnimating;
    }

    /**
     * Process the animation queue
     */
    processQueue() {
        if (this.animationQueue.length > 0 && this.canAnimate()) {
            const nextAction = this.animationQueue.shift();
            // console.log("Processing queued animation:", nextAction.type);

            switch (nextAction.type) {
                case "previous":
                    this.previousSlide();
                    break;
                case "next":
                    this.nextSlide();
                    break;
                case "goto":
                    this.goToSlide(nextAction.slideIndex);
                    break;
            }
        }
    }

    /**
     * Add animation to queue
     */
    queueAnimation(type, slideIndex = null) {
        // Check if queue is full
        if (this.isQueueFull()) {
            // console.log("Queue is full, ignoring animation request:", type);
            this.showQueueFullFeedback();
            return false;
        }

        this.animationQueue.push({ type, slideIndex });
        // console.log(
        //     "Queued animation:",
        //     type,
        //     "Queue length:",
        //     this.animationQueue.length
        // );
        this.processQueue();
        return true;
    }

    /**
     * Check if click is too rapid (debouncing)
     */
    isClickTooRapid() {
        const now = Date.now();
        const timeSinceLastClick = now - this.lastClickTime;

        if (timeSinceLastClick < this.clickDebounceDelay) {
            // console.log(
            //     "Click too rapid, ignoring. Time since last click:",
            //     timeSinceLastClick + "ms"
            // );
            return true;
        }

        this.lastClickTime = now;
        return false;
    }

    /**
     * Check if queue is full
     */
    isQueueFull() {
        return this.animationQueue.length >= this.maxQueueSize;
    }

    /**
     * Clear the animation queue
     */
    clearQueue() {
        const clearedCount = this.animationQueue.length;
        this.animationQueue = [];
        // console.log("Cleared", clearedCount, "queued animations");
    }

    /**
     * Show queue full feedback
     */
    showQueueFullFeedback() {
        // Add a temporary visual indicator
        const buttons = [this.elements.prevButton, this.elements.nextButton];
        buttons.forEach((button) => {
            if (button) {
                button.style.opacity = "0.5";
                button.style.pointerEvents = "none";

                // Restore after a short delay
                setTimeout(() => {
                    button.style.opacity = "1";
                    button.style.pointerEvents = "auto";
                }, 1000);
            }
        });

        // console.log("Queue full - buttons temporarily disabled");
    }

    /**
     * Go to specific slide
     */
    goToSlide(slideIndex) {
        // Check for rapid clicks
        if (this.isClickTooRapid()) {
            return;
        }

        if (!this.canAnimate()) {
            // console.log("Cannot animate, queuing request");
            const queued = this.queueAnimation("goto", slideIndex);
            if (!queued) {
                // console.log("Queue full, request ignored");
            }
            return;
        }

        // Pause auto-advance when manually navigating
        this.pauseAutoAdvance();

        this.previousSlideIndex = this.currentSlide;
        this.currentSlide = slideIndex;
        this.dispatchSlideChangeEvent();
        this.updateSlider();
    }

    /**
     * Go to previous slide
     */
    previousSlide() {
        // Check for rapid clicks
        if (this.isClickTooRapid()) {
            return;
        }

        if (!this.canAnimate()) {
            // console.log("Cannot animate, queuing request");
            const queued = this.queueAnimation("previous");
            if (!queued) {
                // console.log("Queue full, request ignored");
            }
            return;
        }

        // Pause auto-advance when manually navigating
        this.pauseAutoAdvance();

        this.previousSlideIndex = this.currentSlide;
        this.currentSlide =
            this.currentSlide === 0
                ? this.slides.length - 1
                : this.currentSlide - 1;
        this.dispatchSlideChangeEvent();
        this.updateSlider("right");
    }

    /**
     * Go to next slide
     */
    nextSlide(isAutoAdvance = false) {
        // Check for rapid clicks
        if (this.isClickTooRapid()) {
            return;
        }

        if (!this.canAnimate()) {
            // console.log("Cannot animate, queuing request");
            const queued = this.queueAnimation("next");
            if (!queued) {
                // console.log("Queue full, request ignored");
            }
            return;
        }

        // Only pause auto-advance when manually navigating (not during auto-advance)
        if (!isAutoAdvance) {
            this.pauseAutoAdvance();
        }

        this.previousSlideIndex = this.currentSlide;
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.dispatchSlideChangeEvent();
        this.updateSlider("left");
    }

    /**
     * Start auto-advance
     */
    startAutoAdvance() {
        // Don't start if paused
        if (this.isPaused) {
            return;
        }

        // Clear any existing interval
        if (this.autoAdvanceInterval) {
            clearInterval(this.autoAdvanceInterval);
        }

        this.autoAdvanceInterval = setInterval(() => {
            // Only auto-advance if not currently animating and not paused
            if (this.canAnimate() && !this.isPaused) {
                this.nextSlide(true); // Pass true to indicate this is auto-advance
            } else {
                // console.log(
                //     "Skipping auto-advance, animation in progress or paused"
                // );
            }
        }, this.autoAdvanceDuration);
    }

    /**
     * Stop auto-advance
     */
    stopAutoAdvance() {
        if (this.autoAdvanceInterval) {
            clearInterval(this.autoAdvanceInterval);
            this.autoAdvanceInterval = null;
        }
        this.stopProgressBar();
    }

    /**
     * Toggle pause/play state
     */
    togglePausePlay() {
        if (this.isPaused) {
            this.resumeAutoAdvance();
        } else {
            this.pauseAutoAdvance();
        }
    }

    /**
     * Pause auto-advance
     */
    pauseAutoAdvance() {
        this.isPaused = true;
        this.stopAutoAdvance();
        this.updatePausePlayButton();
        // console.log("Auto-advance paused");
    }

    /**
     * Resume auto-advance
     */
    resumeAutoAdvance() {
        this.isPaused = false;
        this.startAutoAdvance();
        this.startProgressBar();
        this.updatePausePlayButton();
        // console.log("Auto-advance resumed");
    }

    /**
     * Update pause/play button appearance
     */
    updatePausePlayButton() {
        if (this.isPaused) {
            // Show play icon, hide pause icon
            this.elements.playIcon.classList.remove("hidden");
            this.elements.pauseIcon.classList.add("hidden");
            this.elements.pausePlayButton.setAttribute(
                "aria-label",
                "Resume auto-slide"
            );
        } else {
            // Show pause icon, hide play icon
            this.elements.playIcon.classList.add("hidden");
            this.elements.pauseIcon.classList.remove("hidden");
            this.elements.pausePlayButton.setAttribute(
                "aria-label",
                "Pause auto-slide"
            );
        }
    }

    /**
     * Dispatch slide change event for Alpine.js synchronization
     */
    dispatchSlideChangeEvent() {
        document.dispatchEvent(
            new CustomEvent("heroSlideChanged", {
                detail: { slideIndex: this.currentSlide },
            })
        );
    }

    /**
     * Update all slide images when theme changes
     */
    updateAllSlideImages() {
        this.slides.forEach((slide, index) => {
            slide.image = this.getThemeImagePath(slide.imageNumber);
        });
        // Update current slide with new theme
        this.updateSlider();

        // Clear any animation classes from wrapper during theme change
        this.elements.heroImageWrapper.classList.remove(
            "hero-image-slide-left",
            "hero-image-slide-right",
            "hero-image-exit-left",
            "hero-image-exit-right"
        );
    }

    /**
     * Initialize the slider
     */
    init() {
        // Set initial content
        const initialSlide = this.slides[0];
        this.elements.heroImage.src = this.getThemeImagePath(
            initialSlide.imageNumber
        );
        this.elements.heroTitle.textContent = initialSlide.title;
        this.elements.heroDescription.innerHTML = initialSlide.description;

        // Set up event listeners
        this.elements.sliderButtons.forEach((button) => {
            button.addEventListener("click", () => {
                this.goToSlide(parseInt(button.dataset.slide));
            });
        });

        this.elements.prevButton.addEventListener("click", () => {
            this.previousSlide();
        });

        this.elements.nextButton.addEventListener("click", () => {
            this.nextSlide();
        });

        this.elements.pausePlayButton.addEventListener("click", () => {
            this.togglePausePlay();
        });

        // Listen for theme changes
        const themeToggleButtons =
            document.querySelectorAll(".theme-toggle-btn");
        themeToggleButtons.forEach((button) => {
            button.addEventListener("click", () => {
                // Small delay to allow theme classes to be applied
                setTimeout(() => {
                    this.updateAllSlideImages();
                }, 100);
            });
        });

        // Listen for system theme changes
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                // Only update if no explicit theme is set
                const html = document.documentElement;
                if (
                    !html.classList.contains("dark") &&
                    !html.classList.contains("light")
                ) {
                    this.updateAllSlideImages();
                }
            });

        // Initialize previousSlideIndex before first update
        this.previousSlideIndex = 0;

        // Initialize with correct theme and first slide content
        this.updateSlider();

        // Initialize pause/play button state
        this.updatePausePlayButton();

        // Start auto-advance and progress bar after initial animation completes
        setTimeout(() => {
            this.startAutoAdvance();
            this.startProgressBar();
        }, 1300); // Wait for initial animation to complete
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    new HeroSlider();
});
