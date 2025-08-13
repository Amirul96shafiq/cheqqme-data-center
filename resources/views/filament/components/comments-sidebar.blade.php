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

<script>
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
    });
});

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