@php
    $record = $getRecord();
    $title = $record->title;
    $eventType = $record->event_type ?: 'online';
    $startTime = $record->start_datetime ? $record->start_datetime->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');

    // Generate the full title using the same logic from the resource
    $formattedDate = 'Date & Time';
    if ($record->start_datetime) {
        try {
            $date = \Carbon\Carbon::parse($record->start_datetime);
            $formattedDate = $date->format('j/n/y - h:i A');
        } catch (\Exception $e) {
            $formattedDate = 'Invalid Date';
        }
    }

    $eventTypeLabel = match ($eventType) {
        'online' => 'Online',
        'offline' => 'Offline',
        default => $eventType
    };

    $fullTitle = "{$title} - {$eventTypeLabel} - {$formattedDate}";
    $displayTitle = \Illuminate\Support\Str::limit($fullTitle, 30, '...');
@endphp

<div class="px-4 py-3">
    @if (empty($fullTitle))
        <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
    @else
        <span class="text-sm text-gray-900 dark:text-white" title="{{ $fullTitle }}">
            {{ $displayTitle }}
        </span>
    @endif
</div>
