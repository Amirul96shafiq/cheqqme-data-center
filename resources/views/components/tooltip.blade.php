@props([
    'position' => 'top', // 'top' or 'bottom'
    'text' => '',
    'key' => null
])

@php
    $key = $key ?? Str::random(8);
@endphp

<div class="relative tooltip-container" data-tooltip-key="{{ $key }}">
    {{ $slot }}
    
    {{-- Tooltip --}}
    <div class="tooltip tooltip-{{ $position }} absolute {{ $position === 'top' ? 'bottom-full left-1/2 transform -translate-x-1/2 mb-2' : 'top-full left-1/2 transform -translate-x-1/2 mt-2' }} px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible transition-all duration-200 pointer-events-none whitespace-nowrap z-50"
         data-tooltip-text="{{ $text }}">
        {{ $text }}
        {{-- Tooltip arrow --}}
        <div class="absolute {{ $position === 'top' ? 'top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900 dark:border-t-gray-700' : 'bottom-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-b-gray-900 dark:border-b-gray-700' }}"></div>
    </div>
</div>
