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

    // Fallback configuration
    return {
        key: "cheqqme-key",
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
