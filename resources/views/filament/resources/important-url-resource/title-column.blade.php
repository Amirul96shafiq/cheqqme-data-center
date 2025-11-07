@php
    $record = $getRecord();
    $title = $record->title;
@endphp

<div class="px-4 py-3">
    @if (empty($title))
        <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
    @else
        @php
            $displayTitle = \Illuminate\Support\Str::limit($title, 15, '...');
        @endphp
        <span class="text-sm text-gray-900 dark:text-white" title="{{ $title }}">
            {{ $displayTitle }}
        </span>
    @endif
</div>

