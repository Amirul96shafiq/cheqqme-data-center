<!-- Chatbot Widget -->
<div class="fixed bottom-4 right-4 z-[99]">
    <!-- Floating Chat Button -->
    <button
        onclick="toggleChatbot()"
        class="fi-btn fi-btn-color-primary fi-btn-size-md fi-btn-shape-circle p-4 bg-primary-600 hover:bg-primary-500 text-white shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg animate-bounce"
        title="Chat with Arem"
    >
        @svg('heroicon-o-chat-bubble-oval-left-ellipsis', 'w-6 h-6')
    </button>

    <!-- Chat Interface -->
    <div id="chatbot-interface" class="absolute bottom-20 right-0 w-96 h-[500px] bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col hidden">
        <!-- Header -->
        <div class="bg-primary-600 text-white px-6 py-4 rounded-t-xl flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                        @svg('heroicon-o-computer-desktop', 'w-5 h-5')
                    </div>
                </div>
                <div>
                    <h3 class="font-semibold text-base">Arem AI</h3>
                    <p class="text-sm text-primary-100/80">Your brilliant assistant</p>
                </div>
            </div>
            <div class="flex items-center space-x-1">
                <button
                    onclick="clearConversation()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-white hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    title="Clear conversation"
                >
                    @svg('heroicon-o-trash', 'w-4 h-4')
                </button>
                <button
                    onclick="toggleChatbot()"
                    class="fi-btn fi-btn-size-sm fi-btn-color-gray fi-btn-variant-ghost text-white hover:bg-white/20 focus:bg-white/20 rounded-lg p-2 transition-colors"
                    title="Close chat"
                >
                    @svg('heroicon-o-x-mark', 'w-4 h-4')
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50 dark:bg-gray-900/50" id="chat-messages">
            <div class="flex justify-start">
                <div class="fi-section bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 shadow-sm max-w-[80%]">
                    <p class="text-sm text-gray-800 dark:text-gray-200">Type anything to start a new conversation!</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-6 bg-white dark:bg-gray-800 rounded-b-xl">
            <form onsubmit="sendMessage(event)" class="flex space-x-3" autocomplete="off">
                <div class="flex-1">
                    <input
                        type="text"
                        id="chat-input"
                        autocomplete="off"
                        autocorrect="off"
                        spellcheck="false"
                        placeholder="Chat here..."
                        class="fi-input w-full px-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:text-white dark:placeholder-gray-400 transition-colors"
                    >
                </div>
                <button
                    type="submit"
                    class="fi-btn fi-btn-color-primary fi-btn-size-md px-4 py-3 rounded-lg focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                >
                    @svg('heroicon-o-paper-airplane', 'w-5 h-5')
                </button>
            </form>
        </div>
    </div>
</div>
