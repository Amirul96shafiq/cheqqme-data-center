@props([
    'user' => null,
    'statusOptions' => [],
    'position' => 'bottom', // 'top' or 'bottom'
    'showTooltip' => false
])

@php
    $positionClasses = $position === 'top'
        ? 'bottom-full mb-1'
        : 'top-full mt-1';
@endphp

<!-- Dropdown Menu -->
<div
    x-show="open"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
    class="absolute {{ $positionClasses }} left-1/2 transform -translate-x-1/2 z-[60] min-w-[180px] overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700"
    style="display: none;"
>{{--  --}}
    <div class="p-2 space-y-1">
        @foreach($statusOptions as $status => $option)
            <button 
                @click="
                    {{-- console.log('Dropdown clicked for status:', '{{ $status }}'); --}}
                    if (typeof window.updateOnlineStatus === 'function') {
                        window.updateOnlineStatus('{{ $status }}');
                    } else {
                        console.error('updateOnlineStatus function not found');
                    }
                    open = false;
                "
                class="w-full flex items-center gap-2 p-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 rounded-md {{ $user->online_status === $status ? 'bg-primary-50 dark:bg-primary-900/10' : '' }}"
            >
                <!-- Status Color Circle -->
                <div class="w-3 h-3 rounded-full border-2 border-white dark:border-gray-900 {{ $option['color'] }} flex-shrink-0"></div>
                
                <!-- Status Label -->
                <span class="text-sm font-medium text-gray-900 dark:text-white flex-1">{{ $option['label'] }}</span>
                
                <!-- Current Status Indicator -->
                @if($user->online_status === $status)
                    <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></div>
                @endif
            </button>
        @endforeach
    </div>
</div>
