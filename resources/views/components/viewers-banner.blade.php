 @props([
     'channel' => 'task-viewers',
     'id' => null,
     'label' => null,
     'fullWidth' => false,
 ])

@php
    $containerClasses = 'hidden items-center gap-2 rounded-md bg-warning-50 px-3 py-1.5 text-warning-700 ring-1 ring-warning-200 dark:bg-warning-500/10 dark:text-warning-300 dark:ring-warning-400/20';
    if ($fullWidth) {
        $containerClasses = 'hidden basis-full w-full items-center gap-2 rounded-md bg-warning-50 px-3 py-1.5 text-warning-700 ring-1 ring-warning-200 dark:bg-warning-500/10 dark:text-warning-300 dark:ring-warning-400/20';
    }
@endphp

<div
    class="viewers-banner {{ $containerClasses }}"
    data-channel="{{ $channel }}"
    @if($id !== null) data-id="{{ $id }}" @endif
>
     <div class="flex w-full flex-wrap items-center gap-2 text-xs sm:text-sm">
         <span class="font-medium">{{ $label ? __($label) : __('action.currently_viewing') }}:</span>
        <div class="avatars flex -space-x-2"></div>
        <span class="names flex-1 truncate"></span>
    </div>
</div>

