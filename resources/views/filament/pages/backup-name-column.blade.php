@php
    $record = $getRecord();
    $backupName = $record->backup_name;
@endphp

@if (empty($backupName))
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $displayName = \Illuminate\Support\Str::limit($backupName, 20, '...');
    @endphp
    <span title="{{ $backupName }}">
        {{ $displayName }}
    </span>
@endif

