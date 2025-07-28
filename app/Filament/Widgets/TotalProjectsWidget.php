<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TotalProjectsWidget extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Projects', Project::count())
                ->description('All registered projects')
                ->color('success')
                ->icon('heroicon-o-user-group'),
        ];
    }
}