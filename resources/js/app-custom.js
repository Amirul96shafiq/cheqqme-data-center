document.addEventListener("DOMContentLoaded", () => {
    // -----------------------------
    // Global Search Keyboard Shortcut + Custom Placeholder
    // -----------------------------
    const searchInput = document.querySelector(".fi-global-search input");

    // Set placeholder with styled overlay
    if (searchInput) {
        // Remove the default placeholder
        searchInput.placeholder = "";

        // Remove TAB key functionality from search input
        searchInput.setAttribute("tabindex", "-1");

        // Hide the default search icon
        const searchIcon = searchInput
            .closest(".fi-input-wrp")
            ?.querySelector(".fi-input-wrp-prefix");
        if (searchIcon) {
            searchIcon.style.display = "none";
        }

        // Create a styled overlay with language support
        const overlay = document.createElement("div");

        // Get language from document or default to 'en'
        const lang = document.documentElement.lang || "en";
        const searchTexts = {
            en: "Type <code>/</code> to search",
            ms: "Taip <code>/</code> untuk carian",
        };

        overlay.innerHTML = searchTexts[lang] || searchTexts["en"];
        overlay.style.position = "absolute";
        overlay.style.left = "12px";
        overlay.style.top = "47%";
        overlay.style.transform = "translateY(-50%)";
        overlay.style.pointerEvents = "none";
        overlay.style.color = "#9CA3AF"; // gray-400
        overlay.style.fontSize = "14px";
        overlay.style.fontFamily = "inherit";
        overlay.style.zIndex = "1";

        // Style the code element to match Next.js website
        const codeElement = overlay.querySelector("code");

        // Function to apply styles based on theme
        const applyCodeStyles = () => {
            const isDarkMode =
                document.documentElement.classList.contains("dark") ||
                document.body.classList.contains("dark");

            if (isDarkMode) {
                // Dark mode styles (Next.js dark theme)
                codeElement.style.color = "#F3F4F680"; // gray-100
                codeElement.style.border = "1px solid #6B728080"; // gray-500
            } else {
                // Light mode styles (Next.js light theme)
                codeElement.style.color = "#33415580"; // gray-700
                codeElement.style.border = "1px solid #E2E8F0"; // gray-200
            }
        };

        // Apply base styles
        codeElement.style.padding = "4px 6px";
        codeElement.style.borderRadius = "4px";
        codeElement.style.fontSize = "11px";
        codeElement.style.fontWeight = "500";
        codeElement.style.fontFamily =
            "ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace";
        codeElement.style.letterSpacing = "0.025em";

        // Apply theme-specific styles
        applyCodeStyles();

        // Listen for theme changes
        const themeObserver = new MutationObserver(() => {
            applyCodeStyles();
        });
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ["class"],
        });

        // Make the search input container relative positioned
        const searchContainer = searchInput.closest(".fi-global-search");
        if (searchContainer) {
            searchContainer.style.position = "relative";
            searchContainer.appendChild(overlay);
        }

        // Hide overlay when user types
        searchInput.addEventListener("input", () => {
            overlay.style.display = searchInput.value ? "none" : "block";
        });

        // Hide overlay when focused (optional)
        searchInput.addEventListener("focus", () => {
            overlay.style.display = "none";
        });

        // Show overlay when blurred and empty
        searchInput.addEventListener("blur", () => {
            overlay.style.display = searchInput.value ? "none" : "block";
        });
    }

    // Keyboard shortcut: / key only
    document.addEventListener("keydown", function (e) {
        if (e.key === "/" && !e.ctrlKey && !e.altKey && !e.metaKey) {
            // Only trigger if not typing in an input field
            if (
                e.target.tagName !== "INPUT" &&
                e.target.tagName !== "TEXTAREA" &&
                !e.target.isContentEditable
            ) {
                e.preventDefault();
                const input = document.querySelector(".fi-global-search input");
                if (input) {
                    input.focus();
                }
            }
        }
    });
});
// -----------------------------
// Enable horizontal drag-scroll on Flowforge board
// -----------------------------
(function () {
    let isBound = false;
    function bind() {
        if (isBound) return;
        isBound = true;
        document.addEventListener("mousedown", function (e) {
            // Target the kanban board columns container directly
            const kanbanBoard = e.target.closest(
                ".ff-board__columns.kanban-board"
            );
            if (!kanbanBoard) return;

            // Don't interfere with card dragging or other interactive elements
            if (e.target.closest(".ff-card")) return;
            if (e.target.closest("button")) return;
            if (e.target.closest("input")) return;
            if (e.target.closest("select")) return;
            if (e.target.closest("textarea")) return;
            if (e.target.closest("[contenteditable]")) return;

            e.preventDefault(); // prevent text selection
            let isDown = true;
            const startX = e.pageX;
            const startScrollLeft = kanbanBoard.scrollLeft;
            kanbanBoard.classList.add("ff-drag-scrolling");

            const onMove = (ev) => {
                if (!isDown) return;
                kanbanBoard.scrollLeft = startScrollLeft - (ev.pageX - startX);
                ev.preventDefault();
            };

            const end = () => {
                isDown = false;
                kanbanBoard.classList.remove("ff-drag-scrolling");
                window.removeEventListener("mousemove", onMove);
                window.removeEventListener("mouseup", end);
                window.removeEventListener("mouseleave", end);
            };

            window.addEventListener("mousemove", onMove);
            window.addEventListener("mouseup", end);
            window.addEventListener("mouseleave", end);
        });
    }
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bind);
    } else {
        bind();
    }
    document.addEventListener("livewire:navigated", function () {
        isBound = false;
        bind();
    });
})();

// -----------------------------
// Show a tiny "Copied" helper bubble after sharing
// -----------------------------
(function () {
    window.showCopiedBubble = function (anchor) {
        try {
            const rect = anchor.getBoundingClientRect();
            const bubble = document.createElement("div");
            // Get the language from document's lang attribute or default to 'en'
            const lang = document.documentElement.lang || "en";
            const messages = {
                en: "Copied!",
                ms: "Kopi!",
            };
            bubble.textContent = messages[lang] || messages["en"];
            bubble.style.position = "fixed";
            bubble.style.zIndex = "9999";
            bubble.style.top = rect.top - 40 + "px";
            bubble.style.left = "0px";
            bubble.style.background = "#00AE9F";
            bubble.style.color = "#fff";
            bubble.style.padding = "4px 8px";
            bubble.style.borderRadius = "6px";
            bubble.style.fontSize = "12px";
            bubble.style.opacity = "0";
            bubble.style.transition =
                "opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
            bubble.style.whiteSpace = "nowrap"; // Prevent text wrapping
            document.body.appendChild(bubble);

            // Center the bubble after it's rendered and we can measure its actual width
            requestAnimationFrame(() => {
                const bubbleRect = bubble.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                bubble.style.left = centerX - bubbleRect.width / 2 + "px";
            });

            // Smooth fade in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    bubble.style.opacity = "1";
                });
            });

            // Smooth fade out and remove
            setTimeout(() => {
                bubble.style.opacity = "0";
                setTimeout(() => bubble.remove(), 400);
            }, 1200);
        } catch (e) {
            // Silently fail if DOM not ready
        }
    };
})();

// -----------------------------
// Show a tiny "Refreshed" helper bubble after refreshing weather
// -----------------------------
(function () {
    window.showRefreshedBubble = function (anchor) {
        try {
            const rect = anchor.getBoundingClientRect();
            const bubble = document.createElement("div");
            // Get the language from document's lang attribute or default to 'en'
            const lang = document.documentElement.lang || "en";
            const messages = {
                en: "Refreshed!",
                ms: "Dikemas Kini!",
            };
            bubble.textContent = messages[lang] || messages["en"];
            bubble.style.position = "fixed";
            bubble.style.zIndex = "9999";
            bubble.style.top = rect.top - 40 + "px";
            bubble.style.left = "0px";
            bubble.style.background = "#00AE9F";
            bubble.style.color = "#fff";
            bubble.style.padding = "4px 8px";
            bubble.style.borderRadius = "6px";
            bubble.style.fontSize = "12px";
            bubble.style.opacity = "0";
            bubble.style.transition =
                "opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1)";
            bubble.style.whiteSpace = "nowrap"; // Prevent text wrapping
            document.body.appendChild(bubble);

            // Center the bubble after it's rendered and we can measure its actual width
            requestAnimationFrame(() => {
                const bubbleRect = bubble.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                bubble.style.left = centerX - bubbleRect.width / 2 + "px";
            });

            // Smooth fade in
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    bubble.style.opacity = "1";
                });
            });

            // Smooth fade out and remove
            setTimeout(() => {
                bubble.style.opacity = "0";
                setTimeout(() => bubble.remove(), 400);
            }, 1200);
        } catch (e) {
            // Silently fail if DOM not ready
        }
    };
})();

// -----------------------------
// Reusable Clipboard Utility
// -----------------------------
window.copyToClipboard = function (
    text,
    successMessage = "Copied to clipboard!"
) {
    if (!text) {
        console.error("No text provided to copy");
        return Promise.reject(new Error("No text provided"));
    }

    // Ensure document is focused for clipboard API
    if (document.hasFocus && !document.hasFocus()) {
        window.focus();
    }

    return navigator.clipboard
        .writeText(text)
        .then(function () {
            // Success - notification will be handled by PHP side
            // console.log(successMessage, text);
            return text;
        })
        .catch(function (err) {
            console.error("Failed to copy to clipboard: ", err);

            // Fallback for older browsers or when clipboard API fails
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-9999px";
            textArea.style.top = "-9999px";
            textArea.style.opacity = "0";
            textArea.setAttribute("readonly", "");
            document.body.appendChild(textArea);

            // Focus and select the textarea
            textArea.focus();
            textArea.select();
            textArea.setSelectionRange(0, 99999); // For mobile devices

            try {
                const successful = document.execCommand("copy");
                document.body.removeChild(textArea);

                if (successful) {
                    // Success - notification will be handled by PHP side
                    // console.log(successMessage + " (fallback):", text);
                    return text;
                } else {
                    throw new Error("execCommand('copy') returned false");
                }
            } catch (fallbackErr) {
                document.body.removeChild(textArea);
                console.error("Fallback copy also failed:", fallbackErr);
                throw fallbackErr;
            }
        });
};

// -----------------------------
// Livewire Event Handlers for Clipboard Operations
// -----------------------------
document.addEventListener("livewire:init", function () {
    // Generic copy event handler
    Livewire.on("copy-to-clipboard", function (data) {
        const { text, message } = data;
        copyToClipboard(text, message);
    });

    // Copy with success callback for meeting links (desktop only)
    Livewire.on("copy-to-clipboard-with-callback", function (data) {
        const { text } = data;

        // Small delay to ensure modal is focused
        setTimeout(function () {
            copyToClipboard(text)
                .then(function () {
                    // Success - dispatch event to show bubble
                    Livewire.dispatch("copy-success");
                })
                .catch(function (error) {
                    console.error("Copy failed:", error);
                    // Could dispatch a failure event here if needed
                });
        }, 100);
    });

    // Legacy event handlers for backward compatibility
    Livewire.on("copy-task-url", function (data) {
        copyToClipboard(data.url, "Task URL copied to clipboard!");
    });

    Livewire.on("copy-api-key", function (data) {
        copyToClipboard(data.apiKey, "API key copied to clipboard!");
    });
});

// -----------------------------
// Apply cover image backgrounds to table rows
// -----------------------------
(function () {
    let periodicCheckInterval;

    // Cache for preloaded images to avoid re-requesting
    const imageCache = new Map();

    function preloadImage(url) {
        // Return cached promise if already loading
        if (imageCache.has(url)) {
            return imageCache.get(url);
        }

        const promise = new Promise((resolve, reject) => {
            const img = new Image();
            img.crossOrigin = "anonymous"; // Enable CORS for external images
            img.onload = () => resolve(img);
            img.onerror = reject;
            img.src = url;
        });

        imageCache.set(url, promise);
        return promise;
    }

    function applyCoverImages() {
        // Find all table cells with cover image data
        const coverImageCells = document.querySelectorAll(
            "[data-cover-image-url]"
        );

        let appliedCount = 0;

        coverImageCells.forEach((cell) => {
            const coverImageUrl = cell.getAttribute("data-cover-image-url");
            if (coverImageUrl) {
                // Find the parent table row
                const tableRow = cell.closest("tr");
                if (tableRow) {
                    // Always reapply the background for cover image rows
                    const isDarkMode =
                        document.documentElement.classList.contains("dark") ||
                        document.body.classList.contains("dark");
                    const gradient = isDarkMode
                        ? `linear-gradient(to right, rgba(24,24,27,0.3) 0%, rgba(24,24,27,0.7) 20%, rgba(24,24,27,0.9) 70%, rgba(24,24,27,1) 100%)`
                        : `linear-gradient(to right, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.7) 20%, rgba(255,255,255,0.9) 70%, rgba(255,255,255,1) 100%)`;

                    // Apply immediately without waiting for image to load
                    // This uses native browser image caching
                    tableRow.style.backgroundImage = `${gradient}, url('${coverImageUrl}')`;
                    tableRow.classList.add("cover-image-row");
                    appliedCount++;

                    // Preload image in background for next render (non-blocking)
                    preloadImage(coverImageUrl).catch(() => {
                        // Silently handle failed loads
                    });
                }
            }
        });
    }

    // Start periodic checking for cover images (fallback mechanism)
    function startPeriodicCheck() {
        if (periodicCheckInterval) {
            clearInterval(periodicCheckInterval);
        }

        periodicCheckInterval = setInterval(() => {
            const coverImageCells = document.querySelectorAll(
                "[data-cover-image-url]"
            );
            const rowsWithoutBackground = Array.from(coverImageCells).filter(
                (cell) => {
                    const tableRow = cell.closest("tr");
                    return tableRow && !tableRow.style.backgroundImage;
                }
            );

            if (rowsWithoutBackground.length > 0) {
                // console.log(
                //     `Found ${rowsWithoutBackground.length} rows without cover images, applying...`
                // );
                applyCoverImages(); // Apply immediately without delay
            }
        }, 200); // Check every 200ms for faster response
    }

    // Apply on page load
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            applyCoverImages();
            startPeriodicCheck();
        });
    } else {
        applyCoverImages();
        startPeriodicCheck();
    }

    // Apply on all relevant Livewire events
    document.addEventListener("livewire:updated", (event) => {
        // console.log("Livewire updated event triggered", event);
        applyCoverImages();
    });

    document.addEventListener("livewire:navigated", (event) => {
        // console.log("Livewire navigated event triggered", event);
        applyCoverImages();
    });

    // Listen for theme changes (Filament light-switch plugin)
    document.addEventListener("theme-changed", (event) => {
        // console.log("Theme changed event triggered", event.detail);
        // Reapply cover images immediately when theme changes
        applyCoverImages();
    });

    // Additional Livewire events that might trigger DOM updates
    document.addEventListener("livewire:loading", () => {
        // console.log("Livewire loading event triggered");
    });

    // Listen for input changes that might trigger search
    document.addEventListener("input", (event) => {
        if (
            event.target.matches("input[wire\\:model*='search']") ||
            event.target.matches("input[wire\\:model*='tableSearch']") ||
            event.target.closest("[wire\\:model*='search']") ||
            event.target.closest("[wire\\:model*='tableSearch']")
        ) {
            // console.log(
            //     "Search input detected, applying cover images immediately"
            // );
            // Apply immediately without delay
            applyCoverImages();
        }
    });

    // Use MutationObserver as a fallback to catch any DOM changes
    const observer = new MutationObserver((mutations) => {
        let shouldApply = false;
        mutations.forEach((mutation) => {
            if (
                mutation.type === "childList" &&
                mutation.addedNodes.length > 0
            ) {
                // Check if any added nodes contain table rows with cover images
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        if (
                            node.matches &&
                            node.matches("[data-cover-image-url]")
                        ) {
                            shouldApply = true;
                        } else if (
                            node.querySelector &&
                            node.querySelector("[data-cover-image-url]")
                        ) {
                            shouldApply = true;
                        }
                        // Also check for table-related elements that might contain our target elements
                        if (
                            node.matches &&
                            (node.matches("table") ||
                                node.matches("tbody") ||
                                node.matches("tr"))
                        ) {
                            shouldApply = true;
                        }
                    }
                });
            }
            // Also monitor attribute changes that might affect our elements
            if (
                mutation.type === "attributes" &&
                mutation.target.matches &&
                (mutation.target.matches("[data-cover-image-url]") ||
                    mutation.target.closest("[data-cover-image-url]"))
            ) {
                shouldApply = true;
            }
        });

        if (shouldApply) {
            // console.log("MutationObserver detected relevant DOM changes");
            applyCoverImages();
        }
    });

    // Start observing the document body for changes
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ["data-cover-image-url", "wire:model"],
    });

    // Also observe the html element for theme class changes
    const htmlObserver = new MutationObserver((mutations) => {
        let themeChanged = false;
        mutations.forEach((mutation) => {
            if (
                mutation.type === "attributes" &&
                mutation.attributeName === "class"
            ) {
                themeChanged = true;
            }
        });

        if (themeChanged) {
            // console.log("HTML class changed, checking for theme change");
            // Check if dark mode status changed and reapply if needed
            applyCoverImages();
        }
    });

    htmlObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });

    // Cleanup on page unload
    window.addEventListener("beforeunload", () => {
        if (periodicCheckInterval) {
            clearInterval(periodicCheckInterval);
        }
        observer.disconnect();
        htmlObserver.disconnect();
    });
})();

// -----------------------------
// Global Alpine.js Functions for Comment Reactions
// -----------------------------
window.commentReactions = function () {
    return {
        commentId: null,
        reactions: [],

        init() {
            // Listen for global reaction update events
            this.setupGlobalEventListener();
        },

        setupGlobalEventListener() {
            // Listen on a comment-scoped event channel to avoid cross-comment updates
            const handler = (event) => {
                const { commentId, emoji, action, count, reaction } =
                    event.detail || {};
                if (commentId === this.commentId) {
                    this.handleReactionUpdate(emoji, action, count, reaction);
                }
            };

            const eventName = `comment-reaction-updated-${this.commentId}`;
            window.addEventListener(eventName, handler);
        },

        handleReactionUpdate(emoji, action, count, reactionData = null) {
            if (action === "added") {
                this.addOrUpdateReaction(emoji, count, reactionData);
            } else if (action === "removed") {
                this.removeOrUpdateReaction(emoji, count);
            }
        },

        addOrUpdateReaction(emoji, count, reactionData = null) {
            const existingIndex = this.reactions.findIndex(
                (r) => r.emoji === emoji
            );

            if (existingIndex >= 0) {
                this.reactions[existingIndex].count = count;
                this.reactions[existingIndex].user_reacted = true;
            } else {
                const currentUser = this.getCurrentUserInfo(reactionData);

                this.reactions.push({
                    emoji: emoji,
                    count: count,
                    user_reacted: true,
                    users: [currentUser],
                });
            }

            this.$nextTick(() => {
                const container = this.$el.closest(".comment-reactions");
                if (!container) return;
                const buttons = container.querySelectorAll(
                    ":scope > .reaction-button"
                );
            });
        },

        removeOrUpdateReaction(emoji, count) {
            const existingIndex = this.reactions.findIndex(
                (r) => r.emoji === emoji
            );

            if (existingIndex >= 0) {
                if (count === 0) {
                    this.reactions.splice(existingIndex, 1);
                } else {
                    this.reactions[existingIndex].count = count;
                    this.reactions[existingIndex].user_reacted = false;

                    const currentUserId = this.getCurrentUserId();
                    this.reactions[existingIndex].users = this.reactions[
                        existingIndex
                    ].users.filter((u) => u.id !== currentUserId);
                }
            }

            this.$nextTick(() => {
                const container = this.$el.closest(".comment-reactions");
                if (!container) return;
                const buttons = container.querySelectorAll(
                    ":scope > .reaction-button"
                );
            });
        },

        getCurrentUserInfo(reactionData = null) {
            if (reactionData && reactionData.user) {
                return reactionData.user;
            }

            const userId = this.getCurrentUserId();
            return {
                id: userId,
                username: "You",
                name: "You",
            };
        },

        getCurrentUserId() {
            const userId = document.body.getAttribute("data-user-id");
            return userId ? parseInt(userId) : 0;
        },

        async updateReactionDisplay() {
            try {
                const response = await fetch(
                    `/api/comments/${this.commentId}/reactions`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN":
                                document
                                    .querySelector('meta[name="csrf-token"]')
                                    ?.getAttribute("content") || "",
                        },
                    }
                );

                if (response.ok) {
                    const data = await response.json();
                    this.reactions = data.data || [];
                }
            } catch (error) {
                console.log("Using local data (server refresh failed):", error);
            }

            this.$nextTick(() => {
                this.$dispatch("reactions-updated", {
                    commentId: this.commentId,
                    reactions: this.reactions,
                });
            });
        },
    };
};

// -----------------------------
// Global Alpine.js Functions for Emoji Picker
// -----------------------------
window.emojiPicker = function (commentId) {
    return {
        commentId: commentId,
        open: false,
        userReactions: [],
        loading: false,
        pickerStyle: {},
        searchQuery: "",
        allEmojis: [],
        filteredEmojis: [],
        recentEmojis: [],

        init() {
            this.initializeEmojis();
            this.loadRecentEmojis();

            this.$watch("commentId", () => {
                if (this.open) {
                    this.loadUserReactions();
                }
            });
        },

        toggle() {
            if (!this.open) {
                this.calculateCenterPosition();
                if (this.userReactions.length === 0) {
                    this.loadUserReactions();
                }
            }

            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        initializeEmojis() {
            this.allEmojis = [
                {
                    emoji: "👍",
                    keywords: [
                        "thumbs up",
                        "like",
                        "good",
                        "approve",
                        "yes",
                        "up",
                    ],
                },
                {
                    emoji: "❤️",
                    keywords: [
                        "heart",
                        "love",
                        "red heart",
                        "like",
                        "favorite",
                    ],
                },
                {
                    emoji: "😂",
                    keywords: [
                        "laughing",
                        "funny",
                        "lol",
                        "happy",
                        "joy",
                        "tears",
                    ],
                },
                {
                    emoji: "😍",
                    keywords: [
                        "heart eyes",
                        "love",
                        "adore",
                        "infatuated",
                        "attractive",
                    ],
                },
                {
                    emoji: "🤔",
                    keywords: [
                        "thinking",
                        "ponder",
                        "consider",
                        "hmm",
                        "question",
                    ],
                },
                {
                    emoji: "😢",
                    keywords: ["crying", "sad", "tears", "upset", "unhappy"],
                },
                {
                    emoji: "😮",
                    keywords: [
                        "surprised",
                        "shock",
                        "wow",
                        "amazed",
                        "astonished",
                    ],
                },
                {
                    emoji: "🔥",
                    keywords: ["fire", "hot", "lit", "amazing", "awesome"],
                },
                {
                    emoji: "💯",
                    keywords: [
                        "100",
                        "perfect",
                        "century",
                        "complete",
                        "score",
                    ],
                },
                {
                    emoji: "🚀",
                    keywords: ["rocket", "launch", "fast", "speed", "success"],
                },
                {
                    emoji: "💪",
                    keywords: ["muscle", "strong", "power", "flex", "biceps"],
                },
                {
                    emoji: "🎉",
                    keywords: [
                        "party",
                        "celebration",
                        "confetti",
                        "happy",
                        "festive",
                    ],
                },
                {
                    emoji: "👏",
                    keywords: [
                        "clap",
                        "applause",
                        "congratulations",
                        "bravo",
                        "praise",
                    ],
                },
                {
                    emoji: "🙌",
                    keywords: [
                        "praise",
                        "hallelujah",
                        "celebration",
                        "victory",
                        "raise hands",
                    ],
                },
                {
                    emoji: "🤝",
                    keywords: [
                        "handshake",
                        "deal",
                        "agreement",
                        "partnership",
                        "shake",
                    ],
                },
                {
                    emoji: "👌",
                    keywords: [
                        "ok",
                        "okay",
                        "good",
                        "perfect",
                        "fine",
                        "alright",
                        "nice",
                    ],
                },
                {
                    emoji: "🥳",
                    keywords: [
                        "party face",
                        "celebration",
                        "birthday",
                        "festive",
                        "fun",
                    ],
                },
                {
                    emoji: "😎",
                    keywords: [
                        "cool",
                        "sunglasses",
                        "awesome",
                        "smug",
                        "confident",
                    ],
                },
            ];

            this.filteredEmojis = [...this.allEmojis];
        },

        filterEmojis() {
            if (!this.searchQuery.trim()) {
                this.filteredEmojis = [...this.allEmojis];
                return;
            }

            const query = this.searchQuery.toLowerCase();
            this.filteredEmojis = this.allEmojis.filter((item) =>
                item.keywords.some((keyword) =>
                    keyword.toLowerCase().includes(query)
                )
            );
        },

        clearSearch() {
            this.searchQuery = "";
            this.filterEmojis();
        },

        loadRecentEmojis() {
            try {
                const userId = window.chatbotUserId || "anonymous";
                const recentKey = `recent_emojis_${userId}`;
                const stored = localStorage.getItem(recentKey);
                this.recentEmojis = stored ? JSON.parse(stored) : [];
            } catch (error) {
                console.error("Failed to load recent emojis:", error);
                this.recentEmojis = [];
            }
        },

        saveRecentEmojis() {
            try {
                const userId = window.chatbotUserId || "anonymous";
                const recentKey = `recent_emojis_${userId}`;
                localStorage.setItem(
                    recentKey,
                    JSON.stringify(this.recentEmojis)
                );
            } catch (error) {
                console.error("Failed to save recent emojis:", error);
            }
        },

        addToRecentEmojis(emoji) {
            this.recentEmojis = this.recentEmojis.filter((e) => e !== emoji);
            this.recentEmojis.unshift(emoji);
            this.recentEmojis = this.recentEmojis.slice(0, 20);
            this.saveRecentEmojis();
        },

        calculateCenterPosition() {
            const commentListContainer = document.querySelector(
                "[data-comment-list]"
            );
            if (!commentListContainer) {
                this.centerInViewport();
                return;
            }

            const containerRect = commentListContainer.getBoundingClientRect();
            const pickerWidth = 320;
            const pickerHeight = 380;

            const centerX =
                containerRect.left + containerRect.width / 2 - pickerWidth / 2;
            const centerY =
                containerRect.top + containerRect.height / 2 - pickerHeight / 2;

            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            let finalX = Math.max(
                10,
                Math.min(centerX, viewportWidth - pickerWidth - 10)
            );
            let finalY = Math.max(
                10,
                Math.min(centerY, viewportHeight - pickerHeight - 10)
            );

            this.pickerStyle = {
                position: "fixed",
                top: `${finalY}px`,
                left: `${finalX}px`,
                zIndex: 9999,
            };
        },

        centerInViewport() {
            const pickerWidth = 320;
            const pickerHeight = 380;
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            const centerX = viewportWidth / 2 - pickerWidth / 2;
            const centerY = viewportHeight / 2 - pickerHeight / 2;

            this.pickerStyle = {
                position: "fixed",
                top: `${centerY}px`,
                left: `${centerX}px`,
                zIndex: 9999,
            };
        },

        async loadUserReactions() {
            try {
                const response = await fetch(
                    `/api/comments/${this.commentId}/reactions`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": document
                                .querySelector('meta[name="csrf-token"]')
                                .getAttribute("content"),
                        },
                    }
                );

                if (response.ok) {
                    const data = await response.json();
                    this.userReactions = data.data
                        .filter((reaction) => reaction.user_reacted)
                        .map((reaction) => reaction.emoji);
                }
            } catch (error) {
                console.error(
                    "[EmojiPicker] Failed to load user reactions for comment",
                    this.commentId,
                    ":",
                    error
                );
            }
        },

        async addReaction(emoji) {
            if (this.loading) return;

            this.loading = true;

            try {
                this.updateReactionOptimistically(emoji);
            } catch (e) {
                console.error(
                    "[EmojiPicker] updateReactionOptimistically error",
                    e
                );
            }

            this.close();

            try {
                const csrfToken = document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

                const response = await fetch("/api/comment-reactions", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": csrfToken || "",
                    },
                    body: JSON.stringify({
                        comment_id: this.commentId,
                        emoji: emoji,
                    }),
                });

                const data = await response.json();

                if (response.ok) {
                    if (data.data.action === "added") {
                        this.userReactions.push(emoji);
                        this.addToRecentEmojis(emoji);
                    } else if (data.data.action === "removed") {
                        this.userReactions = this.userReactions.filter(
                            (e) => e !== emoji
                        );
                    }

                    try {
                        this.updateReactionWithServerData(emoji, data.data);
                    } catch (e) {
                        console.error(
                            "[EmojiPicker] updateReactionWithServerData error",
                            e
                        );
                    }

                    this.$dispatch("reaction-updated", {
                        commentId: this.commentId,
                        emoji: emoji,
                        action: data.data.action,
                        count: data.data.reaction_count,
                        reaction: data.data.reaction || null,
                    });

                    const scopedEventName = `comment-reaction-updated-${this.commentId}`;
                    window.dispatchEvent(
                        new CustomEvent(scopedEventName, {
                            detail: {
                                commentId: this.commentId,
                                emoji: emoji,
                                action: data.data.action,
                                count: data.data.reaction_count,
                                reaction: data.data.reaction || null,
                            },
                        })
                    );
                } else {
                    console.error("API Error:", data);
                    this.revertOptimisticUpdate(emoji);
                }
            } catch (error) {
                console.error("[EmojiPicker] Failed to add reaction", error);
                this.revertOptimisticUpdate(emoji);
            } finally {
                this.loading = false;
            }
        },

        updateReactionOptimistically(emoji) {
            const reactionsContainer = this.$el.closest(".comment-reactions");
            if (!reactionsContainer) {
                console.warn(
                    "[EmojiPicker] reactionsContainer not found for optimistic update"
                );
            }
            const existingButton = reactionsContainer?.querySelector(
                `:scope > .reaction-button[data-emoji="${emoji}"]`
            );

            if (existingButton) {
                const currentCount = parseInt(
                    existingButton.getAttribute("data-count")
                );
                const isUserReacted =
                    existingButton.classList.contains("bg-primary-100/10");

                if (isUserReacted) {
                    const newCount = currentCount - 1;
                    if (newCount === 0) {
                        existingButton.remove();
                    } else {
                        existingButton.setAttribute("data-count", newCount);
                        existingButton.querySelector(".text-xs").textContent =
                            newCount;
                        existingButton.className =
                            existingButton.className.replace(
                                "bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700",
                                "bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600"
                            );
                    }
                } else {
                    const newCount = currentCount + 1;
                    existingButton.setAttribute("data-count", newCount);
                    existingButton.querySelector(".text-xs").textContent =
                        newCount;
                    existingButton.className = existingButton.className.replace(
                        "bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600",
                        "bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700"
                    );
                }
            } else {
                const reactionsContainer2 = reactionsContainer;
                if (reactionsContainer2) {
                    const newButton = this.createOptimisticReactionButton(
                        emoji,
                        1
                    );
                    const emojiPicker = reactionsContainer2.querySelector(
                        ".emoji-picker-trigger"
                    ).parentElement;
                    emojiPicker.parentElement.insertBefore(
                        newButton,
                        emojiPicker.nextSibling
                    );
                }
            }
        },

        updateReactionWithServerData(emoji, serverData) {
            const reactionsContainer = this.$el.closest(".comment-reactions");
            const existingButton = reactionsContainer?.querySelector(
                `:scope > .reaction-button[data-emoji="${emoji}"]`
            );

            if (
                serverData.action === "removed" &&
                serverData.reaction_count === 0
            ) {
                if (existingButton) {
                    existingButton.remove();
                }
            } else if (existingButton) {
                existingButton.setAttribute(
                    "data-count",
                    serverData.reaction_count
                );
                existingButton.querySelector(".text-xs").textContent =
                    serverData.reaction_count;

                if (serverData.action === "added") {
                    existingButton.className = existingButton.className.replace(
                        "bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600",
                        "bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700"
                    );
                } else {
                    existingButton.className = existingButton.className.replace(
                        "bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700",
                        "bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600"
                    );
                }
            }
        },

        revertOptimisticUpdate(emoji) {
            this.refreshReactionsDisplay();
        },

        createOptimisticReactionButton(emoji, count) {
            const button = document.createElement("button");
            button.type = "button";
            button.className =
                "reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700 cursor-default";
            button.setAttribute("data-emoji", emoji);
            button.setAttribute("data-count", count);
            button.setAttribute("title", "You (just now)");

            button.innerHTML = `
                <span class="text-sm">${emoji}</span>
                <span class="text-xs font-medium">${count}</span>
            `;

            return button;
        },

        async refreshReactionsDisplay() {
            if (window.refreshCommentReactions) {
                window.refreshCommentReactions(this.commentId);
                return;
            }

            const reactionsContainer = this.$el.closest(".comment-reactions");
            if (!reactionsContainer) {
                return;
            }

            try {
                const response = await fetch(
                    `/api/comments/${this.commentId}/reactions`,
                    {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN":
                                document
                                    .querySelector('meta[name="csrf-token"]')
                                    ?.getAttribute("content") || "",
                        },
                    }
                );

                if (response.ok) {
                    const data = await response.json();
                    this.updateReactionsHTML(reactionsContainer, data.data);
                }
            } catch (error) {
                console.error(
                    "[EmojiPicker] Error refreshing reactions",
                    error
                );
            }
        },

        updateReactionsHTML(container, reactions) {
            const existingReactions =
                container.querySelectorAll(".reaction-button");
            existingReactions.forEach((btn) => btn.remove());

            reactions.forEach((reaction) => {
                const button = this.createReactionButton(reaction);
                const emojiPicker = container.querySelector(
                    ".emoji-picker-trigger"
                ).parentElement;
                emojiPicker.parentElement.insertBefore(
                    button,
                    emojiPicker.nextSibling
                );
            });
        },

        createReactionButton(reaction) {
            return window.createReactionButton(
                reaction,
                this.commentId,
                this.addReaction.bind(this)
            );
        },
    };
};

// -----------------------------
// Global helper functions for comment reactions
// -----------------------------
window.formatDateTime = function (dateTimeString) {
    try {
        const date = new Date(dateTimeString);
        const day = String(date.getDate()).padStart(2, "0");
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const year = String(date.getFullYear()).slice(-2);
        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, "0");
        const ampm = hours >= 12 ? "PM" : "AM";
        hours = hours % 12;
        hours = hours ? hours : 12;
        const timeStr = `${hours}:${minutes} ${ampm}`;

        return `${day}/${month}/${year} • ${timeStr}`;
    } catch (error) {
        console.error("Error formatting date:", error);
        return "";
    }
};

window.refreshCommentReactions = async function (commentId) {
    const reactionsContainer = document.querySelector(
        `[data-comment-id="${commentId}"] .comment-reactions`
    );
    if (!reactionsContainer) {
        return;
    }

    try {
        const response = await fetch(`/api/comments/${commentId}/reactions`, {
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN":
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") || "",
            },
        });

        if (response.ok) {
            const data = await response.json();

            const existingReactions =
                reactionsContainer.querySelectorAll(".reaction-button");
            existingReactions.forEach((btn) => btn.remove());

            data.data.forEach((reaction) => {
                const button = window.createReactionButton(reaction, commentId);
                const emojiPicker = reactionsContainer.querySelector(
                    ".emoji-picker-trigger"
                ).parentElement;
                emojiPicker.parentElement.insertBefore(
                    button,
                    emojiPicker.nextSibling
                );
            });
        }
    } catch (error) {
        console.error("Error refreshing reactions:", error);
    }
};

window.createReactionButton = function (
    reaction,
    commentId,
    addReactionCallback = null
) {
    const button = document.createElement("button");
    button.type = "button";
    button.className = `reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 ${
        reaction.user_reacted
            ? "bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700 cursor-default"
            : "bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600 cursor-default"
    }`;
    button.setAttribute("data-emoji", reaction.emoji);
    button.setAttribute("data-count", reaction.count);

    let tooltip = "";
    if (reaction.users.length > 0) {
        const user = reaction.users[0];
        const userName = user.name || user.username || "Unknown";
        const reactedAt = user.reacted_at
            ? window.formatDateTime(user.reacted_at)
            : "";

        tooltip = `${userName}${reactedAt ? ` (${reactedAt})` : ""}`;

        if (reaction.users.length > 1) {
            tooltip += ` and ${reaction.users.length - 1} others`;
        }
    }

    button.setAttribute("title", tooltip);

    button.innerHTML = `
        <span class="text-sm">${reaction.emoji}</span>
        <span class="text-xs font-medium">${reaction.count}</span>
    `;

    return button;
};
