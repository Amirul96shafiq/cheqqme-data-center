// -----------------------------
// Chatbot functionality
// -----------------------------
(function () {
    let conversationId =
        localStorage.getItem("chatbot_conversation_id") ||
        "conv_" + Date.now() + "_" + Math.random().toString(36).substr(2, 9);
    let conversation = [];
    let isLoadingConversation = false;
    let conversationLoaded = false;

    // console.log('Initializing chatbot:', {
    //     conversationIdFromStorage: localStorage.getItem('chatbot_conversation_id'),
    //     finalConversationId: conversationId,
    //     isNewConversation: !localStorage.getItem('chatbot_conversation_id')
    // });

    // Save conversation ID to localStorage if it's newly generated
    if (!localStorage.getItem("chatbot_conversation_id")) {
        localStorage.setItem("chatbot_conversation_id", conversationId);
        // console.log('Saved new conversation ID to localStorage:', conversationId);
    }

    // Initialize chatbot state from localStorage
    function initializeChatbotState() {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (!interfaceEl || !chatIcon || !closeIcon) return;

        // Check if chatbot should be open (default to open if no state is saved)
        const shouldBeOpen = localStorage.getItem("chatbot_open") !== "false";

        if (shouldBeOpen) {
            interfaceEl.classList.remove("hidden");
            // Set close icon when chat is open
            chatIcon.classList.add("hidden");
            closeIcon.classList.remove("hidden");
            // Load conversation history when opening chat
            if (!isLoadingConversation) {
                loadConversationHistory();
            }
        } else {
            interfaceEl.classList.add("hidden");
            // Set chat icon when chat is closed
            chatIcon.classList.remove("hidden");
            closeIcon.classList.add("hidden");
        }

        // console.log('Initialized chatbot state:', { shouldBeOpen, currentState: interfaceEl.classList.contains("hidden") });
    }

    // Initialize chatbot state on page load
    initializeChatbotState();

    // Try to load conversation history immediately if we have a conversation ID
    if (conversationId) {
        // console.log('Attempting to load conversation history on page load');
        setTimeout(() => {
            loadConversationHistory();
        }, 1000); // Small delay to ensure DOM is ready
    }

    function toggleChatbot() {
        const interfaceEl = document.getElementById("chatbot-interface");
        const chatIcon = document.getElementById("chat-icon");
        const closeIcon = document.getElementById("close-icon");
        if (!interfaceEl || !chatIcon || !closeIcon) return;

        const isHidden = interfaceEl.classList.contains("hidden");

        interfaceEl.classList.toggle("hidden");
        const isNowHidden = interfaceEl.classList.contains("hidden");

        // Toggle between chat icon and close icon
        if (isNowHidden) {
            // Chat is closed - show chat icon, hide close icon
            chatIcon.classList.remove("hidden");
            closeIcon.classList.add("hidden");
        } else {
            // Chat is open - show close icon, hide chat icon
            chatIcon.classList.add("hidden");
            closeIcon.classList.remove("hidden");
        }

        // Save chatbot state to localStorage
        localStorage.setItem("chatbot_open", isNowHidden ? "false" : "true");

        // console.log('Toggling chatbot:', { wasHidden: isHidden, isNowHidden, conversationId });

        // Load conversation history when opening chat (when it becomes visible)
        if (isHidden && !isNowHidden && !isLoadingConversation) {
            loadConversationHistory();
        }
    }

    async function loadConversationHistory() {
        // console.log('Loading conversation history:', {
        //     conversationId,
        //     conversationLength: conversation.length,
        //     conversationLoaded,
        //     isLoadingConversation
        // });

        if (!conversationId || conversationLoaded || isLoadingConversation) {
            // console.log('Skipping load - conversation already loaded or loading in progress');
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

            // console.log('Conversation history response:', response.status);

            if (response.ok) {
                const data = await response.json();
                // console.log('Conversation data:', data);

                if (data.conversation && data.conversation.length > 0) {
                    // Load conversation messages
                    const chatMessages =
                        document.getElementById("chat-messages");
                    // Clear welcome message
                    if (chatMessages) chatMessages.innerHTML = "";

                    data.conversation.forEach((message) => {
                        addMessage(
                            message.content,
                            message.role,
                            message.timestamp
                        );
                    });

                    conversationLoaded = true;
                    // console.log('Loaded', data.conversation.length, 'messages from conversation');
                } else {
                    // console.log('No conversation messages found in database');
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

    function addMessage(content, role, timestamp = null) {
        const chatMessages = document.getElementById("chat-messages");
        if (!chatMessages) return;

        const messageDiv = document.createElement("div");
        messageDiv.className =
            "flex flex-col space-y-1 " +
            (role === "user" ? "items-end" : "items-start");

        const fullUserName = window.chatbotUserName || "You";
        const userName =
            fullUserName === "You"
                ? "You"
                : `You (${shortenName(fullUserName)})`;
        const nameTag = role === "user" ? userName : "Arem AI";
        const nameTagClass =
            role === "user"
                ? "text-gray-600 dark:text-gray-400 font-semibold text-sm"
                : "text-gray-600 dark:text-gray-400 font-semibold text-sm";

        const messageClass =
            role === "user"
                ? "fi-section bg-[#00AE9F] text-white border-[#00AE9F]"
                : "fi-section bg-white dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-800 dark:text-gray-200";

        const timeClass =
            role === "user"
                ? "text-white/80"
                : "text-gray-500 dark:text-gray-400";

        messageDiv.innerHTML =
            '<div class="' +
            nameTagClass +
            ' px-1">' +
            nameTag +
            "</div>" +
            '<div class="max-w-[80%] ' +
            messageClass +
            ' rounded-xl px-4 py-3 shadow-sm border">' +
            '<p class="text-sm whitespace-pre-wrap leading-relaxed">' +
            content +
            "</p>" +
            '<p class="text-xs ' +
            timeClass +
            ' mt-2 font-medium">' +
            (timestamp ||
                new Date().toLocaleTimeString("en-US", {
                    hour: "2-digit",
                    minute: "2-digit",
                })) +
            "</p>" +
            "</div>";

        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showLoading() {
        const chatMessages = document.getElementById("chat-messages");
        if (!chatMessages) return;

        const loadingDiv = document.createElement("div");
        loadingDiv.id = "loading-message";
        loadingDiv.className = "flex flex-col space-y-1 items-start";
        loadingDiv.innerHTML =
            '<div class="text-gray-600 dark:text-gray-400 font-semibold text-sm px-1">Arem AI</div>' +
            '<div class="fi-section bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 shadow-sm max-w-[80%]">' +
            '<div class="flex items-center space-x-3">' +
            '<div class="flex space-x-1">' +
            '<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>' +
            '<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>' +
            '<div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>' +
            "</div>" +
            '<span class="text-sm text-gray-600 dark:text-gray-300 font-medium">Arem is thinking...</span>' +
            "</div>" +
            "</div>";
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function hideLoading() {
        const loadingMessage = document.getElementById("loading-message");
        if (loadingMessage) {
            loadingMessage.remove();
        }
    }

    async function sendMessage(event) {
        event.preventDefault();

        const input = document.getElementById("chat-input");
        if (!input) return;

        const message = input.value.trim();

        if (!message) return;

        // Add user message
        addMessage(message, "user");
        input.value = "";

        // Show loading
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
                addMessage(data.response, "assistant", data.timestamp);

                // Update conversation ID if provided
                if (data.conversation_id) {
                    conversationId = data.conversation_id;
                    localStorage.setItem(
                        "chatbot_conversation_id",
                        conversationId
                    );
                    // console.log('Updated conversation ID after message:', conversationId);
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
    }

    async function clearConversation() {
        try {
            const csrfToken =
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content") ||
                document.querySelector('input[name="_token"]')?.value;

            // Clear conversation from server
            await fetch("/chatbot/conversation", {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
                }),
            });
        } catch (error) {
            console.error("Error clearing conversation:", error);
        }

        // Clear local conversation
        const chatMessages = document.getElementById("chat-messages");
        if (chatMessages) {
            chatMessages.innerHTML =
                '<div class="flex flex-col space-y-1 items-start">' +
                '<div class="text-gray-600 dark:text-gray-400 font-semibold text-sm px-1">Arem AI</div>' +
                '<div class="fi-section bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 shadow-sm max-w-[80%]">' +
                '<p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed">Type anything to start a new conversation!</p>';
            "</div>" + "</div>";
        }

        // Generate new conversation ID and save to localStorage
        conversationId =
            "conv_" +
            Date.now() +
            "_" +
            Math.random().toString(36).substr(2, 9);
        localStorage.setItem("chatbot_conversation_id", conversationId);

        // Keep chatbot open for new conversation
        localStorage.setItem("chatbot_open", "true");

        conversation = [];
        conversationLoaded = false;
    }

    window.toggleChatbot = toggleChatbot;
    window.sendMessage = sendMessage;
    window.clearConversation = clearConversation;
})();
