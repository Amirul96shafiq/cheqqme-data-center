<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TotalClientsWidget extends StatsOverviewWidget
{
    protected int|string|array $columnSpan = [
        'sm' => 12,
        'md' => 6,
        'lg' => 4,
    ];
    protected function getCards(): array
    {
        return [
            Card::make('Total Clients', Client::count())
                ->description('All registered clients')
                ->color('success')
                ->icon('heroicon-o-user-group'),
        ];
    }
}