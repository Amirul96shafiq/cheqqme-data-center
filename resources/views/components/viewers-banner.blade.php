 @props([
     'channel' => 'task-viewers',
     'id' => null,
     'label' => null,
     'fullWidth' => false,
 ])

@php
    $containerClasses = 'hidden items-center gap-2 rounded-md bg-amber-50 px-3 py-1.5 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20';
    if ($fullWidth) {
        $containerClasses = 'hidden basis-full w-full items-center gap-2 rounded-md bg-amber-50 px-3 py-1.5 text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20';
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

