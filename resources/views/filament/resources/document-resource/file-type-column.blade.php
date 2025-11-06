@php
    $record = $getRecord();
    $fileType = '-';
    
    if ($record->type === 'internal' && $record->file_path) {
        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));
        
        $fileType = match ($extension) {
            'jpg', 'jpeg' => 'JPG',
            'png' => 'PNG',
            'pdf' => 'PDF',
            default => strtoupper($extension),
        };
    } elseif ($record->type === 'external') {
        $fileType = 'URL';
    }
@endphp

@if($fileType !== '-')
    <x-filament::badge color="primary">
        {{ $fileType }}
    </x-filament::badge>
@else
    <span class="text-sm text-gray-400 dark:text-gray-500">-</span>
@endif

