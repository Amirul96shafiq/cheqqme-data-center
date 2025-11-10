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
        return 'AI Assistant Usage';
    }

    public function getDescription(): ?string
    {
        return 'Chatbot conversations and API usage over time';
    }

    protected function getOptions(): array
    {
        return [
            'chart' => [
                'type' => 'line',
                'height' => '300px',
                'toolbar' => [
                    'show' => true,
                ],
            ],
            'series' => [
                [
                    'name' => 'Conversations',
                    'data' => $this->getConversationsData(),
                ],
                [
                    'name' => 'API Calls',
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
            'colors' => ['#3b82f6', '#10b981'],
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
            ],
            'legend' => [
                'position' => 'top',
                'fontFamily' => 'inherit',
            ],
        ];
    }

    protected function getDateCategories(): array
    {
        $categories = [];
        for ($i = 7; $i >= 0; $i--) {
            $categories[] = now()->subDays($i)->format('M d');
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
