<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalClientsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count())
                ->description('All registered clients')
                ->color('success')
                ->icon('heroicon-o-user-group'),
        ];
    }
}