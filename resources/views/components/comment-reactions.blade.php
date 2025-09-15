<div class="comment-reactions flex flex-wrap gap-2 mt-3" x-data="commentReactions" x-init="commentId = {{ $comment->id }}">
    @php
        $reactions = $getReactions();
    @endphp

    @if($reactions->isNotEmpty())
        @foreach($reactions as $reaction)
            <button
                type="button"
                class="reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 {{ $reaction['user_reacted'] ? 'bg-primary-100/25 text-primary-700 border border-primary-200 dark:bg-primary-900/25 dark:text-primary-300 dark:border-primary-700 cursor-default' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' }}"
                data-emoji="{{ $reaction['emoji'] }}"
                data-count="{{ $reaction['count'] }}"
                @click="addReaction('{{ $reaction['emoji'] }}')"
                title="{{ ($reaction['users'][0]['name'] ?? $reaction['users'][0]['username'] ?? 'Unknown') . ($reaction['users'][0]['reacted_at'] ? ' (' . \Carbon\Carbon::parse($reaction['users'][0]['reacted_at'])->format('d/n/y • g:i A') . ')' : '') }}{{ count($reaction['users']) > 1 ? ' and ' . (count($reaction['users']) - 1) . ' others' : '' }}"
            >
                <span class="text-sm">{{ $reaction['emoji'] }}</span>
                <span class="text-xs font-medium">{{ $reaction['count'] }}</span>
            </button>
        @endforeach
    @endif

    <!-- Emoji Picker -->
    <x-emoji-picker :comment-id="$comment->id" trigger-class="emoji-picker-trigger" />
</div>

<script>
function commentReactions() {
    return {
        commentId: null,
        reactions: @json($reactions->toArray()),

        init() {
            // Listen for reaction updates from emoji picker
            this.$watch('$store.reactions', (newReactions) => {
                if (newReactions && newReactions[this.commentId]) {
                    this.reactions = newReactions[this.commentId];
                }
            });
            
            // Listen for global reaction update events
            this.setupGlobalEventListener();
        },

        setupGlobalEventListener() {
            // Listen for global reaction update events
            window.addEventListener('comment-reaction-updated', (event) => {
                const { commentId, emoji, action, count, reaction } = event.detail;
                
                // Only update if this is for our comment
                if (commentId === this.commentId) {
                    console.log('Comment reactions - Received global event:', event.detail);
                    this.handleReactionUpdate(emoji, action, count, reaction);
                }
            });
        },

        handleReactionUpdate(emoji, action, count, reactionData = null) {
            console.log('Comment reactions - Handling reaction update:', { emoji, action, count, reactionData });
            
            if (action === 'added') {
                this.addOrUpdateReaction(emoji, count, reactionData);
            } else if (action === 'removed') {
                this.removeOrUpdateReaction(emoji, count);
            }
            
            // Force UI update
            this.$nextTick(() => {
                this.updateReactionDisplay();
            });
        },

        async addReaction(emoji) {
            console.log('Comment reactions - Adding reaction:', emoji, 'for comment:', this.commentId);
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                console.log('Comment reactions - CSRF Token:', csrfToken ? 'Found' : 'Missing');
                
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

                console.log('Comment reactions - Response status:', response.status);
                const data = await response.json();
                console.log('Comment reactions - Response data:', data);

                if (response.ok) {
                    if (data.data.action === 'added') {
                        this.addOrUpdateReaction(emoji, data.data.reaction_count);
                    } else if (data.data.action === 'removed') {
                        this.removeOrUpdateReaction(emoji, data.data.reaction_count);
                    }
                    
                    // Update the display
                    this.updateReactionDisplay();
                } else {
                    console.error('Comment reactions - API Error:', data);
                }
            } catch (error) {
                console.error('Comment reactions - Failed to add reaction:', error);
            }
        },

        addOrUpdateReaction(emoji, count, reactionData = null) {
            const existingIndex = this.reactions.findIndex(r => r.emoji === emoji);
            
            if (existingIndex >= 0) {
                this.reactions[existingIndex].count = count;
                this.reactions[existingIndex].user_reacted = true;
            } else {
                // Get current user info from the DOM or use the reaction data
                const currentUser = this.getCurrentUserInfo(reactionData);
                
                this.reactions.push({
                    emoji: emoji,
                    count: count,
                    user_reacted: true,
                    users: [currentUser]
                });
            }
        },

        removeOrUpdateReaction(emoji, count) {
            const existingIndex = this.reactions.findIndex(r => r.emoji === emoji);
            
            if (existingIndex >= 0) {
                if (count === 0) {
                    this.reactions.splice(existingIndex, 1);
                } else {
                    this.reactions[existingIndex].count = count;
                    this.reactions[existingIndex].user_reacted = false;
                    
                    // Remove current user from users array
                    const currentUserId = this.getCurrentUserId();
                    this.reactions[existingIndex].users = this.reactions[existingIndex].users.filter(u => u.id !== currentUserId);
                }
            }
        },

        getCurrentUserInfo(reactionData = null) {
            // Try to get user info from reaction data first
            if (reactionData && reactionData.user) {
                return reactionData.user;
            }
            
            // Fallback: get from DOM or use placeholder
            const userId = this.getCurrentUserId();
            return {
                id: userId,
                username: 'You',
                name: 'You'
            };
        },

        getCurrentUserId() {
            // Try to get user ID from the body data attribute
            const userId = document.body.getAttribute('data-user-id');
            if (userId) {
                return parseInt(userId);
            }
            
            // Fallback: return a placeholder ID
            return 0;
        },

        async updateReactionDisplay() {
            // Optionally refresh reactions from server to ensure accuracy
            try {
                const response = await fetch(`/api/comments/${this.commentId}/reactions`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    // Update reactions with fresh data from server
                    this.reactions = data.data || [];
                    console.log('Comment reactions - Refreshed from server:', this.reactions);
                }
            } catch (error) {
                console.log('Comment reactions - Using local data (server refresh failed):', error);
            }
            
            // Trigger a re-render of the reactions
            this.$nextTick(() => {
                this.$dispatch('reactions-updated', {
                    commentId: this.commentId,
                    reactions: this.reactions
                });
            });
        }
    }
}
</script>