/**
 * Remember Me Toggle for Login Page
 * Manages the custom toggle switch for the remember me checkbox
 */
class RememberMeToggle {
    constructor() {
        this.elements = {
            checkbox: document.getElementById("remember"),
            toggleTrack: null,
            toggleThumb: null,
        };

        this.init();
    }

    /**
     * Update toggle visual state
     */
    updateToggle() {
        if (
            !this.elements.checkbox ||
            !this.elements.toggleTrack ||
            !this.elements.toggleThumb
        ) {
            return;
        }

        if (this.elements.checkbox.checked) {
            this.elements.toggleTrack.classList.remove(
                "bg-gray-200",
                "dark:bg-gray-600"
            );
            this.elements.toggleTrack.classList.add("bg-primary-600");
            this.elements.toggleThumb.classList.remove("translate-x-0");
            this.elements.toggleThumb.classList.add("translate-x-5");
        } else {
            this.elements.toggleTrack.classList.remove("bg-primary-600");
            this.elements.toggleTrack.classList.add(
                "bg-gray-200",
                "dark:bg-gray-600"
            );
            this.elements.toggleThumb.classList.remove("translate-x-5");
            this.elements.toggleThumb.classList.add("translate-x-0");
        }
    }

    /**
     * Initialize the toggle
     */
    init() {
        if (!this.elements.checkbox) {
            return;
        }

        // Get toggle elements
        this.elements.toggleTrack = this.elements.checkbox.nextElementSibling;
        this.elements.toggleThumb =
            this.elements.toggleTrack?.nextElementSibling;

        if (!this.elements.toggleTrack || !this.elements.toggleThumb) {
            return;
        }

        // Initialize toggle state
        this.updateToggle();

        // Handle toggle click
        this.elements.checkbox.addEventListener("change", () => {
            this.updateToggle();
        });
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    new RememberMeToggle();
});
