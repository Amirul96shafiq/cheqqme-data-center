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

        <!-- Search Input -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="relative">
                <input
                    type="text"
                    x-model="searchQuery"
                    @input="filterEmojis()"
                    placeholder="{{ __('comments.emoji_picker.search_emojis') }}"
                    class="w-full px-3 py-2 pl-8 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-heroicon-o-magnifying-glass class="w-4 h-4 text-gray-400" />
                </div>
                <button
                    x-show="searchQuery"
                    @click="clearSearch()"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                >
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Recent Emojis Section -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700" x-show="!searchQuery && recentEmojis.length > 0">
            <div class="mb-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('comments.emoji_picker.recent') }}</p>
            </div>
            <div class="grid grid-cols-5 gap-2">
                <template x-for="emoji in recentEmojis.slice(0, 5)" :key="emoji">
                    <button
                        type="button"
                        class="w-full aspect-square flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 text-3xl focus:outline-none focus:ring-2 focus:ring-primary-500"
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
            <div class="grid grid-cols-6 gap-3">
                <template x-for="emojiItem in filteredEmojis" :key="emojiItem.emoji">
                    <button
                        type="button"
                        class="emoji-button w-full aspect-square flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-100 text-2xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :data-emoji="emojiItem.emoji"
                        @click="addReaction(emojiItem.emoji)"
                        :class="{ 'bg-primary-100 dark:bg-primary-900': userReactions.includes(emojiItem.emoji) }"
                    >
                        <span x-text="emojiItem.emoji"></span>
                    </button>
                </template>

                <!-- No results message -->
                <div x-show="filteredEmojis.length === 0" class="col-span-6 text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ __('comments.emoji_picker.no_emojis_found') }} "<span x-text="searchQuery"></span>"</p>
                    <button
                        type="button"
                        @click="clearSearch()"
                        class="mt-2 text-xs text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        {{ __('comments.emoji_picker.clear_search') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>