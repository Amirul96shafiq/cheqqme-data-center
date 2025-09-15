<div class="comment-reactions flex flex-wrap gap-2 mt-3" x-data="commentReactions" x-init="commentId = {{ $comment->id }}">
    @php
        $reactions = $getReactions();
    @endphp

    @if($reactions->isNotEmpty())
        @foreach($reactions as $reaction)
            <button
                type="button"
                class="reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 {{ $reaction['user_reacted'] ? 'bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700 cursor-default' : 'bg-gray-100/10 text-gray-700 border border-gray-200 hover:bg-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600' }}"
                data-emoji="{{ $reaction['emoji'] }}"
                data-count="{{ $reaction['count'] }}"
                @if(!$reaction['user_reacted'])
                    @click="addReaction('{{ $reaction['emoji'] }}')"
                @endif
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
            // Listen for global reaction update events
            this.setupGlobalEventListener();
        },

        setupGlobalEventListener() {
            window.addEventListener('comment-reaction-updated', (event) => {
                const { commentId, emoji, action, count, reaction } = event.detail;
                
                // Only update if this is for our comment
                if (commentId === this.commentId) {
                    this.handleReactionUpdate(emoji, action, count, reaction);
                }
            });
        },

        handleReactionUpdate(emoji, action, count, reactionData = null) {
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
                        this.addOrUpdateReaction(emoji, data.data.reaction_count);
                    } else if (data.data.action === 'removed') {
                        this.removeOrUpdateReaction(emoji, data.data.reaction_count);
                    }
                    
                    this.updateReactionDisplay();
                }
            } catch (error) {
                console.error('Failed to add reaction:', error);
            }
        },

        addOrUpdateReaction(emoji, count, reactionData = null) {
            const existingIndex = this.reactions.findIndex(r => r.emoji === emoji);
            
            if (existingIndex >= 0) {
                this.reactions[existingIndex].count = count;
                this.reactions[existingIndex].user_reacted = true;
            } else {
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
                    
                    const currentUserId = this.getCurrentUserId();
                    this.reactions[existingIndex].users = this.reactions[existingIndex].users.filter(u => u.id !== currentUserId);
                }
            }
        },

        getCurrentUserInfo(reactionData = null) {
            if (reactionData && reactionData.user) {
                return reactionData.user;
            }
            
            const userId = this.getCurrentUserId();
            return {
                id: userId,
                username: 'You',
                name: 'You'
            };
        },

        getCurrentUserId() {
            const userId = document.body.getAttribute('data-user-id');
            return userId ? parseInt(userId) : 0;
        },

        async updateReactionDisplay() {
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
                    this.reactions = data.data || [];
                }
            } catch (error) {
                console.log('Using local data (server refresh failed):', error);
            }
            
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