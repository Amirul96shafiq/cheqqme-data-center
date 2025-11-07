@php
    $record = $getRecord();
@endphp

<div class="px-4 py-3">
    @if (!$record->client)
        <span class="text-sm text-gray-900 dark:text-white">-</span>
    @else
        @php
            $picName = $record->client->pic_name;
            $companyName = $record->client->company_name;
            $clientUrl = $record->client_id ? \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $record->client_id]) : null;
            $tooltipText = __('project.table.tooltip.full_name').": {$picName}".', '. __('project.table.tooltip.company').": {$companyName}";
        @endphp
        @if(empty($picName))
            <span class="text-sm text-gray-900 dark:text-white">-</span>
        @else
            @if($clientUrl)
                <a href="{{ $clientUrl }}"
                   target="_blank"
                   class="text-sm text-primary-600 dark:text-primary-400 hover:underline"
                   title="{{ $tooltipText }}">
                    {{ \App\Helpers\ClientFormatter::formatClientDisplay($picName, $companyName) }}
                </a>
            @else
                <span class="text-sm text-gray-900 dark:text-white">
                    {{ \App\Helpers\ClientFormatter::formatClientDisplay($picName, $companyName) }}
                </span>
            @endif
        @endif
    @endif
</div>

