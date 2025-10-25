/**
 * Service Worker for CheQQme Data Center
 * Provides offline caching and faster subsequent page loads
 */

const CACHE_VERSION = "cheqqme-v1";
const CACHE_NAME = `${CACHE_VERSION}-assets`;

// Assets to cache on install
const PRECACHE_ASSETS = [
    "/favicon.ico",
    "/images/favicon.png",
    "/logos/logo-light.png",
    "/logos/logo-dark.png",
    "/logos/logo-light-vertical.png",
    "/logos/logo-dark-vertical.png",
];

// Cache strategies
const CACHE_FIRST = [
    "css",
    "js",
    "woff",
    "woff2",
    "ttf",
    "eot",
    "otf",
    "png",
    "jpg",
    "jpeg",
    "gif",
    "svg",
    "webp",
    "ico",
];
const NETWORK_FIRST = ["html", "php"];

/**
 * Install event - precache critical assets
 */
self.addEventListener("install", (event) => {
    console.log("[Service Worker] Installing service worker...");

    event.waitUntil(
        caches
            .open(CACHE_NAME)
            .then((cache) => {
                console.log("[Service Worker] Precaching app shell");
                return cache.addAll(PRECACHE_ASSETS);
            })
            .then(() => self.skipWaiting())
    );
});

/**
 * Activate event - clean up old caches
 */
self.addEventListener("activate", (event) => {
    console.log("[Service Worker] Activating service worker...");

    event.waitUntil(
        caches
            .keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames
                        .filter(
                            (name) =>
                                name.startsWith("cheqqme-") &&
                                name !== CACHE_NAME
                        )
                        .map((name) => {
                            console.log(
                                "[Service Worker] Deleting old cache:",
                                name
                            );
                            return caches.delete(name);
                        })
                );
            })
            .then(() => self.clients.claim())
    );
});

/**
 * Fetch event - serve from cache when available
 */
self.addEventListener("fetch", (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== "GET") {
        return;
    }

    // Skip WebSocket connections
    if (url.protocol === "ws:" || url.protocol === "wss:") {
        return;
    }

    // Skip Livewire requests
    if (url.pathname.includes("/livewire/")) {
        return;
    }

    // Skip API requests
    if (url.pathname.startsWith("/api/")) {
        return;
    }

    // Get file extension
    const extension = url.pathname.split(".").pop().toLowerCase();

    // Apply cache-first strategy for static assets
    if (CACHE_FIRST.includes(extension)) {
        event.respondWith(cacheFirst(request));
        return;
    }

    // Apply network-first strategy for HTML/PHP
    if (NETWORK_FIRST.includes(extension) || url.pathname.endsWith("/")) {
        event.respondWith(networkFirst(request));
        return;
    }

    // Default: try network, fall back to cache
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Clone the response before caching
                const responseToCache = response.clone();

                // Cache successful responses
                if (response.status === 200) {
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(request, responseToCache);
                    });
                }

                return response;
            })
            .catch(() => {
                // Fall back to cache if network fails
                return caches.match(request);
            })
    );
});

/**
 * Cache-first strategy: Try cache, fall back to network
 */
async function cacheFirst(request) {
    const cached = await caches.match(request);
    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);

        if (response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        console.log("[Service Worker] Fetch failed for:", request.url);
        throw error;
    }
}

/**
 * Network-first strategy: Try network, fall back to cache
 */
async function networkFirst(request) {
    try {
        const response = await fetch(request);

        if (response.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, response.clone());
        }

        return response;
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        throw error;
    }
}

/**
 * Message handler for cache management
 */
self.addEventListener("message", (event) => {
    if (event.data.action === "skipWaiting") {
        self.skipWaiting();
    }

    if (event.data.action === "clearCache") {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((name) => caches.delete(name))
                );
            })
        );
    }
});

