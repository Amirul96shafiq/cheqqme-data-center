<!-- Chatbot Widget -->
<script>
    window.chatbotUserName = "{{ $userName ?? 'You' }}";
    window.chatbotUserId = "{{ Auth::id() ?? 'anonymous' }}";
    window.chatbot = {
        welcome_message: "{{ __('chatbot.welcome_message') }}",
        ai_name: "{{ __('chatbot.ai_name') }}",
        help_message: "{{ __('chatbot.help_message') }}",
        help_command: "{{ __('chatbot.help_command') }}",
        ready_message: "{{ __('chatbot.ready_message') }}",
        thinking_message: "{{ __('chatbot.thinking_message') }}",
        error_message: "{{ __('chatbot.error_message') }}",
        clearing_message: "{{ __('chatbot.clearing_message') }}",
    };
</script>
<style>
    /* ===== CHATBOT BASE STYLES ===== */
    
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
    @media (max-width: 420px) {
        #chatbot-interface {
            width: calc(100vw - 20px);
            left: 10px;
            right: 10px;
            height: auto;
        }
    }
    
    /* Open chatbot interface */
    #chatbot-interface.open {
        transform: translateY(0);
        opacity: 1;
        animation: chatEntrance 260ms ease;
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
    
    /* User timestamp: white */
    .chatbot-user-timestamp {
        color: white !important;
        font-size: 0.7rem;
        margin-top: 0.5rem;
        font-weight: 500;
    }
    
    /* AI timestamp: #00000050 */
    .chatbot-assistant-timestamp {
        color: #00000080 !important;
        font-size: 0.7rem;
        margin-top: 0.5rem;
        font-weight: 500;
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

    .dark .chatbot-user-timestamp,
    .dark .chatbot-assistant-timestamp {
        color: rgba(255, 255, 255, 0.80) !important;
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
    
    /* Floating emoji picker styling */
    #emoji-picker-container {
        z-index: 1000;
        transition: opacity 0.2s ease, transform 0.2s ease;
        position: fixed;
        pointer-events: auto;
    }
    
    #emoji-picker {
        width: 400px !important;
        max-width: 400px !important;
        border-radius: 0.75rem !important;
        border: 1px solid rgb(229 231 235) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
        backdrop-filter: blur(10px) !important;
    }
    
    .dark #emoji-picker {
        border-color: rgb(75 85 99) !important;
        background-color: rgb(39 39 42) !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.1) !important;
    }
    
    /* Emoji button styling */
    #emoji-button {
        transition: all 0.2s ease;
    }
    
    #emoji-button:hover {
        transform: scale(1.05);
    }
    
    /* Mobile responsive adjustments */
    @media (max-width: 768px) {
        #emoji-picker {
            width: 280px !important;
            max-width: 280px !important;
        }
    }
    
    /* Emoji picker favorites section styling */
    #emoji-picker .favorites,
    emoji-picker .favorites,
    .favorites.onscreen.emoji-menu {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }
    
    /* More specific targeting for emoji picker elements */
    #emoji-picker *[class*="favorites"],
    emoji-picker *[class*="favorites"] {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }
    
    /* Target the specific element from the developer console */
    #emoji-picker [role="menu"][data-on-click="onEmojiClick"][class*="favorites"],
    emoji-picker [role="menu"][data-on-click="onEmojiClick"][class*="favorites"] {
        padding-top: 12px !important;
        padding-bottom: 12px !important;
    }
    
    /* Emoji picker hover effects */
    emoji-picker button:hover,
    emoji-picker [role="button"]:hover,
    emoji-picker [role="menuitem"]:hover,
    emoji-picker .emoji:hover,
    emoji-picker [data-emoji]:hover,
    emoji-picker .category-emoji:hover {
        background-color: rgb(55 65 81) !important;
        background: rgb(55 65 81) !important;
        transform: scale(1.05) !important;
        transition: all 0.2s ease !important;
        border-radius: 8px !important;
        cursor: pointer !important;
    }
    
    /* Emoji picker focus effects */
    emoji-picker button:focus,
    emoji-picker [role="button"]:focus,
    emoji-picker [role="menuitem"]:focus,
    emoji-picker .emoji:focus,
    emoji-picker [data-emoji]:focus,
    emoji-picker .category-emoji:focus {
        background-color: rgb(55 65 81) !important;
        background: rgb(55 65 81) !important;
        outline: 2px solid rgb(59 130 246) !important;
        outline-offset: 2px !important;
        border-radius: 8px !important;
    }
    
    /* Emoji picker active effects */
    emoji-picker button:active,
    emoji-picker [role="button"]:active,
    emoji-picker [role="menuitem"]:active,
    emoji-picker .emoji:active,
    emoji-picker [data-emoji]:active,
    emoji-picker .category-emoji:active {
        background-color: rgb(75 85 99) !important;
        background: rgb(75 85 99) !important;
        transform: scale(0.95) !important;
        border-radius: 8px !important;
    }
</style>
<!-- Chatbot Widget -->
<div class="fixed bottom-4 right-4 z-[99]">
        <!-- Floating Chat Button -->
    <div class="relative">
        <!-- Chat Icon (shown when chat is closed) -->
        <img
            id="chat-icon"
            src="{{ asset('images/chat.png') }}"
            alt="Chat with Arem"
            onclick="toggleChatbot()"
            title="Chat with Arem"
            class="w-14 h-14 shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer rounded-lg opacity-80 hover:opacity-100"
        >
        <!-- Close Icon (shown when chat is open) -->
        <div
            id="close-icon"
            onclick="toggleChatbot()"
            title="Close chat"
            class="w-14 h-14 shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer rounded-lg bg-primary-600 hover:bg-primary-500 text-white hidden"
        >
            <div class="flex items-center justify-center w-full h-full">
                @svg('heroicon-o-x-mark', 'w-8 h-8')
            </div>
        </div>
    </div>

    <!-- Chat Interface -->
    <div id="chatbot-interface" class="absolute bottom-24 right-0 w-[380px] h-[680px] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 hidden">
        <div class="flex flex-col h-full w-full">
        <!-- Header -->
        <div class="bg-primary-600 text-white px-3 py-1 rounded-t-xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- Arem AI Logo -->
                <div class="flex-shrink-0">
                    <img src="{{ asset('images/arem01.png') }}" alt="Arem AI" class="w-20 h-20">
                </div>
                <!-- Arem AI Text -->
                <div>
                    <h3 class="font-semibold text-base">Arem AI</h3>
                    <p class="text-sm text-primary-100/80">Your brilliant assistant</p>
                </div>
            </div>
            <!-- Close and Clear Buttons -->
            <div class="flex items-center space-x-1">
                <!-- Clear Conversation Button -->
                <button
                    onclick="clearConversation()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-white hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    title="Clear conversation"
                >
                    @svg('heroicon-o-trash', 'w-4 h-4')
                </button>
                <!-- Close Chat Button -->
                <button
                    onclick="toggleChatbot()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-white hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    title="Close chat"
                >
                    @svg('heroicon-o-chevron-down', 'w-4 h-4')
                </button>
            </div>
        </div>
        <!-- Chat Messages -->
        <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden p-6 space-y-4 bg-gray-50/50 dark:bg-gray-900/50" id="chat-messages" style="min-height: 180px; overflow-y: scroll; overflow-x: hidden;">
            <!-- Messages will be dynamically loaded here -->
        </div>
        <!-- Input Area -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-5 bg-white dark:bg-gray-800 rounded-b-xl">
            <form onsubmit="sendMessage(event)" class="flex space-x-3" autocomplete="off">
                <!-- Emoji Button -->
                <button
                    type="button"
                    id="emoji-button"
                    onclick="toggleEmojiPicker()"
                    class="flex-shrink-0 w-10 h-10 flex items-center justify-center text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700"
                    title="Add emoji"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 100-2 1 1 0 000 2zm7-1a1 1 0 11-2 0 1 1 0 012 0zm-7.536 5.879a1 1 0 001.415 0 3 3 0 014.242 0 1 1 0 001.415-1.415 5 5 0 00-7.072 0 1 1 0 000 1.415z" clip-rule="evenodd"></path>
                    </svg>
                </button>
                <!-- Chat Input -->
                <div class="flex-1">
                    <input
                        type="text"
                        id="chat-input"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="Chat here."
                        class="fi-input w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white dark:placeholder-gray-400 transition-colors text-sm"
                    >
                </div>
                <!-- Send Message Button -->
                <button
                    type="submit"
                    class="fi-btn fi-btn-color-primary fi-btn-size-md rounded-lg focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                >
                    @svg('heroicon-o-paper-airplane', 'w-5 h-5')
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Floating Emoji Picker Container -->
<div id="emoji-picker-container" class="fixed hidden z-[1000]">
    <emoji-picker id="emoji-picker"></emoji-picker>
</div>

<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!-- Emoji Picker Element -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<!-- Custom Emoji Picker Styles -->
<link rel="stylesheet" href="{{ asset('css/emoji-picker-custom.css') }}">

<!-- Chatbot JavaScript -->
@vite('resources/js/chatbot.js')
