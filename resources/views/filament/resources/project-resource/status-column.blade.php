@php
    $record = $getRecord();
    $status = $record->status;
@endphp

<div class="px-4 py-3 text-center">
    @if (empty($status))
        <span class="text-sm text-gray-900 dark:text-white">-</span>
    @else
        @php
            $statusLabels = [
                'Planning' => __('project.table.planning'),
                'In Progress' => __('project.table.in_progress'),
                'Completed' => __('project.table.completed'),
            ];
            $label = $statusLabels[$status] ?? $status;
        @endphp
        <x-filament::badge color="primary">
            {{ $label }}
        </x-filament::badge>
    @endif
</div>

