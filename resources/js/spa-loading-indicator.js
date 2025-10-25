/**
 * SPA Loading Indicator for Livewire Navigation
 * Shows a progress bar and loading states during page transitions
 */

document.addEventListener("DOMContentLoaded", () => {
    // Create loading bar element
    const loadingBar = document.createElement("div");
    loadingBar.id = "spa-loading-bar";
    loadingBar.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #fbb43e, #e6a135);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
        z-index: 99999;
        box-shadow: 0 0 10px rgba(251, 180, 62, 0.5);
    `;
    document.body.appendChild(loadingBar);

    let progressInterval;
    let currentProgress = 0;

    /**
     * Start loading animation
     */
    function startLoading() {
        currentProgress = 0;
        loadingBar.style.transform = "scaleX(0)";
        loadingBar.style.transition = "none";

        // Animate to 90% over 2 seconds
        setTimeout(() => {
            loadingBar.style.transition = "transform 0.3s ease";
            animateProgress();
        }, 10);
    }

    /**
     * Animate progress bar
     */
    function animateProgress() {
        if (currentProgress < 90) {
            // Fast initial progress, slower as it approaches 90%
            const increment = (90 - currentProgress) * 0.1;
            currentProgress = Math.min(currentProgress + increment, 90);
            loadingBar.style.transform = `scaleX(${currentProgress / 100})`;

            progressInterval = setTimeout(animateProgress, 100);
        }
    }

    /**
     * Complete loading animation
     */
    function completeLoading() {
        clearTimeout(progressInterval);
        currentProgress = 100;
        loadingBar.style.transform = "scaleX(1)";

        // Hide after completion
        setTimeout(() => {
            loadingBar.style.transition = "opacity 0.3s ease";
            loadingBar.style.opacity = "0";

            setTimeout(() => {
                loadingBar.style.transform = "scaleX(0)";
                loadingBar.style.opacity = "1";
                loadingBar.style.transition = "transform 0.3s ease";
            }, 300);
        }, 100);
    }

    // Listen for Livewire navigation events
    document.addEventListener("livewire:navigating", startLoading);
    document.addEventListener("livewire:navigated", completeLoading);

    // Also show during Livewire requests
    let requestCount = 0;

    document.addEventListener("livewire:request", () => {
        requestCount++;
        if (requestCount === 1) {
            startLoading();
        }
    });

    document.addEventListener("livewire:finish", () => {
        requestCount = Math.max(0, requestCount - 1);
        if (requestCount === 0) {
            completeLoading();
        }
    });
});

/**
 * Add skeleton loading states to Filament tables
 */
document.addEventListener("livewire:init", () => {
    Livewire.hook("request", ({ component, succeed, fail }) => {
        // Add loading class to component (check if component and el exist)
        const element = component?.el;
        if (element) {
            element.classList.add("livewire-loading");
        }

        succeed(() => {
            if (element) {
                element.classList.remove("livewire-loading");
            }
        });

        fail(() => {
            if (element) {
                element.classList.remove("livewire-loading");
            }
        });
    });
});

/**
 * Add CSS for loading states
 */
const loadingStyles = document.createElement("style");
loadingStyles.textContent = `
    /* Loading state for Livewire components */
    .livewire-loading {
        opacity: 0.6;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }

    /* Loading spinner for specific elements */
    [wire\\:loading] {
        display: none;
    }

    .wire-loading-visible [wire\\:loading] {
        display: block;
    }

    /* Skeleton loading animation */
    @keyframes skeleton-loading {
        0% {
            background-position: -200px 0;
        }
        100% {
            background-position: calc(200px + 100%) 0;
        }
    }

    .skeleton-loading {
        background: linear-gradient(90deg, 
            rgba(255, 255, 255, 0) 0%, 
            rgba(255, 255, 255, 0.2) 20%, 
            rgba(255, 255, 255, 0.5) 60%, 
            rgba(255, 255, 255, 0)
        );
        background-size: 200px 100%;
        animation: skeleton-loading 1.5s ease-in-out infinite;
    }

    /* Dark mode skeleton */
    .dark .skeleton-loading {
        background: linear-gradient(90deg, 
            rgba(0, 0, 0, 0) 0%, 
            rgba(255, 255, 255, 0.05) 20%, 
            rgba(255, 255, 255, 0.1) 60%, 
            rgba(0, 0, 0, 0)
        );
    }

    /* Pulse animation for loading indicators */
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
    }

    .pulse-loading {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
`;
document.head.appendChild(loadingStyles);
