@php
    $record = $getRecord();
    $picName = $record->pic_name;
    $companyName = $record->company_name;
@endphp

@if (empty($picName))
    <span class="text-sm text-gray-900 dark:text-white" title="-">-</span>
@else
    @php
        $formattedName = \App\Helpers\ClientFormatter::formatClientDisplay($picName, $companyName);
        $tooltipText = __('client.table.tooltip.full_name').": {$picName}".', '.__('client.table.tooltip.company').": {$companyName}";
    @endphp
    <span title="{{ $tooltipText }}">
        {{ $formattedName }}
    </span>
@endif

