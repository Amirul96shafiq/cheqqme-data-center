/**
 * Real-time Online Status Management using Laravel Presence Channels
 *
 * This replaces the polling-based system with real-time presence detection
 */

class PresenceStatusManager {
    constructor() {
        this.echo = null;
        this.presenceChannel = null;
        this.onlineUsers = new Map();
        this.currentUser = null;
        this.isInitialized = false;

        // Bind methods
        this.handleUserJoined = this.handleUserJoined.bind(this);
        this.handleUserLeft = this.handleUserLeft.bind(this);
        this.handleStatusChanged = this.handleStatusChanged.bind(this);
        this.updateUserStatus = this.updateUserStatus.bind(this);
    }

    /**
     * Initialize the presence status manager
     */
    async init() {
        if (this.isInitialized) {
            return;
        }

        try {
            // Check if Echo is available
            if (typeof window.Echo === "undefined") {
                console.warn(
                    "Laravel Echo not found. Please include Echo in your application."
                );
                return;
            }

            this.echo = window.Echo;

            // Get current user from available sources
            this.currentUser = window.currentUser || {
                id: window.chatbotUserId || null,
                name: window.chatbotUserName || "User",
            };

            if (!this.currentUser || !this.currentUser.id) {
                console.warn(
                    "Current user not found. Cannot initialize presence status."
                );
                return;
            }

            // Join the presence channel
            this.presenceChannel = this.echo
                .join("online-users")
                .here((users) => {
                    console.log("Users currently online:", users);
                    this.handleUsersHere(users);
                })
                .joining((user) => {
                    console.log("User joined:", user);
                    this.handleUserJoined(user);
                })
                .leaving((user) => {
                    console.log("User left:", user);
                    this.handleUserLeft(user);
                })
                .listen("user.status.changed", (e) => {
                    console.log("User status changed:", e);
                    this.handleStatusChanged(e);
                })
                .error((error) => {
                    console.error("Presence channel error:", error);
                });

            this.isInitialized = true;
            console.log("Presence Status Manager initialized successfully");
        } catch (error) {
            console.error(
                "Failed to initialize Presence Status Manager:",
                error
            );
        }
    }

    /**
     * Handle users currently online when joining the channel
     */
    handleUsersHere(users) {
        this.onlineUsers.clear();
        users.forEach((user) => {
            this.onlineUsers.set(user.id, user);
        });
        this.updateUI();
    }

    /**
     * Handle user joining the channel
     */
    handleUserJoined(user) {
        this.onlineUsers.set(user.id, user);
        this.updateUI();
        this.showNotification(`${user.name} is now online`, "success");
    }

    /**
     * Handle user leaving the channel
     */
    handleUserLeft(user) {
        this.onlineUsers.delete(user.id);
        this.updateUI();
        this.showNotification(`${user.name} is now offline`, "info");
    }

    /**
     * Handle user status change
     */
    handleStatusChanged(event) {
        const user = event.user;
        const previousStatus = event.previous_status;

        // Update user in the map
        this.onlineUsers.set(user.id, user);

        // Update UI
        this.updateUI();

        // Show notification for status changes
        if (previousStatus && previousStatus !== user.status) {
            const statusText = this.getStatusText(user.status);
            this.showNotification(`${user.name} is now ${statusText}`, "info");
        }
    }

    /**
     * Update user status manually
     */
    async updateUserStatus(newStatus) {
        if (!this.currentUser) {
            console.error("Current user not found");
            return;
        }

        try {
            const response = await fetch("/api/user/status", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({
                    status: newStatus,
                }),
            });

            if (!response.ok) {
                throw new Error("Failed to update status");
            }

            const result = await response.json();
            console.log("Status updated successfully:", result);

            // Return a promise for compatibility with the existing dropdown
            return Promise.resolve(result);
        } catch (error) {
            console.error("Failed to update user status:", error);
            this.showNotification("Failed to update status", "error");
            return Promise.reject(error);
        }
    }

    /**
     * Update the UI to reflect current online users
     */
    updateUI() {
        // Update online users list
        this.updateOnlineUsersList();

        // Update status indicators
        this.updateStatusIndicators();

        // Update user count
        this.updateUserCount();
    }

    /**
     * Update the online users list in the UI
     */
    updateOnlineUsersList() {
        const container = document.getElementById("online-users-list");
        if (!container) return;

        container.innerHTML = "";

        this.onlineUsers.forEach((user) => {
            const userElement = this.createUserElement(user);
            container.appendChild(userElement);
        });
    }

    /**
     * Create a user element for the online users list
     */
    createUserElement(user) {
        const div = document.createElement("div");
        div.className =
            "flex items-center space-x-3 p-2 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg";
        div.innerHTML = `
            <div class="relative">
                <img src="${user.avatar || "/images/default-avatar.png"}" 
                     alt="${user.name}" 
                     class="w-8 h-8 rounded-full">
                <div class="absolute -bottom-1 -right-1 w-3 h-3 ${this.getStatusColor(
                    user.status
                )} rounded-full border-2 border-white dark:border-gray-900"></div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    ${user.name}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    ${this.getStatusText(user.status)}
                </p>
            </div>
        `;
        return div;
    }

    /**
     * Update status indicators throughout the UI
     */
    updateStatusIndicators() {
        // Update user status indicators
        document.querySelectorAll("[data-user-id]").forEach((element) => {
            const userId = element.getAttribute("data-user-id");
            const user = this.onlineUsers.get(parseInt(userId));

            if (user) {
                const statusIndicator =
                    element.querySelector(".status-indicator");
                if (statusIndicator) {
                    statusIndicator.className = `status-indicator w-2 h-2 ${this.getStatusColor(
                        user.status
                    )} rounded-full`;
                }
            }
        });
    }

    /**
     * Update user count display
     */
    updateUserCount() {
        const countElement = document.getElementById("online-users-count");
        if (countElement) {
            countElement.textContent = this.onlineUsers.size;
        }
    }

    /**
     * Get status color class
     */
    getStatusColor(status) {
        const colors = {
            online: "bg-green-500",
            away: "bg-yellow-500",
            dnd: "bg-red-500",
            invisible: "bg-gray-400",
        };
        return colors[status] || "bg-gray-400";
    }

    /**
     * Get status text
     */
    getStatusText(status) {
        const texts = {
            online: "Online",
            away: "Away",
            dnd: "Do Not Disturb",
            invisible: "Invisible",
        };
        return texts[status] || "Unknown";
    }

    /**
     * Show notification
     */
    showNotification(message, type = "info") {
        // You can integrate with your existing notification system
        console.log(`[${type.toUpperCase()}] ${message}`);

        // Example: Show toast notification
        if (window.showToast) {
            window.showToast(message, type);
        }
    }

    /**
     * Get online users
     */
    getOnlineUsers() {
        return Array.from(this.onlineUsers.values());
    }

    /**
     * Check if user is online
     */
    isUserOnline(userId) {
        return this.onlineUsers.has(userId);
    }

    /**
     * Get user status
     */
    getUserStatus(userId) {
        const user = this.onlineUsers.get(userId);
        return user ? user.status : "offline";
    }

    /**
     * Disconnect from presence channel
     */
    disconnect() {
        if (this.presenceChannel) {
            this.echo.leave("online-users");
            this.presenceChannel = null;
        }
        this.isInitialized = false;
        console.log("Presence Status Manager disconnected");
    }
}

// Initialize the presence status manager when the page loads
document.addEventListener("DOMContentLoaded", function () {
    window.presenceStatusManager = new PresenceStatusManager();
    window.presenceStatusManager.init();
});

// Export for use in other modules
if (typeof module !== "undefined" && module.exports) {
    module.exports = PresenceStatusManager;
}
