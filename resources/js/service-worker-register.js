/**
 * Service Worker Registration
 * Register and manage the service worker for offline caching
 */

// Service worker temporarily disabled - re-enable after testing SPA mode
// Only register service worker in production
// if ("serviceWorker" in navigator && import.meta.env.PROD) {
//     window.addEventListener("load", () => {
//         registerServiceWorker();
//     });
// }

async function registerServiceWorker() {
    try {
        const registration = await navigator.serviceWorker.register(
            "/service-worker.js",
            {
                scope: "/",
            }
        );

        console.log(
            "[App] Service Worker registered successfully:",
            registration.scope
        );

        // Check for updates periodically
        setInterval(() => {
            registration.update();
        }, 60 * 60 * 1000); // Check every hour

        // Handle updates
        registration.addEventListener("updatefound", () => {
            const newWorker = registration.installing;

            newWorker.addEventListener("statechange", () => {
                if (
                    newWorker.state === "installed" &&
                    navigator.serviceWorker.controller
                ) {
                    // New service worker available
                    console.log(
                        "[App] New service worker available. Refresh to update."
                    );

                    // Optionally show a notification to the user
                    showUpdateNotification(newWorker);
                }
            });
        });
    } catch (error) {
        console.error("[App] Service Worker registration failed:", error);
    }
}

/**
 * Show update notification to user
 */
function showUpdateNotification(worker) {
    // Check if Filament notifications are available
    if (typeof window.Filament !== "undefined") {
        // Use Filament notification system
        new FilamentNotification()
            .title("Update Available")
            .body("A new version is available. Click to refresh.")
            .info()
            .duration(0) // Persistent
            .actions([
                new FilamentNotificationAction("refresh")
                    .label("Refresh Now")
                    .button()
                    .close()
                    .color("primary"),
            ])
            .send();

        // Listen for refresh action
        document.addEventListener("click", (e) => {
            if (e.target.closest('[data-action="refresh"]')) {
                worker.postMessage({ action: "skipWaiting" });
                window.location.reload();
            }
        });
    } else {
        // Fallback to browser confirm dialog
        if (confirm("A new version is available. Refresh now?")) {
            worker.postMessage({ action: "skipWaiting" });
            window.location.reload();
        }
    }
}

/**
 * Unregister service worker (for development/testing)
 */
window.unregisterServiceWorker = async function () {
    if ("serviceWorker" in navigator) {
        const registrations = await navigator.serviceWorker.getRegistrations();
        for (const registration of registrations) {
            await registration.unregister();
        }
        console.log("[App] Service Worker unregistered");
    }
};

/**
 * Clear service worker cache (for development/testing)
 */
window.clearServiceWorkerCache = async function () {
    if ("caches" in window) {
        const cacheNames = await caches.keys();
        await Promise.all(cacheNames.map((name) => caches.delete(name)));
        console.log("[App] Service Worker cache cleared");
    }
};
