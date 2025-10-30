@php
    $record = $getRecord();
    $title = $record->title;
@endphp

@if (empty($title))
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $displayTitle = \Illuminate\Support\Str::limit($title, 15, '...');
    @endphp
    <span title="{{ $title }}">
        {{ $displayTitle }}
    </span>
@endif

