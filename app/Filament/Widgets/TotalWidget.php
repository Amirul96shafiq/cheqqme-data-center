<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Project;
use App\Models\Document;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count())
                ->description('All registered clients')
                ->color('primary')
                ->icon('heroicon-o-briefcase'),
            Stat::make('Total Projects', Project::count())
                ->description('All registered projects')
                ->color('primary')
                ->icon('heroicon-o-folder-open'),
            Stat::make('Total Documents', Document::count())
                ->description('All registered documents')
                ->color('primary')
                ->icon('heroicon-o-archive-box'),
        ];
    }
}