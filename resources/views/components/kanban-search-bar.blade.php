@props([
    'search' => null,
    'placeholder' => 'Search tasks by title',
    'clearLabel' => 'Clear',
    'wireModel' => 'search',
    'wireClear' => 'clearSearch'
])

<div class="-mb-8 px-4">
    <div class="flex items-center gap-2">
        <div class="relative">
            <input
                type="text"
                wire:model.live.debounce.300ms="{{ $wireModel }}"
                placeholder="{{ $placeholder }}"
                class="w-48 py-3 text-sm bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 rounded-lg text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-gray-300/50 dark:focus:border-gray-600/50 transition-all duration-200 hover:bg-white/40 dark:hover:bg-gray-800/40 focus:bg-white/40 dark:focus:bg-gray-800/40"
                autocomplete="off"
            />
        </div>
        @if($search)
            <button
                wire:click="{{ $wireClear }}"
                class="p-3 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 focus:outline-none bg-white/30 dark:bg-gray-800/30 border border-gray-200/80 dark:border-gray-700/80 hover:bg-white/40 dark:hover:bg-gray-800/40"
                type="button"
                title="{{ $clearLabel }}"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        @endif
    </div>
</div>
