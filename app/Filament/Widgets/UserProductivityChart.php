<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use App\Models\User;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UserProductivityChart extends ApexChartWidget
{
    protected int|string|array $columnSpan = [
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 2,
        'xl' => 2,
        '2xl' => 2,
    ];

    protected static ?int $sort = 2;

    protected static ?string $chartId = 'user-productivity-chart';

    public function getHeading(): ?string
    {
        return 'User Productivity';
    }

    public function getDescription(): ?string
    {
        return 'Tasks completed by each user this month';
    }

    protected function getOptions(): array
    {
        $users = User::all();
        $categories = [];
        $data = [];

        foreach ($users as $user) {
            $taskCount = Task::where('updated_by', $user->id)
                ->where('status', 'completed')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count();

            if ($taskCount > 0) { // Only include users with completed tasks
                $categories[] = $user->short_name ?? $user->name ?? $user->username ?? 'User #'.$user->id;
                $data[] = $taskCount;
            }
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => '300px',
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Completed Tasks',
                    'data' => $data,
                ],
            ],
            'xaxis' => [
                'categories' => $categories,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#fbb43e'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 4,
                    'columnWidth' => '60%',
                ],
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'y' => [
                    'formatter' => 'function (val) {
                        return val + " tasks completed";
                    }',
                ],
            ],
        ];
    }
}
