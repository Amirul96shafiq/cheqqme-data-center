// -----------------------------
// Chatbot functionality
// -----------------------------
import { init, Picker } from "emoji-mart";
(function () {
    let conversationId = null; // Start with null ID
    let conversation = [];
    let isLoadingConversation = false;
    let conversationLoaded = false;
    let emojiPickerInitialized = false; // Flag to prevent multiple initializations
    let addingInitialMessages = false; // Flag to prevent duplicate initial messages
    let animationInterval = null; // For chat icon animation

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
    // Only called when chatbot is opened, not on initial page load
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

        // Only initialize session and load conversation history if chatbot should be open on page load
        // This ensures session is only loaded when chatbot is actually opened
        if (shouldBeOpen) {
            // Initialize session first, then load conversation history
            initializeSession().then(() => {
                if (conversationId && !conversationLoaded) {
                    setTimeout(() => {
                        loadConversationHistory();
                    }, 300);
                }
            });
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

            // Stop animation when chat is open
            stopChatIconAnimation();

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

            // Start animation when chat is closed
            startChatIconAnimation();
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

        // If the chatbox should be open, initialize session and load conversation history
        if (shouldBeOpen) {
            // Initialize session first if not already initialized
            if (!conversationId) {
                initializeSession().then(() => {
                    if (conversationId && !conversationLoaded) {
                        setTimeout(() => {
                            loadConversationHistory();
                        }, 400);
                    }
                });
            } else if (!conversationLoaded) {
                // Session exists, just load conversation history
                setTimeout(() => {
                    loadConversationHistory();
                }, 400);
            }
        }

        chatbotUIInitialized = true;
    }

    // Initialize chatbot state when the DOM is ready
    onDocumentReady(() => {
        // Delay chatbot initialization until AFTER window.load event completes
        // This ensures the page loads faster by prioritizing critical content
        const initializeChatbot = () => {
            // DON'T initialize session here - only load when chatbot is opened
            // This reduces initial page load by avoiding unnecessary network requests

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
            // Opening: initialize session first if not already initialized
            if (!conversationId) {
                initializeSession().then(() => {
                    // After session is initialized, load conversation history
                    if (!isLoadingConversation) {
                        loadConversationHistory();
                    }
                });
            } else {
                // Session already exists, just load conversation history
                if (!isLoadingConversation) {
                    loadConversationHistory();
                }
            }

            // Use the centralized state setter and then animate
            setChatVisibility(true);
            interfaceEl.classList.add("open");
            requestAnimationFrame(() => {
                // ensure animation frame after state application
                // Scroll to bottom after opening
                setTimeout(() => {
                    scrollToBottom();
                }, 100);
            });
        } else {
            // Closing: animate out then hide
            interfaceEl.classList.remove("open");
            interfaceEl.classList.add("closing");
            const transitionMs = 260;
            setTimeout(() => {
                interfaceEl.classList.remove("closing");
                localStorage.setItem(getUserChatStateKey(), "false");
                setChatVisibility(false);

                // Also close emoji picker when chatbot is closed
                const emojiPickerContainer = document.getElementById(
                    "emoji-picker-container"
                );
                if (
                    emojiPickerContainer &&
                    !emojiPickerContainer.classList.contains("hidden")
                ) {
                    // Create a fake event to indicate this is a close action (not opening)
                    const fakeEvent = { target: null, isCloseAction: true };
                    toggleEmojiPicker(fakeEvent);
                }

                // Destroy emoji-mart picker instance to prevent onClickOutside from firing
                if (emojiPickerContainer && emojiPickerContainer.picker) {
                    emojiPickerContainer.picker.destroy();
                    emojiPickerContainer.picker = null;
                }
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
                ? "font-semibold text-xs chatbot-user-name-tag mb-2"
                : "font-semibold text-xs chatbot-ai-name-tag mb-2";

        // Check if content is a single emoji
        const isEmoji = isSingleEmoji(content);

        // Get message class - remove bubble styling for single emojis
        const messageClass = isEmoji
            ? "chatbot-emoji-message"
            : role === "user"
            ? "fi-section bg-[#00AE9F] border-[#00AE9F] chatbot-user-message message-bubble user-message"
            : "fi-section bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 chatbot-assistant-message message-bubble";

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
            const borderRadiusClass =
                role === "user"
                    ? "rounded-xl rounded-tr-none"
                    : "rounded-xl rounded-tl-none";
            messageDiv.innerHTML =
                '<div class="' +
                nameTagClass +
                ' px-1">' +
                nameTag +
                "</div>" +
                '<div class="' +
                messageClass +
                " " +
                borderRadiusClass +
                ' px-4 py-3 border max-w-[80%]">' +
                '<div class="' +
                contentClass +
                '">' +
                normalizeContent(marked.parse(processTranslation(content))) +
                "</div>" +
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
                "</div>";
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
            '<div class="fi-section bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl rounded-tl-none px-4 py-3 max-w-[80%] message-bubble typing-indicator">' +
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
                    // Try to get the error message from the response
                    let errorMessage =
                        window.chatbot?.error_message ||
                        "Sorry, I encountered an error. Please try again.";

                    try {
                        const errorData = await response.json();
                        if (errorData.error) {
                            errorMessage = errorData.error;
                        }
                    } catch (parseError) {
                        // If we can't parse the error response, use the default message
                        console.error(
                            "Failed to parse error response:",
                            parseError
                        );
                    }

                    addMessage(errorMessage, "assistant");
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
                '<div class="fi-section bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-3 max-w-[80%] message-bubble">' +
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

    // Media selection menu functionality
    function toggleMediaMenu(event) {
        const menu = document.getElementById("media-selection-menu");
        const emojiButton = document.getElementById("emoji-gif-sticker-button");

        if (!menu || !emojiButton) {
            return;
        }

        const isCurrentlyHidden = menu.classList.contains("hidden");

        if (isCurrentlyHidden) {
            // Close any open pickers first
            closeAllMediaPickers();

            // Position menu above the button, centered
            // Make menu visible temporarily to get its width
            menu.classList.remove("hidden");
            menu.style.visibility = "hidden";
            menu.style.opacity = "0";
            const menuWidth = menu.offsetWidth || 140; // fallback to min-width
            const buttonWidth = emojiButton.offsetWidth || 20; // fallback to icon size

            // Calculate left position to center menu relative to button
            // Since both are in the same parent container, we center relative to button center
            const leftPosition = (buttonWidth - menuWidth) / 2;

            // Set position and make visible
            menu.style.left = leftPosition + "px";
            menu.style.bottom = "100%";
            menu.style.marginBottom = "16px";
            menu.style.visibility = "visible";

            // Add animation
            menu.style.opacity = "0";
            menu.style.transform = "translateY(10px) scale(0.95)";
            requestAnimationFrame(() => {
                menu.style.transition =
                    "opacity 0.2s ease, transform 0.2s ease";
                menu.style.opacity = "1";
                menu.style.transform = "translateY(0) scale(1)";
            });
        } else {
            // Hide menu
            menu.style.transition = "opacity 0.2s ease, transform 0.2s ease";
            menu.style.opacity = "0";
            menu.style.transform = "translateY(10px) scale(0.95)";
            setTimeout(() => {
                menu.classList.add("hidden");
            }, 200);
        }
    }

    // Close all media pickers
    function closeAllMediaPickers() {
        const emojiPicker = document.getElementById("emoji-picker-container");
        const gifPicker = document.getElementById("gif-picker-container");
        const stickerPicker = document.getElementById(
            "sticker-picker-container"
        );
        const menu = document.getElementById("media-selection-menu");

        if (emojiPicker && !emojiPicker.classList.contains("hidden")) {
            const fakeEvent = { target: null, isCloseAction: true };
            toggleEmojiPicker(fakeEvent);
        }

        if (gifPicker && !gifPicker.classList.contains("hidden")) {
            closeMediaPicker("gifs");
        }

        if (stickerPicker && !stickerPicker.classList.contains("hidden")) {
            closeMediaPicker("stickers");
        }

        if (menu && !menu.classList.contains("hidden")) {
            menu.style.transition = "opacity 0.2s ease, transform 0.2s ease";
            menu.style.opacity = "0";
            menu.style.transform = "translateY(10px) scale(0.95)";
            setTimeout(() => {
                menu.classList.add("hidden");
            }, 200);
        }
    }

    // Open specific media picker
    function openMediaPicker(type) {
        // Close menu first
        const menu = document.getElementById("media-selection-menu");
        if (menu && !menu.classList.contains("hidden")) {
            menu.style.transition = "opacity 0.2s ease, transform 0.2s ease";
            menu.style.opacity = "0";
            menu.style.transform = "translateY(10px) scale(0.95)";
            setTimeout(() => {
                menu.classList.add("hidden");
            }, 200);
        }

        // Close other pickers
        if (type !== "emojis") {
            const emojiPicker = document.getElementById(
                "emoji-picker-container"
            );
            if (emojiPicker && !emojiPicker.classList.contains("hidden")) {
                const fakeEvent = { target: null, isCloseAction: true };
                toggleEmojiPicker(fakeEvent);
            }
        }
        if (type !== "gifs") {
            closeMediaPicker("gifs");
        }
        if (type !== "stickers") {
            closeMediaPicker("stickers");
        }

        // Open selected picker
        if (type === "emojis") {
            // Create a fake event to open emoji picker
            const fakeEvent = {
                target: document.getElementById("emoji-gif-sticker-button"),
                isCloseAction: false,
            };
            toggleEmojiPicker(fakeEvent);
        } else if (type === "gifs") {
            openGifPicker();
        } else if (type === "stickers") {
            openStickerPicker();
        }
    }

    // Open GIF picker
    function openGifPicker() {
        const gifPickerContainer = document.getElementById(
            "gif-picker-container"
        );
        const chatbotInterface = document.getElementById("chatbot-interface");

        if (!gifPickerContainer || !chatbotInterface) {
            return;
        }

        // Make picker visible first to get accurate dimensions
        gifPickerContainer.classList.remove("hidden");
        gifPickerContainer.style.opacity = "0";

        const gifPickerWidth = 352; // Same as emoji picker
        const gifPickerHeight = 400;

        // Check if mobile
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            // Center the picker on mobile
            const centerX = (window.innerWidth - gifPickerWidth) / 2;
            const centerY = (window.innerHeight - gifPickerHeight) / 2;
            gifPickerContainer.style.left = centerX + "px";
            gifPickerContainer.style.top = centerY + "px";
        } else {
            // Desktop: Position beside chatbot box
            const chatRect = chatbotInterface.getBoundingClientRect();
            const gap = 12;
            const leftPosition = chatRect.left - gifPickerWidth - gap;
            const topPosition = chatRect.bottom - gifPickerHeight;
            const finalLeftPosition = Math.max(20, leftPosition);
            const finalTopPosition = Math.max(20, topPosition);
            gifPickerContainer.style.left = finalLeftPosition + "px";
            gifPickerContainer.style.top = finalTopPosition + "px";
        }

        // Add animation
        gifPickerContainer.style.transform = "translateX(20px) scale(0.95)";
        requestAnimationFrame(() => {
            gifPickerContainer.style.transition =
                "opacity 0.2s ease, transform 0.2s ease";
            gifPickerContainer.style.opacity = "1";
            gifPickerContainer.style.transform = "translateX(0) scale(1)";
        });
    }

    // Open Sticker picker
    function openStickerPicker() {
        const stickerPickerContainer = document.getElementById(
            "sticker-picker-container"
        );
        const chatbotInterface = document.getElementById("chatbot-interface");

        if (!stickerPickerContainer || !chatbotInterface) {
            return;
        }

        // Make picker visible first to get accurate dimensions
        stickerPickerContainer.classList.remove("hidden");
        stickerPickerContainer.style.opacity = "0";

        const stickerPickerWidth = 352; // Same as emoji picker
        const stickerPickerHeight = 400;

        // Check if mobile
        const isMobile = window.innerWidth <= 768;

        if (isMobile) {
            // Center the picker on mobile
            const centerX = (window.innerWidth - stickerPickerWidth) / 2;
            const centerY = (window.innerHeight - stickerPickerHeight) / 2;
            stickerPickerContainer.style.left = centerX + "px";
            stickerPickerContainer.style.top = centerY + "px";
        } else {
            // Desktop: Position beside chatbot box
            const chatRect = chatbotInterface.getBoundingClientRect();
            const gap = 12;
            const leftPosition = chatRect.left - stickerPickerWidth - gap;
            const topPosition = chatRect.bottom - stickerPickerHeight;
            const finalLeftPosition = Math.max(20, leftPosition);
            const finalTopPosition = Math.max(20, topPosition);
            stickerPickerContainer.style.left = finalLeftPosition + "px";
            stickerPickerContainer.style.top = finalTopPosition + "px";
        }

        // Add animation
        stickerPickerContainer.style.transform = "translateX(20px) scale(0.95)";
        requestAnimationFrame(() => {
            stickerPickerContainer.style.transition =
                "opacity 0.2s ease, transform 0.2s ease";
            stickerPickerContainer.style.opacity = "1";
            stickerPickerContainer.style.transform = "translateX(0) scale(1)";
        });
    }

    // Close media picker (for GIFs and Stickers)
    function closeMediaPicker(type) {
        let pickerContainer;
        if (type === "gifs") {
            pickerContainer = document.getElementById("gif-picker-container");
        } else if (type === "stickers") {
            pickerContainer = document.getElementById(
                "sticker-picker-container"
            );
        }

        if (!pickerContainer || pickerContainer.classList.contains("hidden")) {
            return;
        }

        pickerContainer.style.transition =
            "opacity 0.2s ease, transform 0.2s ease";
        pickerContainer.style.opacity = "0";
        pickerContainer.style.transform = "translateX(20px) scale(0.95)";
        setTimeout(() => {
            pickerContainer.classList.add("hidden");
        }, 200);
    }

    // Emoji picker functionality
    // Only opens when explicitly called from emoji button click
    function toggleEmojiPicker(event) {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const emojiPicker = document.getElementById("emoji-picker");
        const chatbotInterface = document.getElementById("chatbot-interface");
        const emojiButton = document.getElementById("emoji-gif-sticker-button");

        if (
            !emojiPickerContainer ||
            !emojiPicker ||
            !chatbotInterface ||
            !emojiButton
        ) {
            return;
        }

        const isCurrentlyHidden =
            emojiPickerContainer.classList.contains("hidden");

        // When opening (picker is hidden), ONLY allow if click is from emoji button
        if (isCurrentlyHidden) {
            // If this is a close action (like Escape key), don't allow opening
            if (event && event.isCloseAction) {
                return;
            }

            // If no event provided, don't allow opening (should only open from button click)
            if (!event || !event.target) {
                return;
            }

            // Ensure it's from the emoji button when opening
            const isClickOnEmojiButton =
                emojiButton === event.target ||
                emojiButton.contains(event.target) ||
                event.target.closest("#emoji-gif-sticker-button");

            if (!isClickOnEmojiButton) {
                return; // Don't open if click is not on emoji button
            }
        }

        if (isCurrentlyHidden) {
            // Make picker visible first to get accurate dimensions
            emojiPickerContainer.classList.remove("hidden");
            emojiPickerContainer.style.opacity = "0";

            // Get emoji picker dimensions after making it visible
            const emojiPickerWidth = emojiPicker.offsetWidth || 352; // Default emoji picker width
            const emojiPickerHeight = emojiPicker.offsetHeight || 400; // Get actual height or fallback

            // Check if mobile (same breakpoint as CSS: 768px)
            const isMobile = window.innerWidth <= 768;

            if (isMobile) {
                // Center the emoji picker on mobile
                const centerX = (window.innerWidth - emojiPickerWidth) / 2;
                const centerY = (window.innerHeight - emojiPickerHeight) / 2;

                emojiPickerContainer.style.left = centerX + "px";
                emojiPickerContainer.style.top = centerY + "px";
            } else {
                // Desktop: Position beside chatbot box with bottom alignment
                const chatRect = chatbotInterface.getBoundingClientRect();

                // Calculate position: left of chatbox with a small gap (12px)
                const gap = 12; // Gap between chatbot box and emoji picker
                const leftPosition = chatRect.left - emojiPickerWidth - gap;

                // Align bottom of emoji picker with bottom of chatbot box
                const topPosition = chatRect.bottom - emojiPickerHeight;

                // Ensure it doesn't go off-screen on the left
                const finalLeftPosition = Math.max(20, leftPosition);

                // Ensure it doesn't go off-screen on the top
                const finalTopPosition = Math.max(20, topPosition);

                emojiPickerContainer.style.left = finalLeftPosition + "px";
                emojiPickerContainer.style.top = finalTopPosition + "px";
            }

            // Add animation
            emojiPickerContainer.style.transform =
                "translateX(20px) scale(0.95)";
            requestAnimationFrame(() => {
                emojiPickerContainer.style.transition =
                    "opacity 0.2s ease, transform 0.2s ease";
                emojiPickerContainer.style.opacity = "1";
                emojiPickerContainer.style.transform = "translateX(0) scale(1)";
            });

            // Ensure emoji picker theme is up to date when opened
            updateEmojiPickerTheme();

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

        const emojiPickerContainer = document.getElementById("emoji-picker");
        if (!emojiPickerContainer) {
            return;
        }

        // Mark as initialized
        emojiPickerInitialized = true;

        // Initialize emoji-mart
        init({}).then(() => {
            const isDarkMode =
                document.documentElement.classList.contains("dark");

            // Create emoji picker
            const picker = new Picker({
                parent: emojiPickerContainer,
                theme: isDarkMode ? "dark" : "light",
                onEmojiSelect: (emoji) => {
                    insertEmoji(emoji.native);
                },
                onClickOutside: (event) => {
                    // Only process if picker is actually open
                    const emojiPickerContainer = document.getElementById(
                        "emoji-picker-container"
                    );
                    if (
                        !emojiPickerContainer ||
                        emojiPickerContainer.classList.contains("hidden")
                    ) {
                        return;
                    }

                    // Close picker when clicking outside, but not on emoji button or input
                    const emojiButton = document.getElementById(
                        "emoji-gif-sticker-button"
                    );
                    const chatInput = document.getElementById("chat-input");

                    // Check if click is on emoji button, input, or inside picker container
                    const isClickOnEmojiButton =
                        emojiButton &&
                        (emojiButton === event.target ||
                            emojiButton.contains(event.target) ||
                            event.target.closest("#emoji-gif-sticker-button"));
                    const isClickOnInput =
                        chatInput &&
                        (chatInput === event.target ||
                            chatInput.contains(event.target) ||
                            event.target.closest("#chat-input"));
                    const isClickOnPicker =
                        emojiPickerContainer &&
                        (emojiPickerContainer === event.target ||
                            emojiPickerContainer.contains(event.target) ||
                            event.target.closest("#emoji-picker-container"));

                    // Only close if clicking outside all these elements
                    if (
                        !isClickOnEmojiButton &&
                        !isClickOnInput &&
                        !isClickOnPicker
                    ) {
                        toggleEmojiPicker(event);
                    }
                },
                previewPosition: "none",
                skinTonePosition: "none",
                searchPosition: "sticky",
                navPosition: "top",
                maxFrequentRows: 1,
                emojiSize: 32,
                emojiButtonSize: 52,
                perLine: 5,
                categories: [
                    "frequent",
                    "people",
                    "nature",
                    "foods",
                    "activity",
                    "places",
                    "objects",
                    "symbols",
                ],
            });

            // Store picker instance for cleanup
            emojiPickerContainer.picker = picker;
        });
    }

    // Function to update emoji picker theme based on current theme
    function updateEmojiPickerTheme() {
        const emojiPickerContainer = document.getElementById("emoji-picker");
        if (!emojiPickerContainer || !emojiPickerContainer.picker) return;

        // Update emoji-mart theme
        const isDarkMode = document.documentElement.classList.contains("dark");
        emojiPickerContainer.picker.update({
            theme: isDarkMode ? "dark" : "light",
        });
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

    // Close media pickers and menu when clicking outside
    document.addEventListener("click", function (event) {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const gifPickerContainer = document.getElementById(
            "gif-picker-container"
        );
        const stickerPickerContainer = document.getElementById(
            "sticker-picker-container"
        );
        const mediaMenu = document.getElementById("media-selection-menu");
        const emojiButton = document.getElementById("emoji-gif-sticker-button");
        const chatInput = document.getElementById("chat-input");

        // Check if click is on emoji button, input, or inside any picker/menu
        const isClickOnEmojiButton =
            emojiButton &&
            (emojiButton === event.target ||
                emojiButton.contains(event.target) ||
                event.target.closest("#emoji-gif-sticker-button"));
        const isClickOnInput =
            chatInput &&
            (chatInput === event.target ||
                chatInput.contains(event.target) ||
                event.target.closest("#chat-input"));
        const isClickOnEmojiPicker =
            emojiPickerContainer &&
            (emojiPickerContainer === event.target ||
                emojiPickerContainer.contains(event.target) ||
                event.target.closest("#emoji-picker-container"));
        const isClickOnGifPicker =
            gifPickerContainer &&
            (gifPickerContainer === event.target ||
                gifPickerContainer.contains(event.target) ||
                event.target.closest("#gif-picker-container"));
        const isClickOnStickerPicker =
            stickerPickerContainer &&
            (stickerPickerContainer === event.target ||
                stickerPickerContainer.contains(event.target) ||
                event.target.closest("#sticker-picker-container"));
        const isClickOnMenu =
            mediaMenu &&
            (mediaMenu === event.target ||
                mediaMenu.contains(event.target) ||
                event.target.closest("#media-selection-menu"));

        // Close emoji picker if open and clicking outside
        if (
            emojiPickerContainer &&
            !emojiPickerContainer.classList.contains("hidden") &&
            !isClickOnEmojiButton &&
            !isClickOnInput &&
            !isClickOnEmojiPicker
        ) {
            toggleEmojiPicker(event);
        }

        // Close GIF picker if open and clicking outside
        if (
            gifPickerContainer &&
            !gifPickerContainer.classList.contains("hidden") &&
            !isClickOnEmojiButton &&
            !isClickOnInput &&
            !isClickOnGifPicker
        ) {
            closeMediaPicker("gifs");
        }

        // Close Sticker picker if open and clicking outside
        if (
            stickerPickerContainer &&
            !stickerPickerContainer.classList.contains("hidden") &&
            !isClickOnEmojiButton &&
            !isClickOnInput &&
            !isClickOnStickerPicker
        ) {
            closeMediaPicker("stickers");
        }

        // Close menu if open and clicking outside
        if (
            mediaMenu &&
            !mediaMenu.classList.contains("hidden") &&
            !isClickOnEmojiButton &&
            !isClickOnMenu
        ) {
            mediaMenu.style.transition =
                "opacity 0.2s ease, transform 0.2s ease";
            mediaMenu.style.opacity = "0";
            mediaMenu.style.transform = "translateY(10px) scale(0.95)";
            setTimeout(() => {
                mediaMenu.classList.add("hidden");
            }, 200);
        }
    });

    // Close media pickers and menu on Escape key
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            const emojiPickerContainer = document.getElementById(
                "emoji-picker-container"
            );
            const gifPickerContainer = document.getElementById(
                "gif-picker-container"
            );
            const stickerPickerContainer = document.getElementById(
                "sticker-picker-container"
            );
            const mediaMenu = document.getElementById("media-selection-menu");

            // Close menu first if open
            if (mediaMenu && !mediaMenu.classList.contains("hidden")) {
                mediaMenu.style.transition =
                    "opacity 0.2s ease, transform 0.2s ease";
                mediaMenu.style.opacity = "0";
                mediaMenu.style.transform = "translateY(10px) scale(0.95)";
                setTimeout(() => {
                    mediaMenu.classList.add("hidden");
                }, 200);
            }

            // Close emoji picker if open
            if (
                emojiPickerContainer &&
                !emojiPickerContainer.classList.contains("hidden")
            ) {
                const fakeEvent = { target: null, isCloseAction: true };
                toggleEmojiPicker(fakeEvent);
            }

            // Close GIF picker if open
            if (
                gifPickerContainer &&
                !gifPickerContainer.classList.contains("hidden")
            ) {
                closeMediaPicker("gifs");
            }

            // Close Sticker picker if open
            if (
                stickerPickerContainer &&
                !stickerPickerContainer.classList.contains("hidden")
            ) {
                closeMediaPicker("stickers");
            }
        }
    });

    // Reposition all media pickers on window resize
    window.addEventListener("resize", function () {
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        const emojiPicker = document.getElementById("emoji-picker");
        const gifPickerContainer = document.getElementById(
            "gif-picker-container"
        );
        const stickerPickerContainer = document.getElementById(
            "sticker-picker-container"
        );
        const chatbotInterface = document.getElementById("chatbot-interface");

        if (!chatbotInterface) return;

        const isMobile = window.innerWidth <= 768;

        // Reposition emoji picker
        if (
            emojiPickerContainer &&
            emojiPicker &&
            !emojiPickerContainer.classList.contains("hidden")
        ) {
            const emojiPickerWidth = emojiPicker.offsetWidth || 352;
            const emojiPickerHeight = emojiPicker.offsetHeight || 400;

            if (isMobile) {
                const centerX = (window.innerWidth - emojiPickerWidth) / 2;
                const centerY = (window.innerHeight - emojiPickerHeight) / 2;
                emojiPickerContainer.style.left = centerX + "px";
                emojiPickerContainer.style.top = centerY + "px";
            } else {
                const chatRect = chatbotInterface.getBoundingClientRect();
                const gap = 12;
                const leftPosition = chatRect.left - emojiPickerWidth - gap;
                const topPosition = chatRect.bottom - emojiPickerHeight;
                const finalLeftPosition = Math.max(20, leftPosition);
                const finalTopPosition = Math.max(20, topPosition);
                emojiPickerContainer.style.left = finalLeftPosition + "px";
                emojiPickerContainer.style.top = finalTopPosition + "px";
            }
        }

        // Reposition GIF picker
        if (
            gifPickerContainer &&
            !gifPickerContainer.classList.contains("hidden")
        ) {
            const gifPickerWidth = 352;
            const gifPickerHeight = 400;

            if (isMobile) {
                const centerX = (window.innerWidth - gifPickerWidth) / 2;
                const centerY = (window.innerHeight - gifPickerHeight) / 2;
                gifPickerContainer.style.left = centerX + "px";
                gifPickerContainer.style.top = centerY + "px";
            } else {
                const chatRect = chatbotInterface.getBoundingClientRect();
                const gap = 12;
                const leftPosition = chatRect.left - gifPickerWidth - gap;
                const topPosition = chatRect.bottom - gifPickerHeight;
                const finalLeftPosition = Math.max(20, leftPosition);
                const finalTopPosition = Math.max(20, topPosition);
                gifPickerContainer.style.left = finalLeftPosition + "px";
                gifPickerContainer.style.top = finalTopPosition + "px";
            }
        }

        // Reposition Sticker picker
        if (
            stickerPickerContainer &&
            !stickerPickerContainer.classList.contains("hidden")
        ) {
            const stickerPickerWidth = 352;
            const stickerPickerHeight = 400;

            if (isMobile) {
                const centerX = (window.innerWidth - stickerPickerWidth) / 2;
                const centerY = (window.innerHeight - stickerPickerHeight) / 2;
                stickerPickerContainer.style.left = centerX + "px";
                stickerPickerContainer.style.top = centerY + "px";
            } else {
                const chatRect = chatbotInterface.getBoundingClientRect();
                const gap = 12;
                const leftPosition = chatRect.left - stickerPickerWidth - gap;
                const topPosition = chatRect.bottom - stickerPickerHeight;
                const finalLeftPosition = Math.max(20, leftPosition);
                const finalTopPosition = Math.max(20, topPosition);
                stickerPickerContainer.style.left = finalLeftPosition + "px";
                stickerPickerContainer.style.top = finalTopPosition + "px";
            }
        }
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

    // Listen for theme changes to update emoji picker theme
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

    // Chat icon animation
    function startChatIconAnimation() {
        const closedImage = "/images/chat_closed.webp";
        const openedImage = "/images/chat_opened.webp";
        if (animationInterval) return; // Already animating

        const chatIcon = document.getElementById("chat-icon");
        if (!chatIcon) return;

        let isClosedImage = true;

        function animate() {
            const newSrc = isClosedImage ? closedImage : openedImage;
            chatIcon.src = newSrc;

            if (isClosedImage) {
                // Currently showing closed image, show opened image after 3 seconds
                animationInterval = setTimeout(() => {
                    isClosedImage = false;
                    animate();
                }, 4000);
            } else {
                // Currently showing opened image, show closed image after 1 second
                animationInterval = setTimeout(() => {
                    isClosedImage = true;
                    animate();
                }, 2000);
            }
        }

        animate(); // Start the animation
    }

    function stopChatIconAnimation() {
        if (animationInterval) {
            clearTimeout(animationInterval);
            animationInterval = null;
        }

        // Set to opened image when chat is open
        const chatIcon = document.getElementById("chat-icon");
        if (chatIcon) {
            chatIcon.src = "/images/chat_opened.webp";
        }
    }

    // Start animation on page load
    startChatIconAnimation();

    // Prevent emoji picker from opening when clicking on input field
    function preventEmojiPickerOnInputClick(event) {
        // Ensure emoji picker doesn't open when clicking on input
        // Stop propagation and prevent any emoji picker toggle
        event.stopPropagation();
        event.preventDefault();

        // If emoji picker is open, close it when clicking on input
        const emojiPickerContainer = document.getElementById(
            "emoji-picker-container"
        );
        if (
            emojiPickerContainer &&
            !emojiPickerContainer.classList.contains("hidden")
        ) {
            // Create a fake event to indicate this is a close action (not opening)
            const fakeEvent = { target: null, isCloseAction: true };
            toggleEmojiPicker(fakeEvent);
        }
    }

    // Export functions to the window object
    window.toggleChatbot = toggleChatbot;
    window.sendMessage = sendMessage;
    window.clearConversation = clearConversation;
    window.toggleEmojiPicker = toggleEmojiPicker;
    window.insertEmoji = insertEmoji;
    window.executeClearConversation = executeClearConversation;
    window.preventEmojiPickerOnInputClick = preventEmojiPickerOnInputClick;
    window.toggleMediaMenu = toggleMediaMenu;
    window.openMediaPicker = openMediaPicker;
    window.closeAllMediaPickers = closeAllMediaPickers;

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

    // Prevent emoji picker from opening when clicking on the input field
    // Only close if already open, never open from input click
    document.body.addEventListener("click", function (event) {
        const chatInput = document.getElementById("chat-input");
        if (
            chatInput &&
            (event.target === chatInput || event.target.closest("#chat-input"))
        ) {
            // If emoji picker is open, close it when clicking on input
            const emojiPickerContainer = document.getElementById(
                "emoji-picker-container"
            );
            if (
                emojiPickerContainer &&
                !emojiPickerContainer.classList.contains("hidden")
            ) {
                // Create a fake event to indicate this is a close action (not opening)
                const fakeEvent = { target: null, isCloseAction: true };
                toggleEmojiPicker(fakeEvent);
            }
            // Explicitly prevent emoji picker from opening on input click
            event.stopPropagation();
        }
    });
})();
