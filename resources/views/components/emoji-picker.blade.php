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
            <button 
                type="button" 
                @click="close()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors duration-100"
                aria-label="{{ __('comments.emoji_picker.close') }}"
            >
                <x-heroicon-o-x-mark class="w-4 h-4" />
            </button>
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
            <div class="flex gap-2 flex-wrap justify-center">
                <template x-for="emoji in recentEmojis.slice(0, 6)" :key="emoji">
                    <button
                        type="button"
                        class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 text-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
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
                        class="emoji-button w-12 h-12 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-100 text-2xl focus:outline-none focus:ring-2 focus:ring-primary-500"
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

<script>
function emojiPicker(commentId) {
    return {
        commentId: commentId,
        open: false,
        userReactions: [],
        loading: false,
        pickerStyle: {},
        searchQuery: '',
        allEmojis: [],
        filteredEmojis: [],
        recentEmojis: [],

        init() {
            this.initializeEmojis();
            this.loadUserReactions();
            this.loadRecentEmojis();
            
            // Listen for Livewire updates to refresh component state
            this.$watch('commentId', () => {
                this.loadUserReactions();
            });
        },


        toggle() {
            if (!this.open) {
                this.calculateCenterPosition();
            }
            
            this.open = !this.open;
        },

        close() {
            this.open = false;
        },

        formatDateTime(dateTimeString) {
            try {
                const date = new Date(dateTimeString);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = String(date.getFullYear()).slice(-2); // Get last 2 digits of year
                
                let hours = date.getHours();
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12; // 0 should be 12
                const timeStr = `${hours}:${minutes} ${ampm}`;
                
                return `${day}/${month}/${year} • ${timeStr}`;
            } catch (error) {
                console.error('Error formatting date:', error);
                return '';
            }
        },

        initializeEmojis() {
            // Define a comprehensive list of emojis with search terms
            this.allEmojis = [
                { emoji: '👍', keywords: ['thumbs up', 'like', 'good', 'approve', 'yes', 'up'] },
                { emoji: '❤️', keywords: ['heart', 'love', 'red heart', 'like', 'favorite'] },
                { emoji: '😂', keywords: ['laughing', 'funny', 'lol', 'happy', 'joy', 'tears'] },
                { emoji: '😍', keywords: ['heart eyes', 'love', 'adore', 'infatuated', 'attractive'] },
                { emoji: '🤔', keywords: ['thinking', 'ponder', 'consider', 'hmm', 'question'] },
                { emoji: '😢', keywords: ['crying', 'sad', 'tears', 'upset', 'unhappy'] },
                { emoji: '😮', keywords: ['surprised', 'shock', 'wow', 'amazed', 'astonished'] },
                { emoji: '🔥', keywords: ['fire', 'hot', 'lit', 'amazing', 'awesome'] },
                { emoji: '💯', keywords: ['100', 'perfect', 'century', 'complete', 'score'] },
                { emoji: '🚀', keywords: ['rocket', 'launch', 'fast', 'speed', 'success'] },
                { emoji: '💪', keywords: ['muscle', 'strong', 'power', 'flex', 'biceps'] },
                { emoji: '🎉', keywords: ['party', 'celebration', 'confetti', 'happy', 'festive'] },
                { emoji: '👏', keywords: ['clap', 'applause', 'congratulations', 'bravo', 'praise'] },
                { emoji: '🙌', keywords: ['praise', 'hallelujah', 'celebration', 'victory', 'raise hands'] },
                { emoji: '🤝', keywords: ['handshake', 'deal', 'agreement', 'partnership', 'shake'] },
                { emoji: '👌', keywords: ['ok', 'okay', 'good', 'perfect', 'fine', 'alright', 'nice'] },
                { emoji: '🥳', keywords: ['party face', 'celebration', 'birthday', 'festive', 'fun'] },
                { emoji: '😎', keywords: ['cool', 'sunglasses', 'awesome', 'smug', 'confident'] },
            ];
            
            // Initialize filtered emojis with all emojis
            this.filteredEmojis = [...this.allEmojis];
        },

        filterEmojis() {
            if (!this.searchQuery.trim()) {
                this.filteredEmojis = [...this.allEmojis];
                return;
            }
            
            const query = this.searchQuery.toLowerCase();
            this.filteredEmojis = this.allEmojis.filter(item => 
                item.keywords.some(keyword => keyword.toLowerCase().includes(query))
            );
        },

        clearSearch() {
            this.searchQuery = '';
            this.filterEmojis();
        },

        loadRecentEmojis() {
            try {
                const userId = window.chatbotUserId || 'anonymous';
                const recentKey = `recent_emojis_${userId}`;
                const stored = localStorage.getItem(recentKey);
                this.recentEmojis = stored ? JSON.parse(stored) : [];
            } catch (error) {
                console.error('Failed to load recent emojis:', error);
                this.recentEmojis = [];
            }
        },

        saveRecentEmojis() {
            try {
                const userId = window.chatbotUserId || 'anonymous';
                const recentKey = `recent_emojis_${userId}`;
                localStorage.setItem(recentKey, JSON.stringify(this.recentEmojis));
            } catch (error) {
                console.error('Failed to save recent emojis:', error);
            }
        },

        addToRecentEmojis(emoji) {
            // Remove emoji if it already exists
            this.recentEmojis = this.recentEmojis.filter(e => e !== emoji);
            // Add to beginning of array
            this.recentEmojis.unshift(emoji);
            // Keep only last 20 emojis
            this.recentEmojis = this.recentEmojis.slice(0, 20);
            // Save to localStorage
            this.saveRecentEmojis();
        },

        calculateCenterPosition() {
            // Find the comment listing container
            const commentListContainer = document.querySelector('[data-comment-list]');
            if (!commentListContainer) {
                console.log('Comment list container not found, using viewport center');
                this.centerInViewport();
                return;
            }

            // Get the container's position and dimensions
            const containerRect = commentListContainer.getBoundingClientRect();
            const pickerWidth = 320; // w-80 = 20rem = 320px
            const pickerHeight = 380; // Approximate height with larger emojis, search, and footer

            // Calculate center position
            const centerX = containerRect.left + (containerRect.width / 2) - (pickerWidth / 2);
            const centerY = containerRect.top + (containerRect.height / 2) - (pickerHeight / 2);

            // Ensure the picker stays within the viewport
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;
            
            let finalX = Math.max(10, Math.min(centerX, viewportWidth - pickerWidth - 10));
            let finalY = Math.max(10, Math.min(centerY, viewportHeight - pickerHeight - 10));

            this.pickerStyle = {
                position: 'fixed',
                top: `${finalY}px`,
                left: `${finalX}px`,
                zIndex: 9999
            };

        },

        centerInViewport() {
            // Fallback: center in viewport if comment list container not found
            const pickerWidth = 320; // w-80 = 20rem = 320px
            const pickerHeight = 380; // Approximate height with larger emojis, search, and footer
            const viewportWidth = window.innerWidth;
            const viewportHeight = window.innerHeight;

            const centerX = (viewportWidth / 2) - (pickerWidth / 2);
            const centerY = (viewportHeight / 2) - (pickerHeight / 2);

            this.pickerStyle = {
                position: 'fixed',
                top: `${centerY}px`,
                left: `${centerX}px`,
                zIndex: 9999
            };

        },






        async loadUserReactions() {
            try {
                const response = await fetch(`/api/comments/${this.commentId}/reactions`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.userReactions = data.data
                        .filter(reaction => reaction.user_reacted)
                        .map(reaction => reaction.emoji);
                }
            } catch (error) {
                console.error('Failed to load user reactions:', error);
            }
        },

        async addReaction(emoji) {
            if (this.loading) return;

            this.loading = true;
            // console.debug('[EmojiPicker] addReaction start', { commentId: this.commentId, emoji });
            
            // Optimistic UI update - immediately update the UI
            try {
                this.updateReactionOptimistically(emoji);
            } catch (e) {
                console.error('[EmojiPicker] updateReactionOptimistically error', e);
            }
            
            // Close the picker immediately for instant feedback
            this.close();
            // console.debug('[EmojiPicker] picker closed after optimistic update', { commentId: this.commentId, emoji });
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                // console.debug('[EmojiPicker] POST /api/comment-reactions', { commentId: this.commentId, emoji, csrfPresent: !!csrfToken });
                
                const response = await fetch('/api/comment-reactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken || ''
                    },
                    body: JSON.stringify({
                        comment_id: this.commentId,
                        emoji: emoji
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    // console.debug('[EmojiPicker] server OK', { data });
                    if (data.data.action === 'added') {
                        this.userReactions.push(emoji);
                        this.addToRecentEmojis(emoji);
                    } else if (data.data.action === 'removed') {
                        this.userReactions = this.userReactions.filter(e => e !== emoji);
                    }
                    
                    // Update with actual server response
                    try {
                        this.updateReactionWithServerData(emoji, data.data);
                    } catch (e) {
                        console.error('[EmojiPicker] updateReactionWithServerData error', e);
                    }
                    
                    // Dispatch event to parent component
                    this.$dispatch('reaction-updated', {
                        commentId: this.commentId,
                        emoji: emoji,
                        action: data.data.action,
                        count: data.data.reaction_count,
                        reaction: data.data.reaction || null
                    });
                    
                    // Also dispatch a global, comment-scoped event for other components
                    const scopedEventName = `comment-reaction-updated-${this.commentId}`;
                    // console.debug('[EmojiPicker] dispatch scoped event', scopedEventName, {
                    //     commentId: this.commentId,
                    //     emoji,
                    //     action: data.data.action,
                    //     count: data.data.reaction_count
                    // });
                    window.dispatchEvent(new CustomEvent(scopedEventName, {
                        detail: {
                            commentId: this.commentId,
                            emoji: emoji,
                            action: data.data.action,
                            count: data.data.reaction_count,
                            reaction: data.data.reaction || null
                        }
                    }));
                    
                    // Picker already closed for instant feedback
                } else {
                    console.error('API Error:', data);
                    // Revert optimistic update on error
                    this.revertOptimisticUpdate(emoji);
                }
            } catch (error) {
                console.error('[EmojiPicker] Failed to add reaction', error);
                // Revert optimistic update on error
                this.revertOptimisticUpdate(emoji);
            } finally {
                this.loading = false;
                // console.debug('[EmojiPicker] addReaction end', { commentId: this.commentId, emoji });
            }
        },

        updateReactionOptimistically(emoji) {
            // console.debug('[EmojiPicker] updateReactionOptimistically', { commentId: this.commentId, emoji });
            // Scope strictly to this comment's reactions container (not replies nested inside)
            const reactionsContainer = this.$el.closest('.comment-reactions');
            if (!reactionsContainer) {
                console.warn('[EmojiPicker] reactionsContainer not found for optimistic update');
            }
            const existingButton = reactionsContainer?.querySelector(`:scope > .reaction-button[data-emoji="${emoji}"]`);
            // console.debug('[EmojiPicker] optimistic existingButton', existingButton);
            
            if (existingButton) {
                // Toggle existing reaction
                const currentCount = parseInt(existingButton.getAttribute('data-count'));
                const isUserReacted = existingButton.classList.contains('bg-primary-100/10');
                // console.debug('[EmojiPicker] optimistic existing', { currentCount, isUserReacted, classes: existingButton.className });
                
                if (isUserReacted) {
                    // Remove reaction
                    const newCount = currentCount - 1;
                    if (newCount === 0) {
                        // console.debug('[EmojiPicker] optimistic remove button (count 0)', { emoji });
                        existingButton.remove();
                    } else {
                        existingButton.setAttribute('data-count', newCount);
                        existingButton.querySelector('.text-xs').textContent = newCount;
                        existingButton.className = existingButton.className.replace('bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700', 'bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600');
                        // console.debug('[EmojiPicker] optimistic updated (removed)', { newCount, classes: existingButton.className });
                    }
                } else {
                    // Add reaction
                    const newCount = currentCount + 1;
                    existingButton.setAttribute('data-count', newCount);
                    existingButton.querySelector('.text-xs').textContent = newCount;
                    existingButton.className = existingButton.className.replace('bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600', 'bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700');
                    // console.debug('[EmojiPicker] optimistic updated (added)', { newCount, classes: existingButton.className });
                }
            } else {
                // Create new reaction button
                const reactionsContainer2 = reactionsContainer;
                // console.debug('[EmojiPicker] optimistic create new button', { hasContainer: !!reactionsContainer2 });
                if (reactionsContainer2) {
                    const newButton = this.createOptimisticReactionButton(emoji, 1);
                    const emojiPicker = reactionsContainer2.querySelector('.emoji-picker-trigger').parentElement;
                    emojiPicker.parentElement.insertBefore(newButton, emojiPicker.nextSibling);
                    // console.debug('[EmojiPicker] optimistic inserted new button', { emoji });
                }
            }
        },

        updateReactionWithServerData(emoji, serverData) {
            console.debug('[EmojiPicker] updateReactionWithServerData', { commentId: this.commentId, emoji, serverData });
            // Update the reaction with actual server data
            const reactionsContainer = this.$el.closest('.comment-reactions');
            const existingButton = reactionsContainer?.querySelector(`:scope > .reaction-button[data-emoji="${emoji}"]`);
            // console.debug('[EmojiPicker] server existingButton', existingButton);
            
            if (serverData.action === 'removed' && serverData.reaction_count === 0) {
                // Remove button if count is 0
                if (existingButton) {
                    // console.debug('[EmojiPicker] server removed button (count 0)', { emoji });
                    existingButton.remove();
                }
            } else if (existingButton) {
                // Update count and styling
                existingButton.setAttribute('data-count', serverData.reaction_count);
                existingButton.querySelector('.text-xs').textContent = serverData.reaction_count;
                
                if (serverData.action === 'added') {
                    existingButton.className = existingButton.className.replace('bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600', 'bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700');
                    // console.debug('[EmojiPicker] server updated (added)', { count: serverData.reaction_count });
                } else {
                    existingButton.className = existingButton.className.replace('bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700', 'bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600');
                    // console.debug('[EmojiPicker] server updated (removed)', { count: serverData.reaction_count });
                }
            }
        },

        revertOptimisticUpdate(emoji) {
            // Revert the optimistic update by refreshing from server
            this.refreshReactionsDisplay();
        },

        createOptimisticReactionButton(emoji, count) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700 cursor-default';
            button.setAttribute('data-emoji', emoji);
            button.setAttribute('data-count', count);
            button.setAttribute('title', 'You (just now)');
            
            button.innerHTML = `
                <span class="text-sm">${emoji}</span>
                <span class="text-xs font-medium">${count}</span>
            `;
            
            return button;
        },

        async refreshReactionsDisplay() {
            // console.debug('[EmojiPicker] refreshReactionsDisplay', { commentId: this.commentId });
            // Use the global function as a fallback
            if (window.refreshCommentReactions) {
                // console.debug('[EmojiPicker] delegating to window.refreshCommentReactions');
                window.refreshCommentReactions(this.commentId);
                return;
            }
            
            // Fallback to local implementation
            const reactionsContainer = this.$el.closest('.comment-reactions');
            if (!reactionsContainer) {
                return;
            }

            try {
                // Fetch fresh reactions from server
                const response = await fetch(`/api/comments/${this.commentId}/reactions`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    // Update the reactions display
                    // console.debug('[EmojiPicker] refreshed reactions from server', { reactions: data.data });
                    this.updateReactionsHTML(reactionsContainer, data.data);
                }
            } catch (error) {
                console.error('[EmojiPicker] Error refreshing reactions', error);
            }
        },

        updateReactionsHTML(container, reactions) {
            // Clear existing reactions (except emoji picker)
            const existingReactions = container.querySelectorAll('.reaction-button');
            existingReactions.forEach(btn => btn.remove());
            
            // Add new reactions after the emoji picker (which is now first)
            reactions.forEach(reaction => {
                const button = this.createReactionButton(reaction);
                // Insert after the emoji picker
                const emojiPicker = container.querySelector('.emoji-picker-trigger').parentElement;
                emojiPicker.parentElement.insertBefore(button, emojiPicker.nextSibling);
            });
        },

        createReactionButton(reaction) {
            return window.createReactionButton(reaction, this.commentId, this.addReaction.bind(this));
        },

    }
}

// Global function to format date and time
window.formatDateTime = function(dateTimeString) {
    try {
        const date = new Date(dateTimeString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = String(date.getFullYear()).slice(-2); // Get last 2 digits of year
        
        let hours = date.getHours();
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        const timeStr = `${hours}:${minutes} ${ampm}`;
        
        return `${day}/${month}/${year} • ${timeStr}`;
    } catch (error) {
        console.error('Error formatting date:', error);
        return '';
    }
};

// Global function to refresh reactions for any comment
window.refreshCommentReactions = async function(commentId) {
    const reactionsContainer = document.querySelector(`[data-comment-id="${commentId}"] .comment-reactions`);
    if (!reactionsContainer) {
        return;
    }

    try {
        const response = await fetch(`/api/comments/${commentId}/reactions`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (response.ok) {
            const data = await response.json();
            
            // Clear existing reactions (except emoji picker)
            const existingReactions = reactionsContainer.querySelectorAll('.reaction-button');
            existingReactions.forEach(btn => btn.remove());
            
            // Add new reactions after the emoji picker (which is now first)
            data.data.forEach(reaction => {
                const button = window.createReactionButton(reaction, commentId);
                const emojiPicker = reactionsContainer.querySelector('.emoji-picker-trigger').parentElement;
                emojiPicker.parentElement.insertBefore(button, emojiPicker.nextSibling);
            });
        }
    } catch (error) {
        console.error('Error refreshing reactions:', error);
    }
};

// Helper function to create reaction button
window.createReactionButton = function(reaction, commentId, addReactionCallback = null) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 ${reaction.user_reacted ? 'bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700 cursor-default' : 'bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600 cursor-default'}`;
    button.setAttribute('data-emoji', reaction.emoji);
    button.setAttribute('data-count', reaction.count);
    
    // Add tooltip with date and time
    let tooltip = '';
    if (reaction.users.length > 0) {
        const user = reaction.users[0];
        const userName = user.name || user.username || 'Unknown';
        const reactedAt = user.reacted_at ? window.formatDateTime(user.reacted_at) : '';
        
        tooltip = `${userName}${reactedAt ? ` (${reactedAt})` : ''}`;
        
        if (reaction.users.length > 1) {
            tooltip += ` and ${reaction.users.length - 1} {{ __("comments.emoji_picker.others") }}`;
        }
    }
    
    button.setAttribute('title', tooltip);
    
    // No click handlers - users can only add reactions through the emoji picker
    
    // Add content
    button.innerHTML = `
        <span class="text-sm">${reaction.emoji}</span>
        <span class="text-xs font-medium">${reaction.count}</span>
    `;
    
    return button;
};
</script>