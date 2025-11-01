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

<div class="relative tooltip-container" 
     data-tooltip-key="{{ $key }}"
     x-data="tooltipSmartPositioning()"
     @mouseenter="if (!('ontouchstart' in window) || ('ontouchstart' in window && matchMedia('(hover: hover)').matches)) { positionTooltip($el); }"
     @mouseleave="if (!('ontouchstart' in window) || ('ontouchstart' in window && matchMedia('(hover: hover)').matches)) { resetTooltip($el); }">
    {{ $slot }}
    
    {{-- Tooltip --}}
    <div class="tooltip tooltip-{{ $position }} absolute {{ $yAlign }} {{ $xAlign }} px-3 py-1 bg-white text-gray-900 dark:bg-gray-700 dark:text-white text-sm rounded-md opacity-0 invisible transition-all duration-200 pointer-events-none z-[10000] shadow-lg whitespace-nowrap"
         data-tooltip-text="{{ $text }}"
         data-tooltip-position="{{ $position }}"
         data-tooltip-align="{{ $align }}"
         style="font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', sans-serif;">
        {!! $text !!}

        {{-- Tooltip arrow --}}
        @if($position === 'top')
            <div class="absolute top-full {{ $align === 'start' ? 'left-3' : ($align === 'end' ? 'right-3' : 'left-1/2 transform -translate-x-1/2') }} w-0 h-0 border-l-[6px] border-r-[6px] border-t-[6px] border-l-transparent border-r-transparent border-t-white dark:border-t-gray-700"></div>
        @elseif($position === 'bottom')
            <div class="absolute bottom-full {{ $align === 'start' ? 'left-3' : ($align === 'end' ? 'right-3' : 'left-1/2 transform -translate-x-1/2') }} w-0 h-0 border-l-[6px] border-r-[6px] border-b-[6px] border-l-transparent border-r-transparent border-b-white dark:border-b-gray-700"></div>
        @elseif($position === 'left')
            <div class="absolute left-full {{ $align === 'start' ? 'top-3' : ($align === 'end' ? 'bottom-3' : 'top-1/2 transform -translate-y-1/2') }} w-0 h-0 border-t-[6px] border-b-[6px] border-l-[6px] border-t-transparent border-b-transparent border-l-white dark:border-l-gray-700"></div>
        @elseif($position === 'right')
            <div class="absolute right-full {{ $align === 'start' ? 'top-3' : ($align === 'end' ? 'bottom-3' : 'top-1/2 transform -translate-y-1/2') }} w-0 h-0 border-t-[6px] border-b-[6px] border-r-[6px] border-t-transparent border-b-transparent border-r-white dark:border-r-gray-700"></div>
        @endif
        
    </div>
</div>
