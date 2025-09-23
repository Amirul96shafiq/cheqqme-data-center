@props([
    'position' => 'top', // 'top' or 'bottom'
    'text' => '',
    'key' => null,
    'align' => 'center', // 'start' | 'center' | 'end'
])

@php
    $key = $key ?? Str::random(8);
    $xAlign = match ($align) {
        'start' => 'left-0 transform translate-x-0',
        'end' => 'right-0 transform translate-x-0',
        default => 'left-1/2 transform -translate-x-1/2',
    };

    $yAlign = $position === 'top'
        ? 'bottom-full mb-2'
        : 'top-full mt-2';
@endphp

<div class="relative tooltip-container" data-tooltip-key="{{ $key }}">
    {{ $slot }}
    
    {{-- Tooltip --}}
    <div class="tooltip tooltip-{{ $position }} absolute {{ $yAlign }} {{ $xAlign }} px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible transition-all duration-200 pointer-events-none whitespace-nowrap z-50"
         data-tooltip-text="{{ $text }}">
        {{ $text }}
        {{-- Tooltip arrow --}}
        <div class="absolute {{ $position === 'top' ? 'top-full' : 'bottom-full' }} {{ $align === 'start' ? 'left-3' : ($align === 'end' ? 'right-3' : 'left-1/2 transform -translate-x-1/2') }} border-4 border-transparent {{ $position === 'top' ? 'border-t-gray-900 dark:border-t-gray-700' : 'border-b-gray-900 dark:border-b-gray-700' }}"></div>
    </div>
</div>
