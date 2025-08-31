// -----------------------------
// Chatbot functionality
// -----------------------------
(function () {
    let conversationId = null; // Start with null ID
    let conversation = [];
    let isLoadingConversation = false;
    let conversationLoaded = false;

    // Get user-specific conversation ID from localStorage
    function getUserConversationKey() {
        const userId = window.chatbotUserId || "anonymous";
        return `chatbot_conversation_id_${userId}`;
    }

    // Get user-specific chatbot open state key
    function getUserChatStateKey() {
        const userId = window.chatbotUserId || "anonymous";
        return `chatbot_open_${userId}`;
    }

    // Check if user has changed and reset UI state if needed
    function handleUserChange() {
        const currentUserId = window.chatbotUserId || "anonymous";
        const lastUserId = localStorage.getItem("chatbot_last_user_id");

        console.log(
            `handleUserChange: currentUserId=${currentUserId}, lastUserId=${lastUserId}`
        );

        if (lastUserId && lastUserId !== currentUserId) {
            // User has changed (logout/login), reset conversation loading state
            // But DON'T clear the conversation IDs - they should persist in localStorage
            // and be matched with database records by user_id
            conversationLoaded = false;
            conversation = [];

            console.log(`User changed from ${lastUserId} to ${currentUserId}`);
        } else {
            console.log(`Same user or first load: ${currentUserId}`);
        }

        // Update the last user ID
        localStorage.setItem("chatbot_last_user_id", currentUserId);
    }

    // Handle user change detection
    handleUserChange();

    // Load conversation ID from user-specific localStorage
    conversationId = localStorage.getItem(getUserConversationKey());

    // Fetches the latest or a new conversation session from the backend
    async function initializeSession() {
        try {
            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
            const response = await fetch("/chatbot/session", {
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
            });
            if (response.ok) {
                const data = await response.json();
                conversationId = data.conversation_id;

                // Store the conversation ID with user-specific key
                localStorage.setItem(getUserConversationKey(), conversationId);

                console.log(
                    "Chatbot session initialized with conversation ID:",
                    conversationId,
                    "for user:",
                    window.chatbotUserId
                );

                // Load the history automatically after getting session info
                // Add a small delay to ensure the conversation ID is properly set
                setTimeout(() => {
                    loadConversationHistory();
                }, 200);
            } else {
                console.error(
                    "Failed to initialize chatbot session",
                    response.status,
                    response.statusText
                );
            }
        } catch (error) {
            console.error("Error initializing chatbot session:", error);
        }
    }

    // Initialize chatbot state will run when the DOM is ready
    function initializeChatbotState() {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (!interfaceEl || !chatIcon || !closeIcon) return;

        // Restore previous state from localStorage (defaults to closed if no previous state)
        const chatState = localStorage.getItem(getUserChatStateKey());
        const shouldBeOpen = chatState === "true";

        console.log(
            `Initializing chatbot state - chatState: ${chatState}, shouldBeOpen: ${shouldBeOpen}`
        );

        // Use the centralized visibility setter for consistency
        setChatVisibility(shouldBeOpen);

        // If the chatbox should be open, ensure conversation history is loaded
        if (shouldBeOpen && conversationId && !conversationLoaded) {
            setTimeout(() => {
                loadConversationHistory();
            }, 300);
        }
    }

    // Run initialization after the DOM is ready to ensure elements exist
    function onDocumentReady(callback) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback);
        } else {
            callback();
        }
    }

    // Centralized visibility setter for chatbot UI
    function setChatVisibility(isOpen) {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (!interfaceEl || !chatIcon || !closeIcon) return;

        if (isOpen) {
            // Show chatbot interface
            interfaceEl.classList.remove("hidden");
            interfaceEl.classList.add("open");
            interfaceEl.style.display = "flex";

            // Hide chat icon, show close icon
            chatIcon.style.display = "none";
            chatIcon.classList.add("hidden");
            closeIcon.style.display = "inline-flex";
            closeIcon.classList.remove("hidden");
        } else {
            // Hide chatbot interface
            interfaceEl.classList.add("hidden");
            interfaceEl.classList.remove("open");
            interfaceEl.style.display = "none";

            // Show chat icon, hide close icon
            chatIcon.style.display = "inline-flex";
            chatIcon.classList.remove("hidden");
            closeIcon.style.display = "none";
            closeIcon.classList.add("hidden");
        }

        // Persist open state
        localStorage.setItem(getUserChatStateKey(), isOpen ? "true" : "false");

        // console.log(`Chat visibility set to: ${isOpen ? "open" : "closed"}`);
    }

    // Apply chatbot open/close state when elements exist; safe for dynamic insertion
    let chatbotUIInitialized = false;
    function applyChatbotStateIfElementsPresent() {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (!interfaceEl || !chatIcon || !closeIcon) return;

        const chatState = localStorage.getItem(getUserChatStateKey());
        const shouldBeOpen = chatState === "true";

        console.log(
            `Applying chatbot state - chatState: ${chatState}, shouldBeOpen: ${shouldBeOpen}`
        );

        setChatVisibility(shouldBeOpen);

        // If the chatbox should be open, ensure conversation history is loaded
        if (shouldBeOpen && conversationId && !conversationLoaded) {
            setTimeout(() => {
                loadConversationHistory();
            }, 400);
        }

        // History will be loaded automatically after session initialization
        chatbotUIInitialized = true;
    }

    // Initialize chatbot state when the DOM is ready
    onDocumentReady(() => {
        initializeSession(); // Fetch session info on document ready

        // Use a small delay to ensure DOM elements are ready
        setTimeout(() => {
            initializeChatbotState(); // Initialize state first
            applyChatbotStateIfElementsPresent(); // Apply saved state from localStorage as fallback
        }, 100);
    });

    // Observe DOM changes to re-apply state when chat elements are inserted dynamically
    const chatbotObserver = new MutationObserver(() => {
        if (!chatbotUIInitialized) {
            applyChatbotStateIfElementsPresent();
        }
    });
    chatbotObserver.observe(document.body, { childList: true, subtree: true });

    // Robust initializer: poll for chat elements if not yet present, then apply state
    function pollForChatElements(retriesLeft, delayMs) {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (interfaceEl && chatIcon && closeIcon) {
            applyChatbotStateIfElementsPresent();
            return;
        }
        if (retriesLeft > 0) {
            setTimeout(
                () => pollForChatElements(retriesLeft - 1, delayMs),
                delayMs
            );
        }
    }

    // Kick off polling as a fallback in case elements are injected later
    pollForChatElements(20, 100);

    // Toggle chatbot visibility
    function toggleChatbot() {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (!interfaceEl || !chatIcon || !closeIcon) return;

        // Determine hidden by computed display value (not relying on Tailwind's hidden class)
        const isCurrentlyHidden =
            window.getComputedStyle(interfaceEl).display === "none";

        if (isCurrentlyHidden) {
            // Opening: use centralized state setter and then animate
            setChatVisibility(true);
            interfaceEl.classList.add("open");
            requestAnimationFrame(() => {
                // ensure animation frame after state application
            });
            if (!isLoadingConversation) {
                loadConversationHistory();
            }
        } else {
            // Closing: animate out then hide
            interfaceEl.classList.remove("open");
            interfaceEl.classList.add("closing");
            const transitionMs = 260;
            setTimeout(() => {
                interfaceEl.classList.remove("closing");
                localStorage.setItem(getUserChatStateKey(), "false");
                setChatVisibility(false);
            }, transitionMs);
        }
    }

    // Load conversation history from the backend
    async function loadConversationHistory() {
        console.log("Loading conversation history:", {
            conversationId,
            conversationLength: conversation.length,
            conversationLoaded,
            isLoadingConversation,
            userId: window.chatbotUserId,
        });

        if (!conversationId || isLoadingConversation) {
            console.log(
                "Skipping load - no conversation ID or loading in progress"
            );
            return;
        }

        // Reset conversation loaded flag if conversation ID changed
        const storedConversationId = localStorage.getItem(
            getUserConversationKey()
        );
        if (storedConversationId !== conversationId) {
            conversationLoaded = false;
        }

        if (conversationLoaded) {
            console.log("Conversation already loaded");
            return;
        }

        try {
            isLoadingConversation = true;
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ||
                document.querySelector('input[name="_token"]')?.value;

            const response = await fetch(
                `/chatbot/conversation?conversation_id=${encodeURIComponent(
                    conversationId
                )}`,
                {
                    method: "GET",
                    headers: {
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                }
            );

            console.log("Conversation history response:", response.status);

            if (response.ok) {
                const data = await response.json();
                console.log("Conversation data:", data);

                if (data.conversation && data.conversation.length > 0) {
                    // Load conversation messages
                    const chatMessages =
                        document.getElementById("chat-messages");
                    // Clear existing messages before loading history
                    if (chatMessages) chatMessages.innerHTML = "";

                    data.conversation.forEach((message, index) => {
                        addMessage(
                            message.content,
                            message.role,
                            message.timestamp,
                            index * 100 // Stagger animations by 100ms each
                        );
                    });

                    conversationLoaded = true;
                    console.log(
                        "Loaded",
                        data.conversation.length,
                        "messages from conversation"
                    );
                } else {
                    // No conversation history found - this is normal for new conversations
                    conversationLoaded = true; // Mark as loaded to prevent re-loading
                    // Initiate a friendly first message from the chatbot
                    const welcomeTs = new Date().toLocaleTimeString("en-US", {
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                    addMessage(
                        window.chatbot?.welcome_message ||
                            "Hello! I'm :ai_name. The most genius AI assistant in the world. How can I assist you today?",
                        "assistant",
                        welcomeTs
                    );
                    // Add help information message
                    setTimeout(() => {
                        addMessage(
                            window.chatbot?.help_message ||
                                "Use :help_command to call my available functions!",
                            "assistant",
                            welcomeTs
                        );
                    }, 1000); // Delay to show after welcome message
                    console.log(
                        "No conversation messages found in database - empty conversation; greeting posted"
                    );
                }
            } else {
                console.error(
                    "Failed to load conversation:",
                    response.statusText
                );
            }
        } catch (error) {
            console.error("Error loading conversation history:", error);
        } finally {
            isLoadingConversation = false;
        }
    }

    // Process translation strings with placeholders
    function processTranslation(text, replacements = {}) {
        if (!text) return text;

        let processedText = text;

        // Replace :ai_name with styled AI name
        if (processedText.includes(":ai_name")) {
            const aiName = window.chatbot?.ai_name || "Arem AI";
            const styledAiName = `<span class="chatbot-ai-name">${aiName}</span>`;
            processedText = processedText.replace(/:ai_name/g, styledAiName);
        }

        // Replace :help_command with styled help command
        if (processedText.includes(":help_command")) {
            const helpCommand = window.chatbot?.help_command || "/help";
            const styledHelpCommand = `<span class="chatbot-help-command">${helpCommand}</span>`;
            processedText = processedText.replace(
                /:help_command/g,
                styledHelpCommand
            );
        }

        return processedText;
    }

    // Shorten user name to first name + first letter of last name
    function shortenName(fullName) {
        const nameParts = fullName.trim().split(/\s+/);
        if (nameParts.length === 1) {
            return fullName; // If only one name, return as is
        } else if (nameParts.length === 2) {
            return `${nameParts[0]} ${nameParts[1].charAt(0)}.`; // First name + first letter of last name
        } else {
            // For 3+ names: first name + first letters of middle names + last name
            const firstName = nameParts[0];
            const lastName = nameParts[nameParts.length - 1];
            const middleInitials = nameParts
                .slice(1, -1)
                .map((name) => name.charAt(0) + ".")
                .join(" ");
            return `${firstName} ${middleInitials} ${lastName.charAt(0)}.`;
        }
    }

    // Normalize content to remove excessive whitespace and line breaks from HTML content
    function normalizeContent(htmlContent) {
        // Clean up excessive whitespace and line breaks from HTML content
        return (
            htmlContent
                // Remove empty paragraphs
                .replace(/<p>\s*<\/p>/g, "")
                // Replace multiple consecutive line breaks with single ones
                .replace(/(<br\s*\/?>|\n){3,}/g, "<br>")
                // Remove excessive whitespace between HTML elements
                .replace(/>\s+</g, "><")
                // Normalize whitespace within text content but preserve intentional breaks
                .replace(/\s{2,}/g, " ")
                // Remove leading/trailing whitespace from the entire content
                .trim()
        );
    }

    // Add a message to the chatbot UI
    function addMessage(content, role, timestamp = null, animationDelay = 0) {
        const chatMessages = document.getElementById("chat-messages");
        if (!chatMessages) return;

        // Create message container
        const messageDiv = document.createElement("div");
        messageDiv.className =
            "flex flex-col space-y-1 " +
            (role === "user" ? "items-end" : "items-start");

        // Get user name
        const fullUserName = window.chatbotUserName || "You";
        const userName =
            fullUserName === "You"
                ? "You"
                : `You (${shortenName(fullUserName)})`;

        // Get name tag
        const nameTag = role === "user" ? userName : "Arem AI";

        // Get name tag class
        const nameTagClass =
            role === "user"
                ? "font-semibold text-xs chatbot-user-name-tag"
                : "font-semibold text-xs chatbot-ai-name-tag";

        // Get message class
        const messageClass =
            role === "user"
                ? "fi-section bg-[#00AE9F] border-[#00AE9F] chatbot-user-message message-bubble user-message"
                : "fi-section bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 chatbot-assistant-message message-bubble";

        // Get content class
        const contentClass =
            role === "user"
                ? "text-sm whitespace-pre-wrap leading-relaxed chatbot-user-content"
                : "text-sm whitespace-pre-wrap leading-relaxed chatbot-assistant-content";

        // Get time class
        const timeClass =
            role === "user"
                ? "chatbot-user-timestamp"
                : "chatbot-assistant-timestamp";

        // Add message to the chatbot UI
        messageDiv.innerHTML =
            '<div class="' +
            nameTagClass +
            ' px-1">' +
            nameTag +
            "</div>" +
            '<div class="' +
            messageClass +
            ' rounded-xl px-4 py-3 shadow-sm border max-w-[80%]">' +
            '<div class="' +
            contentClass +
            '">' +
            normalizeContent(marked.parse(processTranslation(content))) +
            "</div>" +
            '<div class="' +
            timeClass +
            '">' +
            (timestamp ||
                new Date().toLocaleTimeString("en-US", {
                    hour: "numeric",
                    minute: "2-digit",
                    hour12: true,
                })) +
            "</div>" +
            "</div>";

        // Apply animation delay if specified
        if (animationDelay > 0) {
            const bubbleDiv = messageDiv.querySelector(".message-bubble");
            if (bubbleDiv) {
                bubbleDiv.style.animationDelay = `${animationDelay}ms`;
            }
        }

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Show loading indicator
    function showLoading() {
        const chatMessages = document.getElementById("chat-messages");
        if (!chatMessages) return;

        const loadingDiv = document.createElement("div");
        loadingDiv.id = "loading-message";
        loadingDiv.className = "flex flex-col space-y-1 items-start";
        loadingDiv.innerHTML =
            '<div class="text-gray-600 dark:text-gray-400 font-semibold text-sm px-1">Arem AI</div>' +
            '<div class="fi-section bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 shadow-sm max-w-[80%] message-bubble typing-indicator">' +
            '<div class="flex items-center space-x-3">' +
            '<div class="flex space-x-1">' +
            '<div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce"></div>' +
            '<div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>' +
            '<div class="w-2 h-2 bg-primary-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>' +
            "</div>" +
            '<span class="text-sm text-gray-600 dark:text-gray-300 font-medium">' +
            (window.chatbot?.thinking_message || "Arem is thinking...") +
            "</span>" +
            "</div>" +
            "</div>";
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Hide loading indicator
    function hideLoading() {
        const loadingMessage = document.getElementById("loading-message");
        if (loadingMessage) {
            loadingMessage.remove();
        }
    }

    // Send message to the chatbot
    async function sendMessage(event) {
        event.preventDefault();

        const input = document.getElementById("chat-input");
        if (!input) return;

        const message = input.value.trim();

        if (!message) return;

        // Add user message
        addMessage(message, "user");
        input.value = "";

        // Show loading and start API call after a brief delay to let user see their message
        setTimeout(async () => {
            showLoading();

            try {
                const csrfToken =
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content") ||
                    document.querySelector('input[name="_token"]')?.value;

                const response = await fetch("/chatbot/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        message: message,
                        conversation_id: conversationId,
                    }),
                });

                hideLoading();

                if (response.ok) {
                    const data = await response.json();

                    // Add AI response
                    addMessage(data.reply, "assistant", data.timestamp);

                    // Update conversation ID if provided
                    if (data.conversation_id) {
                        conversationId = data.conversation_id;
                        localStorage.setItem(
                            getUserConversationKey(),
                            conversationId
                        );
                    }
                } else {
                    addMessage(
                        window.chatbot?.error_message ||
                            "Sorry, I encountered an error. Please try again.",
                        "assistant"
                    );
                }
            } catch (error) {
                hideLoading();
                addMessage(
                    window.chatbot?.error_message ||
                        "Sorry, I encountered an error. Please try again.",
                    "assistant"
                );
                console.error("Chatbot error:", error);
            }
        }, 800); // Delay chatbot response to let user see their message
    }

    // Clear conversation
    async function clearConversation() {
        console.log("Clearing conversation...");

        // Show immediate feedback to user
        const chatMessages = document.getElementById("chat-messages");
        if (chatMessages) {
            chatMessages.innerHTML =
                '<div class="flex flex-col space-y-1 items-start">' +
                '<div class="text-gray-600 dark:text-gray-400 font-semibold text-sm px-1">Arem AI</div>' +
                '<div class="fi-section bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 shadow-sm max-w-[80%] message-bubble">' +
                '<p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">' +
                (window.chatbot?.clearing_message ||
                    "Clearing conversation...") +
                "</p>" +
                "</div>" +
                "</div>";
        }

        try {
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ||
                document.querySelector('input[name="_token"]')?.value;

            // Get a new conversation ID from the backend; pass current conversation to allow server-side cleanup
            const response = await fetch("/chatbot/clear", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({ conversation_id: conversationId }),
            });

            if (response.ok) {
                const data = await response.json();
                conversationId = data.conversation_id; // Set the new ID from the server
                console.log("New conversation ID:", conversationId);

                // Update localStorage with new conversation ID
                localStorage.setItem(getUserConversationKey(), conversationId);
            } else {
                console.error(
                    "Failed to clear conversation on the server:",
                    response.status,
                    response.statusText
                );
                // Still proceed with local clearing even if server fails
                conversationId = "conv_" + Date.now(); // Generate local fallback ID
                localStorage.setItem(getUserConversationKey(), conversationId);
            }
        } catch (error) {
            console.error("Error clearing conversation:", error);
            // Still proceed with local clearing even if server fails
            conversationId = "conv_" + Date.now(); // Generate local fallback ID
            localStorage.setItem(getUserConversationKey(), conversationId);
        }

        // Clear local conversation UI with a short delay for better UX
        setTimeout(() => {
            const chatMessages = document.getElementById("chat-messages");
            if (chatMessages) {
                chatMessages.innerHTML =
                    '<div class="flex flex-col space-y-1 items-start">' +
                    '<div class="text-gray-600 dark:text-gray-400 font-semibold text-sm px-1">Arem AI</div>' +
                    '<div class="fi-section bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 shadow-sm max-w-[80%] message-bubble">' +
                    '<p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">' +
                    processTranslation(
                        window.chatbot?.ready_message ||
                            "Ready for a fresh start! What would you like to know or work on?"
                    ) +
                    "</p>" +
                    "</div>" +
                    "</div>";

                // Add help information message
                setTimeout(() => {
                    addMessage(
                        window.chatbot?.help_message ||
                            "Use :help_command to call my available functions!",
                        "assistant"
                    );
                }, 1000); // Delay to show after initial message
            }
        }, 500); // 500ms delay

        // Keep chatbot open for new conversation
        localStorage.setItem(getUserChatStateKey(), "true");

        // Reset conversation state
        conversation = [];
        conversationLoaded = false;

        console.log(
            "Conversation cleared successfully. New ID:",
            conversationId
        );
    }

    // Emoji picker functionality
    function toggleEmojiPicker() {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const emojiPicker = document.getElementById("emoji-picker");
        const chatbotInterface = document.getElementById("chatbot-interface");
        const emojiButton = document.getElementById("emoji-button");

        if (
            !emojiPickerContainer ||
            !emojiPicker ||
            !chatbotInterface ||
            !emojiButton
        )
            return;

        if (emojiPickerContainer.classList.contains("hidden")) {
            // Position the emoji picker on the left side of the chatbox
            const chatRect = chatbotInterface.getBoundingClientRect();

            // Calculate position: left of chatbox with some spacing
            const leftPosition = chatRect.left - 420; // 400px width + 20px spacing

            // Calculate top position so that bottom of emoji picker aligns with bottom of chatbox
            // Subtract the emoji picker height from the chatbox bottom position
            const emojiPickerHeight = emojiPicker.offsetHeight || 400; // Get actual height or fallback
            const topPosition = chatRect.bottom - emojiPickerHeight;

            // Ensure it doesn't go off-screen on the left
            const finalLeftPosition = Math.max(20, leftPosition);

            // Ensure it doesn't go off-screen on the top
            const finalTopPosition = Math.max(20, topPosition);

            // Add a small offset to account for any margins/padding
            const adjustedTopPosition = finalTopPosition - 1;

            // Ensure the emoji picker doesn't go off the top of the screen
            const minTopPosition = 20;
            const finalAdjustedTopPosition = Math.max(
                minTopPosition,
                adjustedTopPosition
            );

            emojiPickerContainer.style.left = finalLeftPosition + "px";
            emojiPickerContainer.style.top = finalAdjustedTopPosition + "px";

            emojiPickerContainer.classList.remove("hidden");

            // Add animation
            emojiPickerContainer.style.opacity = "0";
            emojiPickerContainer.style.transform =
                "translateX(20px) scale(0.95)";
            requestAnimationFrame(() => {
                emojiPickerContainer.style.transition =
                    "opacity 0.2s ease, transform 0.2s ease";
                emojiPickerContainer.style.opacity = "1";
                emojiPickerContainer.style.transform = "translateX(0) scale(1)";
            });

            // Focus the emoji picker for better UX
            emojiPicker.focus();
        } else {
            emojiPickerContainer.style.transition =
                "opacity 0.2s ease, transform 0.2s ease";
            emojiPickerContainer.style.opacity = "0";
            emojiPickerContainer.style.transform =
                "translateX(20px) scale(0.95)";
            setTimeout(() => {
                emojiPickerContainer.classList.add("hidden");
            }, 200);
        }
    }

    // Initialize emoji picker when DOM is ready
    function initializeEmojiPicker() {
        const emojiPicker = document.getElementById("emoji-picker");
        if (!emojiPicker) {
            console.log("❌ Emoji picker element not found!");
            return;
        }

        console.log("✅ Emoji picker element found:", emojiPicker);
        console.log("🏷️ Tag name:", emojiPicker.tagName);
        console.log("📋 Classes:", emojiPicker.className);
        console.log("🆔 ID:", emojiPicker.id);

        // Configure emoji picker
        emojiPicker.addEventListener("emoji-click", (event) => {
            const emoji = event.detail.unicode;
            insertEmoji(emoji);
            toggleEmojiPicker();
        });

        // Set custom styling
        emojiPicker.style.setProperty(
            "--background",
            "var(--tw-bg-opacity, 1)"
        );
        emojiPicker.style.setProperty("--border-color", "rgb(229 231 235)");
        emojiPicker.style.setProperty("--category-emoji-size", "1.5rem");
        emojiPicker.style.setProperty("--emoji-size", "1.5rem");
        emojiPicker.style.setProperty("--num-columns", "8");
        emojiPicker.style.setProperty("--border-radius", "0.5rem");

        // Dark mode support
        if (document.documentElement.classList.contains("dark")) {
            emojiPicker.style.setProperty("--background", "rgb(31 41 55)");
            emojiPicker.style.setProperty("--border-color", "rgb(75 85 99)");
            emojiPicker.style.setProperty("--color", "rgb(255 255 255)");
        }

        // Apply padding to favorites section after emoji picker is loaded
        setTimeout(() => {
            console.log("🚀 Emoji picker loaded, starting investigation...");
            console.log("📦 Emoji picker element:", emojiPicker);
            console.log("🔍 Emoji picker HTML:", emojiPicker.outerHTML);

            // Check if it's a custom element
            if (emojiPicker.tagName === "EMOJI-PICKER") {
                console.log("✅ Confirmed: emoji-picker is a custom element");
            }

            // Check for Shadow DOM
            if (emojiPicker.shadowRoot) {
                console.log("🌑 Shadow DOM found:", emojiPicker.shadowRoot);
                console.log(
                    "🌑 Shadow DOM HTML:",
                    emojiPicker.shadowRoot.innerHTML
                );
            } else {
                console.log("❌ No Shadow DOM detected");
            }
            const favoritesElements = emojiPicker.querySelectorAll(
                '[class*="favorites"]'
            );
            favoritesElements.forEach((element) => {
                element.style.paddingTop = "12px";
                element.style.paddingBottom = "12px";
            });

            // Also target the specific element from developer console
            const specificFavorites = emojiPicker.querySelector(
                '[role="menu"][data-on-click="onEmojiClick"][class*="favorites"]'
            );
            if (specificFavorites) {
                specificFavorites.style.paddingTop = "12px";
                specificFavorites.style.paddingBottom = "12px";
            }

            // Inject CSS directly into the document head for maximum specificity
            const style = document.createElement("style");
            style.textContent = `
                emoji-picker .favorites,
                emoji-picker [class*="favorites"],
                emoji-picker [role="menu"][data-on-click="onEmojiClick"][class*="favorites"],
                emoji-picker div[role="menu"][data-on-click="onEmojiClick"].favorites.onscreen.emoji-menu {
                    padding-top: 12px !important;
                    padding-bottom: 12px !important;
                }
                
                /* Target all elements with favorites in their class name */
                emoji-picker *[class*="favorites"] {
                    padding-top: 12px !important;
                    padding-bottom: 12px !important;
                }
            `;
            document.head.appendChild(style);

            // Use MutationObserver to watch for dynamically added elements
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            // Check if the added node has favorites class
                            if (
                                node.classList &&
                                node.classList.contains("favorites")
                            ) {
                                node.style.paddingTop = "12px";
                                node.style.paddingBottom = "12px";
                            }

                            // Also check child elements
                            const favoritesInNode = node.querySelectorAll(
                                '[class*="favorites"]'
                            );
                            favoritesInNode.forEach((element) => {
                                element.style.paddingTop = "12px";
                                element.style.paddingBottom = "12px";
                            });
                        }
                    });
                });
            });

            // Start observing the emoji picker for changes
            observer.observe(emojiPicker, {
                childList: true,
                subtree: true,
            });

            // Apply padding to favorites section and background color to all elements in Shadow DOM
            const applyFavoritesPadding = () => {
                // Set CSS custom properties on the emoji-picker element itself
                if (document.documentElement.classList.contains("dark")) {
                    emojiPicker.style.setProperty(
                        "--background",
                        "rgb(39 39 42)",
                        "important"
                    );
                    emojiPicker.style.setProperty(
                        "--border-color",
                        "rgb(75 85 99)",
                        "important"
                    );
                    emojiPicker.style.setProperty(
                        "--color",
                        "rgb(255 255 255)",
                        "important"
                    );
                    emojiPicker.style.setProperty(
                        "background-color",
                        "rgb(39 39 42)",
                        "important"
                    );
                    emojiPicker.style.setProperty(
                        "background",
                        "rgb(39 39 42)",
                        "important"
                    );
                }

                // Check if Shadow DOM exists and apply padding to favorites
                if (emojiPicker.shadowRoot) {
                    const shadowElements =
                        emojiPicker.shadowRoot.querySelectorAll("*");

                    shadowElements.forEach((element) => {
                        // Apply background color to container elements only, not interactive elements
                        if (
                            document.documentElement.classList.contains("dark")
                        ) {
                            // Skip interactive elements to preserve hover effects
                            const isInteractive =
                                element.tagName === "BUTTON" ||
                                element.getAttribute("role") === "button" ||
                                element.getAttribute("role") === "menuitem" ||
                                element.onclick !== null ||
                                element.classList.contains("emoji") ||
                                element.classList.contains("category-emoji");

                            if (!isInteractive) {
                                element.style.setProperty(
                                    "background-color",
                                    "rgb(39 39 42)",
                                    "important"
                                );
                                element.style.setProperty(
                                    "background",
                                    "rgb(39 39 42)",
                                    "important"
                                );
                            }
                        }

                        // Apply padding to favorites section
                        if (
                            element.className &&
                            element.className.includes("favorites")
                        ) {
                            element.style.setProperty(
                                "padding-top",
                                "12px",
                                "important"
                            );
                            element.style.setProperty(
                                "padding-bottom",
                                "12px",
                                "important"
                            );
                        }
                    });

                    // Inject CSS into Shadow DOM to override CSS custom properties
                    if (document.documentElement.classList.contains("dark")) {
                        const styleElement = document.createElement("style");
                        styleElement.textContent = `
                            :host {
                                --background: rgb(39 39 42) !important;
                                --border-color: rgb(75 85 99) !important;
                                --color: rgb(255 255 255) !important;
                                background-color: rgb(39 39 42) !important;
                                background: rgb(39 39 42) !important;
                            }
                            .picker {
                                background: rgb(39 39 42) !important;
                                background-color: rgb(39 39 42) !important;
                            }
                            /* Target specific background elements without affecting interactive elements */
                            .picker > div,
                            .picker > section,
                            .picker > div > div,
                            .picker > section > div {
                                background-color: rgb(39 39 42) !important;
                                background: rgb(39 39 42) !important;
                            }
                            /* Preserve hover effects on interactive elements */
                            .picker button:hover,
                            .picker [role="button"]:hover,
                            .picker [role="menuitem"]:hover,
                            .picker .emoji:hover,
                            .picker [data-emoji]:hover,
                            .picker .category-emoji:hover {
                                background-color: rgb(55 65 81) !important;
                                background: rgb(55 65 81) !important;
                                transform: scale(1.05) !important;
                                transition: all 0.2s ease !important;
                                border-radius: 8px !important;
                                cursor: pointer !important;
                            }
                            /* Preserve focus states */
                            .picker button:focus,
                            .picker [role="button"]:focus,
                            .picker [role="menuitem"]:focus {
                                background-color: rgb(55 65 81) !important;
                                background: rgb(55 65 81) !important;
                                outline: 2px solid rgb(59 130 246) !important;
                                outline-offset: 2px !important;
                            }
                            /* Preserve active states */
                            .picker button:active,
                            .picker [role="button"]:active,
                            .picker [role="menuitem"]:active {
                                background-color: rgb(75 85 99) !important;
                                background: rgb(75 85 99) !important;
                                transform: scale(0.95) !important;
                            }
                        `;
                        emojiPicker.shadowRoot.appendChild(styleElement);
                    }
                }

                // Also apply background to the container
                const emojiPickerContainer = document.getElementById(
                    "emoji-picker-container"
                );
                if (
                    emojiPickerContainer &&
                    document.documentElement.classList.contains("dark")
                ) {
                    emojiPickerContainer.style.setProperty(
                        "background-color",
                        "rgb(39 39 42)",
                        "important"
                    );
                    emojiPickerContainer.style.setProperty(
                        "background",
                        "rgb(39 39 42)",
                        "important"
                    );
                }
            };

            // Apply immediately
            applyFavoritesPadding();

            // Apply every 500ms for the first 5 seconds
            let attempts = 0;
            const interval = setInterval(() => {
                applyFavoritesPadding();
                attempts++;
                if (attempts >= 10) {
                    clearInterval(interval);
                }
            }, 500);

            // Also apply when the emoji picker becomes visible
            const applyWhenVisible = () => {
                const emojiPickerContainer = document.getElementById(
                    "emoji-picker-container"
                );
                if (
                    emojiPickerContainer &&
                    !emojiPickerContainer.classList.contains("hidden")
                ) {
                    applyFavoritesPadding();
                }
            };

            // Check every 100ms when emoji picker is visible
            setInterval(applyWhenVisible, 100);
        }, 100);
    }

    // Insert emoji into the input field
    function insertEmoji(emoji) {
        const input = document.getElementById("chat-input");
        if (!input) return;

        const cursorPos = input.selectionStart;
        const textBefore = input.value.substring(0, cursorPos);
        const textAfter = input.value.substring(cursorPos);

        input.value = textBefore + emoji + textAfter;
        input.setSelectionRange(
            cursorPos + emoji.length,
            cursorPos + emoji.length
        );
        input.focus();
    }

    // Close emoji picker when clicking outside or inside chatbox
    document.addEventListener("click", function (event) {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const emojiButton = document.getElementById("emoji-button");
        const chatbotInterface = document.getElementById("chatbot-interface");

        if (
            emojiPickerContainer &&
            !emojiPickerContainer.classList.contains("hidden")
        ) {
            // Close if clicking outside the emoji picker and emoji button
            if (
                !emojiPickerContainer.contains(event.target) &&
                !emojiButton.contains(event.target)
            ) {
                // Also close if clicking anywhere inside the chatbot interface
                if (
                    chatbotInterface &&
                    chatbotInterface.contains(event.target)
                ) {
                    toggleEmojiPicker();
                } else if (!chatbotInterface.contains(event.target)) {
                    // Close if clicking outside the chatbot interface as well
                    toggleEmojiPicker();
                }
            }
        }
    });

    // Close emoji picker on Escape key
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            const emojiPickerContainer = document.getElementById(
                "emoji-picker-container"
            );
            if (
                emojiPickerContainer &&
                !emojiPickerContainer.classList.contains("hidden")
            ) {
                toggleEmojiPicker();
            }
        }
    });

    // Reposition emoji picker on window resize
    window.addEventListener("resize", function () {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        if (
            emojiPickerContainer &&
            !emojiPickerContainer.classList.contains("hidden")
        ) {
            // Close the picker on resize to avoid positioning issues
            toggleEmojiPicker();
        }
    });

    // Initialize emoji picker when DOM is ready
    onDocumentReady(() => {
        initializeSession(); // Fetch session info on document ready

        // Use a small delay to ensure DOM elements are ready
        setTimeout(() => {
            initializeChatbotState(); // Initialize state first
            applyChatbotStateIfElementsPresent(); // Apply saved state from localStorage as fallback

            // Initialize emoji picker after a short delay to ensure the element is loaded
            setTimeout(() => {
                initializeEmojiPicker();
            }, 500);
        }, 100);
    });

    // Export functions to the window object
    window.toggleChatbot = toggleChatbot;
    window.sendMessage = sendMessage;
    window.clearConversation = clearConversation;
    window.toggleEmojiPicker = toggleEmojiPicker;
    window.insertEmoji = insertEmoji;

    // Persist open state on page unload to help with navigation
    window.addEventListener("beforeunload", function () {
        const interfaceEl = document.getElementById("chatbot-interface");
        const isOpenVisible =
            interfaceEl &&
            interfaceEl.style.display !== "none" &&
            !interfaceEl.classList.contains("hidden");
        localStorage.setItem(
            getUserChatStateKey(),
            isOpenVisible ? "true" : "false"
        );
    });

    // BFCache resume: restore saved state on resume
    window.addEventListener("pageshow", function (event) {
        if (event.persisted) {
            // On BFCache resume, restore the saved state from localStorage
            const shouldBeOpen =
                localStorage.getItem(getUserChatStateKey()) === "true";
            setChatVisibility(shouldBeOpen);
        }
    });

    // --- Event Listeners ---
    // Use event delegation on the body.
    document.body.addEventListener("click", function (event) {
        // Toggle chatbot visibility
        if (event.target.closest("#chatbot-toggler")) {
            toggleChatbot();
        }
        // Clear conversation
        if (event.target.closest("#clear-chat")) {
            clearConversation();
        }

        // Close chatbot when clicking outside
        const chatbotInterface = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");

        // Only close if chatbot is currently open and click is outside the chatbot elements
        if (
            chatbotInterface &&
            chatbotInterface.style.display !== "none" &&
            !chatbotInterface.classList.contains("hidden") &&
            !event.target.closest("#chatbot-interface") &&
            !event.target.closest("#chat-icon") &&
            !event.target.closest("#close-icon")
        ) {
            toggleChatbot();
        }
    });

    // Send message on submit
    document.body.addEventListener("submit", function (event) {
        // Send message
        if (event.target.id === "chat-form") {
            sendMessage(event);
        }
    });

    // Send message on Enter key press in the input field
    document.body.addEventListener("keydown", function (event) {
        // Send message on Enter key press in the input field
        if (
            event.target.id === "chat-input" &&
            event.key === "Enter" &&
            !event.shiftKey
        ) {
            event.preventDefault();
            sendMessage(new Event("submit", { cancelable: true }));
        }
    });
})();
