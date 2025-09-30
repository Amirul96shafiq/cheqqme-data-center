@props([
    'search' => null,
    'placeholder' => 'Search tasks by title',
    'clearLabel' => 'Clear',
    'wireModel' => 'search',
    'wireClear' => 'clearSearch'
])

<div class="kanban-search-container">
    <div class="kanban-search-bar">
        <div class="kanban-search-input-wrapper">
            <input
                type="text"
                wire:model.live.debounce.300ms="{{ $wireModel }}"
                placeholder="{{ $placeholder }}"
                class="kanban-search-input"
                autocomplete="off"
            />
            @if($search)
                <button
                    wire:click="{{ $wireClear }}"
                    class="kanban-search-clear"
                    type="button"
                    title="{{ $clearLabel }}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            @endif
        </div>
    </div>
</div>
