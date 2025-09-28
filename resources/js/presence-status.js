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
            console.log("Presence Status Manager already initialized");
            return true;
        }

        try {
            // Check if Echo is available
            if (typeof window.Echo === "undefined") {
                console.warn(
                    "Laravel Echo not found. Please include Echo in your application."
                );
                return false;
            }

            this.echo = window.Echo;

            // Get current user from available sources
            this.currentUser = this.getCurrentUser();

            if (!this.currentUser || !this.currentUser.id) {
                console.warn(
                    "Current user not found. Cannot initialize presence status."
                );
                return false;
            }

            // Wait a bit for Echo to be fully ready
            await new Promise((resolve) => setTimeout(resolve, 100));

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
                    this.handleChannelError(error);
                });

            // Set up heartbeat to maintain connection
            this.setupHeartbeat();

            // Set up visibility change handler for auto-status updates
            this.setupVisibilityHandlers();

            this.isInitialized = true;
            console.log("Presence Status Manager initialized successfully");
            return true;
        } catch (error) {
            console.error(
                "Failed to initialize Presence Status Manager:",
                error
            );
            this.handleInitializationError(error);
            return false;
        }
    }

    /**
     * Get current user from available sources
     */
    getCurrentUser() {
        return (
            window.currentUser || {
                id: window.chatbotUserId || null,
                name: window.chatbotUserName || "User",
            }
        );
    }

    /**
     * Handle channel errors
     */
    handleChannelError(error) {
        console.error("Presence channel error:", error);
        // Could implement reconnection logic here
    }

    /**
     * Handle initialization errors
     */
    handleInitializationError(error) {
        console.error("Initialization error:", error);
        // Could implement fallback logic here
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
        // Notification removed - no longer showing when users come online
    }

    /**
     * Handle user leaving the channel
     */
    handleUserLeft(user) {
        this.onlineUsers.delete(user.id);
        this.updateUI();
        // Notification removed - no longer showing when users go offline
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

        // Update all status indicators for this specific user
        this.updateUserStatusIndicators(user.id, user.status);

        // Notification removed - no longer showing when users change status
    }

    /**
     * Update status indicators for a specific user
     */
    updateUserStatusIndicators(userId, status) {
        // Find all indicators for this user
        document
            .querySelectorAll(`[data-user-id="${userId}"]`)
            .forEach((element) => {
                // Update the status indicator element
                this.updateStatusIndicatorElement(element, status);

                // Update tooltip if present
                const tooltipElement =
                    element.querySelector("[data-tooltip-text]") || element;
                if (tooltipElement) {
                    const statusConfig = this.getStatusConfig();
                    if (statusConfig[status]) {
                        tooltipElement.setAttribute(
                            "data-tooltip-text",
                            statusConfig[status].label
                        );
                        tooltipElement.setAttribute(
                            "title",
                            statusConfig[status].label
                        );
                    }
                }
            });
    }

    /**
     * Update user status manually
     */
    async updateUserStatus(newStatus) {
        if (!this.currentUser) {
            const error = new Error("Current user not found");
            console.error(error.message);
            return Promise.reject(error);
        }

        if (!this.isValidStatus(newStatus)) {
            const error = new Error(`Invalid status: ${newStatus}`);
            console.error(error.message);
            return Promise.reject(error);
        }

        try {
            const response = await this.makeStatusUpdateRequest(newStatus);

            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(
                    errorData.message ||
                        `HTTP ${response.status}: Failed to update status`
                );
            }

            const result = await response.json();
            console.log("Status updated successfully:", result);

            // Update current user's status in the map
            this.onlineUsers.set(this.currentUser.id, {
                ...this.currentUser,
                status: newStatus,
            });

            // Update all status indicators for current user
            this.updateUserStatusIndicators(this.currentUser.id, newStatus);

            return Promise.resolve(result);
        } catch (error) {
            console.error("Failed to update user status:", error);
            // Error notification removed - errors are still logged to console
            return Promise.reject(error);
        }
    }

    /**
     * Make the status update request
     */
    async makeStatusUpdateRequest(newStatus) {
        const csrfToken = this.getCsrfToken();

        return fetch("/api/user/status", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                status: newStatus,
            }),
        });
    }

    /**
     * Get CSRF token
     */
    getCsrfToken() {
        const tokenElement = document.querySelector('meta[name="csrf-token"]');
        if (!tokenElement) {
            throw new Error("CSRF token not found");
        }
        return tokenElement.getAttribute("content");
    }

    /**
     * Validate status
     */
    isValidStatus(status) {
        const validStatuses = ["online", "away", "dnd", "invisible"];
        return validStatuses.includes(status);
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
        // Update all online status indicators on the page
        document
            .querySelectorAll(
                ".online-status-indicator, [data-status-indicator]"
            )
            .forEach((element) => {
                const userId = element.getAttribute("data-user-id");
                const user = this.onlineUsers.get(parseInt(userId));

                if (user) {
                    // Update the status indicator with new classes
                    this.updateStatusIndicatorElement(element, user.status);
                }
            });

        // Update tooltip texts
        this.updateStatusTooltips();
    }

    /**
     * Update a single status indicator element
     */
    updateStatusIndicatorElement(element, status) {
        // Get status configuration
        const statusConfig = this.getStatusConfig();

        if (statusConfig[status]) {
            // Remove old status classes
            Object.values(statusConfig).forEach((config) => {
                element.classList.remove(config.color);
            });

            // Add new status class
            element.classList.add(statusConfig[status].color);

            // Update data attribute
            element.setAttribute("data-current-status", status);
        }
    }

    /**
     * Update tooltip texts for status indicators
     */
    updateStatusTooltips() {
        const statusConfig = this.getStatusConfig();

        document.querySelectorAll("[data-tooltip-text]").forEach((element) => {
            const userId = element.getAttribute("data-user-id");
            const user = this.onlineUsers.get(parseInt(userId));

            if (user && statusConfig[user.status]) {
                const newTooltipText = statusConfig[user.status].label;
                element.setAttribute("data-tooltip-text", newTooltipText);
                element.setAttribute("title", newTooltipText);

                // Update text content if it's a text element
                if (element.textContent) {
                    element.textContent = newTooltipText;
                }
            }
        });
    }

    /**
     * Get status configuration from backend
     */
    getStatusConfig() {
        // Try to get from global window object first (set by backend)
        if (window.statusConfig) {
            return window.statusConfig;
        }

        // Fallback configuration
        return {
            online: {
                label: "Online",
                color: "bg-teal-500",
                icon: "heroicon-o-check-circle",
            },
            away: {
                label: "Away",
                color: "bg-primary-500",
                icon: "heroicon-o-clock",
            },
            dnd: {
                label: "Do Not Disturb",
                color: "bg-red-500",
                icon: "heroicon-o-x-circle",
            },
            invisible: {
                label: "Invisible",
                color: "bg-gray-400",
                icon: "heroicon-o-eye-slash",
            },
        };
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
        console.log(`[${type.toUpperCase()}] ${message}`);

        // Try different notification systems
        if (window.showNotification) {
            window.showNotification(type, message);
        } else if (window.showToast) {
            window.showToast(message, type);
        } else if (window.$wire && window.$wire.$dispatch) {
            window.$wire.$dispatch("notify", {
                type: type,
                message: message,
            });
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
     * Set up heartbeat to maintain connection
     */
    setupHeartbeat() {
        // Send heartbeat every 30 seconds
        this.heartbeatInterval = setInterval(() => {
            if (this.presenceChannel && this.isInitialized) {
                // Echo handles heartbeat automatically, but we can add custom logic here
                this.sendActivity();
            }
        }, 30000);
    }

    /**
     * Set up visibility change handlers for auto-status updates
     */
    setupVisibilityHandlers() {
        document.addEventListener("visibilitychange", () => {
            if (document.hidden) {
                // Tab is hidden, could set to away
                this.handleTabHidden();
            } else {
                // Tab is visible again, set back to online
                this.handleTabVisible();
            }
        });

        // Handle page unload
        window.addEventListener("beforeunload", () => {
            this.handlePageUnload();
        });
    }

    /**
     * Send activity signal to maintain online status
     */
    sendActivity() {
        // This could trigger a backend endpoint to update last_activity
        if (this.currentUser) {
            fetch("/api/user/activity", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": this.getCsrfToken(),
                },
                body: JSON.stringify({
                    timestamp: new Date().toISOString(),
                }),
            }).catch((error) => {
                console.warn("Failed to send activity signal:", error);
            });
        }
    }

    /**
     * Handle tab becoming hidden
     */
    handleTabHidden() {
        console.log(
            "Tab hidden - maintaining online status for proper away time counting"
        );
        // Do NOT change status when tab is hidden - let the 5-minute away timer handle it
        // This allows proper away time counting instead of immediately going invisible
    }

    /**
     * Handle tab becoming visible
     */
    handleTabVisible() {
        console.log(
            "Tab visible - checking if user should be restored from auto-status"
        );
        // Set status back to online when tab becomes visible (only if user was auto-away)
        if (
            this.currentUser &&
            this.getUserStatus(this.currentUser.id) === "away"
        ) {
            this.updateUserStatus("online").catch(console.error);
        }
    }

    /**
     * Handle page unload
     */
    handlePageUnload() {
        console.log("Page unloading - disconnecting from presence channel");
        // Clean up heartbeat
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }

        // Disconnect from presence channel
        this.disconnect();
    }

    /**
     * Disconnect from presence channel
     */
    disconnect() {
        if (this.presenceChannel) {
            this.echo.leave("online-users");
            this.presenceChannel = null;
        }

        // Clean up heartbeat
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
            this.heartbeatInterval = null;
        }

        this.isInitialized = false;
        console.log("Presence Status Manager disconnected");
    }
}

// Initialize the presence status manager when Echo is ready
function initializePresenceStatusManager() {
    if (!window.presenceStatusManager) {
        window.presenceStatusManager = new PresenceStatusManager();
        window.presenceStatusManager.init().catch((error) => {
            console.error(
                "Failed to initialize presence status manager:",
                error
            );
        });
    }
}

// Wait for Echo to be loaded
if (window.Echo) {
    // Echo is already available
    initializePresenceStatusManager();
} else {
    // Wait for Echo to be loaded
    window.addEventListener("EchoLoaded", initializePresenceStatusManager);

    // Fallback: try to initialize after a delay
    setTimeout(() => {
        if (!window.presenceStatusManager && window.Echo) {
            initializePresenceStatusManager();
        }
    }, 1000);
}

// Export for use in other modules
if (typeof module !== "undefined" && module.exports) {
    module.exports = PresenceStatusManager;
}
