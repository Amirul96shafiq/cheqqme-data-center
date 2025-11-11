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
        return 'Task Status Distribution';
    }

    protected function getOptions(): array
    {
        $orderedStatuses = [
            'issue_tracker' => __('action.status.issue_tracker'),
            'todo' => __('task.status.todo'),
            'in_progress' => __('task.status.in_progress'),
            'toreview' => __('task.status.toreview'),
            'completed' => __('task.status.completed'),
            'archived' => __('task.status.archived'),
        ];

        $data = array_map(
            static fn (string $status): int => Task::where('status', $status)->count(),
            array_keys($orderedStatuses),
        );

        $labels = array_map(
            fn (string $status): string => $orderedStatuses[$status] ?? ucfirst(str_replace('_', ' ', $status)),
            array_keys($orderedStatuses),
        );

        // Also count issue trackers that have tracking_token but different status
        $additionalIssueTrackers = Task::whereNotNull('tracking_token')
            ->whereNotIn('status', array_keys($orderedStatuses))
            ->count();

        if ($additionalIssueTrackers > 0) {
            $data[] = (int) $additionalIssueTrackers;
            $labels[] = 'Other Issue Trackers';
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
