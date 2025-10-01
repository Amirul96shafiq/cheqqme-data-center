@props([
    'isOpen' => false,
    'placeholder' => 'Select option...',
    'selectedText' => null,
    'buttonClass' => 'relative w-full cursor-default rounded-lg bg-white dark:bg-gray-800 py-2 pl-3 pr-10 text-left ring-1 ring-inset ring-gray-300 dark:ring-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm',
    'panelWidth' => 'w-64',
    'panelPosition' => 'top-full left-0',
    'zIndex' => 'z-[60]',
    'clickOutside' => true,
])

<div class="relative" @if($clickOutside) @click.outside="{{ $isOpen }} = false" @endif>
    <!-- Dropdown Trigger Button -->
    <button
        @click="{{ $isOpen }} = !{{ $isOpen }}"
        type="button"
        class="{{ $buttonClass }}"
    >
        <span class="block truncate text-gray-900 dark:text-white">
            @if($selectedText)
                {{ $selectedText }}
            @else
                <span class="text-gray-500 dark:text-gray-400">{{ $placeholder }}</span>
            @endif
        </span>
        <span class="pointer-events-none absolute inset-y-0 right-0 ml-3 flex items-center pr-2">
            <x-heroicon-m-chevron-down 
                class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                ::class="{ 'rotate-180': {{ $isOpen }} }"
            />
        </span>
    </button>
    
    <!-- Dropdown Panel -->
    <x-dropdown-panel 
        :is-open="$isOpen"
        :width="$panelWidth"
        :position="$panelPosition"
        :z-index="$zIndex"
    >
        {{ $slot }}
    </x-dropdown-panel>
</div>
