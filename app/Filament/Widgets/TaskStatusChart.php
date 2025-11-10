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
        $allStatuses = ['todo', 'in_progress', 'to_review', 'completed', 'archived', 'issue_tracker'];
        $statusLabels = [
            'todo' => 'To Do',
            'in_progress' => 'In Progress',
            'to_review' => 'To Review',
            'completed' => 'Completed',
            'archived' => 'Archived',
            'issue_tracker' => 'Issue Tracker',
        ];

        $data = [];
        $labels = [];

        // Count all tasks by status (including both regular tasks and issue trackers)
        foreach ($allStatuses as $status) {
            $count = Task::where('status', $status)->count();
            if ($count > 0) {
                $data[] = (int) $count;
                $labels[] = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
            }
        }

        // Also count issue trackers that have tracking_token but different status
        $additionalIssueTrackers = Task::whereNotNull('tracking_token')
            ->whereNotIn('status', $allStatuses)
            ->count();

        if ($additionalIssueTrackers > 0) {
            $data[] = (int) $additionalIssueTrackers;
            $labels[] = 'Other Issue Trackers';
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => array_values($data),
            'labels' => array_values($labels),
            'colors' => array_slice([
                '#fbb43e',
                '#3b82f6',
                '#f59e0b',
                '#10b981',
                '#6b7280',
                '#8b5cf6',
                '#ec4899',
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
            ],
        ];
    }
}
