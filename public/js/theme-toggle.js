/**
 * Theme Toggle for Login Page
 * Manages theme switching between light, dark, and system preferences
 */
class ThemeToggle {
    constructor() {
        this.elements = {
            loginLogo: document.getElementById("loginLogo"),
            themeButtons: document.querySelectorAll(".theme-toggle-btn"),
        };

        this.init();
    }

    /**
     * Update logo based on theme
     */
    updateLogos(isDark) {
        if (this.elements.loginLogo) {
            if (isDark) {
                this.elements.loginLogo.src = `${window.location.origin}/logos/logo-dark.png`;
            } else {
                this.elements.loginLogo.src = `${window.location.origin}/logos/logo-light.png`;
            }
        }
    }

    /**
     * Apply theme to document
     */
    applyTheme(theme) {
        const html = document.documentElement;
        localStorage.setItem("theme", theme);

        if (theme === "dark") {
            html.classList.add("dark");
            html.classList.remove("light");
            this.updateLogos(true);
        } else if (theme === "light") {
            html.classList.remove("dark");
            html.classList.add("light");
            this.updateLogos(false);
        } else if (theme === "system") {
            const prefersDark = window.matchMedia(
                "(prefers-color-scheme: dark)"
            ).matches;
            html.classList.toggle("dark", prefersDark);
            html.classList.remove("light");
            this.updateLogos(prefersDark);
        }
    }

    /**
     * Set active button state
     */
    setActiveButton(activeTheme) {
        this.elements.themeButtons.forEach((btn) => {
            const icon = btn.querySelector("svg");
            if (btn.dataset.theme === activeTheme) {
                btn.classList.add("active");
                icon?.classList.add("text-primary-600");
            } else {
                btn.classList.remove("active");
                icon?.classList.remove("text-primary-600");
            }
        });
    }

    /**
     * Add hover effects to theme buttons
     */
    addHoverEffects() {
        this.elements.themeButtons.forEach((btn) => {
            const icon = btn.querySelector("svg");

            // Add hover effect
            btn.addEventListener("mouseenter", () => {
                if (!btn.classList.contains("active")) {
                    icon?.classList.add("text-primary-600");
                }
            });

            // Remove hover effect
            btn.addEventListener("mouseleave", () => {
                if (!btn.classList.contains("active")) {
                    icon?.classList.remove("text-primary-600");
                }
            });
        });
    }

    /**
     * Initialize theme toggle
     */
    init() {
        const storedTheme = localStorage.getItem("theme");
        const currentTheme = storedTheme || "system";

        // Apply initial theme
        this.applyTheme(currentTheme);

        // Set initial active button
        this.setActiveButton(currentTheme);

        // Add click handlers
        this.elements.themeButtons.forEach((btn) => {
            btn.addEventListener("click", () => {
                const theme = btn.dataset.theme;
                this.applyTheme(theme);
                this.setActiveButton(theme);
            });
        });

        // Add hover effects
        this.addHoverEffects();

        // Listen for system theme changes
        window
            .matchMedia("(prefers-color-scheme: dark)")
            .addEventListener("change", (e) => {
                if (localStorage.getItem("theme") === "system") {
                    this.applyTheme("system");
                }
            });
    }
}

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
    new ThemeToggle();
});
