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
            const transitionMs = 260;
            setTimeout(() => {
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
                        "Hello! I'm Arem AI. The most genius AI assistant in the world. How can I assist you today?",
                        "assistant",
                        welcomeTs
                    );
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
            normalizeContent(marked.parse(content)) +
            "</div>" +
            '<div class="' +
            timeClass +
            '">' +
            (timestamp || new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })) +
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
            '<span class="text-sm text-gray-600 dark:text-gray-300 font-medium">Arem is thinking...</span>' +
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
                        "Sorry, I encountered an error. Please try again.",
                        "assistant"
                    );
                }
            } catch (error) {
                hideLoading();
                addMessage(
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
                '<p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">Clearing conversation...</p>' +
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
                    '<p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">Ready for a fresh start! What would you like to know or work on?</p>' +
                    "</div>" +
                    "</div>";
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

    // Export functions to the window object
    window.toggleChatbot = toggleChatbot;
    window.sendMessage = sendMessage;
    window.clearConversation = clearConversation;

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
