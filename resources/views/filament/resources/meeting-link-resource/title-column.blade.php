@php
    $record = $getRecord();
    $title = $record->title;
    $platform = $record->meeting_platform ?: 'Google Meet';
    $startTime = $record->meeting_start_time ? $record->meeting_start_time->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s');
    $duration = $record->meeting_duration ?: 60;
    
    // Generate the full title using the same logic from the resource
    $formattedDate = 'Date & Time';
    if ($record->meeting_start_time) {
        try {
            $date = \Carbon\Carbon::parse($record->meeting_start_time);
            $formattedDate = $date->format('j/n/y - h:i A');
        } catch (\Exception $e) {
            $formattedDate = 'Invalid Date';
        }
    }
    
    $durationText = 'Duration';
    if ($duration) {
        $durationText = match ($duration) {
            30 => '30 minutes',
            60 => '1 hour',
            90 => '1 hour 30 minutes',
            120 => '2 hours',
            default => $duration.' minutes'
        };
    }
    
    $fullTitle = "{$title} - {$platform} - {$formattedDate} - {$durationText}";
    $displayTitle = \Illuminate\Support\Str::limit($fullTitle, 30, '...');
@endphp

@if (empty($fullTitle))
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    <span title="{{ $fullTitle }}">
        {{ $displayTitle }}
    </span>
@endif

