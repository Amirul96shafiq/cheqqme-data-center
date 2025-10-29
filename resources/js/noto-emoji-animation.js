// Noto Emoji Animation integration using Lottie
// Based on: https://googlefonts.github.io/noto-emoji-animation/documentation

// Configuration - set to false to disable animated emojis
window.NOTO_EMOJI_ANIMATION_ENABLED =
    window.NOTO_EMOJI_ANIMATION_ENABLED !== false;

// Suppress console errors from Lottie for 404s on animation URLs
(function () {
    const originalConsoleError = console.error;
    const originalConsoleWarn = console.warn;
    const originalConsoleLog = console.log;

    // Intercept console.error to suppress 404 errors for animation URLs
    console.error = function (...args) {
        const message = args.join(" ");
        // Suppress 404 errors for Noto Emoji animation URLs
        if (
            (message.includes("404") || message.includes("Not Found")) &&
            (message.includes("notoemoji") ||
                message.includes("lottie.json") ||
                message.includes("/latest/"))
        ) {
            // Suppress this error - it's expected when animations don't exist
            return;
        }
        originalConsoleError.apply(console, args);
    };

    // Also intercept console.warn for Lottie warnings
    console.warn = function (...args) {
        const message = args.join(" ");
        if (
            (message.includes("404") || message.includes("Not Found")) &&
            (message.includes("notoemoji") ||
                message.includes("lottie.json") ||
                message.includes("/latest/"))
        ) {
            // Suppress this warning
            return;
        }
        originalConsoleWarn.apply(console, args);
    };

    // Also intercept console.log for Lottie logs
    console.log = function (...args) {
        const message = args.join(" ");
        if (
            (message.includes("404") || message.includes("Not Found")) &&
            (message.includes("notoemoji") ||
                message.includes("lottie.json") ||
                message.includes("/latest/"))
        ) {
            // Suppress this log
            return;
        }
        originalConsoleLog.apply(console, args);
    };
})();

// Optional: custom mapping of emoji to animation JSON URLs
// If provided, this map overrides the default auto-generated URLs
// Useful for hosting animations locally or using different CDNs
window.NOTO_EMOJI_ANIMATION_MAP = window.NOTO_EMOJI_ANIMATION_MAP || {};

/**
 * Convert emoji Unicode character to hex code for Noto animation file path
 */
function getEmojiHexCode(emoji) {
    const codePoints = [];
    for (let i = 0; i < emoji.length; i++) {
        const code = emoji.codePointAt(i);
        if (code > 0xffff) {
            i++; // Skip surrogate pair
        }
        codePoints.push(code.toString(16).toLowerCase().padStart(4, "0"));
    }
    return codePoints.join("_");
}

/**
 * Get animation file URL for an emoji
 * 1) Use explicit mapping if provided
 * 2) Otherwise, generate URL using Google's Noto Emoji CDN pattern
 */
function getNotoAnimationUrl(emoji) {
    // First check if there's an explicit mapping
    if (
        window.NOTO_EMOJI_ANIMATION_MAP &&
        window.NOTO_EMOJI_ANIMATION_MAP[emoji]
    ) {
        return window.NOTO_EMOJI_ANIMATION_MAP[emoji];
    }

    // Generate URL using Google's Noto Emoji CDN
    // Pattern: https://fonts.gstatic.com/s/e/notoemoji/latest/{hexCode}/lottie.json
    const hexCode = getEmojiHexCode(emoji);
    return `https://fonts.gstatic.com/s/e/notoemoji/latest/${hexCode}/lottie.json`;
}

/**
 * Check if Lottie is loaded
 */
function isLottieAvailable() {
    return typeof lottie !== "undefined" && lottie !== null;
}

/**
 * Create a Noto animated emoji element using Lottie
 * Falls back to static emoji if animation is not available
 */
function createNotoAnimatedEmoji(emoji, size = "1em") {
    const container = document.createElement("span");
    container.className = "noto-animated-emoji-wrapper";
    container.setAttribute("data-emoji", emoji);
    container.style.display = "inline-block";
    container.style.width = size;
    container.style.height = size;
    container.style.verticalAlign = "middle";
    container.style.position = "relative";
    container.style.fontSize = size; // Ensure proper sizing

    // Check if animated emojis are enabled
    if (!window.NOTO_EMOJI_ANIMATION_ENABLED) {
        container.textContent = emoji;
        container.style.textAlign = "center";
        container.style.lineHeight = size;
        return container;
    }

    // Create Lottie animation container
    const lottieContainer = document.createElement("div");
    lottieContainer.className = "noto-emoji-lottie-container";
    lottieContainer.style.width = "100%";
    lottieContainer.style.height = "100%";
    lottieContainer.style.display = "flex";
    lottieContainer.style.alignItems = "center";
    lottieContainer.style.justifyContent = "center";

    // Fallback to static emoji initially
    container.textContent = emoji;
    container.style.textAlign = "center";
    container.style.lineHeight = size;

    // Try to load animated version if Lottie is available
    if (isLottieAvailable()) {
        const animationUrl = getNotoAnimationUrl(emoji);
        let anim = null;
        let animationLoaded = false;
        let animationAttempted = false;

        // Try to load the animation - if it doesn't exist, data_failed will handle it
        try {
            animationAttempted = true;
            // Remove static emoji and add Lottie container
            container.textContent = "";
            container.appendChild(lottieContainer);

            anim = lottie.loadAnimation({
                container: lottieContainer,
                renderer: "svg",
                loop: false, // Don't loop - play once on load and on hover
                autoplay: true, // Play once on initial load
                path: animationUrl,
            });

            // Handle animation load failures - immediately show static emoji if animation doesn't exist
            anim.addEventListener("data_failed", () => {
                // Animation doesn't exist - immediately show static emoji and clean up
                container.textContent = emoji;
                if (lottieContainer.parentNode) {
                    lottieContainer.remove();
                }
                if (anim) {
                    anim.destroy();
                    anim = null;
                }
            });

            // Mark as successfully loaded when config is ready
            anim.addEventListener("config_ready", () => {
                animationLoaded = true;
            });

            // Reset to first frame when animation completes (shows static state)
            anim.addEventListener("complete", () => {
                if (anim && animationLoaded) {
                    anim.goToAndStop(0); // Show first frame (static) after animation
                }
            });

            anim.setSpeed(1);

            // Play animation on hover (only if animation successfully loaded)
            container.addEventListener("mouseenter", () => {
                if (animationLoaded && anim) {
                    anim.goToAndPlay(0); // Restart animation from beginning
                }
            });
        } catch (error) {
            // Error initializing animation, keep static emoji silently
            if (animationAttempted) {
                container.textContent = emoji;
                if (lottieContainer.parentNode) {
                    lottieContainer.remove();
                }
            }
        }
    }

    return container;
}

/**
 * Replace static emojis in HTML content with animated versions
 * This processes already-rendered HTML and replaces emoji characters
 */
function replaceEmojisInElement(element) {
    if (!element) return;

    // Comprehensive emoji regex pattern
    const emojiRegex =
        /[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|[\u{1F900}-\u{1F9FF}]|[\u{1FA00}-\u{1FAFF}]|[\u{1FA70}-\u{1FAFF}]/gu;

    // Process all text nodes in the element
    const walker = document.createTreeWalker(
        element,
        NodeFilter.SHOW_TEXT,
        {
            acceptNode: function (node) {
                // Skip if parent is already a noto wrapper
                if (
                    node.parentNode &&
                    node.parentNode.classList.contains(
                        "noto-animated-emoji-wrapper"
                    )
                ) {
                    return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
            },
        },
        false
    );

    const textNodes = [];
    let node;
    while ((node = walker.nextNode())) {
        if (emojiRegex.test(node.textContent)) {
            textNodes.push(node);
        }
    }

    textNodes.forEach((textNode) => {
        const text = textNode.textContent;
        const parts = [];
        const emojis = [];
        let lastIndex = 0;
        let match;

        // Reset regex
        emojiRegex.lastIndex = 0;

        while ((match = emojiRegex.exec(text)) !== null) {
            // Add text before emoji
            if (match.index > lastIndex) {
                parts.push(text.substring(lastIndex, match.index));
            }
            // Add emoji
            emojis.push(match[0]);
            parts.push(null); // Placeholder for emoji
            lastIndex = emojiRegex.lastIndex;
        }

        // Add remaining text
        if (lastIndex < text.length) {
            parts.push(text.substring(lastIndex));
        }

        // Create fragment to replace text node
        if (emojis.length > 0) {
            const fragment = document.createDocumentFragment();
            let emojiIndex = 0;

            parts.forEach((part) => {
                if (part === null) {
                    // Insert animated emoji
                    const animatedEmoji = createNotoAnimatedEmoji(
                        emojis[emojiIndex++]
                    );
                    fragment.appendChild(animatedEmoji);
                } else if (part) {
                    // Insert text
                    fragment.appendChild(document.createTextNode(part));
                }
            });

            textNode.parentNode.replaceChild(fragment, textNode);
        }
    });
}

// Export functions for use in chatbot.js
window.NotoEmojiAnimation = {
    createAnimatedEmoji: createNotoAnimatedEmoji,
    replaceEmojisInElement: replaceEmojisInElement,
    getEmojiHexCode: getEmojiHexCode,
    getAnimationUrl: getNotoAnimationUrl,
    isLottieAvailable: isLottieAvailable,
};
