<div class="relative inline-block" x-data="emojiPicker({{ $commentId }})">
    <!-- Emoji Picker Trigger Button -->
    <button
        type="button"
        class="{{ $triggerClass }} inline-flex items-center justify-center w-8 h-8 rounded-full text-transition-colors duration-100"
        @click="toggle()"
        @keydown.enter.prevent="toggle()"
        @keydown.space.prevent="toggle()"
        :aria-expanded="open"
        aria-label="{{ __('comments.emoji_picker.add_reaction') }}"
    >
        <x-heroicon-o-face-smile class="w-4 h-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors duration-100" />
    </button>

    <!-- Emoji Picker Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.escape.window="close()"
        @click.outside="close()"
        x-ref="emojiPicker"
        class="fixed w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-[9999]"
        style="display: none;"
        :style="pickerStyle"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('comments.emoji_picker.add_reaction') }}</h3>
            <x-close-button
                @click="close()"
                :aria-label="__('comments.emoji_picker.close')"
            />
        </div>

        <!-- Recent Emojis Section -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700" x-show="recentEmojis.length > 0">
            <div class="mb-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('comments.emoji_picker.recent') }}</p>
            </div>
            <div class="flex gap-2 px-2">
                <template x-for="emoji in recentEmojis.slice(0, 5)" :key="emoji">
                    <button
                        type="button"
                        class="flex-shrink-0 w-12 h-12 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 text-2xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :data-emoji="emoji"
                        @click="addReaction(emoji)"
                        :class="{ 'bg-primary-100 dark:bg-primary-900': userReactions.includes(emoji) }"
                        :title="`Recently used: ${emoji}`"
                    >
                        <span x-text="emoji"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Emoji Grid -->
        <div class="p-4">
            <div class="flex gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                <template x-for="emojiItem in allEmojis" :key="emojiItem.emoji">
                    <button
                        type="button"
                        class="emoji-button flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-100 text-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :data-emoji="emojiItem.emoji"
                        @click="addReaction(emojiItem.emoji)"
                        :class="{ 'bg-primary-100 dark:bg-primary-900': userReactions.includes(emojiItem.emoji) }"
                    >
                        <span x-text="emojiItem.emoji"></span>
                    </button>
                </template>

            </div>
        </div>
    </div>
</div>