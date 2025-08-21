<!-- Comments Section -->
<div class="h-full flex flex-col">
    <!-- Comment Input Area (Trello Style) -->
        <div class="mb-4">
            <form onsubmit="return false;" onclick="event.stopPropagation();">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center shadow-sm">
                                <span class="text-sm font-medium text-white">
                                    {{ substr(auth()->user()->username ?? auth()->user()->name ?? 'U', 0, 1) }}
                                </span>
                            </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="relative">
                            <textarea
                                wire:model="newComment"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white resize-none transition-all"
                                rows="2"
                                placeholder="Write a comment..."
                            ></textarea>
                        
                        <!-- Mention Dropdown -->
                        @if($showMentionDropdown && !empty($filteredUsers))
                            <div class="mention-dropdown absolute z-50 w-64 mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @foreach($filteredUsers as $index => $user)
                                    <button
                                        type="button"
                                        wire:click="selectUser({{ $user['id'] }})"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center space-x-3 {{ $index === $selectedUserIndex ? 'bg-primary-50 dark:bg-primary-900/20' : '' }} transition-colors duration-150"
                                    >
                                        <div class="w-6 h-6 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm">
                                            <span class="text-xs font-medium text-white">
                                                {{ substr($user['name'] ?? $user['username'], 0, 1) }}
                                            </span>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                {{ $user['name'] ?? 'No Name' }}
                                            </div>
                                            @if($user['username'] && $user['username'] !== ($user['name'] ?? ''))
                                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                                    @{{ $user['username'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex justify-between items-center mt-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Press @ to mention someone • Shift+Enter for new line • Click Save to submit
                        </div>
                        <button 
                            wire:click="addComment" 
                            class="px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all"
                            wire:loading.attr="disabled"
                        >
                            <span wire:loading.remove>Save</span>
                            <span wire:loading class="flex items-center">
                                <svg class="loading-spinner -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
        
        <!-- Comments List (Trello Style) -->
        <div class="flex-1 overflow-y-auto min-h-0">
            <div class="space-y-3">
                @forelse($this->comments as $comment)
                    <div class="flex items-start space-x-3" wire:key="comment-{{ $comment->id }}">
                        @if($editingId === $comment->id)
                            <!-- Edit Mode -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $comment->user->username ?? $comment->user->name ?? 'Unknown User' }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 bg-yellow-100 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-200 px-2 py-0.5 rounded-full">
                                        editing...
                                    </span>
                                </div>
                                
                                <div class="relative mb-3">
                                    <textarea
                                        wire:model="editingText"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white resize-none transition-all"
                                        rows="3"
                                        placeholder="Edit your comment..."
                                        wire:keydown.enter.prevent="saveEdit"
                                        wire:keydown.escape="cancelEdit"
                                    ></textarea>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button
                                        wire:click="saveEdit"
                                        class="px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all"
                                        wire:loading.attr="disabled"
                                    >
                                        <span wire:loading.remove>Save</span>
                                        <span wire:loading class="flex items-center">
                                            <svg class="loading-spinner -ml-1 mr-1 h-3 w-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Saving...
                                        </span>
                                    </button>
                                    <button
                                        wire:click="cancelEdit"
                                        class="px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        @else
                            <!-- View Mode (Trello Style) -->
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center shadow-sm">
                                    <span class="text-sm font-medium text-white">
                                        {{ substr($comment->user->username ?? $comment->user->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $comment->user->username ?? $comment->user->name ?? 'Unknown User' }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $comment->created_at->format('M j, Y, g:i A') }}
                                    </span>
                                </div>
                                
                                <div class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                                    {!! $comment->formatted_comment !!}
                                </div>
                                
                                @if($comment->is_editable)
                                    <div class="flex space-x-3">
                                        <button
                                            wire:click="startEdit({{ $comment->id }})"
                                            class="text-xs text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-medium hover:underline transition-all"
                                        >
                                            Reply
                                        </button>
                                        <button
                                            wire:click="confirmDelete({{ $comment->id }})"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium hover:underline transition-all"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No comments yet</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Be the first to add a comment to this task.</p>
                    </div>
                @endforelse
                
                @if($this->hasMoreComments)
                    <div class="text-center pt-4">
                        <button
                            wire:click="showMore"
                            class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 font-medium hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-md transition-all"
                        >
                            Show More Comments ({{ $this->totalComments - $this->visibleCount }} remaining)
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

<!-- Delete Confirmation Modal -->
@if($confirmingDeleteId)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/20">
                    <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4 mb-2">
                    Delete Comment
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Are you sure you want to delete this comment? This action cannot be undone.
                </p>
                <div class="flex space-x-3 justify-center">
                    <button
                        wire:click="performDelete"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all focus-ring"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Delete</span>
                        <span wire:loading class="flex items-center">
                            <svg class="loading-spinner -ml-1 mr-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Deleting...
                        </span>
                    </button>
                    <button
                        wire:click="cancelDelete"
                        class="btn-secondary focus-ring"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
document.addEventListener('livewire:init', function() {
    // Enhanced mention detection and handling
    const composer = document.querySelector('[wire\\:model="newComment"]');
    const saveButton = document.querySelector('[wire\\:click="addComment"]');
    
    // Debug: Log when addComment is called to identify the source
    if (@this && @this.addComment) {
        console.log('TaskCommentsNew component loaded, monitoring for addComment calls');
    }
    
    if (composer) {
        let mentionTimeout;
        let isComposing = false; // Track IME composition
        
        composer.addEventListener('compositionstart', () => {
            isComposing = true;
        });
        
        composer.addEventListener('compositionend', () => {
            isComposing = false;
        });
        
        // Focus tracking removed since we no longer use Enter key for saving
        
        composer.addEventListener('input', function(e) {
            // Don't process mentions during IME composition
            if (isComposing) return;
            
            const text = e.target.value;
            const lastAtSymbol = text.lastIndexOf('@');
            
            if (lastAtSymbol !== -1) {
                const searchTerm = text.substring(lastAtSymbol + 1).trim();
                
                // Clear previous timeout
                if (mentionTimeout) {
                    clearTimeout(mentionTimeout);
                }
                
                // Debounce the search
                mentionTimeout = setTimeout(() => {
                    if (searchTerm.length > 0) {
                        @this.searchUsers(searchTerm);
                    }
                }, 300);
            } else {
                @this.set('showMentionDropdown', false);
            }
        });
        
        // Handle keyboard navigation in mention dropdown
        composer.addEventListener('keydown', function(e) {
            // Don't handle keys during IME composition
            if (isComposing) return;
            
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                if (@this.showMentionDropdown && @this.filteredUsers.length > 0) {
                    e.preventDefault();
                    const currentIndex = @this.selectedUserIndex;
                    const userCount = @this.filteredUsers.length;
                    
                    if (e.key === 'ArrowDown') {
                        @this.set('selectedUserIndex', (currentIndex + 1) % userCount);
                    } else {
                        @this.set('selectedUserIndex', currentIndex === 0 ? userCount - 1 : currentIndex - 1);
                    }
                }
            } else if (e.key === 'Enter' && @this.showMentionDropdown && @this.filteredUsers.length > 0) {
                e.preventDefault();
                const selectedUser = @this.filteredUsers[@this.selectedUserIndex];
                if (selectedUser) {
                    @this.selectUser(selectedUser.id);
                }
            } else if (e.key === 'Escape') {
                if (@this.showMentionDropdown) {
                    e.preventDefault();
                    @this.set('showMentionDropdown', false);
                }
            }
        });
        
        // Remove Enter key handler - only Save button clicks should trigger saving
        // Enter key now only works for new lines (Shift+Enter) and mention selection
    }
    
    // Improved click-outside detection for mention dropdown and comment area
    document.addEventListener('click', function(e) {
        // Check if click is on the composer, save button, or mention dropdown
        const isComposerClick = e.target.closest('[wire\\:model="newComment"]');
        const isSaveButtonClick = e.target.closest('[wire\\:click="addComment"]');
        const isMentionDropdownClick = e.target.closest('.mention-dropdown') || 
                                     e.target.closest('[wire\\:click*="selectUser"]');
        
        // CRITICAL: Prevent any accidental addComment calls when clicking outside
        if (!isSaveButtonClick && !isComposerClick && !isMentionDropdownClick) {
            // Stop any potential event bubbling that might trigger unwanted actions
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            // Only close dropdown if clicking outside both areas
            if (@this.showMentionDropdown) {
                @this.set('showMentionDropdown', false);
            }
            
            // Blur the composer for better UX when clicking outside
            if (document.activeElement === composer) {
                composer.blur();
            }
            
            return false; // Exit early to prevent any other processing
        }
        
        // Only close dropdown if clicking outside both areas
        if (!isComposerClick && !isMentionDropdownClick) {
            @this.set('showMentionDropdown', false);
        }
    }, true); // Use capture phase to catch events early
    
    // Additional protection against unwanted Livewire events
    document.addEventListener('livewire:call', function(e) {
        // Prevent addComment calls that don't come from the save button
        if (e.detail && e.detail.method === 'addComment') {
            const clickEvent = e.detail.event || {};
            const target = clickEvent.target;
            
            // Only allow if the call comes from the save button
            if (!target || !target.closest('[wire\\:click="addComment"]')) {
                console.log('Preventing unauthorized addComment call');
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        }
    }, true);
    
    // Global keyboard handlers
    document.addEventListener('keydown', function(e) {
        // Close mention dropdown on escape key globally
        if (e.key === 'Escape' && @this.showMentionDropdown) {
            @this.set('showMentionDropdown', false);
        }
        
        // No Enter key handlers for saving - only Save button clicks can trigger saving
    });
    
    // Close mention dropdown when window loses focus
    window.addEventListener('blur', function() {
        @this.set('showMentionDropdown', false);
    });
});
</script>