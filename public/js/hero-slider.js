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
        this.elements = {
            heroImage: document.getElementById("heroImage"),
            heroTitle: document.getElementById("heroTitle"),
            heroDescription: document.getElementById("heroDescription"),
            sliderButtons: document.querySelectorAll("#sliderNav button"),
            prevButton: document.getElementById("prevSlide"),
            nextButton: document.getElementById("nextSlide"),
        };

        this.autoAdvanceInterval = null;
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
        // Determine animation direction if not provided
        if (direction === null) {
            direction = this.getSlideDirection();
        }

        // Remove any existing animation classes
        this.elements.heroImage.classList.remove(
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
            this.elements.heroImage.classList.add("hero-image-exit-left");
        } else if (direction === "right") {
            this.elements.heroImage.classList.add("hero-image-exit-right");
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
            this.elements.heroImage.classList.remove(
                "hero-image-exit-left",
                "hero-image-exit-right"
            );

            // Apply entrance animation to new hero image
            if (direction === "left") {
                this.elements.heroImage.classList.add("hero-image-slide-left");
            } else if (direction === "right") {
                this.elements.heroImage.classList.add("hero-image-slide-right");
            }

            // Fade in text content
            this.elements.heroTitle.style.opacity = "1";
            this.elements.heroDescription.style.opacity = "1";

            // Update button states
            this.updateSliderButtons();

            // Restore transition after entrance animation completes
            setTimeout(() => {
                this.elements.heroImage.classList.remove(
                    "hero-image-slide-left",
                    "hero-image-slide-right"
                );
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
                    "dark:bg-white/50"
                );
                button.classList.add("w-12", "bg-primary-400");
            } else {
                button.classList.remove("w-12", "bg-primary-400");
                button.classList.add("w-4", "bg-gray-400", "dark:bg-white/50");
            }
        });
    }

    /**
     * Go to specific slide
     */
    goToSlide(slideIndex) {
        this.previousSlideIndex = this.currentSlide;
        this.currentSlide = slideIndex;
        this.updateSlider();
    }

    /**
     * Go to previous slide
     */
    previousSlide() {
        this.previousSlideIndex = this.currentSlide;
        this.currentSlide =
            this.currentSlide === 0
                ? this.slides.length - 1
                : this.currentSlide - 1;
        this.updateSlider("right");
    }

    /**
     * Go to next slide
     */
    nextSlide() {
        this.previousSlideIndex = this.currentSlide;
        this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        this.updateSlider("left");
    }

    /**
     * Start auto-advance
     */
    startAutoAdvance() {
        this.autoAdvanceInterval = setInterval(() => {
            this.nextSlide();
        }, 10000);
    }

    /**
     * Stop auto-advance
     */
    stopAutoAdvance() {
        if (this.autoAdvanceInterval) {
            clearInterval(this.autoAdvanceInterval);
            this.autoAdvanceInterval = null;
        }
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

        // Start auto-advance
        this.startAutoAdvance();

        // Initialize with correct theme and first slide content
        this.updateSlider();
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    new HeroSlider();
});
