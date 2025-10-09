/**
 * Marquee Animation Alpine.js Component
 * Reusable component for scrolling text animations
 */

document.addEventListener("alpine:init", () => {
    Alpine.data("marqueeAnimation", (text, minLength = 25) => ({
        isLongText: false,

        init() {
            this.checkTextLength(text, minLength);
        },

        checkTextLength(text, minLength) {
            this.isLongText = text && text.length > minLength;
        },

        // Method to update text dynamically if needed
        updateText(newText, minLength = 25) {
            this.checkTextLength(newText, minLength);
        },
    }));
});
