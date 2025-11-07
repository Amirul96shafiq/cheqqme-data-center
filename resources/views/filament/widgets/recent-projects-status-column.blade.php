@php
    $record = $getRecord();
    $status = $record->status;
@endphp

<div class="px-4 py-3">
    @if (empty($status))
        <span class="text-sm text-gray-900 dark:text-white">-</span>
    @else
        @php
            $statusLabels = [
                'Planning' => __('dashboard.recent_projects.planning'),
                'In Progress' => __('dashboard.recent_projects.in_progress'),
                'Completed' => __('dashboard.recent_projects.completed'),
            ];
            $label = $statusLabels[$status] ?? $status;
            $color = match ($status) {
                'Planning' => 'primary',
                'In Progress' => 'info',
                'Completed' => 'success',
                default => 'gray',
            };
        @endphp
        <x-filament::badge color="{{ $color }}">
            {{ $label }}
        </x-filament::badge>
    @endif
</div>

