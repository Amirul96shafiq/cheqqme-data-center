<div class="flex items-start space-x-3 p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
    <!-- User Avatar -->
    <div class="flex-shrink-0">
        @if($notification->data['mentioned_by_id'] && $user = \App\Models\User::find($notification->data['mentioned_by_id']))
            @if($user->avatar)
                <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->username }}" class="w-10 h-10 rounded-full object-cover">
            @else
                <div class="w-10 h-10 bg-primary-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                    {{ substr($user->username ?? 'U', 0, 1) }}
                </div>
            @endif
        @else
            <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white text-sm font-medium">
                ?
            </div>
        @endif
    </div>
    
    <!-- Notification Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    @if($notification->data['mentioned_by_username'])
                        <span class="font-semibold">{{ $notification->data['mentioned_by_username'] }}</span>
                    @else
                        <span class="font-semibold">Someone</span>
                    @endif
                    mentioned you in a comment
                </p>
            </div>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ $notification->created_at->diffForHumans() }}
            </span>
        </div>
        
        <div class="mt-2">
            <p class="text-sm text-gray-600 dark:text-gray-300">
                <span class="font-medium">{{ $notification->data['task_title'] ?? 'a task' }}</span>
            </p>
            @if($notification->data['comment_preview'])
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 italic">
                    "{{ $notification->data['comment_preview'] }}"
                </p>
            @endif
        </div>
        
        <!-- Action Button -->
        <div class="mt-3">
            <a href="{{ $notification->data['action_url'] ?? '#' }}" 
               class="inline-flex items-center px-3 py-2 text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 dark:bg-primary-900/20 dark:hover:bg-primary-900/30 dark:text-primary-400 rounded-md transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                View Task
            </a>
        </div>
    </div>
    
    <!-- Close Button -->
    <button onclick="markAsRead('{{ $notification->id }}')" 
            class="flex-shrink-0 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
    }).then(() => {
        // Remove the notification from the DOM
        const notification = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notification) {
            notification.remove();
        }
    }).catch(error => {
        console.error('Error marking notification as read:', error);
    });
}
</script>
