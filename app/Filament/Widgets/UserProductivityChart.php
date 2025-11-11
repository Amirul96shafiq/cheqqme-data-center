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
        return __('dashboard.analytics.user_productivity.heading');
    }

    public function getDescription(): ?string
    {
        return __('dashboard.analytics.user_productivity.description');
    }

    protected function getOptions(): array
    {
        $users = User::all();
        $categories = [];
        $taskData = [];
        $commentData = [];
        $meetingsData = [];
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

            // Meetings joined
            $meetingsJoinedCount = MeetingLink::query()
                ->where(function ($query) use ($user) {
                    $query
                        ->whereJsonContains('user_ids', $user->id)
                        ->orWhereJsonContains('user_ids', (string) $user->id)
                        ->orWhereRaw('JSON_EXTRACT(user_ids, "$") LIKE ?', ['%"'.$user->id.'"%'])
                        ->orWhere('created_by', $user->id);
                })
                ->count();

            // Other resources
            $resourcesCount += MeetingLink::where('created_by', $user->id)->count();
            $resourcesCount += TrelloBoard::where('created_by', $user->id)->count();
            $resourcesCount += Client::where('updated_by', $user->id)->count();
            $resourcesCount += Project::where('updated_by', $user->id)->count();
            $resourcesCount += Document::where('updated_by', $user->id)->count();
            $resourcesCount += ImportantUrl::where('updated_by', $user->id)->count();
            $resourcesCount += PhoneNumber::where('updated_by', $user->id)->count();
            
            // Include users with any activity
            if ($taskCount > 0 || $commentCount > 0 || $resourcesCount > 0 || $meetingsJoinedCount > 0) {
                $categories[] = $this->formatDisplayName($user);
                $taskData[] = $taskCount;
                $commentData[] = $commentCount;
                $meetingsData[] = $meetingsJoinedCount;
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
                    'name' => __('dashboard.analytics.user_productivity.series.completed_tasks'),
                    'data' => $taskData,
                ],
                [
                    'name' => __('dashboard.analytics.user_productivity.series.comments_made'),
                    'data' => $commentData,
                ],
                [
                    'name' => __('dashboard.analytics.user_productivity.series.meetings_joined'),
                    'data' => $meetingsData,
                ],
                [
                    'name' => __('dashboard.analytics.user_productivity.series.resources_created'),
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
            'colors' => ['#fbb43e', '#10b981', '#3b82f6', '#8b5cf6'],
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
                'enabled' => true,
                'theme' => 'custom-tooltip-theme',
                'cssClass' => 'apexcharts-tooltip-custom',
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'inherit',
                ],
                'y' => [
                    'formatter' => 'function (val, opts) {
                        const seriesName = opts.seriesName;
                        if (seriesName === "'.__('dashboard.analytics.user_productivity.series.completed_tasks').'") {
                            return val + " '.__('dashboard.analytics.user_productivity.tooltip.completed_tasks').'";
                        } else if (seriesName === "'.__('dashboard.analytics.user_productivity.series.comments_made').'") {
                            return val + " '.__('dashboard.analytics.user_productivity.tooltip.comments_made').'";
                        } else if (seriesName === "'.__('dashboard.analytics.user_productivity.series.meetings_joined').'") {
                            return val + " '.__('dashboard.analytics.user_productivity.tooltip.meetings_joined').'";
                        } else if (seriesName === "'.__('dashboard.analytics.user_productivity.series.resources_created').'") {
                            return val + " '.__('dashboard.analytics.user_productivity.tooltip.resources_created').'";
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
