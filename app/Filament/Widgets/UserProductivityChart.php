<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Comment;
use App\Models\Document;
use App\Models\ImportantUrl;
use App\Models\MeetingLink;
use App\Models\PhoneNumber;
use App\Models\Project;
use App\Models\Task;
use App\Models\TrelloBoard;
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
        return 'All-time tasks assigned & completed/archived, comments made, and resources created by each user';
    }

    protected function getOptions(): array
    {
        $users = User::all();
        $categories = [];
        $taskData = [];
        $commentData = [];
        $resourcesData = [];

        foreach ($users as $user) {
            $taskCount = Task::whereJsonContains('assigned_to', (string) $user->id)
                ->whereIn('status', ['completed', 'archived'])
                ->count();

            $commentCount = Comment::where('user_id', $user->id)
                ->where('status', '!=', 'deleted')
                ->whereNull('deleted_at')
                ->whereNull('parent_id') // Only count top-level comments, not replies
                ->count();

            // Count resources created by this user
            $resourcesCount = 0;

            // Tasks created (all tasks, not just completed)
            $resourcesCount += Task::where('updated_by', $user->id)->count();

            // Other resources
            $resourcesCount += MeetingLink::where('created_by', $user->id)->count();
            $resourcesCount += TrelloBoard::where('created_by', $user->id)->count();
            $resourcesCount += Client::where('updated_by', $user->id)->count();
            $resourcesCount += Project::where('updated_by', $user->id)->count();
            $resourcesCount += Document::where('updated_by', $user->id)->count();
            $resourcesCount += ImportantUrl::where('updated_by', $user->id)->count();
            $resourcesCount += PhoneNumber::where('updated_by', $user->id)->count();

            // Include users with any activity
            if ($taskCount > 0 || $commentCount > 0 || $resourcesCount > 0) {
                $categories[] = $this->formatDisplayName($user);
                $taskData[] = $taskCount;
                $commentData[] = $commentCount;
                $resourcesData[] = $resourcesCount;
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
                    'name' => 'Completed & Archived Tasks',
                    'data' => $taskData,
                ],
                [
                    'name' => 'Comments Made',
                    'data' => $commentData,
                ],
                [
                    'name' => 'Resources Created',
                    'data' => $resourcesData,
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
            'colors' => ['#fbb43e', '#10b981', '#3b82f6'],
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
                        if (seriesName === "Completed & Archived Tasks") {
                            return val + " assigned tasks completed & archived";
                        } else if (seriesName === "Comments Made") {
                            return val + " comments made";
                        } else if (seriesName === "Resources Created") {
                            return val + " resources created (hover individual bars for details)";
                        }
                        return val;
                    }',
                ],
            ],
        ];
    }

    private function formatDisplayName(User $user): string
    {
        $name = $user->name ?? $user->username;

        if (! $name) {
            return 'User #'.$user->id;
        }

        $parts = array_values(array_filter(preg_split('/\s+/', trim($name))));

        if (count($parts) === 1) {
            return $parts[0];
        }

        $firstName = array_shift($parts);
        $initials = array_map(
            fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)).'.',
            $parts
        );

        return $firstName.' '.implode(' ', $initials);
    }
}
