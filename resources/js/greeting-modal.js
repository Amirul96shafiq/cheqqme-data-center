// Greeting Modal Functionality
// Handles modal opening, closing, HTML generation, and user interactions

// Cache for frequently accessed DOM elements
const modalCache = {
    modalOverlay: null,
    weatherSection: null,
    statusIndicators: null,
};

// Make modalCache globally available for other modules
window.modalCache = modalCache;

// Refresh DOM cache when modal opens
function refreshModalCache() {
    modalCache.weatherSection = document.querySelector(".weather-section");
    modalCache.statusIndicators = document.querySelectorAll(
        ".online-status-indicator"
    );
}

// Status config - will be initialized from Blade template
let STATUS_CONFIG = null;
let STATUS_COLORS = null;

// Initialize status config from global data
function initializeStatusConfig() {
    if (window.greetingStatusConfig) {
        STATUS_CONFIG = window.greetingStatusConfig.config;
        STATUS_COLORS = window.greetingStatusConfig.colors;
    }
}

// Initialize status config on module load
initializeStatusConfig();

// Setup greeting background image based on dark/light mode
function setupGreetingBackgroundImage() {
    const greetingSection = document.querySelector("[data-bg-light]");
    if (!greetingSection) return;

    // Function to update background image based on theme
    function updateBackgroundImage() {
        // Simple check: if dark class is present, use dark image, otherwise use light image
        const isDarkMode =
            document.documentElement.classList.contains("dark") ||
            document.body.classList.contains("dark");

        const bgImage = isDarkMode
            ? greetingSection.getAttribute("data-bg-dark")
            : greetingSection.getAttribute("data-bg-light");

        greetingSection.style.backgroundImage = `url('${bgImage}')`;
    }

    // Set initial background image
    updateBackgroundImage();

    // Listen for theme changes
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            if (
                mutation.type === "attributes" &&
                (mutation.attributeName === "class" ||
                    mutation.attributeName === "data-theme")
            ) {
                updateBackgroundImage();
            }
        });
    });

    // Observe document element and body for theme changes
    observer.observe(document.documentElement, { attributes: true });
    observer.observe(document.body, { attributes: true });

    // Listen for system theme changes
    window
        .matchMedia("(prefers-color-scheme: dark)")
        .addEventListener("change", updateBackgroundImage);
}

// Open greeting modal
function openGreetingModal(forceOpen = false) {
    // Check if user has enabled 'no show greeting today' for today
    // Skip this check if forceOpen is true (manual click)
    if (!forceOpen) {
        const today = new Date().toDateString();
        const lastDismissed = localStorage.getItem("greetingModalDismissed");

        if (lastDismissed === today) {
            return; // Don't show modal if dismissed today
        }
    }

    // Create modal overlay
    const modal = document.createElement("div");
    modal.id = "greeting-modal-overlay";
    modal.className =
        "fixed inset-0 z-[9999] flex items-center justify-center p-4";
    modal.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
    modal.style.backdropFilter = "blur(12px)";

    // Set modal HTML from pre-generated template
    modal.innerHTML = window.greetingModalHTML;

    // Add to body
    document.body.appendChild(modal);

    // Refresh cache for new elements
    refreshModalCache();

    // Animate in
    setTimeout(() => {
        const modalContent = modal.querySelector(".bg-white");
        if (modalContent) {
            modalContent.style.transform = "scale(1)";
            modalContent.style.opacity = "1";
        }

        // Set up dark mode background image switching after modal is rendered
        setTimeout(() => {
            setupGreetingBackgroundImage();
        }, 50);

        // Check user location and fetch weather data
        setTimeout(() => {
            checkUserLocationAndFetchWeather();
        }, 200);
    }, 10);

    // Prevent body scroll
    document.body.style.overflow = "hidden";

    // Close on backdrop click
    modal.addEventListener("click", function (e) {
        if (e.target === modal) {
            closeGreetingModal();
        }
    });

    // Close on escape key
    const handleEscape = function (e) {
        if (e.key === "Escape") {
            closeGreetingModal();
        }
    };
    document.addEventListener("keydown", handleEscape);

    // Store the escape handler for cleanup
    modal.escapeHandler = handleEscape;

    // Make modal globally accessible
    modalCache.modalOverlay = modal;
}

// Close greeting modal
function closeGreetingModal() {
    const modal =
        modalCache.modalOverlay ||
        document.getElementById("greeting-modal-overlay");
    if (modal) {
        // Check if "no show greeting today" is checked
        const noShowCheckbox = modal.querySelector("#noShowGreetingToday");
        if (noShowCheckbox && noShowCheckbox.checked) {
            // Store dismissal for today
            const today = new Date().toDateString();
            localStorage.setItem("greetingModalDismissed", today);
        }

        // Animate out
        const modalContent = modal.querySelector(".bg-white");
        modalContent.style.transform = "scale(0.95)";
        modalContent.style.opacity = "0";

        // Remove after animation
        setTimeout(() => {
            // Remove escape key listener
            if (modal.escapeHandler) {
                document.removeEventListener("keydown", modal.escapeHandler);
            }

            // Restore body scroll
            document.body.style.overflow = "";

            // Remove modal
            modal.remove();

            // Clear cache
            modalCache.modalOverlay = null;
        }, 300);
    }
}

// Navigation functions for quick actions
function navigateToProfile() {
    closeGreetingModal();
    window.location.href = "/admin/profile";
}

function navigateToSettings() {
    closeGreetingModal();
    window.location.href = "/admin/settings";
}

function navigateToActionBoard() {
    closeGreetingModal();
    window.location.href = "/admin/action-board";
}

function navigateToMeetingLinks() {
    closeGreetingModal();
    window.location.href = "/admin/meeting-links";
}

function navigateToUsers() {
    closeGreetingModal();
    window.location.href = "/admin/users";
}

// Scroll to weather section function
function scrollToWeatherSection() {
    const weatherSection =
        modalCache.weatherSection || document.querySelector(".weather-section");
    if (weatherSection) {
        weatherSection.scrollIntoView({
            behavior: "smooth",
            block: "start",
        });
    }
}

// Video source URL constant
const TUTORIAL_VIDEO_SRC = "/videos/resources_tutorial_video01.mp4";

// Helper function to load video source dynamically
function loadVideoSource(video) {
    if (!video.querySelector("source")) {
        const source = document.createElement("source");
        source.src = TUTORIAL_VIDEO_SRC;
        source.type = "video/mp4";
        video.appendChild(source);
        video.load();
    }
}

// Helper function to play video safely
function playVideoSafely(video) {
    if (video.readyState >= 2) {
        video.currentTime = 0;
        video.play().catch((error) => {
            console.error("Video play failed:", error);
        });
    } else {
        video.addEventListener(
            "canplay",
            () => {
                video.currentTime = 0;
                video.play().catch((error) => {
                    console.error("Video play failed:", error);
                });
            },
            { once: true }
        );

        video.addEventListener(
            "error",
            (e) => {
                console.error("Video error:", e.target.error);
            },
            { once: true }
        );
    }
}

// Toggle Resources video container
function toggleDataManagementVideo() {
    const videoContainer = document.getElementById("data-management-video");
    const quickActionsContainer =
        videoContainer?.parentElement?.querySelector(".space-y-3");

    if (videoContainer && quickActionsContainer) {
        const isHidden = videoContainer.classList.contains("hidden");

        if (isHidden) {
            // Show video container with animation
            videoContainer.classList.remove("hidden");
            // Force reflow to ensure the element is visible before animation
            videoContainer.offsetHeight;
            // Add animation classes
            videoContainer.classList.remove("opacity-0", "scale-95");
            videoContainer.classList.add("opacity-100", "scale-100");

            // Update Alpine.js state for icon rotation - find the specific action button
            const iconContainer = quickActionsContainer.querySelector(
                'button[onclick*="toggleDataManagementVideo"] [x-data*="isVideoActive"]'
            );

            if (iconContainer && iconContainer._x_dataStack) {
                iconContainer._x_dataStack[0].isVideoActive = true;
            }

            // Hide other quick actions
            const otherActions = quickActionsContainer.querySelectorAll(
                'button:not([onclick="toggleDataManagementVideo()"])'
            );
            otherActions.forEach((action) => {
                action.style.display = "none";
            });

            // Reset video to beginning when showing
            setTimeout(() => {
                const video = videoContainer.querySelector("video");
                // console.log(
                //     "Video element found:",
                //     !!video,
                //     "in container:",
                //     "data-management-video"
                // );
                if (video) {
                    loadVideoSource(video);
                    playVideoSafely(video);
                } else {
                    console.error(
                        "No video element found in container: data-management-video"
                    );
                }
            }, 100);
        } else {
            // Hide video container with animation
            videoContainer.classList.remove("opacity-100", "scale-100");
            videoContainer.classList.add("opacity-0", "scale-95");

            // Update Alpine.js state for icon rotation - find the specific action button
            const iconContainer = quickActionsContainer.querySelector(
                'button[onclick*="toggleDataManagementVideo"] [x-data*="isVideoActive"]'
            );

            if (iconContainer && iconContainer._x_dataStack) {
                iconContainer._x_dataStack[0].isVideoActive = false;
            }

            // Pause video when hiding
            const video = videoContainer.querySelector("video");
            if (video) {
                video.pause();
            }

            // Hide element after animation completes
            setTimeout(() => {
                videoContainer.classList.add("hidden");

                // Show other quick actions after video container is completely hidden
                setTimeout(() => {
                    const otherActions = quickActionsContainer.querySelectorAll(
                        'button:not([onclick="toggleDataManagementVideo()"])'
                    );
                    otherActions.forEach((action) => {
                        action.style.display = "flex";
                    });
                }, 100); // Additional delay after video container is hidden
            }, 300);
        }
    }
}

// Video Custom Controls
function toggleVideoPlay(video) {
    if (video.paused) {
        video.play();
    } else {
        video.pause();
    }
}

function playVideoInFullscreen() {
    // Find the currently visible video
    const videoIds = [
        "resource-tutorial-video",
        "profile-tutorial-video",
        "settings-tutorial-video",
        "action-board-tutorial-video",
    ];
    let video = null;

    for (const videoId of videoIds) {
        const currentVideo = document.getElementById(videoId);
        if (currentVideo && !currentVideo.closest(".hidden")) {
            video = currentVideo;
            break;
        }
    }

    if (video) {
        if (video.requestFullscreen) {
            video.requestFullscreen();
        } else if (video.webkitRequestFullscreen) {
            video.webkitRequestFullscreen();
        } else if (video.msRequestFullscreen) {
            video.msRequestFullscreen();
        }
        video.play();
    }
}

// Auto-detect clicks on greeting menu item
document.addEventListener("DOMContentLoaded", function () {
    setTimeout(function () {
        const selectors = [
            '[data-filament-dropdown-list] a[href="javascript:void(0)"]',
            '.fi-dropdown-list a[href="javascript:void(0)"]',
            '[role="menu"] a[href="javascript:void(0)"]',
        ];

        let greetingLink = null;

        for (const selector of selectors) {
            const links = document.querySelectorAll(selector);

            links.forEach(function (link) {
                const text = link.textContent.trim().toLowerCase();
                if (
                    (text.includes("good morning") ||
                        text.includes("good afternoon") ||
                        text.includes("good evening") ||
                        text.includes("goodnight") ||
                        text.includes("morning") ||
                        text.includes("afternoon") ||
                        text.includes("evening") ||
                        text.includes("night") ||
                        text.includes("pagi") ||
                        text.includes("petang") ||
                        text.includes("malam") ||
                        text.includes("selamat malam")) &&
                    (link.closest("[data-filament-dropdown-list]") ||
                        link.closest(".fi-dropdown-list") ||
                        link.closest('[role="menu"]'))
                ) {
                    greetingLink = link;
                }
            });

            if (greetingLink) break;
        }

        if (greetingLink) {
            greetingLink.addEventListener("click", function (e) {
                e.preventDefault();
                e.stopPropagation();
                openGreetingModal(true); // Force open when manually clicked
            });
        }
    }, 2000);

    // Auto-open greeting modal on dashboard
    setTimeout(function () {
        const currentPath = window.location.pathname;
        if (currentPath === "/admin" || currentPath === "/admin/") {
            openGreetingModal();
        }
    }, 2000);
});

// Fallback: Use event delegation to catch clicks on any element
document.addEventListener("click", function (e) {
    let element = e.target;

    // Check up to 3 parent levels
    for (let i = 0; i < 3; i++) {
        if (element && element.textContent) {
            const text = element.textContent.trim().toLowerCase();
            if (
                (text.includes("good morning") ||
                    text.includes("good afternoon") ||
                    text.includes("good evening") ||
                    text.includes("goodnight") ||
                    text.includes("morning") ||
                    text.includes("afternoon") ||
                    text.includes("evening") ||
                    text.includes("night") ||
                    text.includes("pagi") ||
                    text.includes("petang") ||
                    text.includes("malam") ||
                    text.includes("selamat malam")) &&
                (element.closest("[data-filament-dropdown-list]") ||
                    element.closest(".fi-dropdown-list") ||
                    element.closest('[role="menu"]'))
            ) {
                e.preventDefault();
                e.stopPropagation();
                openGreetingModal(true); // Force open when manually clicked
                break;
            }
        }
        element = element?.parentElement;
    }
});

// Optimized status update functions with cached queries and batched DOM updates

// Batch DOM updates using requestAnimationFrame
function batchStatusUpdates(updates) {
    requestAnimationFrame(() => {
        updates.forEach((update) => update());
    });
}

// Optimized function to update all status indicators on the page
window.updateAllStatusIndicators = function (
    newStatus,
    currentUserOnly = false
) {
    // Get cached elements or query fresh
    const indicators =
        modalCache.statusIndicators ||
        document.querySelectorAll(".online-status-indicator");
    const tooltips = document.querySelectorAll(".tooltip[data-tooltip-text]");
    const alpineComponents = document.querySelectorAll("[x-data]");

    // Prepare updates array for batching
    const updates = [];

    // Update all status indicator buttons with cached config
    indicators.forEach((indicator) => {
        // Skip non-current user indicators if currentUserOnly is true
        if (currentUserOnly) {
            const isCurrentUser =
                indicator.getAttribute("data-is-current-user") === "true";
            if (!isCurrentUser) return;
        }

        updates.push(() => {
            // Remove all possible status classes using cached color array
            STATUS_COLORS.forEach((colorClass) => {
                indicator.classList.remove(colorClass);
            });

            // Add new status class using cached config
            if (STATUS_CONFIG[newStatus]) {
                indicator.classList.add(STATUS_CONFIG[newStatus].color);
            }
        });
    });

    // Update all tooltip texts
    tooltips.forEach((tooltip) => {
        // Skip non-current user tooltips if currentUserOnly is true
        if (currentUserOnly) {
            const indicator =
                tooltip
                    .closest(".tooltip-container")
                    ?.querySelector(
                        ".online-status-indicator, [data-status-indicator]"
                    ) ||
                tooltip.parentElement?.querySelector(
                    ".online-status-indicator, [data-status-indicator]"
                ) ||
                document.querySelector(
                    `[data-tooltip-text="${tooltip.getAttribute(
                        "data-tooltip-text"
                    )}"]`
                );

            if (indicator) {
                const isCurrentUser =
                    indicator.getAttribute("data-is-current-user") === "true";
                if (!isCurrentUser) return;
            } else {
                return;
            }
        }

        updates.push(() => {
            if (STATUS_CONFIG[newStatus]) {
                tooltip.setAttribute(
                    "data-tooltip-text",
                    STATUS_CONFIG[newStatus].label
                );
                tooltip.textContent = STATUS_CONFIG[newStatus].label;
            }
        });
    });

    // Update interactive dropdown selection states
    alpineComponents.forEach((component) => {
        const statusButtons = component.querySelectorAll(
            'button[class*="space-x-3"][class*="text-left"]'
        );
        if (statusButtons.length > 0) {
            updates.push(() => {
                // Remove current selection styling from all buttons
                statusButtons.forEach((button) => {
                    button.classList.remove(
                        "bg-primary-50",
                        "dark:bg-primary-900/10"
                    );
                    const currentIndicator = button.querySelector(
                        ".w-2.h-2.rounded-full.bg-primary-500"
                    );
                    if (currentIndicator) {
                        currentIndicator.remove();
                    }
                });

                // Add selection styling to the new status button
                statusButtons.forEach((button) => {
                    const statusText = button.textContent.trim();
                    const statusKey = Object.keys(STATUS_CONFIG).find(
                        (key) => STATUS_CONFIG[key].label === statusText
                    );

                    if (statusKey === newStatus) {
                        button.classList.add(
                            "bg-primary-50",
                            "dark:bg-primary-900/10"
                        );

                        // Add current status indicator dot
                        const indicator = document.createElement("div");
                        indicator.className =
                            "w-2 h-2 rounded-full bg-primary-500 flex-shrink-0";
                        button.appendChild(indicator);
                    }
                });
            });
        }
    });

    // Update tooltip text in interactive components
    alpineComponents.forEach((component) => {
        const tooltip = component.querySelector(".tooltip[data-tooltip-text]");
        if (tooltip && STATUS_CONFIG[newStatus]) {
            // Skip non-current user tooltips if currentUserOnly is true
            if (currentUserOnly) {
                const indicator = component.querySelector(
                    ".online-status-indicator, [data-status-indicator]"
                );
                if (indicator) {
                    const isCurrentUser =
                        indicator.getAttribute("data-is-current-user") ===
                        "true";
                    if (!isCurrentUser) return;
                } else {
                    return;
                }
            }

            updates.push(() => {
                tooltip.setAttribute(
                    "data-tooltip-text",
                    STATUS_CONFIG[newStatus].label
                );
                tooltip.textContent = STATUS_CONFIG[newStatus].label;
                tooltip.setAttribute("title", STATUS_CONFIG[newStatus].label);
            });
        }
    });

    // Update only the current user's status indicator
    indicators.forEach((indicator) => {
        const isCurrentUser =
            indicator.getAttribute("data-is-current-user") === "true";

        if (isCurrentUser) {
            const currentStatus = indicator.getAttribute("data-current-status");

            if (currentStatus !== newStatus) {
                updates.push(() => {
                    // Update the data attribute
                    indicator.setAttribute("data-current-status", newStatus);

                    // Update the CSS classes using cached config
                    const sizeClasses =
                        indicator.className.match(/w-\d+ h-\d+/);
                    const baseClasses = sizeClasses
                        ? sizeClasses[0]
                        : "w-4 h-4";
                    const borderClasses =
                        "border-2 border-white dark:border-gray-900";
                    const roundedClasses = "rounded-full";

                    // Remove old status classes and add new ones
                    STATUS_COLORS.forEach((colorClass) => {
                        indicator.classList.remove(colorClass);
                    });
                    if (STATUS_CONFIG[newStatus]) {
                        indicator.className = `${baseClasses} ${borderClasses} ${roundedClasses} ${STATUS_CONFIG[newStatus].color} online-status-indicator`;
                    }
                });
            }
        }
    });

    // Execute all updates in a single batch
    batchStatusUpdates(updates);
};

// Function to sync all users' statuses from database
window.syncAllUserStatuses = function () {
    // Get all user IDs from cached indicators or query fresh
    const indicators =
        modalCache.statusIndicators ||
        document.querySelectorAll(".online-status-indicator");
    const userIds = Array.from(indicators)
        .map((indicator) => indicator.getAttribute("data-user-id"))
        .filter((id) => id);

    if (userIds.length === 0) return;

    // Fetch current statuses from API
    fetch("/api/user/statuses", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "",
            "X-Requested-With": "XMLHttpRequest",
            Authorization: "Bearer " + (window.chatbotApiToken || ""),
        },
        body: JSON.stringify({
            user_ids: userIds,
        }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success && data.statuses) {
                // Prepare batch updates for all status changes
                const updates = [];

                indicators.forEach((indicator) => {
                    const userId = indicator.getAttribute("data-user-id");
                    const isCurrentUser =
                        indicator.getAttribute("data-is-current-user") ===
                        "true";
                    const actualStatus = data.statuses[userId];

                    if (actualStatus) {
                        const currentStatus = indicator.getAttribute(
                            "data-current-status"
                        );

                        if (currentStatus !== actualStatus) {
                            updates.push(() => {
                                // Update the data attribute
                                indicator.setAttribute(
                                    "data-current-status",
                                    actualStatus
                                );

                                // Update the CSS classes
                                const sizeClasses =
                                    indicator.className.match(/w-\d+ h-\d+/);
                                const baseClasses = sizeClasses
                                    ? sizeClasses[0]
                                    : "w-4 h-4";
                                const borderClasses =
                                    "border-2 border-white dark:border-gray-900";
                                const roundedClasses = "rounded-full";

                                STATUS_COLORS.forEach((colorClass) => {
                                    indicator.classList.remove(colorClass);
                                });
                                if (STATUS_CONFIG[actualStatus]) {
                                    indicator.className = `${baseClasses} ${borderClasses} ${roundedClasses} ${STATUS_CONFIG[actualStatus].color} online-status-indicator`;
                                }

                                // Update tooltip text for this indicator
                                const tooltipContainer =
                                    indicator.closest(".tooltip-container");
                                if (tooltipContainer) {
                                    const tooltip =
                                        tooltipContainer.querySelector(
                                            ".tooltip[data-tooltip-text]"
                                        );
                                    if (
                                        tooltip &&
                                        STATUS_CONFIG[actualStatus]
                                    ) {
                                        tooltip.setAttribute(
                                            "data-tooltip-text",
                                            STATUS_CONFIG[actualStatus].label
                                        );
                                        tooltip.textContent =
                                            STATUS_CONFIG[actualStatus].label;
                                    }
                                }
                            });
                        }
                    }
                });

                // Execute batch updates
                batchStatusUpdates(updates);
            }
        })
        .catch((error) => {
            // Silent fail for status sync
        });
};

// Global function to update online status via AJAX with presence channels
window.updateOnlineStatus = function (status) {
    // Show loading state
    const button = event.target.closest("button");
    let originalContent = null;
    if (button) {
        originalContent = button.innerHTML;
        button.innerHTML =
            '<div class="w-4 h-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>';
        button.disabled = true;
    }

    // Use presence status manager if available, otherwise fallback to AJAX
    if (
        window.presenceStatusManager &&
        window.presenceStatusManager.isInitialized
    ) {
        // Use presence channel for real-time updates
        window.presenceStatusManager
            .updateUserStatus(status)
            .then(() => {
                // Update only the current user's status indicators
                window.updateAllStatusIndicators(status, true); // true = current user only

                // Show success notification
                if (window.showNotification) {
                    window.showNotification(
                        "success",
                        window.statusLocalization?.online_status_updated ||
                            "Online status updated successfully"
                    );
                }

                // Restore button content immediately after status update
                if (button && originalContent) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }

                // Dispatch Livewire event to update form fields
                if (window.Livewire) {
                    window.Livewire.dispatch("online-status-updated", {
                        status: status,
                    });
                }
            })
            .catch((error) => {
                console.error(
                    "Error updating status via presence channel:",
                    error
                );
                if (window.showNotification) {
                    window.showNotification(
                        "error",
                        window.statusLocalization
                            ?.online_status_update_failed ||
                            "Failed to update online status"
                    );
                }
            })
            .finally(() => {
                // Button state is restored in the success handler
                if (
                    button &&
                    originalContent &&
                    button.innerHTML.includes("animate-spin")
                ) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            });
    } else {
        // Fallback to AJAX request
        fetch("/admin/profile/update-online-status", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: JSON.stringify({
                online_status: status,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Update only the current user's status indicators
                    window.updateAllStatusIndicators(status, true); // true = current user only

                    // Show success notification
                    if (window.showNotification) {
                        window.showNotification(
                            "success",
                            window.statusLocalization?.online_status_updated ||
                                "Online status updated successfully"
                        );
                    }

                    // Restore button content immediately after status update
                    if (button && originalContent) {
                        button.innerHTML = originalContent;
                        button.disabled = false;
                    }

                    // Dispatch Livewire event to update form fields
                    if (window.Livewire) {
                        window.Livewire.dispatch("online-status-updated", {
                            status: status,
                        });
                    }
                } else {
                    // Show error notification
                    if (window.showNotification) {
                        window.showNotification(
                            "error",
                            data.message ||
                                window.statusLocalization
                                    ?.online_status_update_failed ||
                                "Failed to update online status"
                        );
                    }
                }
            })
            .catch((error) => {
                console.error("Error updating status:", error);
                if (window.showNotification) {
                    window.showNotification(
                        "error",
                        window.statusLocalization
                            ?.online_status_update_failed ||
                            "Failed to update online status"
                    );
                }
            })
            .finally(() => {
                // Button state is restored in the success handler
                if (
                    button &&
                    originalContent &&
                    button.innerHTML.includes("animate-spin")
                ) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            });
    }
};

// Test function to manually test status updates (can be called from browser console)
window.testStatusUpdate = function (status) {
    if (window.updateAllStatusIndicators) {
        window.updateAllStatusIndicators(status, true); // true = current user only
    } else {
        console.error("updateAllStatusIndicators function not found");
    }
};

// Test function to manually sync user statuses (can be called from browser console)
window.testSyncUserStatuses = function () {
    if (window.syncAllUserStatuses) {
        window.syncAllUserStatuses();
    } else {
        console.error("syncAllUserStatuses function not found");
    }
};

// Test function to debug tooltip structure (can be called from browser console)
window.debugTooltipStructure = function () {
    const indicators =
        modalCache.statusIndicators ||
        document.querySelectorAll(".online-status-indicator");

    indicators.forEach((indicator, index) => {
        const userId = indicator.getAttribute("data-user-id");
        const currentStatus = indicator.getAttribute("data-current-status");
        const tooltipContainer = indicator.closest(".tooltip-container");

        console.log(`User ${userId} (${index + 1}):`, {
            currentStatus: currentStatus,
            tooltipContainer: tooltipContainer,
            hasTooltip: tooltipContainer
                ? tooltipContainer.querySelector(".tooltip[data-tooltip-text]")
                : null,
        });
    });
};

// User Activity Tracking for Online Status
// Following Microsoft Teams behavior: any interaction resets auto-away to online
window.trackUserActivity = function () {
    if (window.userActivityTimeout) {
        clearTimeout(window.userActivityTimeout);
    }

    // Clear any pending auto-away timeout
    if (window.autoAwayTimeout) {
        clearTimeout(window.autoAwayTimeout);
        window.autoAwayTimeout = null;
    }

    // Debounce activity tracking to avoid excessive requests (increased to 2 seconds)
    window.userActivityTimeout = setTimeout(() => {
        fetch("/admin/profile/track-activity", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Update status indicators if status changed (only for current user)
                    if (data.status && window.updateAllStatusIndicators) {
                        window.updateAllStatusIndicators(data.status, true); // true = current user only
                    }

                    // Start auto-away timer if user is online
                    if (data.status === "online") {
                        window.startAutoAwayTimer();
                    }
                }
            })
            .catch((error) => {
                // Silent fail for activity tracking
            });
    }, 2000); // Increased debounce to 2 seconds to reduce server load
};

// Auto-Away Timer - Set to 10 seconds (for testing) | 5 minutes (300000ms) - for production
window.startAutoAwayTimer = function () {
    // Clear existing timer
    if (window.autoAwayTimeout) {
        clearTimeout(window.autoAwayTimeout);
    }

    window.autoAwayTimeout = setTimeout(() => {
        // Call API to set user as away due to inactivity
        fetch("/api/user/auto-away", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
                "X-Requested-With": "XMLHttpRequest",
                Authorization: "Bearer " + (window.chatbotApiToken || ""),
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Update status indicators (current user only)
                    if (window.updateAllStatusIndicators) {
                        window.updateAllStatusIndicators("away", true);
                    }
                }
            })
            .catch((error) => {
                // Silent fail for auto-away
            });
    }, 300000); // 5 minutes (300000ms) - for production
};

// Polling fallback for status updates when real-time is not available
window.startStatusPolling = function () {
    // Clear any existing polling interval
    if (window.statusPollingInterval) {
        clearInterval(window.statusPollingInterval);
    }

    // Poll every 10 seconds
    window.statusPollingInterval = setInterval(() => {
        if (window.syncAllUserStatuses) {
            window.syncAllUserStatuses();
        }
    }, 10000); // 10 seconds
};

// Stop polling when real-time becomes available
window.stopStatusPolling = function () {
    if (window.statusPollingInterval) {
        clearInterval(window.statusPollingInterval);
        window.statusPollingInterval = null;
    }
};

// Function to check and restore user from auto-status when returning to tab
function checkAndRestoreFromAutoStatus() {
    // Call API to restore from auto-status if applicable
    fetch("/api/user/restore-auto-status", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN":
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") || "",
            "X-Requested-With": "XMLHttpRequest",
            Authorization: "Bearer " + (window.chatbotApiToken || ""),
        },
    })
        .then((response) => response.json())
        .then((data) => {
            if (
                data.success &&
                data.data &&
                data.data.previous_status !== data.data.status
            ) {
                // Update status indicators (current user only)
                if (window.updateAllStatusIndicators) {
                    window.updateAllStatusIndicators(data.data.status, true);
                }
                // Restart auto-away timer
                if (data.data.status === "online") {
                    window.startAutoAwayTimer();
                }
            }
        })
        .catch((error) => {
            // Silent fail for auto-status restore
        });
}

// Export functions globally for backward compatibility
window.openGreetingModal = openGreetingModal;
window.closeGreetingModal = closeGreetingModal;
window.navigateToProfile = navigateToProfile;
window.navigateToSettings = navigateToSettings;
window.navigateToActionBoard = navigateToActionBoard;
window.navigateToMeetingLinks = navigateToMeetingLinks;
window.navigateToUsers = navigateToUsers;
window.scrollToWeatherSection = scrollToWeatherSection;
// Toggle Profile video container
function toggleProfileVideo() {
    toggleGenericVideo(
        "profile-video",
        'button:not([onclick="toggleProfileVideo()"])'
    );
}

// Toggle Settings video container
function toggleSettingsVideo() {
    toggleGenericVideo(
        "settings-video",
        'button:not([onclick="toggleSettingsVideo()"])'
    );
}

// Toggle Action Board video container
function toggleActionBoardVideo() {
    toggleGenericVideo(
        "action-board-video",
        'button:not([onclick="toggleActionBoardVideo()"])'
    );
}

// Toggle Meeting Links video container
function toggleMeetingLinksVideo() {
    toggleGenericVideo(
        "meeting-links-video",
        'button:not([onclick="toggleMeetingLinksVideo()"])'
    );
}

// Toggle Users video container
function toggleUsersVideo() {
    toggleGenericVideo(
        "users-video",
        'button:not([onclick="toggleUsersVideo()"])'
    );
}

// Generic video toggle function
function toggleGenericVideo(videoId, otherActionsSelector) {
    const videoContainer = document.getElementById(videoId);
    const quickActionsContainer =
        videoContainer?.parentElement?.querySelector(".space-y-3");

    if (videoContainer && quickActionsContainer) {
        const isHidden = videoContainer.classList.contains("hidden");

        if (isHidden) {
            // Show video container with animation
            videoContainer.classList.remove("hidden");
            // Force reflow to ensure the element is visible before animation
            videoContainer.offsetHeight;
            // Add animation classes
            videoContainer.classList.remove("opacity-0", "scale-95");
            videoContainer.classList.add("opacity-100", "scale-100");

            // Update Alpine.js state for icon rotation - find the specific action button
            let iconContainer = null;
            if (videoId === "profile-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleProfileVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "settings-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleSettingsVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "action-board-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleActionBoardVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "meeting-links-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleMeetingLinksVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "data-management-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleDataManagementVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "users-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleUsersVideo"] [x-data*="isVideoActive"]'
                );
            }

            if (iconContainer && iconContainer._x_dataStack) {
                iconContainer._x_dataStack[0].isVideoActive = true;
            }

            // Hide other quick actions
            const otherActions =
                quickActionsContainer.querySelectorAll(otherActionsSelector);
            otherActions.forEach((action) => {
                action.style.display = "none";
            });

            // Reset video to beginning when showing
            setTimeout(() => {
                const video = videoContainer.querySelector("video");
                // console.log(
                //     "Video element found:",
                //     !!video,
                //     "in container:",
                //     videoId
                // );
                if (video) {
                    loadVideoSource(video);
                    playVideoSafely(video);
                } else {
                    console.error(
                        "No video element found in container:",
                        videoId
                    );
                }
            }, 100);
        } else {
            // Hide video container with animation
            videoContainer.classList.remove("opacity-100", "scale-100");
            videoContainer.classList.add("opacity-0", "scale-95");

            // Update Alpine.js state for icon rotation - find the specific action button
            let iconContainer = null;
            if (videoId === "profile-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleProfileVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "settings-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleSettingsVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "action-board-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleActionBoardVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "meeting-links-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleMeetingLinksVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "data-management-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleDataManagementVideo"] [x-data*="isVideoActive"]'
                );
            } else if (videoId === "users-video") {
                iconContainer = quickActionsContainer.querySelector(
                    'button[onclick*="toggleUsersVideo"] [x-data*="isVideoActive"]'
                );
            }

            if (iconContainer && iconContainer._x_dataStack) {
                iconContainer._x_dataStack[0].isVideoActive = false;
            }

            // Pause video when hiding
            const video = videoContainer.querySelector("video");
            if (video) {
                video.pause();
            }

            // Hide element after animation completes
            setTimeout(() => {
                videoContainer.classList.add("hidden");

                // Show other quick actions after video container is completely hidden
                setTimeout(() => {
                    const otherActions =
                        quickActionsContainer.querySelectorAll(
                            otherActionsSelector
                        );
                    otherActions.forEach((action) => {
                        action.style.display = "flex";
                    });
                }, 100); // Additional delay after video container is hidden
            }, 300);
        }
    }
}

window.toggleDataManagementVideo = toggleDataManagementVideo;
window.toggleProfileVideo = toggleProfileVideo;
window.toggleSettingsVideo = toggleSettingsVideo;
window.toggleActionBoardVideo = toggleActionBoardVideo;
window.toggleMeetingLinksVideo = toggleMeetingLinksVideo;
window.toggleUsersVideo = toggleUsersVideo;
window.toggleVideoPlay = toggleVideoPlay;
window.playVideoInFullscreen = playVideoInFullscreen;
