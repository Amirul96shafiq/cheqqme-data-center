@props([
    'commentId',
    'isReply' => false,
    'canEdit' => false,
    'canDelete' => false,
    'showReply' => false,
])

<div class="relative" x-data="{ open: false }">
    <!-- Dropdown trigger button -->
    <button 
        type="button" 
        @click="open = !open"
        @click.away="open = false"
        class="p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
        title="{{ __('comments.buttons.more_actions') }}"
    >
        @svg('heroicon-o-ellipsis-horizontal', 'w-4 h-4')
    </button>

    <!-- Dropdown menu -->
    <div 
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
        style="display: none;"
    >
        <div class="py-1" role="menu" aria-orientation="vertical">
            @if($showReply)
                <!-- Reply action (outside group) -->
                <button 
                    type="button" 
                    wire:click="startReply({{ $commentId }})"
                    @click="open = false"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
                    role="menuitem"
                >
                    @svg('heroicon-o-chat-bubble-left-right', 'w-4 h-4 mr-3')
                    {{ __('comments.buttons.reply') }}
                </button>
                @if($canEdit || $canDelete)
                    <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                @endif
            @endif

            @if($canEdit)
                <!-- Edit action -->
                <button 
                    type="button" 
                    wire:click="{{ $isReply ? 'startEditReply(' . $commentId . ')' : 'startEdit(' . $commentId . ')' }}"
                    @click="open = false"
                    class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700"
                    role="menuitem"
                >
                    @svg('heroicon-o-pencil-square', 'w-4 h-4 mr-3')
                    {{ __('comments.buttons.edit') }}
                </button>
            @endif

            @if($canDelete)
                <!-- Delete action -->
                <button 
                    type="button" 
                    wire:click="{{ $isReply ? 'confirmDeleteReply(' . $commentId . ')' : 'confirmDelete(' . $commentId . ')' }}"
                    @click="open = false"
                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:bg-red-50 dark:focus:bg-red-900/20"
                    role="menuitem"
                >
                    @svg('heroicon-o-trash', 'w-4 h-4 mr-3')
                    {{ __('comments.buttons.delete') }}
                </button>
            @endif
        </div>
    </div>
</div>
