@props([
    'position' => 'top', // 'top', 'bottom', 'left', or 'right'
    'text' => '',
    'key' => null,
    'align' => 'center', // 'start' | 'center' | 'end'
])

@php
    $key = $key ?? Str::random(8);
    
    // Handle positioning based on tooltip position
    if (in_array($position, ['left', 'right'])) {
        // For left/right positions, align controls vertical alignment
        $yAlign = match ($align) {
            'start' => 'top-0 transform translate-y-0',
            'end' => 'bottom-0 transform translate-y-0',
            default => 'top-1/2 transform -translate-y-1/2',
        };
        
        $xAlign = $position === 'left'
            ? 'right-full mr-2'
            : 'left-full ml-2';
    } else {
        // For top/bottom positions, align controls horizontal alignment
        $xAlign = match ($align) {
            'start' => 'left-0 transform translate-x-0',
            'end' => 'right-0 transform translate-x-0',
            default => 'left-1/2 transform -translate-x-1/2',
        };
        
        $yAlign = $position === 'top'
            ? 'bottom-full mb-2'
            : 'top-full mt-2';
    }
@endphp

<div class="relative tooltip-container" data-tooltip-key="{{ $key }}">
    {{ $slot }}
    
    {{-- Tooltip --}}
    <div class="tooltip tooltip-{{ $position }} absolute {{ $yAlign }} {{ $xAlign }} px-3 py-2 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible transition-all duration-200 pointer-events-none whitespace-nowrap z-50"
         data-tooltip-text="{{ $text }}">
        {{ $text }}
        {{-- Tooltip arrow --}}
        @if($position === 'top')
            <div class="absolute top-full {{ $align === 'start' ? 'left-3' : ($align === 'end' ? 'right-3' : 'left-1/2 transform -translate-x-1/2') }} border-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
        @elseif($position === 'bottom')
            <div class="absolute bottom-full {{ $align === 'start' ? 'left-3' : ($align === 'end' ? 'right-3' : 'left-1/2 transform -translate-x-1/2') }} border-4 border-transparent border-b-gray-900 dark:border-b-gray-700"></div>
        @elseif($position === 'left')
            <div class="absolute left-full {{ $align === 'start' ? 'top-3' : ($align === 'end' ? 'bottom-3' : 'top-1/2 transform -translate-y-1/2') }} border-4 border-transparent border-l-gray-900 dark:border-l-gray-700"></div>
        @elseif($position === 'right')
            <div class="absolute right-full {{ $align === 'start' ? 'top-3' : ($align === 'end' ? 'bottom-3' : 'top-1/2 transform -translate-y-1/2') }} border-4 border-transparent border-r-gray-900 dark:border-r-gray-700"></div>
        @endif
    </div>
</div>
