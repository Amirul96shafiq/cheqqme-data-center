<!-- Chatbot Widget -->
<div class="fixed bottom-4 right-4 z-[99]">
    <!-- Floating Chat Button -->
    <button
        onclick="toggleChatbot()"
        class="bg-primary-600 hover:bg-primary-700 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-300"
        title="Chat with Arem"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
    </button>

    <!-- Chat Interface -->
    <div id="chatbot-interface" class="absolute bottom-20 right-0 w-96 h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col hidden">
        <!-- Header -->
        <div class="bg-primary-600 text-white px-4 py-3 rounded-t-lg flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div>
                    <h3 class="font-semibold">Arem AI</h3>
                    <p class="text-xs text-primary-100">Your brilliant assistant</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button
                    onclick="clearConversation()"
                    class="p-1 hover:bg-white/20 rounded transition-colors"
                    title="Clear conversation"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <button
                    onclick="toggleChatbot()"
                    class="p-1 hover:bg-white/20 rounded transition-colors"
                    title="Close chat"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chat-messages">
            <div class="flex justify-start">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2 shadow-sm">
                    <p class="text-sm text-gray-800 dark:text-gray-200">Type anything to start a new conversation!</p>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <form onsubmit="sendMessage(event)" class="flex space-x-2" autocomplete="off">
                <input
                    type="text"
                    id="chat-input"
                    autocomplete="off"
                    autocorrect="off"
                    spellcheck="false"
                    placeholder="Chat here"
                    class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                >
                <button
                    type="submit"
                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>
