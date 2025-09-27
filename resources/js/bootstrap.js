import axios from "axios";
import Echo from "laravel-echo";
import Pusher from "pusher-js";

window.axios = axios;
window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";

window.Pusher = Pusher;

// Get Reverb configuration from Laravel config or fallback to defaults
const getReverbConfig = () => {
    // Try to get from window object first (set by Laravel)
    if (window.reverbConfig && window.reverbConfig.key) {
        return window.reverbConfig;
    }

    // Try to get from environment variables
    if (import.meta.env.VITE_REVERB_APP_KEY) {
        return {
            key: import.meta.env.VITE_REVERB_APP_KEY,
            host: import.meta.env.VITE_REVERB_HOST || "localhost",
            port: import.meta.env.VITE_REVERB_PORT || 8080,
            scheme: import.meta.env.VITE_REVERB_SCHEME || "http",
        };
    }

    // Fallback configuration
    return {
        key: "yv9fc1p3gektc95okepj",
        host: "localhost",
        port: 8080,
        scheme: "http",
    };
};

const reverbConfig = getReverbConfig();

// Validate configuration before initializing Echo
if (!reverbConfig.key) {
    console.error(
        "Reverb app key is missing. Please check your broadcasting configuration."
    );
} else {
    try {
        window.Echo = new Echo({
            broadcaster: "reverb",
            key: reverbConfig.key,
            wsHost: reverbConfig.host,
            wsPort: reverbConfig.port,
            wssPort: reverbConfig.port,
            forceTLS: reverbConfig.scheme === "https",
            enabledTransports: ["ws", "wss"],
        });

        console.log("Echo initialized successfully with Reverb:", {
            key: reverbConfig.key,
            host: reverbConfig.host,
            port: reverbConfig.port,
            scheme: reverbConfig.scheme,
        });
    } catch (error) {
        console.error("Failed to initialize Echo:", error);
    }
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import "./echo";
