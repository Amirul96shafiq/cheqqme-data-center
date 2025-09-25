/**
 * TypingAnimation - A reusable JavaScript class for creating typing animations
 *
 * This class provides a flexible way to create typing animations on any text element.
 * It supports random text cycling, customizable speeds, and various configuration options.
 *
 * @author CheQQme Data Center
 * @version 1.0.0
 *
 * Usage:
 *   const typing = new TypingAnimation('#my-element', {
 *     texts: ['Hello', 'World', 'Welcome'],
 *     interval: 5000
 *   });
 *
 * @example
 *   // Basic usage
 *   const typing = new TypingAnimation('.hero-title', {
 *     texts: ['Innovative Solutions', 'Creative Design', 'Expert Development'],
 *     interval: 8000
 *   });
 *
 *   // Advanced configuration
 *   const advancedTyping = new TypingAnimation('#subtitle', {
 *     texts: ['Text 1', 'Text 2', 'Text 3'],
 *     interval: 10000,
 *     typeSpeed: { min: 50, max: 100 },
 *     eraseSpeed: { min: 30, max: 50 },
 *     pauseBetween: 300,
 *     randomize: true,
 *     autoStart: true,
 *     onTextChange: (text, index) => console.log(`Changed to: ${text}`),
 *     onComplete: () => console.log('Cycle completed')
 *   });
 *
 *   // Control methods
 *   typing.start();
 *   typing.pause();
 *   typing.resume();
 *   typing.setTexts(['New', 'Text', 'Array']);
 *   typing.setInterval(3000);
 *   typing.destroy();
 */
class TypingAnimation {
    /**
     * Creates a new TypingAnimation instance
     *
     * @param {string|HTMLElement} element - CSS selector string or DOM element
     * @param {Object} options - Configuration options
     * @param {Array} options.texts - Array of texts to cycle through
     * @param {number} options.interval - Time between text changes (ms)
     * @param {Object} options.typeSpeed - Typing speed range {min, max} (ms)
     * @param {Object} options.eraseSpeed - Erasing speed range {min, max} (ms)
     * @param {number} options.pauseBetween - Pause between erase and type (ms)
     * @param {string} options.cursorClass - CSS class for cursor
     * @param {boolean} options.randomize - Whether to randomize text selection
     * @param {boolean} options.loop - Whether to loop through texts
     * @param {boolean} options.autoStart - Whether to start automatically
     * @param {Function} options.onStart - Callback when animation starts
     * @param {Function} options.onComplete - Callback when animation completes
     * @param {Function} options.onTextChange - Callback when text changes
     */
    constructor(element, options = {}) {
        this.element =
            typeof element === "string"
                ? document.querySelector(element)
                : element;
        if (!this.element) {
            console.warn("TypingAnimation: Element not found");
            return;
        }

        // Default configuration
        this.config = {
            texts: [], // Array of texts to cycle through
            interval: 10000, // Time between text changes (ms)
            typeSpeed: { min: 40, max: 60 }, // Typing speed range (ms)
            eraseSpeed: { min: 20, max: 30 }, // Erasing speed range (ms)
            pauseBetween: 200, // Pause between erase and type (ms)
            cursorClass: "typing-cursor", // CSS class for cursor
            randomize: true, // Whether to randomize text selection
            loop: true, // Whether to loop through texts
            autoStart: true, // Whether to start automatically
            onStart: null, // Callback when animation starts
            onComplete: null, // Callback when animation completes
            onTextChange: null, // Callback when text changes
            ...options,
        };

        this.currentIndex = 0;
        this.isTyping = false;
        this.intervalId = null;
        this.isDestroyed = false;
        this.usedIndices = new Set();

        // Initialize
        this.init();
    }

    /**
     * Initialize the animation
     * @private
     */
    init() {
        if (this.config.texts.length === 0) {
            console.warn("TypingAnimation: No texts provided");
            return;
        }

        // Add cursor to initial text
        this.addCursor();

        if (this.config.autoStart) {
            this.start();
        }
    }

    /**
     * Add typing cursor to the element
     * @private
     */
    addCursor() {
        if (!this.element.querySelector(`.${this.config.cursorClass}`)) {
            this.element.innerHTML += `<span class="${this.config.cursorClass}"></span>`;
        }
    }

    /**
     * Get next random index ensuring no consecutive repeats
     * @private
     * @returns {number} Next index to use
     */
    getRandomIndex() {
        if (!this.config.randomize || this.config.texts.length <= 1) {
            return (this.currentIndex + 1) % this.config.texts.length;
        }

        // If we haven't used all texts yet, pick from unused ones
        if (!this.usedIndices) {
            this.usedIndices = new Set();
        }

        // If all texts have been used, reset the used set
        if (this.usedIndices.size >= this.config.texts.length) {
            this.usedIndices.clear();
        }

        // Get available indices (excluding current and already used)
        const availableIndices = [];
        for (let i = 0; i < this.config.texts.length; i++) {
            if (i !== this.currentIndex && !this.usedIndices.has(i)) {
                availableIndices.push(i);
            }
        }

        // If no available indices (shouldn't happen), fallback to random
        if (availableIndices.length === 0) {
            let randomIndex;
            do {
                randomIndex = Math.floor(
                    Math.random() * this.config.texts.length
                );
            } while (randomIndex === this.currentIndex);
            return randomIndex;
        }

        // Pick a random index from available ones
        const randomIndex =
            availableIndices[
                Math.floor(Math.random() * availableIndices.length)
            ];
        this.usedIndices.add(randomIndex);

        return randomIndex;
    }

    /**
     * Type text character by character
     * @private
     * @param {string} text - Text to type
     * @param {Function} callback - Callback when typing is complete
     */
    typeText(text, callback) {
        if (this.isTyping || this.isDestroyed) return;

        this.isTyping = true;
        let currentText = "";
        let index = 0;

        const typeNextChar = () => {
            if (index < text.length && !this.isDestroyed) {
                currentText += text[index];
                this.element.innerHTML =
                    currentText +
                    `<span class="${this.config.cursorClass}"></span>`;
                index++;

                const speed =
                    this.config.typeSpeed.min +
                    Math.random() *
                        (this.config.typeSpeed.max - this.config.typeSpeed.min);
                setTimeout(typeNextChar, speed);
            } else {
                this.isTyping = false;
                if (callback) callback();
            }
        };

        typeNextChar();
    }

    /**
     * Erase text character by character
     * @private
     * @param {Function} callback - Callback when erasing is complete
     */
    eraseText(callback) {
        if (this.isTyping || this.isDestroyed) return;

        this.isTyping = true;
        let currentText = this.element.textContent.trim();

        const eraseNextChar = () => {
            if (currentText.length > 0 && !this.isDestroyed) {
                currentText = currentText.slice(0, -1);
                this.element.innerHTML =
                    currentText +
                    `<span class="${this.config.cursorClass}"></span>`;

                const speed =
                    this.config.eraseSpeed.min +
                    Math.random() *
                        (this.config.eraseSpeed.max -
                            this.config.eraseSpeed.min);
                setTimeout(eraseNextChar, speed);
            } else {
                this.element.innerHTML = `<span class="${this.config.cursorClass}"></span>`;
                this.isTyping = false;
                if (callback) callback();
            }
        };

        eraseNextChar();
    }

    /**
     * Change to next text
     * @private
     */
    changeText() {
        if (this.isTyping || this.isDestroyed) return;

        const nextIndex = this.getRandomIndex();
        const nextText = this.config.texts[nextIndex];
        const currentText = this.element.textContent.trim();

        if (nextText !== currentText) {
            this.eraseText(() => {
                setTimeout(() => {
                    this.typeText(nextText, () => {
                        this.currentIndex = nextIndex;
                        if (this.config.onTextChange) {
                            this.config.onTextChange(nextText, nextIndex);
                        }
                    });
                }, this.config.pauseBetween);
                if (this.config.onComplete) {
                    this.config.onComplete();
                }
            });
        }
    }

    /**
     * Start the animation
     * @public
     */
    start() {
        if (this.isDestroyed) return;

        this.stop(); // Clear any existing interval
        this.intervalId = setInterval(
            () => this.changeText(),
            this.config.interval
        );

        if (this.config.onStart) {
            this.config.onStart();
        }
    }

    /**
     * Stop the animation
     * @public
     */
    stop() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    /**
     * Destroy the animation instance
     * @public
     */
    destroy() {
        this.stop();
        this.isDestroyed = true;
        this.isTyping = false;
    }

    /**
     * Update the texts array
     * @public
     * @param {Array} texts - New array of texts
     */
    setTexts(texts) {
        this.config.texts = texts;
        this.currentIndex = 0;
        this.usedIndices.clear();
    }

    /**
     * Update the interval
     * @public
     * @param {number} interval - New interval in milliseconds
     */
    setInterval(interval) {
        this.config.interval = interval;
        if (this.intervalId) {
            this.start(); // Restart with new interval
        }
    }

    /**
     * Pause the animation (alias for stop)
     * @public
     */
    pause() {
        this.stop();
    }

    /**
     * Resume the animation (alias for start)
     * @public
     */
    resume() {
        this.start();
    }
}

// Export for use in modules
if (typeof module !== "undefined" && module.exports) {
    module.exports = TypingAnimation;
}

// Make available globally
if (typeof window !== "undefined") {
    window.TypingAnimation = TypingAnimation;
}
