@php
    $record = $getRecord();
    $client = $record->client;
@endphp

@if (!$client)
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $picName = $client->pic_name;
        $companyName = $client->company_name;
        $formattedName = \App\Helpers\ClientFormatter::formatClientDisplay($picName, $companyName);
        $tooltipText = __('importanturl.table.tooltip.full_name').": {$picName}".', '.__('importanturl.table.tooltip.company').": {$companyName}";
        $clientUrl = $record->client_id ? \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $record->client_id]) : null;
    @endphp
    @if(empty($picName))
        <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
    @else
        @if($clientUrl)
            <a href="{{ $clientUrl }}" 
               target="_blank" 
               class="text-primary-600 dark:text-primary-400 hover:underline"
               title="{{ $tooltipText }}">
                {{ $formattedName }}
            </a>
        @else
            <span title="{{ $tooltipText }}">
                {{ $formattedName }}
            </span>
        @endif
    @endif
@endif

