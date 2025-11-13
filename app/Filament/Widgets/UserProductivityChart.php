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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Concerns\CanFilter;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UserProductivityChart extends ApexChartWidget
{
    use CanFilter;

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

    public function getSubheading(): ?string
    {
        return __('dashboard.analytics.user_productivity.description');
    }

    protected function getFormSchema(): array
    {
        return [

            Select::make('user_ids')
                ->label(__('dashboard.analytics.user_productivity.filters.users'))
                ->options(function () {
                    return User::withTrashed()
                        ->orderBy('username')
                        ->get()
                        ->mapWithKeys(fn ($u) => [
                            $u->id => ($u->username ?: 'User #'.$u->id).($u->deleted_at ? ' (deleted)' : ''),
                        ])
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->native(false)
                ->nullable()
                ->placeholder(__('dashboard.analytics.user_productivity.filters.all_users'))
                ->columnSpanFull(),

            Select::make('quick_filter')
                ->label(__('dashboard.analytics.user_productivity.filters.quick_filter'))
                ->options([
                    'today' => __('dashboard.analytics.user_productivity.filters.today'),
                    'yesterday' => __('dashboard.analytics.user_productivity.filters.yesterday'),
                    'this_week' => __('dashboard.analytics.user_productivity.filters.this_week'),
                    'this_month' => __('dashboard.analytics.user_productivity.filters.this_month'),
                    'this_year' => __('dashboard.analytics.user_productivity.filters.this_year'),
                    'overall' => __('dashboard.analytics.user_productivity.filters.overall'),
                ])
                ->searchable()
                ->default('this_week')
                ->afterStateUpdated(function ($state, callable $set) {
                    $this->updateDateRange($state, $set);
                })
                ->live(),

            DatePicker::make('date_start')
                ->label(__('dashboard.analytics.user_productivity.filters.date_start'))
                ->placeholder('DD/MM/YYYY')
                ->native(false)
                ->displayFormat('j/n/y')
                ->default(now()->startOfWeek()->toDateString()),

            DatePicker::make('date_end')
                ->label(__('dashboard.analytics.user_productivity.filters.date_end'))
                ->placeholder('DD/MM/YYYY')
                ->native(false)
                ->displayFormat('j/n/y')
                ->default(now()->endOfWeek()->toDateString()),

        ];
    }

    protected function updateDateRange($filter, callable $set): void
    {
        $now = now();

        switch ($filter) {
            case 'today':
                $set('date_start', $now->startOfDay()->toDateString());
                $set('date_end', $now->endOfDay()->toDateString());
                break;
            case 'yesterday':
                $set('date_start', $now->subDay()->startOfDay()->toDateString());
                $set('date_end', $now->subDay()->endOfDay()->toDateString());
                break;
            case 'this_week':
                $set('date_start', $now->startOfWeek()->toDateString());
                $set('date_end', $now->endOfWeek()->toDateString());
                break;
            case 'this_month':
                $set('date_start', $now->startOfMonth()->toDateString());
                $set('date_end', $now->endOfMonth()->toDateString());
                break;
            case 'this_year':
                $set('date_start', $now->startOfYear()->toDateString());
                $set('date_end', $now->endOfYear()->toDateString());
                break;
            case 'overall':
                // For overall, we'll handle this specially in getOptions method
                $set('date_start', null);
                $set('date_end', null);
                break;
        }
    }

    protected function getOptions(): array
    {
        $userId = $this->filterFormData['user_ids'] ?? null;
        $startDate = $this->filterFormData['date_start'] ?? now()->startOfWeek()->toDateString();
        $endDate = $this->filterFormData['date_end'] ?? now()->endOfWeek()->toDateString();
        $quickFilter = $this->filterFormData['quick_filter'] ?? 'this_week';

        // For overall filter, don't apply date filtering
        $applyDateFilter = $quickFilter !== 'overall' && $startDate && $endDate;

        if ($applyDateFilter) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
        }

        // Filter users if a specific user is selected
        $usersQuery = User::query();
        if ($userId) {
            $usersQuery->where('id', $userId);
        }
        $users = $usersQuery->get();

        $categories = [];
        $taskData = [];
        $commentData = [];
        $meetingsData = [];
        $resourcesData = [];

        foreach ($users as $user) {
            $taskQuery = Task::whereJsonContains('assigned_to', (string) $user->id)
                ->whereIn('status', ['completed', 'archived']);
            if ($applyDateFilter) {
                $taskQuery->whereBetween('updated_at', [$start, $end]);
            }
            $taskCount = $taskQuery->count();

            $commentQuery = Comment::where('user_id', $user->id)
                ->where('status', '!=', 'deleted')
                ->whereNull('deleted_at')
                ->whereNull('parent_id'); // Only count top-level comments, not replies
            if ($applyDateFilter) {
                $commentQuery->whereBetween('created_at', [$start, $end]);
            }
            $commentCount = $commentQuery->count();

            // Count resources created by this user
            $resourcesCount = 0;

            // Tasks created (all tasks, not just completed)
            $taskCreatedQuery = Task::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $taskCreatedQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $taskCreatedQuery->count();

            // Meetings joined
            $meetingsQuery = MeetingLink::query()
                ->where(function ($query) use ($user) {
                    $query
                        ->whereJsonContains('user_ids', $user->id)
                        ->orWhereJsonContains('user_ids', (string) $user->id)
                        ->orWhereRaw('JSON_EXTRACT(user_ids, "$") LIKE ?', ['%"'.$user->id.'"%'])
                        ->orWhere('created_by', $user->id);
                });
            if ($applyDateFilter) {
                $meetingsQuery->whereBetween('created_at', [$start, $end]);
            }
            $meetingsJoinedCount = $meetingsQuery->count();

            // Other resources
            $meetingCreatedQuery = MeetingLink::where('created_by', $user->id);
            if ($applyDateFilter) {
                $meetingCreatedQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $meetingCreatedQuery->count();

            $trelloQuery = TrelloBoard::where('created_by', $user->id);
            if ($applyDateFilter) {
                $trelloQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $trelloQuery->count();

            $clientQuery = Client::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $clientQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $clientQuery->count();

            $projectQuery = Project::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $projectQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $projectQuery->count();

            $documentQuery = Document::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $documentQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $documentQuery->count();

            $urlQuery = ImportantUrl::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $urlQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $urlQuery->count();

            $phoneQuery = PhoneNumber::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $phoneQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $phoneQuery->count();

            $userQuery = User::where('updated_by', $user->id);
            if ($applyDateFilter) {
                $userQuery->whereBetween('created_at', [$start, $end]);
            }
            $resourcesCount += $userQuery->count();

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
            'colors' => [
                '#fbb43e',
                '#14b8a6',
                '#fcd34d',
                '#0d9488',
                '#f59e0b',
                '#5eead4',
                '#b45309',
                '#115e59',
            ],
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
