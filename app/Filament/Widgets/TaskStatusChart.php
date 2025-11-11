<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TaskStatusChart extends ApexChartWidget
{
    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
        'xl' => 2,
        '2xl' => 2,
    ];

    protected static ?int $sort = 1;

    protected static ?string $chartId = 'task-status-chart';

    public function getHeading(): ?string
    {
        return __('dashboard.analytics.task_status_distribution.heading');
    }

    public function getSubheading(): ?string
    {
        return __('dashboard.analytics.task_status_distribution.description');
    }

    protected function getOptions(): array
    {
        $orderedStatuses = Task::availableStatuses();
        $statusKeys = array_keys($orderedStatuses);

        $data = array_map(
            static fn (string $status): int => Task::where('status', $status)->count(),
            $statusKeys,
        );

        $labels = array_map(
            fn (string $status): string => $orderedStatuses[$status] ?? ucfirst(str_replace('_', ' ', $status)),
            $statusKeys,
        );

        // Also count issue trackers that have tracking_token but different status
        $additionalIssueTrackers = Task::whereNotNull('tracking_token')
            ->whereNotIn('status', $statusKeys)
            ->count();

        if ($additionalIssueTrackers > 0) {
            $data[] = (int) $additionalIssueTrackers;
            $labels[] = __('dashboard.analytics.task_status_distribution.other_issue_trackers');
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => '315px',
            ],
            'series' => $data,
            'labels' => $labels,
            'colors' => array_slice([
                '#fbb43e',
                '#14b8a6',
                '#fcd34d',
                '#0d9488',
                '#f59e0b',
                '#5eead4',
                '#b45309',
                '#115e59',
            ], 0, count($data)),
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
            'dataLabels' => [
                'enabled' => true,
            ],
            'tooltip' => [
                'enabled' => true,
                'theme' => 'custom-tooltip-theme',
                'cssClass' => 'apexcharts-tooltip-custom',
                'fillSeriesColor' => false,
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }
}
