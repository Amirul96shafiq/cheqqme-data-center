<div class="relative inline-block" x-data="emojiPicker({{ $commentId }})">
    <!-- Emoji Picker Trigger Button -->
    <button 
        type="button"
        class="{{ $triggerClass }} inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 transition-colors duration-200 group"
        @click="toggle()"
        @keydown.enter.prevent="toggle()"
        @keydown.space.prevent="toggle()"
        :aria-expanded="open"
        aria-label="Add reaction"
    >
        <x-heroicon-o-face-smile class="w-4 h-4 text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-gray-200" />
    </button>

    <!-- Overlay Background -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 bg-black bg-opacity-50 z-[9998]"
        style="display: none;"
    ></div>

    <!-- Emoji Picker Dropdown -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.escape.window="close()"
        x-ref="emojiPicker"
        class="fixed w-80 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-[9999]"
        style="display: none;"
        :style="pickerStyle"
    >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Add Reaction</h3>
            <button 
                type="button" 
                @click="close()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors duration-200"
                aria-label="Close"
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
                    placeholder="Search emojis..."
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

        <!-- Emoji Grid -->
        <div class="p-4">
            <div class="grid grid-cols-6 gap-3">
                <template x-for="emojiItem in filteredEmojis" :key="emojiItem.emoji">
                    <button
                        type="button"
                        class="emoji-button w-12 h-12 flex items-center justify-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200 text-2xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                        :data-emoji="emojiItem.emoji"
                        @click="addReaction(emojiItem.emoji)"
                        :class="{ 'bg-primary-100 dark:bg-primary-900': userReactions.includes(emojiItem.emoji) }"
                    >
                        <span x-text="emojiItem.emoji"></span>
                    </button>
                </template>
                
                <!-- No results message -->
                <div x-show="filteredEmojis.length === 0" class="col-span-6 text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No emojis found for "<span x-text="searchQuery"></span>"</p>
                    <button 
                        type="button"
                        @click="clearSearch()"
                        class="mt-2 text-xs text-primary-600 dark:text-primary-400 hover:underline"
                    >
                        Clear search
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <p class="text-xs text-gray-500 dark:text-gray-400">Click an emoji to react</p>
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

        init() {
            this.initializeEmojis();
            this.loadUserReactions();
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
                { emoji: '👎', keywords: ['thumbs down', 'dislike', 'bad', 'disapprove', 'no', 'down'] },
                { emoji: '❤️', keywords: ['heart', 'love', 'red heart', 'like', 'favorite'] },
                { emoji: '😂', keywords: ['laughing', 'funny', 'lol', 'happy', 'joy', 'tears'] },
                { emoji: '😍', keywords: ['heart eyes', 'love', 'adore', 'infatuated', 'attractive'] },
                { emoji: '🤔', keywords: ['thinking', 'ponder', 'consider', 'hmm', 'question'] },
                { emoji: '😢', keywords: ['crying', 'sad', 'tears', 'upset', 'unhappy'] },
                { emoji: '😮', keywords: ['surprised', 'shock', 'wow', 'amazed', 'astonished'] },
                { emoji: '😴', keywords: ['sleeping', 'tired', 'sleepy', 'zzz', 'rest'] },
                { emoji: '🔥', keywords: ['fire', 'hot', 'lit', 'amazing', 'awesome'] },
                { emoji: '💯', keywords: ['100', 'perfect', 'century', 'complete', 'score'] },
                { emoji: '✨', keywords: ['sparkles', 'magic', 'shine', 'beautiful', 'special'] },
                { emoji: '🚀', keywords: ['rocket', 'launch', 'fast', 'speed', 'success'] },
                { emoji: '💪', keywords: ['muscle', 'strong', 'power', 'flex', 'biceps'] },
                { emoji: '👻', keywords: ['ghost', 'spooky', 'halloween', 'scary', 'invisible'] },
                { emoji: '🎉', keywords: ['party', 'celebration', 'confetti', 'happy', 'festive'] },
                { emoji: '👏', keywords: ['clap', 'applause', 'congratulations', 'bravo', 'praise'] },
                { emoji: '🙌', keywords: ['praise', 'hallelujah', 'celebration', 'victory', 'raise hands'] },
                { emoji: '🤝', keywords: ['handshake', 'deal', 'agreement', 'partnership', 'shake'] },
                { emoji: '👌', keywords: ['ok', 'okay', 'good', 'perfect', 'fine', 'alright', 'nice'] },
                { emoji: '❤️‍🩹', keywords: ['mending heart', 'healing', 'recovery', 'broken heart'] },
                { emoji: '🥳', keywords: ['party face', 'celebration', 'birthday', 'festive', 'fun'] },
                { emoji: '😎', keywords: ['cool', 'sunglasses', 'awesome', 'smug', 'confident'] },
                { emoji: '🤩', keywords: ['star eyes', 'amazed', 'impressed', 'fascinated', 'wow'] },
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

            console.log('Comment list container found:', containerRect);
            console.log('Center position calculated:', this.pickerStyle);
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

            console.log('Centered in viewport:', this.pickerStyle);
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
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
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
                    if (data.data.action === 'added') {
                        this.userReactions.push(emoji);
                        this.$dispatch('emojiReactionNotification', {
                            message: 'Reaction added successfully',
                            type: 'success'
                        });
                        this.showCustomNotification('Reaction added successfully', 'success');
                    } else if (data.data.action === 'removed') {
                        this.userReactions = this.userReactions.filter(e => e !== emoji);
                        this.$dispatch('emojiReactionNotification', {
                            message: 'Reaction removed successfully',
                            type: 'info'
                        });
                        this.showCustomNotification('Reaction removed successfully', 'info');
                    }
                    
                    // Refresh the reactions display
                    this.$dispatch('reaction-updated', {
                        commentId: this.commentId,
                        emoji: emoji,
                        action: data.data.action,
                        count: data.data.reaction_count,
                        reaction: data.data.reaction || null
                    });
                    
                    // Also dispatch a global event for the parent component
                    window.dispatchEvent(new CustomEvent('comment-reaction-updated', {
                        detail: {
                            commentId: this.commentId,
                            emoji: emoji,
                            action: data.data.action,
                            count: data.data.reaction_count,
                            reaction: data.data.reaction || null
                        }
                    }));
                    
                    // Force refresh the reactions display directly
                    this.refreshReactionsDisplay();
                    
                    // Close the picker after successfully adding/removing a reaction
                    this.close();
                    
                    // Also try a simple page refresh as fallback (commented out for now)
                    // setTimeout(() => { location.reload(); }, 1000);
                } else {
                    console.error('API Error:', data);
                    this.$dispatch('emojiReactionNotification', {
                        message: data.message || 'Failed to add reaction',
                        type: 'danger'
                    });
                }
            } catch (error) {
                console.error('Failed to add reaction:', error);
                this.$dispatch('emojiReactionNotification', {
                    message: 'Failed to add reaction',
                    type: 'danger'
                });
            } finally {
                this.loading = false;
            }
        },


        async refreshReactionsDisplay() {
            console.log('Refreshing reactions display for comment:', this.commentId);
            
            // Use the global function as a fallback
            if (window.refreshCommentReactions) {
                window.refreshCommentReactions(this.commentId);
                return;
            }
            
            // Fallback to local implementation
            const reactionsContainer = document.querySelector(`[data-comment-id="${this.commentId}"] .comment-reactions`);
            if (!reactionsContainer) {
                console.log('Reactions container not found for comment:', this.commentId);
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
                    console.log('Fresh reactions data:', data.data);
                    
                    // Update the reactions display
                    this.updateReactionsHTML(reactionsContainer, data.data);
                } else {
                    console.error('Failed to fetch reactions:', response.status);
                }
            } catch (error) {
                console.error('Error refreshing reactions:', error);
            }
        },

        updateReactionsHTML(container, reactions) {
            console.log('Updating reactions HTML:', reactions);
            
            // Clear existing reactions (except emoji picker)
            const existingReactions = container.querySelectorAll('.reaction-button');
            existingReactions.forEach(btn => btn.remove());
            
            // Add new reactions
            reactions.forEach(reaction => {
                const button = this.createReactionButton(reaction);
                // Insert before the emoji picker
                const emojiPicker = container.querySelector('.emoji-picker-trigger').parentElement;
                container.insertBefore(button, emojiPicker);
            });
        },

        createReactionButton(reaction) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 ${reaction.user_reacted ? 'bg-primary-100 text-primary-700 border border-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:border-primary-700' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600'}`;
            button.setAttribute('data-emoji', reaction.emoji);
            button.setAttribute('data-count', reaction.count);
            
            // Add tooltip with date and time
            let tooltip = '';
            if (reaction.users.length > 0) {
                const user = reaction.users[0];
                const userName = user.name || user.username || 'Unknown';
                const reactedAt = user.reacted_at ? this.formatDateTime(user.reacted_at) : '';
                
                console.log('Creating tooltip for user:', user);
                console.log('Formatted date:', reactedAt);
                
                tooltip = `${userName}${reactedAt ? ` (${reactedAt})` : ''}`;
                
                if (reaction.users.length > 1) {
                    tooltip += ` and ${reaction.users.length - 1} others`;
                }
            }
            
            console.log('Final tooltip:', tooltip);
            button.setAttribute('title', tooltip);
            
            // Add click handler
            button.addEventListener('click', () => {
                this.addReaction(reaction.emoji);
            });
            
            // Add content
            button.innerHTML = `
                <span class="text-sm">${reaction.emoji}</span>
                <span class="text-xs font-medium">${reaction.count}</span>
            `;
            
            return button;
        },

        showCustomNotification(message, type = 'info') {
            // Use the global notification system
            if (window.showCustomNotification) {
                window.showCustomNotification(message, type);
            } else {
                // Fallback: dispatch a custom event
                this.$dispatch('show-notification', {
                    message: message,
                    type: type
                });
            }
        }
    }
}

// Global function to refresh reactions for any comment
window.refreshCommentReactions = async function(commentId) {
    console.log('Global refresh function called for comment:', commentId);
    
    const reactionsContainer = document.querySelector(`[data-comment-id="${commentId}"] .comment-reactions`);
    if (!reactionsContainer) {
        console.log('Reactions container not found for comment:', commentId);
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
            console.log('Global refresh - Fresh reactions data:', data.data);
            
            // Clear existing reactions (except emoji picker)
            const existingReactions = reactionsContainer.querySelectorAll('.reaction-button');
            existingReactions.forEach(btn => btn.remove());
            
            // Add new reactions
            data.data.forEach(reaction => {
                const button = createReactionButton(reaction, commentId);
                const emojiPicker = reactionsContainer.querySelector('.emoji-picker-trigger').parentElement;
                reactionsContainer.insertBefore(button, emojiPicker);
            });
        } else {
            console.error('Global refresh - Failed to fetch reactions:', response.status);
        }
    } catch (error) {
        console.error('Global refresh - Error refreshing reactions:', error);
    }
};

// Helper function to create reaction button
function createReactionButton(reaction, commentId) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 ${reaction.user_reacted ? 'bg-primary-100 text-primary-700 border border-primary-200 dark:bg-primary-900 dark:text-primary-300 dark:border-primary-700' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600'}`;
    button.setAttribute('data-emoji', reaction.emoji);
    button.setAttribute('data-count', reaction.count);
    
    // Add tooltip
    const tooltip = reaction.users.length > 0 ? 
        `${reaction.users[0].name || reaction.users[0].username || 'Unknown'}${reaction.users.length > 1 ? ` and ${reaction.users.length - 1} others` : ''}` : 
        '';
    button.setAttribute('title', tooltip);
    
    // Add click handler
    button.addEventListener('click', () => {
        // Trigger the emoji picker's addReaction function
        const emojiPickerElement = document.querySelector(`[data-comment-id="${commentId}"] [x-data*="emojiPicker"]`);
        if (emojiPickerElement && emojiPickerElement._x_dataStack && emojiPickerElement._x_dataStack[0]) {
            emojiPickerElement._x_dataStack[0].addReaction(reaction.emoji);
        } else {
            console.log('Emoji picker component not found, falling back to global refresh');
            // Fallback: just refresh the reactions
            window.refreshCommentReactions(commentId);
        }
    });
    
    // Add content
    button.innerHTML = `
        <span class="text-sm">${reaction.emoji}</span>
        <span class="text-xs font-medium">${reaction.count}</span>
    `;
    
    return button;
}
</script>