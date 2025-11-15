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
