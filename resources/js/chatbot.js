// -----------------------------
// Chatbot functionality
// -----------------------------
(function () {
    let conversationId = null; // Start with null ID
    let conversation = [];
    let isLoadingConversation = false;
    let conversationLoaded = false;
    let emojiPickerInitialized = false; // Flag to prevent multiple initializations
    let addingInitialMessages = false; // Flag to prevent duplicate initial messages

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

        // console.log(
        //     `handleUserChange: currentUserId=${currentUserId}, lastUserId=${lastUserId}`
        // );

        if (lastUserId && lastUserId !== currentUserId) {
            // User has changed (logout/login), reset conversation loading state
            // But DON'T clear the conversation IDs - they should persist in localStorage
            // and be matched with database records by user_id
            conversationLoaded = false;
            conversation = [];
            addingInitialMessages = false;

            // console.log(`User changed from ${lastUserId} to ${currentUserId}`);
        } else {
            // console.log(`Same user or first load: ${currentUserId}`);
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

                // console.log(
                //     "Chatbot session initialized with conversation ID:",
                //     conversationId,
                //     "for user:",
                //     window.chatbotUserId
                // );

                // DON'T load conversation history automatically - only load when user opens chatbot
                // This reduces initial page load by avoiding unnecessary network requests
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

        // console.log(
        //     `Initializing chatbot state - chatState: ${chatState}, shouldBeOpen: ${shouldBeOpen}`
        // );

        // Use the centralized visibility setter for consistency
        setChatVisibility(shouldBeOpen);

        // Only load conversation history if chatbot should be open on page load
        // DON'T load automatically - only when user actually opens chatbot interface
        // This reduces initial page load by avoiding unnecessary network requests
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

            // Scroll to bottom after opening
            setTimeout(() => {
                scrollToBottom();
            }, 200);
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

        // console.log(
        //     `Applying chatbot state - chatState: ${chatState}, shouldBeOpen: ${shouldBeOpen}`
        // );

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
        // Delay chatbot initialization until AFTER window.load event completes
        // This ensures the page loads faster by prioritizing critical content
        const initializeChatbot = () => {
            initializeSession(); // Fetch session info

            // Use a small delay to ensure DOM elements are ready
            setTimeout(() => {
                initializeChatbotState(); // Initialize state first
                applyChatbotStateIfElementsPresent(); // Apply saved state from localStorage as fallback

                // Initialize emoji picker after a short delay to ensure the element is loaded
                setTimeout(() => {
                    initializeEmojiPicker();
                }, 500);
            }, 100);
        };

        // Wait for window load event to ensure all critical resources are loaded first
        // Use requestIdleCallback with a significant delay for optimal performance
        const initAfterLoad = () => {
            if ("requestIdleCallback" in window) {
                // Wait for browser idle time with a minimum delay of 2 seconds
                requestIdleCallback(
                    initializeChatbot,
                    { timeout: 5000 } // Wait up to 5 seconds for idle time
                );
            } else {
                // Fallback: use setTimeout with a minimum delay
                setTimeout(initializeChatbot, 2000);
            }
        };

        // Always wait for window load event before initializing
        if (document.readyState === "complete") {
            // Page already loaded, initialize with delay
            initAfterLoad();
        } else {
            // Wait for load event
            window.addEventListener("load", initAfterLoad);
        }
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
                // Scroll to bottom after opening
                setTimeout(() => {
                    scrollToBottom();
                }, 100);
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
        // console.log("Loading conversation history:", {
        //     conversationId,
        //     conversationLength: conversation.length,
        //     conversationLoaded,
        //     isLoadingConversation,
        //     userId: window.chatbotUserId,
        // });

        if (!conversationId || isLoadingConversation) {
            // console.log(
            //     "Skipping load - no conversation ID or loading in progress"
            // );
            return;
        }

        // Check if conversation is already loaded or initial messages are being added
        if (conversationLoaded || addingInitialMessages) {
            // console.log(
            //     "Conversation already loaded or initial messages being added"
            // );
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

            // console.log("Conversation history response:", response.status);

            if (response.ok) {
                const data = await response.json();
                // console.log("Conversation data:", data);

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
                    // console.log(
                    //     "Loaded",
                    //     data.conversation.length,
                    //     "messages from conversation"
                    // );

                    // Scroll to bottom after loading conversation history
                    setTimeout(() => {
                        scrollToBottom();
                    }, 100);
                } else {
                    // No conversation history found - this is normal for new conversations
                    addingInitialMessages = true; // Prevent duplicate calls
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
                        addingInitialMessages = false; // Reset flag after messages are added

                        // Ensure scroll to bottom after initial messages
                        setTimeout(() => {
                            scrollToBottom();
                        }, 100);
                    }, 1000); // Delay to show after welcome message
                    // console.log(
                    //     "No conversation messages found in database - empty conversation; greeting posted"
                    // );
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

    // Check if content is a single emoji
    function isSingleEmoji(content) {
        // Remove HTML tags and normalize whitespace
        const cleanContent = content.replace(/<[^>]*>/g, "").trim();

        // More comprehensive emoji regex pattern
        const emojiRegex =
            /^[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|[\u{1F900}-\u{1F9FF}]|[\u{1F018}-\u{1F270}]|[\u{238C}-\u{2454}]|[\u{20D0}-\u{20FF}]|[\u{FE00}-\u{FE0F}]|[\u{1F000}-\u{1F02F}]|[\u{1F0A0}-\u{1F0FF}]|[\u{1F100}-\u{1F64F}]|[\u{1F910}-\u{1F96B}]|[\u{1F980}-\u{1F9E0}]|[\u{1F9E0}-\u{1F9FF}]|[\u{1F004}]|[\u{1F0CF}]|[\u{1F170}-\u{1F251}]|[\u{1F300}-\u{1F321}]|[\u{1F324}-\u{1F393}]|[\u{1F396}-\u{1F397}]|[\u{1F399}-\u{1F39B}]|[\u{1F39E}-\u{1F3F0}]|[\u{1F3F3}-\u{1F3F5}]|[\u{1F3F7}-\u{1F3FA}]|[\u{1F400}-\u{1F4FD}]|[\u{1F4FF}-\u{1F53D}]|[\u{1F549}-\u{1F54E}]|[\u{1F550}-\u{1F567}]|[\u{1F56F}-\u{1F570}]|[\u{1F573}-\u{1F57A}]|[\u{1F587}]|[\u{1F58A}-\u{1F58D}]|[\u{1F590}]|[\u{1F595}-\u{1F596}]|[\u{1F5A4}-\u{1F5A5}]|[\u{1F5A8}]|[\u{1F5B1}-\u{1F5B2}]|[\u{1F5BC}]|[\u{1F5C2}-\u{1F5C4}]|[\u{1F5D1}-\u{1F5D3}]|[\u{1F5DC}-\u{1F5DE}]|[\u{1F5E1}]|[\u{1F5E3}]|[\u{1F5E8}]|[\u{1F5EF}]|[\u{1F5F3}]|[\u{1F5FA}-\u{1F64F}]|[\u{1F680}-\u{1F6C5}]|[\u{1F6CB}-\u{1F6D2}]|[\u{1F6E0}-\u{1F6E5}]|[\u{1F6E9}]|[\u{1F6EB}-\u{1F6EC}]|[\u{1F6F0}]|[\u{1F6F3}-\u{1F6F9}]|[\u{1F6FB}-\u{1F6FF}]|[\u{1F774}-\u{1F775}]|[\u{1F7D5}-\u{1F7D9}]|[\u{1F7E0}-\u{1F7EB}]|[\u{1F7F0}]|[\u{1F90C}-\u{1F93A}]|[\u{1F93C}-\u{1F945}]|[\u{1F947}-\u{1F978}]|[\u{1F97A}-\u{1F9CB}]|[\u{1F9CD}-\u{1F9FF}]|[\u{1FA70}-\u{1FA74}]|[\u{1FA78}-\u{1FA7A}]|[\u{1FA7B}-\u{1FA7C}]|[\u{1FA80}-\u{1FA86}]|[\u{1FA90}-\u{1FAAC}]|[\u{1FAB0}-\u{1FABA}]|[\u{1FAC0}-\u{1FAC2}]|[\u{1FAD0}-\u{1FAD6}]|[\u{1FAE0}-\u{1FAE7}]|[\u{1FAF0}-\u{1FAF6}]$/u;

        // Check if content is exactly one emoji character (1-2 characters for most emojis)
        return (
            emojiRegex.test(cleanContent) &&
            (cleanContent.length === 1 || cleanContent.length === 2)
        );
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

    // Scroll chat messages to bottom
    function scrollToBottom() {
        const chatMessages = document.getElementById("chat-messages");
        if (chatMessages) {
            // Use requestAnimationFrame to ensure DOM updates are complete
            requestAnimationFrame(() => {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            });
        }
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

        // Check if content is a single emoji
        const isEmoji = isSingleEmoji(content);

        // Get message class - remove bubble styling for single emojis
        const messageClass = isEmoji
            ? "chatbot-emoji-message"
            : role === "user"
            ? "fi-section bg-[#00AE9F] border-[#00AE9F] chatbot-user-message message-bubble user-message"
            : "fi-section bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 chatbot-assistant-message message-bubble";

        // Get content class - make emoji bigger
        const contentClass = isEmoji
            ? "text-7xl leading-none chatbot-emoji-content"
            : role === "user"
            ? "text-sm whitespace-pre-wrap leading-relaxed chatbot-user-content"
            : "text-sm whitespace-pre-wrap leading-relaxed chatbot-assistant-content";

        // Get time class
        const timeClass =
            role === "user"
                ? "chatbot-user-timestamp"
                : "chatbot-assistant-timestamp";

        // Add message to the chatbot UI
        if (isEmoji) {
            // For single emojis, render without bubble background
            messageDiv.innerHTML =
                '<div class="' +
                nameTagClass +
                ' px-1">' +
                nameTag +
                "</div>" +
                '<div class="' +
                messageClass +
                ' px-4 py-2">' +
                '<div class="' +
                contentClass +
                '">' +
                "</div>" +
                '<div class="' +
                timeClass +
                ' text-xs text-gray-500 dark:text-gray-400 mt-1">' +
                (timestamp ||
                    new Date().toLocaleTimeString("en-US", {
                        hour: "numeric",
                        minute: "2-digit",
                        hour12: true,
                    })) +
                "</div>" +
                "</div>";

            // Get the content div and add animated emoji
            const contentDiv = messageDiv.querySelector(
                "." + contentClass.split(" ")[0]
            );
            if (contentDiv) {
                if (
                    window.NotoEmojiAnimation &&
                    window.NotoEmojiAnimation.createAnimatedEmoji
                ) {
                    // Extract emoji from content (remove any HTML tags)
                    const cleanContent = content.replace(/<[^>]*>/g, "").trim();
                    const animatedEmoji =
                        window.NotoEmojiAnimation.createAnimatedEmoji(
                            cleanContent,
                            "4.5rem"
                        );
                    contentDiv.appendChild(animatedEmoji);
                } else {
                    // Fallback to static emoji
                    contentDiv.innerHTML = normalizeContent(
                        marked.parse(processTranslation(content))
                    );
                }
            }
        } else {
            // For regular messages, use bubble styling
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

            // Replace emojis in regular messages with animated versions
            if (
                window.NotoEmojiAnimation &&
                window.NotoEmojiAnimation.replaceEmojisInElement
            ) {
                const contentDiv = messageDiv.querySelector(
                    "." + contentClass.split(" ")[0]
                );
                if (contentDiv) {
                    // Process the content to replace emojis with animations
                    window.NotoEmojiAnimation.replaceEmojisInElement(
                        contentDiv
                    );
                }
            }
        }

        // Apply animation delay if specified
        if (animationDelay > 0) {
            const bubbleDiv = messageDiv.querySelector(".message-bubble");
            if (bubbleDiv) {
                bubbleDiv.style.animationDelay = `${animationDelay}ms`;
            }
        }

        chatMessages.appendChild(messageDiv);
        scrollToBottom();
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
        scrollToBottom();
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
        // Show global confirmation modal
        if (typeof window.showGlobalModal === "function") {
            window.showGlobalModal("clearConversation");
        } else {
            // Fallback to browser confirm if global modal system is not available
            const confirmed = confirm(
                window.chatbot?.clear_confirmation_message ||
                    "Are you sure you want to clear the conversation? This action cannot be undone."
            );

            if (confirmed) {
                executeClearConversation();
            }
        }
    }

    // Execute actual clear conversation
    async function executeClearConversation() {
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
                // console.log("New conversation ID:", conversationId);

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

        // Clear local conversation UI and add initial messages
        setTimeout(() => {
            const chatMessages = document.getElementById("chat-messages");
            if (chatMessages) {
                // Clear the chat messages
                chatMessages.innerHTML = "";

                // Set flags to prevent loadConversationHistory from adding duplicate messages
                addingInitialMessages = true;
                conversationLoaded = true;

                // Add initial messages using the proper addMessage function
                const readyTs = new Date().toLocaleTimeString("en-US", {
                    hour: "2-digit",
                    minute: "2-digit",
                });

                addMessage(
                    processTranslation(
                        window.chatbot?.ready_message ||
                            "Ready for a fresh start! What would you like to know or work on?"
                    ),
                    "assistant",
                    readyTs
                );

                // Add help information message
                setTimeout(() => {
                    addMessage(
                        window.chatbot?.help_message ||
                            "Use :help_command to call my available functions!",
                        "assistant",
                        readyTs
                    );
                    addingInitialMessages = false; // Reset flag after messages are added

                    // Ensure scroll to bottom after clearing and adding new messages
                    setTimeout(() => {
                        scrollToBottom();
                    }, 100);
                }, 1000); // Delay to show after initial message
            }
        }, 500); // 500ms delay

        // Keep chatbot open for new conversation
        localStorage.setItem(getUserChatStateKey(), "true");

        // Reset conversation state (but keep loaded flags as they're set above)
        conversation = [];

        // Show success notification using the custom notification system
        if (typeof window.showSuccessNotification === "function") {
            window.showSuccessNotification(
                window.chatbot?.clear_success_message ||
                    "Conversation cleared successfully!"
            );
        } else {
            // Fallback if notification system is not loaded
            console.log("Notification system not available");
        }

        // console.log(
        //     "Conversation cleared successfully. New ID:",
        //     conversationId
        // );
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

            // Ensure emoji picker follows current theme when opened
            if (emojiPicker.shadowRoot) {
                updateEmojiPickerTheme();
            }

            // Focus the emoji picker for better UX
            emojiPicker.focus();

            // Add a small visual indicator that multiple emojis can be selected
            // Handled by the emoji picker's built-in UI
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
        // Prevent multiple initializations
        if (emojiPickerInitialized) {
            return;
        }

        const emojiPicker = document.getElementById("emoji-picker");
        if (!emojiPicker) {
            return;
        }

        // Mark as initialized
        emojiPickerInitialized = true;

        // Configure emoji picker
        emojiPicker.addEventListener("emoji-click", (event) => {
            const emoji = event.detail.unicode;
            insertEmoji(emoji);
        });

        // Set basic emoji picker properties
        emojiPicker.style.setProperty("--category-emoji-size", "1.5rem");
        emojiPicker.style.setProperty("--emoji-size", "1.5rem");
        emojiPicker.style.setProperty("--num-columns", "8");
        emojiPicker.style.setProperty("--border-radius", "0.5rem");

        // Apply padding to favorites section after emoji picker is loaded
        setTimeout(() => {
            // Function to apply padding to favorites elements
            const applyFavoritesPadding = (root) => {
                // Try multiple selectors to find favorites elements
                const selectors = [
                    '[class*="favorites"]',
                    ".favorites",
                    '[role="menu"][data-on-click="onEmojiClick"][class*="favorites"]',
                    'div[role="menu"][data-on-click="onEmojiClick"].favorites.onscreen.emoji-menu',
                ];

                let foundElements = false;
                selectors.forEach((selector) => {
                    const elements = root.querySelectorAll(selector);
                    if (elements.length > 0) {
                        foundElements = true;
                        elements.forEach((element) => {
                            element.style.paddingTop = "12px";
                            element.style.paddingBottom = "12px";
                        });
                    }
                });
                return foundElements;
            };

            // Function to inject theme styles into Shadow DOM based on current theme
            const injectThemeStyles = (shadowRoot) => {
                const isDarkMode =
                    document.documentElement.classList.contains("dark");

                // Create a style element for the current theme
                const styleElement = document.createElement("style");
                styleElement.setAttribute(
                    "data-theme",
                    isDarkMode ? "dark" : "light"
                );

                if (isDarkMode) {
                    styleElement.textContent = `
                        /* Dark theme styles for emoji picker */
                        :host {
                            --background: rgb(39 39 42) !important;
                            --border-color: rgb(63 63 70) !important;
                            --color: rgb(255 255 255) !important;
                            background-color: rgb(39 39 42) !important;
                            border-color: rgb(63 63 70) !important;
                        }
                        
                        /* Dark theme hover effects */
                        button:hover,
                        [role="button"]:hover,
                        [role="menuitem"]:hover,
                        .emoji:hover,
                        [data-emoji]:hover,
                        .category-emoji:hover {
                            background-color: rgb(55 65 81) !important;
                            transform: scale(1.02) !important;
                            transition: all 0.2s ease !important;
                            border-radius: 4px !important;
                            cursor: pointer !important;
                            padding: 0px !important;
                        }
                        
                        /* Dark theme focus states */
                        button:focus,
                        [role="button"]:focus,
                        [role="menuitem"]:focus {
                            background-color: rgb(55 65 81) !important;
                            outline: 2px solid rgb(59 130 246) !important;
                            outline-offset: 2px !important;
                        }
                        
                        /* Dark theme active states */
                        button:active,
                        [role="button"]:active,
                        [role="menuitem"]:active {
                            background-color: rgb(63 63 70) !important;
                            transform: scale(0.98) !important;
                            padding: 0px !important;
                        }
                        
                        /* Dark theme scrollbar */
                        ::-webkit-scrollbar {
                            width: 8px !important;
                        }
                        
                        ::-webkit-scrollbar-track {
                            background: rgb(55 65 81) !important;
                        }
                        
                        ::-webkit-scrollbar-thumb {
                            background: rgb(107 114 128) !important;
                            border-radius: 4px !important;
                        }
                        
                        ::-webkit-scrollbar-thumb:hover {
                            background: rgb(156 163 175) !important;
                        }
                        
                        /* Ensure all interactive elements have proper dark theme styling */
                        .picker,
                        .picker > div,
                        .picker > section {
                            background-color: rgb(39 39 42) !important;
                            color: rgb(255 255 255) !important;
                        }
                        
                        /* Category buttons and emoji buttons */
                        .category-emoji,
                        .emoji,
                        [data-emoji] {
                            background-color: transparent !important;
                            transition: all 0.2s ease !important;
                        }
                        
                        /* Search input styling */
                        input[type="search"],
                        input[type="text"] {
                            background-color: rgb(39 39 42) !important;
                            border-color: rgb(63 63 70) !important;
                            color: rgb(255 255 255) !important;
                            font-size: 0.875rem !important;
                        }
                    `;
                } else {
                    styleElement.textContent = `
                        /* Light theme styles for emoji picker */
                        :host {
                            --background: rgb(255 255 255) !important;
                            --border-color: rgb(229 231 235) !important;
                            --color: rgb(0 0 0) !important;
                            background-color: rgb(255 255 255) !important;
                            border-color: rgb(229 231 235) !important;
                        }
                        
                        /* Light theme hover effects */
                        button:hover,
                        [role="button"]:hover,
                        [role="menuitem"]:hover,
                        .emoji:hover,
                        [data-emoji]:hover,
                        .category-emoji:hover {
                            background-color: rgb(243 244 246) !important;
                            transform: scale(1.02) !important;
                            transition: all 0.2s ease !important;
                            border-radius: 4px !important;
                            cursor: pointer !important;
                            padding: 0px !important;
                        }
                        
                        /* Light theme focus states */
                        button:focus,
                        [role="button"]:focus,
                        [role="menuitem"]:focus {
                            background-color: rgb(243 244 246) !important;
                            outline: 2px solid rgb(59 130 246) !important;
                            outline-offset: 2px !important;
                        }
                        
                        /* Light theme active states */
                        button:active,
                        [role="button"]:active,
                        [role="menuitem"]:active {
                            background-color: rgb(229 231 235) !important;
                            transform: scale(0.98) !important;
                            padding: 0px !important;
                        }
                        
                        /* Light theme scrollbar */
                        ::-webkit-scrollbar {
                            width: 8px !important;
                        }
                        
                        ::-webkit-scrollbar-track {
                            background: rgb(255 255 255) !important;
                        }
                        
                        ::-webkit-scrollbar-thumb {
                            background: rgb(229 231 235) !important;
                            border-radius: 4px !important;
                        }
                        
                        ::-webkit-scrollbar-thumb:hover {
                            background: rgb(209 213 219) !important;
                        }
                        
                        /* Ensure all interactive elements have proper light theme styling */
                        .picker,
                        .picker > div,
                        .picker > section {
                            background-color: rgb(255 255 255) !important;
                            color: rgb(0 0 0) !important;
                        }
                        
                        /* Category buttons and emoji buttons */
                        .category-emoji,
                        .emoji,
                        [data-emoji] {
                            background-color: transparent !important;
                            transition: all 0.2s ease !important;
                        }
                        
                        /* Search input styling */
                        input[type="search"],
                        input[type="text"] {
                            background-color: rgb(255 255 255) !important;
                            border-color: rgb(229 231 235) !important;
                            color: rgb(0 0 0) !important;
                            font-size: 0.875rem !important;
                        }
                    `;
                }

                // Inject the style into Shadow DOM
                shadowRoot.appendChild(styleElement);
            };

            // Access Shadow DOM root
            const shadowRoot = emojiPicker.shadowRoot;
            if (!shadowRoot) {
                // console.log("Shadow DOM not accessible, trying direct access");
                // Fallback: try direct access
                applyFavoritesPadding(emojiPicker);
                return;
            }

            // Inject theme styles into Shadow DOM based on current theme
            injectThemeStyles(shadowRoot);

            // Apply padding to favorites elements within Shadow DOM
            const foundElements = applyFavoritesPadding(shadowRoot);

            // If no elements found, retry after a longer delay
            if (!foundElements) {
                setTimeout(() => {
                    applyFavoritesPadding(shadowRoot);
                }, 500);
            }

            // Use MutationObserver to watch for dynamically added elements within Shadow DOM
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

            // Start observing the Shadow DOM for changes
            observer.observe(shadowRoot, {
                childList: true,
                subtree: true,
            });
        }, 100);
    }

    // Insert emoji into the input field
    function insertEmoji(emoji) {
        const input = document.getElementById("chat-input");
        if (!input) {
            return;
        }

        const cursorPos = input.selectionStart;
        const textBefore = input.value.substring(0, cursorPos);
        const textAfter = input.value.substring(cursorPos);

        input.value = textBefore + emoji + textAfter;
        input.setSelectionRange(
            cursorPos + emoji.length,
            cursorPos + emoji.length
        );

        // Keep focus on input and emoji picker open for multiple selections
        input.focus();

        // Trigger input event to update any listeners
        input.dispatchEvent(new Event("input", { bubbles: true }));
    }

    // Close emoji picker when clicking outside
    document.addEventListener("click", function (event) {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const emojiButton = document.getElementById("emoji-button");
        const emojiPicker = document.getElementById("emoji-picker");

        if (
            emojiPickerContainer &&
            !emojiPickerContainer.classList.contains("hidden")
        ) {
            // Close if clicking outside the emoji picker, emoji button, and emoji picker element
            if (
                !emojiPickerContainer.contains(event.target) &&
                !emojiButton.contains(event.target) &&
                !emojiPicker.contains(event.target)
            ) {
                toggleEmojiPicker();
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

    // Function to update emoji picker theme based on current theme
    function updateEmojiPickerTheme() {
        const emojiPicker = document.getElementById("emoji-picker");
        if (!emojiPicker || !emojiPicker.shadowRoot) return;

        const isDarkMode = document.documentElement.classList.contains("dark");
        const shadowRoot = emojiPicker.shadowRoot;

        // Remove existing theme styles
        const existingStyles = shadowRoot.querySelectorAll("style[data-theme]");
        existingStyles.forEach((style) => style.remove());

        // Create new theme styles
        const styleElement = document.createElement("style");
        styleElement.setAttribute("data-theme", isDarkMode ? "dark" : "light");

        if (isDarkMode) {
            styleElement.textContent = `
                /* Dark theme styles for emoji picker */
                :host {
                    --background: rgb(39 39 42) !important;
                    --border-color: rgb(63 63 70) !important;
                    --color: rgb(255 255 255) !important;
                    background-color: rgb(39 39 42) !important;
                    border-color: rgb(63 63 70) !important;
                }
                
                /* Dark theme hover effects */
                button:hover,
                [role="button"]:hover,
                [role="menuitem"]:hover,
                .emoji:hover,
                [data-emoji]:hover,
                .category-emoji:hover {
                    background-color: rgb(55 65 81) !important;
                    transform: scale(1.02) !important;
                    transition: all 0.2s ease !important;
                    border-radius: 4px !important;
                    cursor: pointer !important;
                    padding: 0px !important;
                }
                
                /* Dark theme focus states */
                button:focus,
                [role="button"]:focus,
                [role="menuitem"]:focus {
                    background-color: rgb(55 65 81) !important;
                    outline: 2px solid rgb(59 130 246) !important;
                    outline-offset: 2px !important;
                }
                
                /* Dark theme active states */
                button:active,
                [role="button"]:active,
                [role="menuitem"]:active {
                    background-color: rgb(63 63 70) !important;
                    transform: scale(0.98) !important;
                    padding: 0px !important;
                }
                
                /* Dark theme scrollbar - synced with global theme */
                ::-webkit-scrollbar {
                    width: 8px !important;
                }
                
                ::-webkit-scrollbar-track {
                    background: transparent !important; /* synced with global theme */
                }
                
                ::-webkit-scrollbar-thumb {
                    background: rgb(113 113 122) !important; /* zinc-500 - synced with global */
                    border-radius: 4px !important;
                }
                
                ::-webkit-scrollbar-thumb:hover {
                    background: rgb(82 82 91) !important; /* zinc-600 - synced with global */
                }
                
                /* Ensure all interactive elements have proper dark theme styling */
                .picker,
                .picker > div,
                .picker > section {
                    background-color: rgb(39 39 42) !important;
                    color: rgb(255 255 255) !important;
                }
                
                /* Category buttons and emoji buttons */
                .category-emoji,
                .emoji,
                [data-emoji] {
                    background-color: transparent !important;
                    transition: all 0.2s ease !important;
                }
                
                /* Search input styling - synced with chat input design */
                input[type="search"],
                input[type="text"] {
                    background-color: rgb(39 39 42) !important;
                    border: 1px solid rgb(63 63 70) !important; /* gray-600 to match chat input */
                    color: rgb(255 255 255) !important; /* white text */
                    font-size: 0.875rem !important; /* text-sm to match chat input */
                    border-radius: 0.5rem !important; /* rounded-lg to match chat input */
                    padding: 0.75rem 1rem !important; /* py-3 px-4 to match chat input */
                    transition: all 0.2s ease !important;
                }
                
                /* Search input focus state - synced with chat input focus */
                input[type="search"]:focus,
                input[type="text"]:focus {
                    outline: none !important;
                    border-color: rgb(0 174 159) !important;              
                }
                
                /* Search input placeholder styling */
                input[type="search"]::placeholder,
                input[type="text"]::placeholder {
                    color: rgb(156 163 175) !important; /* gray-400 for placeholder */
                }
                
                /* Indicator styling - synced with project primary color */
                .indicator {
                    background-color: rgb(0 174 159) !important; /* primary-500 to match project theme */
                }
            `;
        } else {
            styleElement.textContent = `
                /* Light theme styles for emoji picker - synced with chatbox design */
                :host {
                    --background: rgb(249 250 251) !important; /* gray-50 to match chat input */
                    --border-color: rgb(209 213 219) !important; /* gray-300 to match chat input */
                    --color: rgb(0 0 0) !important;
                    background-color: rgb(249 250 251) !important; /* gray-50 to match chat input */
                    border-color: rgb(209 213 219) !important; /* gray-300 to match chat input */
                }
                
                /* Light theme hover effects */
                button:hover,
                [role="button"]:hover,
                [role="menuitem"]:hover,
                .emoji:hover,
                [data-emoji]:hover,
                .category-emoji:hover {
                    background-color: rgb(243 244 246) !important; /* gray-100 for subtle hover */
                    transform: scale(1.02) !important;
                    transition: all 0.2s ease !important;
                    border-radius: 4px !important;
                    cursor: pointer !important;
                    padding: 0px !important;
                }
                
                /* Light theme focus states */
                button:focus,
                [role="button"]:focus,
                [role="menuitem"]:focus {
                    background-color: rgb(243 244 246) !important; /* gray-100 for focus */
                    outline: 2px solid rgb(59 130 246) !important; /* primary-500 focus ring */
                    outline-offset: 2px !important;
                }
                
                /* Light theme active states */
                button:active,
                [role="button"]:active,
                [role="menuitem"]:active {
                    background-color: rgb(229 231 235) !important; /* gray-200 for active */
                    transform: scale(0.98) !important;
                    padding: 0px !important;
                }
                
                /* Light theme scrollbar - synced with global theme */
                ::-webkit-scrollbar {
                    width: 8px !important;
                }
                
                ::-webkit-scrollbar-track {
                    background: transparent !important; /* synced with global theme */
                }
                
                ::-webkit-scrollbar-thumb {
                    background: rgb(161 161 170) !important; /* zinc-400 - synced with global */
                    border-radius: 4px !important;
                }
                
                ::-webkit-scrollbar-thumb:hover {
                    background: rgb(113 113 122) !important; /* zinc-500 - synced with global */
                }
                
                /* Ensure all interactive elements have proper light theme styling */
                .picker,
                .picker > div,
                .picker > section {
                    background-color: rgb(249 250 251) !important; /* gray-50 to match chat input */
                    color: rgb(0 0 0) !important;
                }
                
                /* Category buttons and emoji buttons */
                .category-emoji,
                .emoji,
                [data-emoji] {
                    background-color: transparent !important;
                    transition: all 0.2s ease !important;
                }
                
                /* Search input styling - synced with chat input design */
                input[type="search"],
                input[type="text"] {
                    background-color: rgb(255 255 255) !important;
                    border: 1px solid rgb(209 213 219) !important; /* gray-300 to match chat input */
                    color: rgb(0 0 0) !important;
                    font-size: 0.875rem !important; /* text-sm to match chat input */
                    border-radius: 0.5rem !important; /* rounded-lg to match chat input */
                    padding: 0.75rem 1rem !important; /* py-3 px-4 to match chat input */
                    transition: all 0.2s ease !important;
                }
                
                /* Search input focus state - synced with chat input focus */
                input[type="search"]:focus,
                input[type="text"]:focus {
                    outline: none !important;
                    border-color: rgb(0 174 159) !important; /* primary-500 to match chat input focus */
                }
                
                /* Search input placeholder styling */
                input[type="search"]::placeholder,
                input[type="text"]::placeholder {
                    color: rgb(156 163 175) !important; /* gray-400 for placeholder */
                }
                
                /* Indicator styling - synced with project primary color */
                .indicator {
                    background-color: rgb(0 174 159) !important; /* primary-500 to match project theme */
                }
            `;
        }

        // Inject the style into Shadow DOM
        shadowRoot.appendChild(styleElement);
    }

    // Listen for theme changes
    const themeObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (
                mutation.type === "attributes" &&
                mutation.attributeName === "class"
            ) {
                updateEmojiPickerTheme();
            }
        });
    });

    // Start observing theme changes
    themeObserver.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ["class"],
    });

    // Listen for backup restored event to refresh conversation list
    document.addEventListener("livewire:init", () => {
        Livewire.on("backup-restored", () => {
            // console.log(
            //     "Backup restored event received, refreshing conversation list..."
            // );
            // Reset conversation loaded flag to force reload
            conversationLoaded = false;
            // Reload conversation history if chatbot is open
            if (conversationId) {
                setTimeout(() => {
                    loadConversationHistory();
                }, 500); // Small delay to ensure backend is ready
            }
        });
    });

    // Export functions to the window object
    window.toggleChatbot = toggleChatbot;
    window.sendMessage = sendMessage;
    window.clearConversation = clearConversation;
    window.toggleEmojiPicker = toggleEmojiPicker;
    window.insertEmoji = insertEmoji;
    window.executeClearConversation = executeClearConversation;

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

            // Reload conversation history after page resume
            if (conversationId) {
                conversationLoaded = false;
                setTimeout(() => {
                    loadConversationHistory();
                }, 100);
            }
        }
    });

    // Handle page visibility changes to reload conversations when page becomes visible
    document.addEventListener("visibilitychange", function () {
        if (!document.hidden && conversationId && !isLoadingConversation) {
            // Page became visible, ensure conversation is loaded
            // console.log("Page became visible, checking conversation loading");
            setTimeout(() => {
                if (!conversationLoaded) {
                    loadConversationHistory();
                }
            }, 100);
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
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const globalModalContainer = document.getElementById(
            "global-modal-container"
        );

        // Only close if chatbot is currently open and click is outside the chatbot elements
        // AND not inside the emoji picker or global modals
        if (
            chatbotInterface &&
            chatbotInterface.style.display !== "none" &&
            !chatbotInterface.classList.contains("hidden") &&
            !event.target.closest("#chatbot-interface") &&
            !event.target.closest("#chat-icon") &&
            !event.target.closest("#close-icon") &&
            !event.target.closest("#emoji-picker-container") &&
            !event.target.closest("#global-modal-container")
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
