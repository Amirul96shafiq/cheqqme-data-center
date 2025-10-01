@props([
    'isOpen' => false,
    'width' => 'w-64',
    'position' => 'top-full left-0', // CSS classes for positioning
    'zIndex' => 'z-[60]',
])

<div 
    x-show="{{ $isOpen }}"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="absolute {{ $zIndex }} {{ $position }} mt-1 {{ $width }} overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 focus:outline-none"
    style="display: none;"
>
    {{ $slot }}
</div>
