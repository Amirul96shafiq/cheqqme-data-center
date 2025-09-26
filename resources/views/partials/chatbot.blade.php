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
        bottom: 1rem !important;
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
    
    /* ===== SINGLE EMOJI MESSAGE STYLING ===== */
    
    /* Emoji message container - no background, no border */
    .chatbot-emoji-message {
        background: none !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0.5rem 1rem !important;
        max-width: none !important;
        border-radius: 0 !important;
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
    }
    

</style>
<!-- Chatbot Widget -->
<div class="fixed bottom-4 right-4 z-[10]">
        <!-- Floating Chat Button -->
    <div class="relative">
        <!-- Chat Icon (shown when chat is closed) -->
        <img
            id="chat-icon"
            src="{{ asset('images/chat.png') }}"
            alt="Chat with Arem"
            onclick="toggleChatbot()"
            title="Chat with Arem"
            class="w-12 h-12 shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer rounded-lg opacity-80 hover:opacity-100 bounce-bounce"
            draggable="false"
        >
        <!-- Close Icon (shown when chat is open) -->
        <div
            id="close-icon"
            onclick="toggleChatbot()"
            title="Close chat"
            class="w-12 h-12 shadow-lg hover:shadow-xl transition-all duration-200 cursor-pointer rounded-lg bg-primary-600 hover:bg-primary-500 text-primary-900 hidden"
        >
            <div class="flex items-center justify-center w-full h-full">
                @svg('heroicon-o-x-mark', 'w-8 h-8')
            </div>
        </div>
    </div>

    <!-- Chat Interface -->
    <div id="chatbot-interface" class="absolute bottom-16 right-0 w-[380px] h-[680px] bg-white/65 dark:bg-gray-800/65 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 hidden backdrop-blur-sm">
        <div class="flex flex-col h-full w-full">
        <!-- Header -->
        <div class="bg-primary-600 text-primary-900 px-3 py-1 rounded-t-xl flex items-center justify-between bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/chatbot-bg.png') }}');">
            <div class="flex items-center space-x-3">
                <!-- Arem AI Logo -->
                <div class="flex-shrink-0">
                    <img src="{{ asset('images/arem01.png') }}" alt="Arem AI" class="w-20 h-20" draggable="false">
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
                <button
                    onclick="clearConversation()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-primary-900 hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    title="Clear conversation"
                >
                    @svg('heroicon-o-trash', 'w-4 h-4')
                </button>
                <!-- Close Chat Button -->
                <button
                    onclick="toggleChatbot()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-primary-900 hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
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
                <!-- Chat Input with Emoji Button Inside -->
                <div class="flex-1 relative">
                    <input
                        type="text"
                        id="chat-input"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="Chat here."
                        class="fi-input w-full pl-12 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white dark:placeholder-gray-400 transition-colors text-sm"
                    >
                    <!-- Emoji Button Inside Input -->
                    <button
                        type="button"
                        id="emoji-button"
                        onclick="toggleEmojiPicker()"
                        class="absolute left-3 top-3 flex items-center justify-center text-gray-400 hover:text-primary-500 dark:text-gray-500 dark:hover:text-primary-400 transition-colors"
                        title="Add emoji"
                    >
                        @svg('heroicon-o-face-smile', 'w-5 h-5')
                    </button>
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


<!-- Marked.js for Markdown rendering -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!-- Emoji Picker Element -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<!-- Task Comments CSS -->
@vite('resources/css/task-comments.css')

<!-- Emoji Picker Theme CSS -->
@vite('resources/css/emoji-picker-theme.css')

<!-- Typing Animation Styles -->
@vite('resources/css/typing-animation.css')

<!-- Typing Animation Script -->
@vite('resources/js/typing-animation.js')

<!-- Custom Notification System -->
@vite('resources/js/custom-notifications.js')

<!-- Chatbot JavaScript -->
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
                'subheading05'
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
                'subheading05'
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
        onTextChange: (text, index) => {
            console.log(`Chatbot subheading changed to: "${text}" (index: ${index})`);
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        chatbotTyping.destroy();
    });
});
</script>
