<?php

namespace App\Filament\Widgets;

use App\Models\Comment;
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
        return 'All-time tasks completed and comments made by each user';
    }

    protected function getOptions(): array
    {
        $users = User::all();
        $categories = [];
        $taskData = [];
        $commentData = [];

        foreach ($users as $user) {
            $taskCount = Task::where('updated_by', $user->id)
                ->where('status', 'completed')
                ->count();

            $commentCount = Comment::where('user_id', $user->id)
                ->where('status', '!=', 'deleted')
                ->whereNull('deleted_at')
                ->whereNull('parent_id') // Only count top-level comments, not replies
                ->count();

            // Include users with either completed tasks or comments
            if ($taskCount > 0 || $commentCount > 0) {
                $categories[] = $user->name ?? $user->username ?? 'User #'.$user->id;
                $taskData[] = $taskCount;
                $commentData[] = $commentCount;
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
                    'data' => $taskData,
                ],
                [
                    'name' => 'Comments Made',
                    'data' => $commentData,
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
            'colors' => ['#fbb43e', '#10b981'],
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
                    'formatter' => 'function (val, opts) {
                        const seriesName = opts.seriesName;
                        if (seriesName === "Completed Tasks") {
                            return val + " tasks completed";
                        } else if (seriesName === "Comments Made") {
                            return val + " comments made";
                        }
                        return val;
                    }',
                ],
            ],
        ];
    }
}
