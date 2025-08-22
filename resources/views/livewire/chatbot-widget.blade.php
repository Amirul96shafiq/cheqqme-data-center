<div class="fixed bottom-4 right-4 z-50">
    <!-- Floating Chat Button -->
    <button 
        wire:click="toggleChatbot"
        class="bg-primary-600 hover:bg-primary-700 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-110 focus:outline-none focus:ring-4 focus:ring-primary-300"
        title="Chat with CheQQme AI Assistant"
    >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
    </button>

    <!-- Chat Interface -->
    @if($isOpen)
        <div class="absolute bottom-20 right-0 w-96 h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col">
            <!-- Header -->
            <div class="bg-primary-600 text-white px-4 py-3 rounded-t-lg flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold">CheQQme AI</h3>
                        <p class="text-xs text-primary-100">Your helpful assistant</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button 
                        wire:click="clearConversation"
                        class="p-1 hover:bg-white/20 rounded transition-colors"
                        title="Clear conversation"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                    <button 
                        wire:click="toggleChatbot"
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
                @foreach($conversation as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs lg:max-w-md {{ $message['role'] === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200' }} rounded-lg px-3 py-2 shadow-sm">
                            <p class="text-sm whitespace-pre-wrap">{{ $message['content'] }}</p>
                            @if(isset($message['timestamp']))
                                <p class="text-xs {{ $message['role'] === 'user' ? 'text-primary-100' : 'text-gray-500 dark:text-gray-400' }} mt-1">
                                    {{ \Carbon\Carbon::parse($message['timestamp'])->format('H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if($isLoading)
                    <div class="flex justify-start">
                        <div class="bg-gray-100 dark:bg-gray-700 rounded-lg px-3 py-2 shadow-sm">
                            <div class="flex items-center space-x-2">
                                <div class="flex space-x-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">CheQQme is thinking...</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($error)
                    <div class="flex justify-start">
                        <div class="bg-red-100 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-3 py-2 shadow-sm">
                            <p class="text-sm text-red-700 dark:text-red-400">{{ $error }}</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Input Area -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <form wire:submit.prevent="sendMessage" class="flex space-x-2">
                    <input 
                        type="text" 
                        wire:model="message" 
                        placeholder="Type your message..."
                        class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                        {{ $isLoading ? 'disabled' : '' }}
                    >
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        {{ $isLoading ? 'disabled' : '' }}
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('scrollToBottom', () => {
        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
});
</script>