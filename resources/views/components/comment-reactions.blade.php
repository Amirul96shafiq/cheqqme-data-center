<div class="comment-reactions flex flex-wrap gap-2 mt-1" x-data="commentReactions" x-init="commentId = {{ $comment->id }}">
    @php
        $reactions = $getReactions();
    @endphp

    <!-- Emoji Picker - Always positioned first (left side) -->
    @if(!$comment->isDeleted())
        <x-emoji-picker :comment-id="$comment->id" trigger-class="emoji-picker-trigger" />
    @endif

    @if($reactions->isNotEmpty())
        @foreach($reactions as $reaction)
            <button
                type="button"
                class="reaction-button inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-sm transition-colors duration-200 {{ $reaction['user_reacted'] ? 'bg-primary-100/10 text-primary-700 border border-primary-200 dark:bg-primary-900/10 dark:text-primary-300 dark:border-primary-700 cursor-default' : 'bg-gray-100/10 text-gray-700 border border-gray-200 dark:bg-gray-700/10 dark:text-gray-300 dark:border-gray-600 cursor-default' }}"
                data-emoji="{{ $reaction['emoji'] }}"
                data-count="{{ $reaction['count'] }}"
                title="{{ ($reaction['users'][0]['name'] ?? $reaction['users'][0]['username'] ?? 'Unknown') . ($reaction['users'][0]['reacted_at'] ? ' (' . \Carbon\Carbon::parse($reaction['users'][0]['reacted_at'])->format('d/n/y • g:i A') . ')' : '') }}{{ count($reaction['users']) > 1 ? ' and ' . (count($reaction['users']) - 1) . ' others' : '' }}"
            >
                <span class="text-sm">{{ $reaction['emoji'] }}</span>
                <span class="text-xs font-medium">{{ $reaction['count'] }}</span>
            </button>
        @endforeach
    @endif
</div>
