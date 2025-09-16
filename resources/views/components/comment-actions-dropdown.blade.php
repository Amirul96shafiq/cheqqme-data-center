@props([
    'commentId',
    'isReply' => false,
    'canEdit' => false,
    'canDelete' => false,
    'canForceDelete' => false,
    'showReply' => false,
    'showFocus' => false,
])

<x-filament::dropdown
    placement="bottom-end"
    width="!max-w-[10rem]"
    :attributes="new \Illuminate\View\ComponentAttributeBag(['style' => 'z-index: 9995;'])"
    x-on:close-dropdown="$el.querySelector('button[data-dropdown-trigger]')?.click()"
>
    <x-slot name="trigger">
        <button 
            type="button" 
            class="fi-dropdown-trigger flex cursor-pointer p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/40 transition-all duration-200"
            title="{{ __('comments.buttons.more_actions') }}"
            data-dropdown-trigger="{{ $commentId }}"
        >
            @svg('heroicon-o-ellipsis-horizontal', 'w-4 h-4')
        </button>
    </x-slot>

    <x-filament::dropdown.list>
        @if($showReply)
            <!-- Reply action (outside group) -->
            <x-filament::dropdown.list.item
                :icon="'heroicon-o-chat-bubble-left-right'"
                wire:click="startReply({{ $commentId }})"
            >
                {{ __('comments.buttons.reply') }}
            </x-filament::dropdown.list.item>
        @endif

        @if($showFocus && !$isReply)
            <!-- Focus action (only for main comments, not replies) -->
            <x-filament::dropdown.list.item
                :icon="'heroicon-o-eye'"
                x-on:click="enterFocusMode({{ $commentId }}); $dispatch('close-dropdown')"
                x-show="!isFocusMode"
            >
                {{ __('comments.buttons.focus') }}
            </x-filament::dropdown.list.item>
        @endif

        @if($canEdit)
            <!-- Edit action -->
            <x-filament::dropdown.list.item
                :icon="'heroicon-o-pencil-square'"
                wire:click="{{ $isReply ? 'startEditReply(' . $commentId . ')' : 'startEdit(' . $commentId . ')' }}"
            >
                {{ __('comments.buttons.edit') }}
            </x-filament::dropdown.list.item>
        @endif

        @if($canDelete)
            <!-- Delete action -->
            <x-filament::dropdown.list.item
                :icon="'heroicon-o-trash'"
                :color="'warning'"
                wire:click="{{ $isReply ? 'confirmDeleteReply(' . $commentId . ')' : 'confirmDelete(' . $commentId . ')' }}"
            >
                {{ __('comments.buttons.delete') }}
            </x-filament::dropdown.list.item>
        @endif

        @if($canForceDelete)
            <!-- Force Delete action -->
            <x-filament::dropdown.list.item
                :icon="'heroicon-o-trash'"
                :color="'danger'"
                wire:click="{{ $isReply ? 'confirmForceDeleteReply(' . $commentId . ')' : 'confirmForceDelete(' . $commentId . ')' }}"
            >
                {{ __('comments.buttons.force_delete') }}
            </x-filament::dropdown.list.item>
        @endif
    </x-filament::dropdown.list>
</x-filament::dropdown>
