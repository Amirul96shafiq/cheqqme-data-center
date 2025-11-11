<?php

namespace App\Filament\Widgets;

use App\Models\ChatbotConversation;
use App\Models\OpenaiLog;
use Filament\Forms\Components\DatePicker;
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

            Select::make('quick_filter')
                ->label(__('dashboard.analytics.chatbot_usage.filters.quick_filter'))
                ->options([
                    'today' => __('dashboard.analytics.chatbot_usage.filters.today'),
                    'yesterday' => __('dashboard.analytics.chatbot_usage.filters.yesterday'),
                    'this_week' => __('dashboard.analytics.chatbot_usage.filters.this_week'),
                    'this_month' => __('dashboard.analytics.chatbot_usage.filters.this_month'),
                ])
                ->searchable()
                ->default('this_week')
                ->afterStateUpdated(function ($state, callable $set) {
                    $this->updateDateRange($state, $set);
                })
                ->live(),

            DatePicker::make('date_start')
                ->label(__('dashboard.analytics.chatbot_usage.filters.date_start'))
                ->placeholder('DD/MM/YYYY')
                ->native(false)
                ->displayFormat('j/n/y')
                ->default(now()->startOfWeek()->toDateString()),

            DatePicker::make('date_end')
                ->label(__('dashboard.analytics.chatbot_usage.filters.date_end'))
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

        }
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
        $startDate = $this->filterFormData['date_start'] ?? now()->subDays(7)->toDateString();
        $endDate = $this->filterFormData['date_end'] ?? now()->toDateString();

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

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
        $startDate = $this->filterFormData['date_start'] ?? now()->subDays(7)->toDateString();
        $endDate = $this->filterFormData['date_end'] ?? now()->toDateString();

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        $data = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $count = ChatbotConversation::whereDate('created_at', $current->toDateString())->count();
            $data[] = $count;
            $current->addDay();
        }

        return $data;
    }

    protected function getApiCallsData(): array
    {
        $startDate = $this->filterFormData['date_start'] ?? now()->subDays(7)->toDateString();
        $endDate = $this->filterFormData['date_end'] ?? now()->toDateString();

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);

        $data = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $count = OpenaiLog::whereDate('created_at', $current->toDateString())->count();
            $data[] = $count;
            $current->addDay();
        }

        return $data;
    }
}
