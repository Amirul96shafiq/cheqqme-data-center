@props([
    'onClick' => null,
    'ariaLabel' => 'Close',
    'size' => 'md', // sm, md, lg
    'variant' => 'default' // default, minimal
])

@php
    $sizeClasses = match($size) {
        'sm' => 'w-3 h-3',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        default => 'w-4 h-4'
    };
    
    $variantClasses = match($variant) {
        'minimal' => 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200',
        'default' => 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600',
        default => 'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600'
    };
@endphp

<button 
    type="button" 
    @if($onClick) onclick="{{ $onClick }}" @endif
    class="{{ $variantClasses }}"
    aria-label="{{ $ariaLabel }}"
    {{ $attributes }}
>
    <x-heroicon-o-x-mark class="{{ $sizeClasses }}" />
</button>
