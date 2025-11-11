<?php

namespace App\Filament\Widgets;

use App\Models\ChatbotConversation;
use App\Models\OpenaiLog;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ChatbotUsageChart extends ApexChartWidget
{
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
        $categories = [];
        for ($i = 7; $i >= 0; $i--) {
            $categories[] = now()->subDays($i)->format('j M');
        }

        return $categories;
    }

    protected function getConversationsData(): array
    {
        $data = [];
        for ($i = 7; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = ChatbotConversation::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }

    protected function getApiCallsData(): array
    {
        $data = [];
        for ($i = 7; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $count = OpenaiLog::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return $data;
    }
}
