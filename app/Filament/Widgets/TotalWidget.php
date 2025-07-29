<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Project;
use App\Models\Document;
use App\Models\ImportantUrl;
use App\Models\PhoneNumber;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Actions\Action;

class TotalWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Clients', Client::count())
                ->description('View All Clients →')
                ->color('primary')
                ->icon('heroicon-o-briefcase')
                ->url(route('filament.admin.resources.clients.index')),
            Stat::make('Total Projects', Project::count())
                ->description('View All Projects →')
                ->color('primary')
                ->icon('heroicon-o-folder-open')
                ->url(route('filament.admin.resources.projects.index')),
            Stat::make('Total Documents', Document::count())
                ->description('View All Documents →')
                ->color('primary')
                ->icon('heroicon-o-archive-box')
                ->url(route('filament.admin.resources.documents.index')),
        ];
    }
}