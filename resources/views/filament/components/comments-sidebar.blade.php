{{-- filepath: g:\projects\cheqqme-data-center\resources\views\filament\components\comments-sidebar.blade.php --}}
<div class="h-96 flex flex-col bg-gray-50 dark:bg-gray-900 rounded-lg border" data-csrf-token="{{ csrf_token() }}">
    <!-- Comments Header -->
    <div class="p-3 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ __('comments.header.title') }} ({{ $comments->count() }})
        </h3>
    </div>

    <!-- Comments List -->
    <div class="flex-1 overflow-y-auto p-3 space-y-3">
        @forelse($comments as $comment)
            <div class="flex space-x-2" data-comment-id="{{ $comment->id }}">
                <!-- User Avatar -->
                <div class="flex-shrink-0">
                    <div class="w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center text-white text-xs font-medium">
                        {{ substr($comment->user->username ?? 'U', 0, 1) }}
                    </div>
                </div>
                
                <!-- Comment Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium text-gray-900 dark:text-gray-100">
                            {{ $comment->user->username ?? __('comments.meta.unknown_user') }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $comment->created_at->format('M j, H:i') }}
                            @if($comment->updated_at->gt($comment->created_at))
                                <span class="text-xs text-gray-400">({{ __('comments.meta.edited') }})</span>
                            @endif
                        </p>
                    </div>
                    <div class="mt-1">
                        <p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words">{{ $comment->comment }}</p>
                    </div>
                    
                    <!-- Emoji Reaction Button - Below Comment -->
                    <div class="mt-2 flex items-center justify-start">
                        <div class="emoji-container" data-comment-id="{{ $comment->id }}">
                            <button 
                                type="button"
                                class="emoji-reaction-btn p-1 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
                                data-comment-id="{{ $comment->id }}"
                                title="Add emoji reaction"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    @if(auth()->id() === $comment->user_id)
                        <div class="mt-1 flex space-x-2">
                            <button 
                                type="button"
                                class="edit-comment-btn text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                data-comment-id="{{ $comment->id }}"
                                data-comment-text="{{ addslashes($comment->comment) }}"
                            >
                                {{ __('comments.buttons.edit') }}
                            </button>
                            <button 
                                type="button"
                                class="delete-comment-btn text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                data-comment-id="{{ $comment->id }}"
                            >
                                {{ __('comments.buttons.delete') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-500 dark:text-gray-400 italic text-center py-8">
                {{ __('comments.list.none_long') }}
            </p>
        @endforelse
    </div>

    <!-- Add Comment Section -->
    <div class="p-3 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        {{-- Removed hidden nested form: nested forms inside Filament modal form are invalid HTML and prevent submission to /comments. Using fetch exclusively. --}}
        
        <textarea 
            id="comment-input-{{ $taskId ?? 'new' }}" 
            placeholder="{{ __('comments.composer.placeholder') }}" 
            rows="3" 
            class="w-full p-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        ></textarea>
        
        <div class="mt-2 flex justify-end">
            <button 
                type="button"
                id="save-comment-btn-{{ $taskId ?? 'new' }}"
                class="save-comment-btn inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-md transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                data-task-id="{{ $taskId ?? '' }}"
            >
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                {{ __('comments.buttons.save_comment') }}
            </button>
        </div>
    </div>
</div>

<!-- Floating Emoji Picker Container for Comments -->
<div id="comments-emoji-picker-container" class="fixed hidden z-[11]">
    <emoji-picker id="comments-emoji-picker"></emoji-picker>
</div>

<!-- Emoji Picker Element -->
<script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>

<!-- Emoji Picker Theme CSS -->
@vite('resources/css/emoji-picker-theme.css')

<script>
// Load existing emoji reactions for all comments
function loadExistingEmojiReactions() {
    // Get all comment IDs from the page
    const commentElements = document.querySelectorAll('[data-comment-id]');
    const commentIds = Array.from(commentElements).map(el => el.getAttribute('data-comment-id'));
    
    if (commentIds.length === 0) {
        console.log('No comments found to load emoji reactions for');
        return;
    }
    
    console.log('Loading emoji reactions for comments:', commentIds);
    
    // Send batch request to get emoji reactions
    const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                     document.querySelector('input[name="_token"]')?.value || '';
    
    fetch('/comments/emoji/batch', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ comment_ids: commentIds })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Loaded emoji reactions:', data.reactions);
            
            // Update UI for each comment with an emoji reaction
            Object.keys(data.reactions).forEach(commentId => {
                const reactionData = data.reactions[commentId];
                if (reactionData) {
                    // Store in local state
                    commentEmojiStates[commentId] = {
                        emoji: reactionData.emoji,
                        username: reactionData.username,
                        created_at: reactionData.created_at
                    };
                    
                    // Update UI
                    const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
                    if (commentElement) {
                        const emojiContainer = commentElement.querySelector('.emoji-container');
                        if (emojiContainer) {
                            // Replace the button with the emoji
                            emojiContainer.innerHTML = `
                                <button 
                                    type="button"
                                    class="emoji-display-btn p-1 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
                                    data-comment-id="${commentId}"
                                    title="${reactionData.username} - ${reactionData.created_at}"
                                >
                                    <span class="text-lg">${reactionData.emoji}</span>
                                </button>
                            `;
                            
                            // Add click handler to remove the emoji
                            const emojiDisplayBtn = emojiContainer.querySelector('.emoji-display-btn');
                            const commentIdToRemove = commentId; // Capture the comment ID
                            emojiDisplayBtn.addEventListener('click', function() {
                                removeEmojiReaction(commentIdToRemove);
                            });
                        }
                    }
                }
            });
        } else {
            console.error('Failed to load emoji reactions:', data.message);
        }
    })
    .catch(error => {
        console.error('Error loading emoji reactions:', error);
    });
}

// Use event delegation to handle comment actions
document.addEventListener('DOMContentLoaded', function() {
    console.log('Comment script loaded');
    
    // Handle save comment button clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('save-comment-btn') || e.target.closest('.save-comment-btn')) {
            const btn = e.target.classList.contains('save-comment-btn') ? e.target : e.target.closest('.save-comment-btn');
            const taskId = btn.getAttribute('data-task-id');
            saveComment(taskId, btn);
        }
        
        // Handle edit comment button clicks
        if (e.target.classList.contains('edit-comment-btn')) {
            const commentId = e.target.getAttribute('data-comment-id');
            const commentText = e.target.getAttribute('data-comment-text');
            editComment(commentId, commentText);
        }
        
        // Handle delete comment button clicks
        if (e.target.classList.contains('delete-comment-btn')) {
            const commentId = e.target.getAttribute('data-comment-id');
            deleteComment(commentId);
        }
        
        // Handle emoji reaction button clicks
        if (e.target.classList.contains('emoji-reaction-btn') || e.target.closest('.emoji-reaction-btn')) {
            const btn = e.target.classList.contains('emoji-reaction-btn') ? e.target : e.target.closest('.emoji-reaction-btn');
            const commentId = btn.getAttribute('data-comment-id');
            toggleCommentsEmojiPicker(commentId, btn);
        }
    });
    
    // Initialize comments emoji picker
    initializeCommentsEmojiPicker();
    
    // Load existing emoji reactions after a short delay to ensure DOM is fully loaded
    setTimeout(loadExistingEmojiReactions, 100);
});

// Comments Emoji Picker Functions
let commentsEmojiPickerInitialized = false;
let currentCommentId = null;
let commentEmojiStates = {}; // Track emoji state per comment

function toggleCommentsEmojiPicker(commentId, button) {
    const emojiPickerContainer = document.getElementById("comments-emoji-picker-container");
    const emojiPicker = document.getElementById("comments-emoji-picker");
    
    if (!emojiPickerContainer || !emojiPicker) {
        console.error('Comments emoji picker elements not found');
        return;
    }
    
    currentCommentId = commentId;
    
    if (emojiPickerContainer.classList.contains("hidden")) {
        // Position the emoji picker to the left of the comment
        const buttonRect = button.getBoundingClientRect();
        const commentElement = button.closest('[data-comment-id]');
        
        if (commentElement) {
            const commentRect = commentElement.getBoundingClientRect();
            
            // Position to the left of the comment with some spacing
            const leftPosition = commentRect.left - 430; // 420px width + 10px spacing
            
            // Position vertically centered with the comment
            const topPosition = commentRect.top + (commentRect.height / 2) - 200; // Center vertically
            
            // Ensure it doesn't go off-screen
            const finalLeftPosition = Math.max(20, Math.min(leftPosition, window.innerWidth - 420));
            const finalTopPosition = Math.max(20, Math.min(topPosition, window.innerHeight - 420));
            
            emojiPickerContainer.style.left = finalLeftPosition + "px";
            emojiPickerContainer.style.top = finalTopPosition + "px";
            
            emojiPickerContainer.classList.remove("hidden");
            
            // Add animation
            emojiPickerContainer.style.opacity = "0";
            emojiPickerContainer.style.transform = "translateX(-20px) scale(0.95)";
            requestAnimationFrame(() => {
                emojiPickerContainer.style.transition = "opacity 0.2s ease, transform 0.2s ease";
                emojiPickerContainer.style.opacity = "1";
                emojiPickerContainer.style.transform = "translateX(0) scale(1)";
            });
            
            // Focus the emoji picker
            emojiPicker.focus();
        }
    } else {
        // Close the picker
        emojiPickerContainer.style.transition = "opacity 0.2s ease, transform 0.2s ease";
        emojiPickerContainer.style.opacity = "0";
        emojiPickerContainer.style.transform = "translateX(-20px) scale(0.95)";
        setTimeout(() => {
            emojiPickerContainer.classList.add("hidden");
        }, 200);
    }
}

function initializeCommentsEmojiPicker() {
    if (commentsEmojiPickerInitialized) {
        return;
    }
    
    const emojiPicker = document.getElementById("comments-emoji-picker");
    if (!emojiPicker) {
        return;
    }
    
    commentsEmojiPickerInitialized = true;
    
    // Configure emoji picker
    emojiPicker.addEventListener("emoji-click", (event) => {
        const emoji = event.detail.unicode;
        addEmojiReaction(emoji);
    });
    
    // Set emoji picker properties
    emojiPicker.style.setProperty("--category-emoji-size", "1.5rem");
    emojiPicker.style.setProperty("--emoji-size", "1.5rem");
    emojiPicker.style.setProperty("--num-columns", "8");
    emojiPicker.style.setProperty("--border-radius", "0.5rem");
    
    // Close picker when clicking outside
    document.addEventListener('click', (e) => {
        const container = document.getElementById("comments-emoji-picker-container");
        const button = e.target.closest('.emoji-reaction-btn');
        
        if (container && !container.contains(e.target) && !button) {
            if (!container.classList.contains("hidden")) {
                container.style.transition = "opacity 0.2s ease, transform 0.2s ease";
                container.style.opacity = "0";
                container.style.transform = "translateX(-20px) scale(0.95)";
                setTimeout(() => {
                    container.classList.add("hidden");
                }, 200);
            }
        }
    });
}

function addEmojiReaction(emoji) {
    if (!currentCommentId) {
        console.error('No comment ID set for emoji reaction');
        return;
    }
    
    console.log('Adding emoji reaction:', emoji, 'to comment:', currentCommentId);
    
    // Capture the comment ID before making the request
    const commentId = currentCommentId;
    
    // Send emoji reaction to server
    const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                     document.querySelector('input[name="_token"]')?.value || '';
    
    fetch(`/comments/${commentId}/emoji`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ emoji: emoji })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Store the emoji state for this comment
            commentEmojiStates[commentId] = {
                emoji: emoji,
                username: data.username,
                created_at: data.created_at
            };
            
            // Find the comment element and replace the button with the emoji
            const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
            if (commentElement) {
                const emojiContainer = commentElement.querySelector('.emoji-container');
                if (emojiContainer) {
                    // Replace the button with the emoji
                    emojiContainer.innerHTML = `
                        <button 
                            type="button"
                            class="emoji-display-btn p-1 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
                            data-comment-id="${commentId}"
                            title="${data.username} - ${data.created_at}"
                        >
                            <span class="text-lg">${emoji}</span>
                        </button>
                    `;
                    
                    // Add click handler to remove the emoji
                    const emojiDisplayBtn = emojiContainer.querySelector('.emoji-display-btn');
                    const commentIdToRemove = commentId; // Capture the comment ID
                    emojiDisplayBtn.addEventListener('click', function() {
                        removeEmojiReaction(commentIdToRemove);
                    });
                }
            }
        } else {
            console.error('Failed to save emoji reaction:', data.message);
            alert('Failed to save emoji reaction: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving emoji reaction:', error);
        alert('Error saving emoji reaction: ' + error.message);
    });
    
    // Close the emoji picker
    const container = document.getElementById("comments-emoji-picker-container");
    if (container) {
        container.style.transition = "opacity 0.2s ease, transform 0.2s ease";
        container.style.opacity = "0";
        container.style.transform = "translateX(-20px) scale(0.95)";
        setTimeout(() => {
            container.classList.add("hidden");
        }, 200);
    }
    
    // Reset current comment ID
    currentCommentId = null;
}

function removeEmojiReaction(commentId) {
    console.log('removeEmojiReaction called with commentId:', commentId);
    console.log('Current emoji states:', commentEmojiStates);
    
    // Send remove request to server
    const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                     document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                     document.querySelector('input[name="_token"]')?.value || '';
    
    fetch(`/comments/${commentId}/emoji`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the emoji state for this comment
            delete commentEmojiStates[commentId];
            
            // Find the comment element and restore the picker button
            const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
            console.log('Found comment element:', commentElement);
            
            if (commentElement) {
                const emojiContainer = commentElement.querySelector('.emoji-container');
                console.log('Found emoji container:', emojiContainer);
                
                if (emojiContainer) {
                    // Restore the original picker button
                    emojiContainer.innerHTML = `
                        <button 
                            type="button"
                            class="emoji-reaction-btn p-1 rounded-md text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
                            data-comment-id="${commentId}"
                            title="Add emoji reaction"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                    `;
                    console.log('Restored picker button for comment:', commentId);
                }
            }
        } else {
            console.error('Failed to remove emoji reaction:', data.message);
            alert('Failed to remove emoji reaction: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error removing emoji reaction:', error);
        alert('Error removing emoji reaction: ' + error.message);
    });
}

function saveComment(taskId, saveBtn) {
    console.log('saveComment invoked', { taskId });
    const textarea = document.getElementById(`comment-input-${taskId}`);
    if (!textarea) {
        alert('Textarea not found');
        return;
    }
    const comment = textarea.value.trim();
    if (!comment) {
        alert('Please enter a comment');
        return;
    }
    if (!taskId) {
        alert('Task ID missing');
        return;
    }
    saveBtn.disabled = true;
    const originalHtml = saveBtn.innerHTML;
    saveBtn.innerHTML = 'Saving...';

    // CSRF token resolution
    let csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token')
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || (typeof window._token !== 'undefined' ? window._token : '')
        || document.querySelector('input[name="_token"]')?.value;

    if (!csrfToken) {
        console.error('CSRF token not found');
        alert('Security token missing; refresh page.');
        resetSaveButton(saveBtn);
        return;
    }

    const payload = { task_id: taskId, comment };
    console.log('Sending fetch to /comments', payload);

    fetch('/comments', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
    })
        .then(async response => {
            const text = await response.text();
            let json;
            try { json = JSON.parse(text); } catch { json = null; }
            console.log('Raw response text:', text);
            if (!response.ok) {
                throw new Error('HTTP ' + response.status + ' ' + (json?.message || text));
            }
            return json;
        })
        .then(data => {
            if (data?.success) {
                textarea.value = '';
                // Optimistically insert without reload
                try {
                    const list = textarea.closest('.flex.flex-col').querySelector('.flex-1');
                    if (list) {
                        const wrapper = document.createElement('div');
                        wrapper.className = 'flex space-x-2';
                        wrapper.innerHTML = `
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-primary-500 rounded-full flex items-center justify-center text-white text-xs font-medium">You</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-medium text-gray-900 dark:text-gray-100">You</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Just now</p>
                                </div>
                                <div class="mt-1"><p class="text-xs text-gray-700 dark:text-gray-300 whitespace-pre-wrap break-words"></p></div>
                            </div>`;
                        wrapper.querySelector('p.text-xs.text-gray-700.dark\:text-gray-300').textContent = comment;
                        list.prepend(wrapper);
                    }
                } catch (e) { console.warn('Optimistic insert failed', e); }
                // Optionally reload after short delay to sync counts
                setTimeout(() => location.reload(), 800);
            } else {
                alert('Failed to save comment');
            }
        })
        .catch(err => {
            console.error('Save failed', err);
            alert('Error: ' + err.message);
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalHtml;
        });
}

function resetSaveButton(saveBtn) {
    saveBtn.disabled = false;
    saveBtn.innerHTML = `
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
        </svg>
        Save Comment
    `;
}

function editComment(commentId, currentComment) {
    const newComment = prompt('Edit your comment:', currentComment);
    if (newComment && newComment !== currentComment) {
        const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                         document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value || '';
        
        fetch(`/comments/${commentId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ comment: newComment })
        }).then(() => {
            location.reload();
        }).catch(error => {
            console.error('Edit error:', error);
            alert('Error updating comment');
        });
    }
}

function deleteComment(commentId) {
    if (confirm('Are you sure you want to delete this comment?')) {
        const csrfToken = document.querySelector('[data-csrf-token]')?.getAttribute('data-csrf-token') || 
                         document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                         document.querySelector('input[name="_token"]')?.value || '';
        
        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        }).then(() => {
            location.reload();
        }).catch(error => {
            console.error('Delete error:', error);
            alert('Error deleting comment');
        });
    }
}

console.log('Comment functions loaded successfully');
</script>