<?php

namespace App\Filament\Widgets;

use App\Models\ChatbotConversation;
use App\Models\OpenaiLog;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Concerns\CanFilter;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ChatbotUsageChart extends ApexChartWidget
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

    protected static ?int $sort = 3;

    protected static ?string $chartId = 'chatbot-usage-chart';

    public function getHeading(): ?string
    {
        return __('dashboard.analytics.chatbot_usage.heading');
    }

    public function getSubheading(): ?string
    {
        return __('dashboard.analytics.chatbot_usage.description');
    }

    protected function getFormSchema(): array
    {
        return [

            Select::make('user_ids')
                ->label(__('dashboard.analytics.chatbot_usage.filters.users'))
                ->options(\App\Models\User::getUserSelectOptions())
                ->searchable()
                ->preload()
                ->native(false)
                ->nullable()
                ->placeholder(__('dashboard.analytics.chatbot_usage.filters.all_users'))
                ->columnSpanFull(),

            Select::make('quick_filter')
                ->label(__('dashboard.analytics.chatbot_usage.filters.quick_filter'))
                ->options([
                    'today' => __('dashboard.analytics.chatbot_usage.filters.today'),
                    'yesterday' => __('dashboard.analytics.chatbot_usage.filters.yesterday'),
                    'this_week' => __('dashboard.analytics.chatbot_usage.filters.this_week'),
                ])
                ->searchable()
                ->default('this_week')
                ->live(),

        ];
    }

    protected function getDateRange(): array
    {
        $quickFilter = $this->filterFormData['quick_filter'] ?? 'this_week';
        $now = now();

        return match ($quickFilter) {
            'today' => [
                'start' => $now->copy()->startOfDay()->toDateString(),
                'end' => $now->copy()->endOfDay()->toDateString(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay()->toDateString(),
                'end' => $now->copy()->subDay()->endOfDay()->toDateString(),
            ],
            'this_week' => [
                'start' => $now->copy()->startOfWeek()->toDateString(),
                'end' => $now->copy()->endOfWeek()->toDateString(),
            ],
            default => [
                'start' => $now->copy()->startOfWeek()->toDateString(),
                'end' => $now->copy()->endOfWeek()->toDateString(),
            ],
        };
    }

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'line',
                'height' => '300px',
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => __('dashboard.analytics.chatbot_usage.series.conversations'),
                    'data' => $this->getConversationsData(),
                ],
                [
                    'name' => __('dashboard.analytics.chatbot_usage.series.api_calls'),
                    'data' => $this->getApiCallsData(),
                ],
            ],
            'xaxis' => [
                'categories' => $this->getDateCategories(),
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
            'stroke' => [
                'curve' => 'smooth',
                'width' => [3, 2],
                'dashArray' => [0, 5],
            ],
            'markers' => [
                'size' => 4,
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
            'tooltip' => [
                'shared' => true,
                'intersect' => false,
                'theme' => 'custom-tooltip-theme',
                'cssClass' => 'apexcharts-tooltip-custom',
                'style' => [
                    'fontSize' => '12px',
                    'fontFamily' => 'inherit',
                ],
            ],
            'legend' => [
                'position' => 'bottom',
                'fontFamily' => 'inherit',
            ],
        ];
    }

    protected function getDateCategories(): array
    {
        $dateRange = $this->getDateRange();
        $start = \Carbon\Carbon::parse($dateRange['start']);
        $end = \Carbon\Carbon::parse($dateRange['end']);

        $categories = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $categories[] = $current->format('j M');
            $current->addDay();
        }

        return $categories;
    }

    protected function getConversationsData(): array
    {
        $dateRange = $this->getDateRange();
        $userId = $this->filterFormData['user_ids'] ?? null;

        $start = \Carbon\Carbon::parse($dateRange['start']);
        $end = \Carbon\Carbon::parse($dateRange['end']);

        $data = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $query = ChatbotConversation::whereDate('created_at', $current->toDateString());

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $count = $query->count();
            $data[] = $count;
            $current->addDay();
        }

        return $data;
    }

    protected function getApiCallsData(): array
    {
        $dateRange = $this->getDateRange();
        $userId = $this->filterFormData['user_ids'] ?? null;

        $start = \Carbon\Carbon::parse($dateRange['start']);
        $end = \Carbon\Carbon::parse($dateRange['end']);

        $data = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $query = OpenaiLog::whereDate('created_at', $current->toDateString());

            if ($userId) {
                $query->where('user_id', $userId);
            }

            $count = $query->count();
            $data[] = $count;
            $current->addDay();
        }

        return $data;
    }
}
