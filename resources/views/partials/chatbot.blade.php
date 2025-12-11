<!-- Chatbot Widget -->
<script>
    window.chatbotUserName = "{{ $userName ?? 'You' }}";
    window.chatbotUserId = "{{ Auth::id() ?? 'anonymous' }}";
    window.chatbotApiToken = "{{ Auth::user()?->getAutoAwayToken() ?? '' }}";
    window.chatbot = {
        welcome_message: "{{ __('chatbot.welcome_message') }}",
        ai_name: "{{ __('chatbot.ai_name') }}",
        help_message: "{{ __('chatbot.help_message') }}",
        help_command: "{{ __('chatbot.help_command') }}",
        ready_message: "{{ __('chatbot.ready_message') }}",
        thinking_message: "{{ __('chatbot.thinking_message') }}",
        error_message: "{{ __('chatbot.error_message') }}",
        clearing_message: "{{ __('chatbot.clearing_message') }}",
        clear_confirmation_message: "{{ __('chatbot.clear_confirmation_message') }}",
        clear_success_message: "{{ __('chatbot.clear_success_message') }}",
    };
</script>
<style>
    /* ===== CHATBOT BASE STYLES ===== */
    
    /* Ensure chatbot button is always visible above background */
    .fixed.bottom-4.right-4 {
        z-index: 10 !important;
        position: fixed !important;
        bottom: 2rem !important;
        right: 1rem !important;
    }
    
    /* Ensure chat icon is always visible */
    #chat-icon {
        z-index: 10 !important;
        position: relative !important;
    }
    
    /* Ensure close icon is always visible */
    #close-icon {
        z-index: 10 !important;
        position: relative !important;
    }
    
    /* ===== CHATBOT STYLING CLASSES ===== */

    /* AI name styling */
    .chatbot-ai-name {
        font-weight: bold;
        color: #00AE9F;
    }

    /* Help command styling */
    .chatbot-help-command {
        font-weight: bold;
        background-color: #fbb43e;
        padding: 2px 6px;
        border-radius: 4px;
        font-family: monospace;
    }
    .dark .chatbot-help-command {
        background-color: #00AE9F;
        color: #d1d5db;
    }

    /* Chat interface animation */
    #chatbot-interface {
        transform: translateY(20px);
        opacity: 0;
        transition: transform 260ms ease, opacity 260ms ease;
        width: clamp(320px, 40vw, 420px);
        min-width: 0;
        max-width: 90vw;
        height: 640px;
        box-sizing: border-box;
    }

    /* Mobile view */
    @media (max-width: 768px) {
        /* Make chatbot interface fixed to viewport on mobile for proper centering */
        #chatbot-interface {
            position: fixed !important;
            width: 95vw !important;
            height: 80vh !important;
            max-height: 80vh !important;
            left: 50% !important;
            right: auto !important;
            transform: translateX(-50%) translateY(20px) !important;
            bottom: 80px !important;
            top: auto !important;
            margin: 0 !important;
            z-index: 50 !important;
        }
        
        /* Ensure parent container doesn't limit stacking on mobile */
        .fixed.bottom-4.right-4 {
            z-index: 50 !important;
        }
        
        /* Ensure close button appears above chatbot interface */
        #close-icon {
            z-index: 51 !important;
        }
        
        /* Ensure emoji picker appears above chatbot interface on mobile */
        #emoji-picker-container {
            z-index: 51 !important;
        }
        
        /* Ensure media menu and pickers appear above chatbot interface on mobile */
        #media-selection-menu,
        #gif-picker-container,
        #sticker-picker-container {
            z-index: 51 !important;
        }
    }
    
    /* Open chatbot interface */
    #chatbot-interface.open {
        transform: translateY(0);
        opacity: 1;
        animation: chatEntrance 260ms ease;
    }
    
    /* Mobile open state - maintain centering */
    @media (max-width: 768px) {
        #chatbot-interface.open {
            transform: translateX(-50%) translateY(0) !important;
        }
        
        #chatbot-interface.closing {
            transform: translateX(-50%) translateY(15px) !important;
        }
    }

    /* Closing animation */
    #chatbot-interface.closing {
        transform: translateY(15px);
        opacity: 0;
    }

    @keyframes chatEntrance {
        0% {
            transform: translateY(15px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    /* Message bubble entrance animations */
    .message-bubble {
        animation: messageSlideIn 0.4s ease-out;
        transform-origin: left center;
    }
    
    .message-bubble.user-message {
        animation: messageSlideInRight 0.4s ease-out;
        transform-origin: right center;
    }
    
    @keyframes messageSlideIn {
        0% {
            opacity: 0;
            transform: translateX(-20px) scale(0.95);
        }
        50% {
            opacity: 0.7;
            transform: translateX(-5px) scale(0.98);
        }
        100% {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }
    
    @keyframes messageSlideInRight {
        0% {
            opacity: 0;
            transform: translateX(20px) scale(0.95);
        }
        50% {
            opacity: 0.7;
            transform: translateX(5px) scale(0.98);
        }
        100% {
            opacity: 1;
            transform: translateX(0) scale(1);
        }
    }
    
    /* Typing indicator animation */
    .typing-indicator {
        animation: messageSlideIn 0.3s ease-out;
    }
    
    .typing-dots {
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }
    
    .typing-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: currentColor;
        opacity: 0.4;
        animation: typingPulse 1.4s infinite;
    }
    
    .typing-dot:nth-child(1) { animation-delay: 0s; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    
    @keyframes typingPulse {
        0%, 60%, 100% {
            opacity: 0.4;
            transform: scale(1);
        }
        30% {
            opacity: 1;
            transform: scale(1.2);
        }
    }
    
    /* ===== CHATBOT COLOR SCHEME ===== */
    
    /* === LIGHT MODE === */
    /* User name tag: dark-800 */
    .chatbot-user-name-tag {
        color: #1f2937 !important;
    }
    
    /* AI name tag: dark-400 */
    .chatbot-ai-name-tag {
        color: #9ca3af !important;
    }
    
    /* User message content: white */
    .chatbot-user-content {
        color: white !important;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        width: 100%;
    }
    
    /* AI message content: dark-800 */
    .chatbot-assistant-content {
        color: #1f2937 !important;
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
        width: 100%;
    }
    
    /* User timestamp: dark gray for visibility on light background */
    .chatbot-user-timestamp {
        color: #374151 !important; /* gray-700 */
        font-size: 0.7rem;
        margin-top: 0.25rem;
        font-weight: 500;
        text-align: right !important;
        width: 100%;
        max-width: 80%;
        display: block !important;
    }

    /* AI timestamp: #00000050 */
    .chatbot-assistant-timestamp {
        color: #00000080 !important;
        font-size: 0.7rem;
        margin-top: 0.25rem;
        font-weight: 500;
        text-align: left !important;
        width: 100%;
        max-width: 80%;
        display: block !important;
    }
    
    /* === DARK MODE (Class-based) === */
    .dark .chatbot-user-name-tag {
        color: rgba(255, 255, 255) !important;
    }
    
    .dark .chatbot-ai-name-tag {
        color: rgba(255, 255, 255, 0.60) !important;
    }
    
    .dark .chatbot-user-content,
    .dark .chatbot-assistant-content {
        color: white !important;
    }

    .dark .chatbot-user-timestamp {
        color: rgba(255, 255, 255, 0.80) !important;
    }
    .dark .chatbot-assistant-timestamp {
        color: rgba(255, 255, 255, 0.60) !important;
    }
    
    /* ===== MARKDOWN CONTENT STYLING ===== */
    /* Ensure markdown elements inherit message content colors */
    .chatbot-user-content p,
    .chatbot-user-content div,
    .chatbot-user-content span,
    .chatbot-user-content strong,
    .chatbot-user-content em,
    .chatbot-user-content ul,
    .chatbot-user-content ol,
    .chatbot-user-content li {
        color: inherit !important;
    }
    
    .chatbot-assistant-content p,
    .chatbot-assistant-content div,
    .chatbot-assistant-content span,
    .chatbot-assistant-content strong,
    .chatbot-assistant-content em,
    .chatbot-assistant-content ul,
    .chatbot-assistant-content ol,
    .chatbot-assistant-content li {
        color: inherit !important;
    }
    
    /* Remove bottom margins/padding from last elements to eliminate white space */
    .chatbot-user-content > *:last-child,
    .chatbot-assistant-content > *:last-child {
        margin-bottom: 0 !important;
        padding-bottom: 0 !important;
    }
    
    /* Remove top margins from first elements for consistency */
    .chatbot-user-content > *:first-child,
    .chatbot-assistant-content > *:first-child {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }
    
    /* Ensure proper spacing for multiple paragraphs while removing excess */
    .chatbot-user-content p,
    .chatbot-assistant-content p {
        margin: 0 0 0.5rem 0 !important;
        line-height: 1.4 !important;
    }
    
    .chatbot-user-content p:last-child,
    .chatbot-assistant-content p:last-child {
        margin-bottom: 0 !important;
    }
    
    /* Control line breaks and spacing */
    .chatbot-user-content br,
    .chatbot-assistant-content br {
        line-height: 1.2 !important;
    }
    
    /* Reduce spacing for lists */
    .chatbot-user-content ul,
    .chatbot-assistant-content ul,
    .chatbot-user-content ol,
    .chatbot-assistant-content ol {
        margin: 0.25rem 0 !important;
        padding-left: 1rem !important;
    }
    
    .chatbot-user-content li,
    .chatbot-assistant-content li {
        margin: 0.1rem 0 !important;
    }
    
    /* Link styling with custom colors and formatting */
    .chatbot-assistant-content a {
        color: #00AE9F !important;
        font-weight: bold !important;
        text-decoration: underline;
        word-break: break-all;
        line-break: anywhere;
        display: inline-block;
        max-width: 100%;
    }
    .dark .chatbot-assistant-content a {
        color: rgb(230 161 53 / var(--tw-bg-opacity, 1)) !important;
    }
    .chatbot-user-content a:hover,
    .chatbot-assistant-content a:hover {
        opacity: 0.8;
        text-decoration: none;
    }
    
/* ===== SINGLE EMOJI MESSAGE STYLING ===== */

/* Emoji message container - no background, no border */
.chatbot-emoji-message {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    padding: 0.5rem 1rem !important;
    max-width: none !important;
    border-radius: 0 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
}

/* Emoji content - large size, centered */
.chatbot-emoji-content {
    font-size: 4.5rem !important;
    line-height: 1 !important;
    text-align: center !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 5rem !important;
}

/* Emoji timestamp styling */
.chatbot-emoji-message .chatbot-user-timestamp,
.chatbot-emoji-message .chatbot-assistant-timestamp {
    text-align: center !important;
    margin-top: 0.25rem !important;
    font-size: 0.7rem !important;
    opacity: 0.7 !important;
    color: inherit !important;
}

/* Sticker content styling - display images larger */
.chatbot-sticker-content {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 5rem !important;
}

.chatbot-sticker-content img {
    max-width: 200px !important;
    max-height: 200px !important;
    width: auto !important;
    height: auto !important;
    object-fit: contain !important;
    border-radius: 0.5rem !important;
}

/* GIF content styling - display images larger with border radius */
.chatbot-gif-content {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-height: 5rem !important;
}

.chatbot-gif-content img {
    max-width: 200px !important;
    max-height: 200px !important;
    width: auto !important;
    height: auto !important;
    object-fit: contain !important;
    border-radius: 0.5rem !important;
}

/* Apply border radius to GIF images in chat messages */
.chatbot-user-content img[src*="/gifs/"],
.chatbot-assistant-content img[src*="/gifs/"] {
    border-radius: 0.5rem !important;
}

/* Active category icon color */
emoji-picker {
    --rgb-accent: 251, 180, 62; /* primary-500 / amber-500 for active elements */
    --rgb-input: 249, 250, 251;
}

/* Emoji picker custom styling for dark mode */
.dark #emoji-picker-container {
    border: 1px solid rgb(63, 63, 70) !important; /* zinc-700 border */
    border-radius: 12px !important; /* rounded corners */
    overflow: hidden !important; /* ensure border-radius works */
}

.dark emoji-picker {
    --background-rgb: 39, 39, 42; /* zinc-800 */
    --rgb-background: 39, 39, 42; /* zinc-800 for various background elements */
    --rgb-input: 63, 63, 70;
}

/* Media selection menu styling - matches online status dropdown */
#media-selection-menu {
    /* shadow-xl is already applied via Tailwind class */
}

/* GIF and Sticker picker styling */
#gif-picker-container,
#sticker-picker-container {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.dark #gif-picker-container,
.dark #sticker-picker-container {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
}

</style>

<!-- Chatbot Widget -->
<div class="fixed bottom-4 right-4 z-[10]">

    <!-- Floating Chat Button -->
    <div class="relative">

        <!-- Chat Icon (shown when chat is closed) -->
        <x-tooltip position="left" text="{{ __('chatbot.action.open_chat') }}">
            <img
                id="chat-icon"
                src="{{ asset('images/chat_closed.webp') }}"
                alt="Chat with Arem"
                onclick="toggleChatbot()"
                loading="lazy"
                fetchpriority="low"
                class="w-auto h-20 cursor-pointer bounce-bounce"
                draggable="false"
            >
        </x-tooltip>

        <!-- Close Icon (shown when chat is open) -->
        <x-tooltip position="left" text="{{ __('chatbot.action.close_chat') }}">
            <img
                id="close-icon"
                src="{{ asset('images/chat_opened.webp') }}"
                alt="Close Chat"
                onclick="toggleChatbot()"
                loading="lazy"
                fetchpriority="low"
                class="w-auto h-20 cursor-pointer hidden"
                draggable="false"
            >
        </x-tooltip>

    </div>

    <!-- Chat Interface -->
    <div id="chatbot-interface" class="absolute bottom-14 right-3 w-[380px] h-[680px] bg-white/65 dark:bg-gray-800/65 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 hidden backdrop-blur-sm">
        <div class="flex flex-col h-full w-full">

        <!-- Header -->
        <div class="bg-primary-600 text-primary-900 px-3 py-1 rounded-t-xl flex items-center justify-between bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/chatbot-bg.png') }}');">
            <div class="flex items-center space-x-3">
                
                <!-- Arem AI Logo -->
                <div class="flex-shrink-0">
                    <img src="{{ asset('images/arem01.png') }}" alt="Arem AI" class="w-20 h-20" loading="lazy" fetchpriority="low" draggable="false">
                </div>

                <!-- Arem AI Text -->
                <div>
                    <h3 class="font-semibold text-base">Arem AI</h3>
                    <p class="text-sm/4 text-primary-900/90 typing-text" id="subheading-text">{{ __('chatbot.header.subheading01') }}<span class="typing-cursor"></span></p>
                </div>
            </div>

            <!-- Close and Clear Buttons -->
            <div class="flex items-center space-x-1">

                <!-- Clear Conversation Button -->
                <x-tooltip position="left" text="{{ __('chatbot.action.clear_conversation') }}">
                    <button
                        onclick="clearConversation()"
                        class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-primary-900 hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    >
                        @svg('heroicon-o-trash', 'w-4 h-4')
                    </button>
                </x-tooltip>

                <!-- Close Chat Button -->
                <x-tooltip position="left" text="{{ __('chatbot.action.close_chat') }}">
                    <button
                        onclick="toggleChatbot()"
                        class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-primary-900 hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    >
                        @svg('heroicon-o-chevron-down', 'w-4 h-4')
                    </button>
                </x-tooltip>

            </div>

        </div>

        <!-- Chat Messages -->
        <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-6 space-y-6 bg-gray-50/50 dark:bg-gray-900/50" id="chat-messages" style="min-height: 180px; overflow-y: scroll; overflow-x: hidden;">
        <!-- Messages will be dynamically loaded here -->
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-5 bg-white dark:bg-gray-800 rounded-b-xl">
            <form onsubmit="sendMessage(event)" class="flex space-x-3" autocomplete="off">

                <!-- Chat Input with Emoji, GIF, and Sticker Button Inside -->
                <div class="flex-1 relative">
                    <input
                        type="text"
                        id="chat-input"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="{{ __('chatbot.input.placeholder') }}"
                        onclick="preventEmojiPickerOnInputClick(event)"
                        class="fi-input w-full pl-20 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white dark:placeholder-gray-400 transition-colors text-sm"
                    >

                    <!-- Input Actions: Command Menu & Media Picker -->
                    <div class="absolute left-3 top-3 flex items-center space-x-1">
                        
                        <!-- Command Menu Button -->
                         <x-tooltip position="top" text="{{ __('chatbot.action.commands') ?? 'Commands' }}">
                            <button
                                type="button"
                                id="command-menu-button"
                                onclick="toggleCommandMenu(event); event.stopPropagation(); event.preventDefault();"
                                class="flex items-center justify-center text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors p-1"
                            >
                                @svg('heroicon-o-command-line', 'w-5 h-5')
                            </button>
                        </x-tooltip>

                        <!-- Emoji, GIF, and Sticker Button -->
                        <x-tooltip position="top" text="{{ __('chatbot.action.add_emojis_gifs_stickers') }}">
                            <button
                                type="button"
                                id="emoji-gif-sticker-button"
                                onclick="toggleMediaMenu(event); event.stopPropagation(); event.preventDefault();"
                                class="flex items-center justify-center text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors p-1"
                            >
                                @svg('heroicon-o-plus-circle', 'w-5 h-5')
                            </button>
                        </x-tooltip>
                    </div>

                </div>

                <!-- Send Message Button -->
                <button
                    type="submit"
                    class="fi-btn fi-btn-size-md 3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white transition-colors px-3"
                >
                    @svg('heroicon-m-paper-airplane', 'w-4 h-4')
                </button>

            </form>
        </div>

    </div>
    
</div>

<!-- Floating Emoji Picker Container -->
<div id="emoji-picker-container" class="fixed hidden z-[11]">
    <emoji-picker id="emoji-picker"></emoji-picker>
</div>

<!-- Command Selection Menu -->
<div id="command-selection-menu" class="fixed hidden bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 z-[12] w-[240px] max-h-80 overflow-y-auto flex flex-col py-1">
    <button type="button" onclick="executeCommand('/help', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-question-mark-circle', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Help</span>
            <span class="text-[10px] text-gray-400">/help</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/mytask', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-clipboard-document-list', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">My Tasks</span>
            <span class="text-[10px] text-gray-400">/mytask</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/myissue', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-bug-ant', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">My Issues</span>
            <span class="text-[10px] text-gray-400">/myissue</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/mywishlist', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-heart', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">My Wishlist</span>
            <span class="text-[10px] text-gray-400">/mywishlist</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/client', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-building-office', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Clients</span>
            <span class="text-[10px] text-gray-400">/client</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/project', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-briefcase', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Projects</span>
            <span class="text-[10px] text-gray-400">/project</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/document', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-document-text', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Documents</span>
            <span class="text-[10px] text-gray-400">/document</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/important-url', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-link', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Important URLs</span>
            <span class="text-[10px] text-gray-400">/important-url</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/phone-number', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover-bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-phone', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Phone Numbers</span>
            <span class="text-[10px] text-gray-400">/phone-number</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/user', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-user-group', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Users</span>
            <span class="text-[10px] text-gray-400">/user</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/meeting-link', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-calendar-days', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Meeting Links</span>
            <span class="text-[10px] text-gray-400">/meeting-link</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/event', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-sparkles', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Events</span>
            <span class="text-[10px] text-gray-400">/event</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/resources', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-archive-box', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Resources</span>
            <span class="text-[10px] text-gray-400">/resources</span>
        </div>
    </button>
    <button type="button" onclick="executeCommand('/trello-board', event);" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors flex items-center gap-3">
        @svg('heroicon-m-view-columns', 'w-4 h-4 text-primary-500')
        <div class="flex flex-col">
            <span class="font-medium">Trello Boards</span>
            <span class="text-[10px] text-gray-400">/trello-board</span>
        </div>
    </button>
</div>

<!-- Floating GIF Picker Container -->
<div id="gif-picker-container" class="fixed hidden z-[11] bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 w-[288px] h-[435px] flex flex-col">
    @php
        $gifGroups = [];
        $gifsPath = public_path('gifs');
        
        if (file_exists($gifsPath)) {
            $items = scandir($gifsPath);
            
            // First pass for root files (General)
            $rootGifs = [];
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $fullPath = $gifsPath . '/' . $item;
                
                if (is_file($fullPath)) {
                    if (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), ['gif', 'webp', 'mp4', 'webm'])) {
                        $rootGifs[] = $item;
                    }
                } elseif (is_dir($fullPath)) {
                    // It's a directory, scan it
                    $subFiles = scandir($fullPath);
                    $groupGifs = [];
                    foreach ($subFiles as $subFile) {
                        if (in_array(strtolower(pathinfo($subFile, PATHINFO_EXTENSION)), ['gif', 'webp', 'mp4', 'webm'])) {
                            // Store relative path from gifs/ folder
                            $groupGifs[] = $item . '/' . $subFile;
                        }
                    }
                    if (!empty($groupGifs)) {
                        $gifGroups[$item] = $groupGifs;
                    }
                }
            }
            
            // Add General GIFs if any
            if (!empty($rootGifs)) {
                $gifGroups = array_merge(['General' => $rootGifs], $gifGroups);
            }
        }
    @endphp

    @if(count($gifGroups) > 0)
        <div 
            x-data="stickerAccordion({{ json_encode(array_keys($gifGroups)) }}, '{{ array_key_first($gifGroups) }}')"
            class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            @foreach($gifGroups as $groupName => $gifs)
                <div class="mb-4 last:mb-0">
                    <button 
                        @click="activeSection = (activeSection === '{{ $groupName }}' ? null : '{{ $groupName }}')"
                        type="button"
                        class="w-full flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-150"
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ $groupName }}</span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal normal-case">({{ count($gifs) }})</span>
                        </div>
                        <div :class="{ 'rotate-180': activeSection === '{{ $groupName }}' }" class="transition-transform duration-200">
                            @svg('heroicon-m-chevron-down', 'w-4 h-4')
                        </div>
                    </button>
                    
                    <div x-show="activeSection === '{{ $groupName }}'" 
                         x-collapse>
                        <div class="grid grid-cols-3 gap-2 pl-1">
                            @foreach($gifs as $gif)
                                <div class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors flex items-center justify-center aspect-square" 
                                     onclick="sendGif('{{ $gif }}')">
                                    <img src="{{ asset('gifs/' . $gif) }}" alt="GIF" class="w-full h-full object-cover pointer-events-none rounded-lg" loading="lazy" draggable="false">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="w-full flex-1 flex items-center justify-center text-gray-400 dark:text-gray-500 p-4">
            <div class="text-center">
                <p class="text-lg mb-2">🎬</p>
                <p class="text-sm">{{ __('chatbot.action.select_gifs') }}</p>
                <p class="text-xs mt-2 opacity-75">No GIFs found</p>
            </div>
        </div>
    @endif

    <!-- GIF Request Footer -->
    <div class="border-t border-gray-200 dark:border-gray-700 p-2 text-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg">
        <p>{!! str_replace(':link', '<a href="https://github.com/Amirul96shafiq/cheqqme-data-center/discussions/6" target="_blank" class="text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400 font-medium hover:underline">' . __('chatbot.footer.link_text') . '</a>', __('chatbot.footer.gif_request')) !!}</p>
    </div>
</div>

<!-- Sticker Accordion Alpine.js Component -->
<script>
    function stickerAccordion(validGroups, firstGroup) {
        return {
            activeSection: null,
            init() {
                // Get user-specific localStorage key
                const userId = window.chatbotUserId || 'anonymous';
                const storageKey = `chatbot_sticker_accordion_${userId}`;
                
                // Load saved accordion section from localStorage
                const savedSection = localStorage.getItem(storageKey);
                
                // Use saved section if it exists and is valid, otherwise use first group
                this.activeSection = savedSection && validGroups.includes(savedSection) 
                    ? savedSection 
                    : firstGroup;
                
                // Watch for changes and save to localStorage
                this.$watch('activeSection', (value) => {
                    localStorage.setItem(storageKey, value || '');
                });
            }
        };
    }
</script>

<!-- Floating Sticker Picker Container -->
<div id="sticker-picker-container" class="fixed hidden z-[11] bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 w-[288px] h-[435px] flex flex-col">
    @php
        $stickerGroups = [];
        $stickersPath = public_path('stickers');
        
        if (file_exists($stickersPath)) {
            $items = scandir($stickersPath);
            
            // First pass for root files (General)
            $rootStickers = [];
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                
                $fullPath = $stickersPath . '/' . $item;
                
                if (is_file($fullPath)) {
                    if (in_array(strtolower(pathinfo($item, PATHINFO_EXTENSION)), ['webp', 'png', 'jpg', 'gif'])) {
                        $rootStickers[] = $item;
                    }
                } elseif (is_dir($fullPath)) {
                    // It's a directory, scan it
                    $subFiles = scandir($fullPath);
                    $groupStickers = [];
                    foreach ($subFiles as $subFile) {
                        if (in_array(strtolower(pathinfo($subFile, PATHINFO_EXTENSION)), ['webp', 'png', 'jpg', 'gif'])) {
                            // Store relative path from stickers/ folder
                            $groupStickers[] = $item . '/' . $subFile;
                        }
                    }
                    if (!empty($groupStickers)) {
                        $stickerGroups[$item] = $groupStickers;
                    }
                }
            }
            
            // Add General stickers if any
            if (!empty($rootStickers)) {
                $stickerGroups = array_merge(['General' => $rootStickers], $stickerGroups);
            }
        }
    @endphp

    @if(count($stickerGroups) > 0)
        <div 
            x-data="stickerAccordion({{ json_encode(array_keys($stickerGroups)) }}, '{{ array_key_first($stickerGroups) }}')"
            class="flex-1 overflow-y-auto p-4 custom-scrollbar">
            @foreach($stickerGroups as $groupName => $stickers)
                <div class="mb-4 last:mb-0">
                    <button 
                        @click="activeSection = (activeSection === '{{ $groupName }}' ? null : '{{ $groupName }}')"
                        type="button"
                        class="w-full flex items-center justify-between text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-2 hover:text-gray-700 dark:hover:text-gray-300 transition-colors duration-150"
                    >
                        <div class="flex items-center gap-2">
                            <span>{{ $groupName }}</span>
                            <span class="text-[10px] text-gray-400 dark:text-gray-500 font-normal normal-case">({{ count($stickers) }})</span>
                        </div>
                        <div :class="{ 'rotate-180': activeSection === '{{ $groupName }}' }" class="transition-transform duration-200">
                            @svg('heroicon-m-chevron-down', 'w-4 h-4')
                        </div>
                    </button>
                    
                    <div x-show="activeSection === '{{ $groupName }}'" 
                         x-collapse>
                        <div class="grid grid-cols-3 gap-2 pl-1">
                            @foreach($stickers as $sticker)
                                <div class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 p-2 rounded-lg transition-colors flex items-center justify-center aspect-square" 
                                     onclick="sendSticker('{{ $sticker }}')">
                                    <img src="{{ asset('stickers/' . $sticker) }}" alt="Sticker" class="w-full h-full object-contain pointer-events-none" loading="lazy" draggable="false">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="w-full h-full flex items-center justify-center text-gray-400 dark:text-gray-500 p-4">
            <div class="text-center">
                <p class="text-lg mb-2">🎨</p>
                <p class="text-sm">{{ __('chatbot.action.select_stickers') }}</p>
                <p class="text-xs mt-2 opacity-75">No stickers found</p>
            </div>
        </div>
    @endif

    <!-- Sticker Request Footer -->
    <div class="border-t border-gray-200 dark:border-gray-700 p-2 text-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/50 rounded-b-lg">
        <p>{!! str_replace(':link', '<a href="https://github.com/Amirul96shafiq/cheqqme-data-center/discussions/5" target="_blank" class="text-primary-600 hover:text-primary-700 dark:text-primary-500 dark:hover:text-primary-400 font-medium hover:underline">' . __('chatbot.footer.link_text') . '</a>', __('chatbot.footer.sticker_request')) !!}</p>
    </div>
</div>

<!-- Media Selection Menu (Fixed Position) -->
<div id="media-selection-menu" class="fixed hidden bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 p-1 z-[12] flex items-center gap-1 w-[288px]">
    <button
        type="button"
        id="media-button-emojis"
        data-media-type="emojis"
        onclick="openMediaPicker('emojis'); event.stopPropagation();"
        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 rounded-md media-button"
    >
        @svg('heroicon-m-face-smile', 'w-4 h-4 text-gray-500 dark:text-gray-400 media-icon')
        <span class="hidden sm:inline">{{ __('chatbot.action.select_emojis') }}</span>
    </button>
    <button
        type="button"
        id="media-button-gifs"
        data-media-type="gifs"
        onclick="openMediaPicker('gifs'); event.stopPropagation();"
        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 rounded-md media-button"
    >
        @svg('heroicon-m-gif', 'w-4 h-4 text-gray-500 dark:text-gray-400 media-icon')
        <span class="hidden sm:inline">{{ __('chatbot.action.select_gifs') }}</span>
    </button>
    <button
        type="button"
        id="media-button-stickers"
        data-media-type="stickers"
        onclick="openMediaPicker('stickers'); event.stopPropagation();"
        class="flex-1 flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 rounded-md media-button"
    >
        @svg('heroicon-m-sparkles', 'w-4 h-4 text-gray-500 dark:text-gray-400 media-icon')
        <span class="hidden sm:inline">{{ __('chatbot.action.select_stickers') }}</span>
    </button>
</div>


<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>


<!-- Lottie Web for animated emojis -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js" integrity="sha512-jEnuDt6jfecCjthQAJ+ed0MTVA++5ZKmlUcmDGBv2vUI/REn6FuIdixLNnQT+vKusE2hhTk2is3cFvv5wA+Sgg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>


<!-- Task Comments CSS -->
@vite('resources/css/task-comments.css')


<!-- Typing Animation Styles -->
@vite('resources/css/typing-animation.css')

<!-- Typing Animation Script -->
@vite('resources/js/typing-animation.js')

<!-- Custom Notification System -->
@vite('resources/js/custom-notifications.js')

<!-- Noto Emoji Animation integration -->
@vite('resources/js/noto-emoji-animation.js')

<!-- Chatbot JavaScript - Deferred loading for performance -->
@vite('resources/js/chatbot.js')

<!-- Chatbot-specific implementation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get subheadings from Laravel language files
    const subheadings = {
        en: [
            @foreach([
                'subheading01',
                'subheading02',
                'subheading03',
                'subheading04',
                'subheading05',
                'subheading06'

            ] as $key)
            '{{ __("chatbot.header.{$key}") }}'{{ !$loop->last ? ',' : '' }}
            @endforeach
        ],
        ms: [
            @php
            $currentLocale = app()->getLocale();
            app()->setLocale('ms');
            @endphp
            @foreach([
                'subheading01',
                'subheading02',
                'subheading03',
                'subheading04',
                'subheading05',
                'subheading06'
            ] as $key)
            '{{ __("chatbot.header.{$key}") }}'{{ !$loop->last ? ',' : '' }}
            @endforeach
            @php
            app()->setLocale($currentLocale);
            @endphp
        ]
    };

    // Detect current language
    const currentLang = document.documentElement.lang || '{{ app()->getLocale() }}';
    const availableSubheadings = subheadings[currentLang] || subheadings.en;
    
    const subheadingElement = document.getElementById('subheading-text');
    if (!subheadingElement) return;

    // Initialize typing animation for chatbot subheading
    const chatbotTyping = new TypingAnimation(subheadingElement, {
        texts: availableSubheadings,
        interval: 10000,
        typeSpeed: { min: 20, max: 40 },
        eraseSpeed: { min: 10, max: 20 },
        pauseBetween: 200,
        randomize: true,
        autoStart: true,
        // onTextChange: (text, index) => {
        //     console.log(`Chatbot subheading changed to: "${text}" (index: ${index})`);
        // }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        chatbotTyping.destroy();
    });
});
</script>
