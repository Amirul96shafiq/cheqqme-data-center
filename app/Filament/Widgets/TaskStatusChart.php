<?php

namespace App\Filament\Widgets;

use App\Models\Task;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Concerns\CanFilter;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TaskStatusChart extends ApexChartWidget
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

    protected function getFormSchema(): array
    {
        return [
            Select::make('user_ids')
                ->label(__('dashboard.analytics.task_status_distribution.filters.users'))
                ->options(\App\Models\User::getUserSelectOptions())
                ->searchable()
                ->preload()
                ->native(false)
                ->nullable()
                ->placeholder(__('dashboard.analytics.task_status_distribution.filters.all_users'))
                ->columnSpanFull(),

            Select::make('card_type')
                ->label(__('dashboard.analytics.task_status_distribution.filters.card_type'))
                ->options([
                    'all' => __('dashboard.analytics.task_status_distribution.filters.all_cards'),
                    'tasks' => __('dashboard.analytics.task_status_distribution.filters.tasks'),
                    'issue_trackers' => __('dashboard.analytics.task_status_distribution.filters.issue_trackers'),
                    'wishlist_trackers' => __('dashboard.analytics.task_status_distribution.filters.wishlist_trackers'),
                ])
                ->default('all')
                ->searchable()
                ->columnSpanFull(),
        ];
    }

    protected function getOptions(): array
    {
        $cardType = $this->filterFormData['card_type'] ?? 'all';
        $userId = $this->filterFormData['user_ids'] ?? null;

        $orderedStatuses = Task::availableStatuses();
        $statusKeys = array_keys($orderedStatuses);

        // Build base query based on card type and user
        $baseQuery = Task::query();

        // Apply card type filtering
        $baseQuery = match ($cardType) {
            'tasks' => $baseQuery->whereNull('tracking_token'),
            'issue_trackers' => $baseQuery->whereNotNull('tracking_token'),
            'wishlist_trackers' => $baseQuery->wishlistTokens(),
            default => $baseQuery, // 'all' - no filtering
        };

        // Apply user filtering - filter by tasks assigned to the user
        if ($userId) {
            $baseQuery = $baseQuery->where(function ($query) use ($userId) {
                $query->whereJsonContains('assigned_to', (int) $userId)
                    ->orWhereJsonContains('assigned_to', (string) $userId);
            });
        }

        $data = array_map(
            function (string $status) use ($baseQuery) {
                return (clone $baseQuery)->where('status', $status)->count();
            },
            $statusKeys,
        );

        $labels = array_map(
            fn (string $status): string => $orderedStatuses[$status] ?? ucfirst(str_replace('_', ' ', $status)),
            $statusKeys,
        );

        // Also count issue trackers that have tracking_token but different status
        // Only include this if we're showing all cards or issue trackers
        if (in_array($cardType, ['all', 'issue_trackers'])) {
            $additionalIssueTrackersQuery = Task::whereNotNull('tracking_token')
                ->whereNotIn('status', $statusKeys);

            // If we're showing only issue trackers, no need to filter further
            // The base query already filters for tracking_token
            if ($cardType === 'all') {
                $additionalIssueTrackers = $additionalIssueTrackersQuery->count();
            } else {
                // For issue_trackers filter, we need to count all issue trackers with non-standard status
                $additionalIssueTrackers = (clone $baseQuery)->whereNotIn('status', $statusKeys)->count();
            }

            if ($additionalIssueTrackers > 0) {
                $data[] = (int) $additionalIssueTrackers;
                $labels[] = __('dashboard.analytics.task_status_distribution.other_issue_trackers');
            }
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
