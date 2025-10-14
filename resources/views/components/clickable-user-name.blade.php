@props([
    'user' => null,
    'date' => null,
    'dateFormat' => 'j/n/y, h:i A',
    'showDate' => true,
    'fallbackText' => 'Unknown'
])

@if($user)
    <div class="inline-flex items-center">
        @if($showDate && $date)
            <span class="text-sm text-gray-900 dark:text-white">
                {{ $date->format($dateFormat) }}
            </span>
            <span class="ml-1">(</span>
        @endif
        
        <x-clickable-avatar-wrapper :user="$user">
            <button 
                class="cursor-pointer text-sm font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200 underline transition-colors duration-200"
                type="button"
            >
                {{ $user->short_name }}
            </button>
        </x-clickable-avatar-wrapper>
        
        @if($showDate && $date)
            <span>)</span>
        @endif
    </div>
@else
    @if($showDate && $date)
        <span class="text-sm text-gray-900 dark:text-white">
            {{ $date->format($dateFormat) }}
        </span>
        <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">({{ $fallbackText }})</span>
    @else
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $fallbackText }}</span>
    @endif
@endif
