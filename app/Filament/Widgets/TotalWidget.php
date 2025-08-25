<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Document;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(__('dashboard.total_clients.title'), Client::count())
                ->description(__('dashboard.actions.view_all_clients'))
                ->color('primary')
                ->icon('heroicon-o-briefcase')
                ->url(route('filament.admin.resources.clients.index')),
            Stat::make(__('dashboard.total_projects.title'), Project::count())
                ->description(__('dashboard.actions.view_all_projects'))
                ->color('primary')
                ->icon('heroicon-o-folder-open')
                ->url(route('filament.admin.resources.projects.index')),
            Stat::make(__('dashboard.total_documents.title'), Document::count())
                ->description(__('dashboard.actions.view_all_documents'))
                ->color('primary')
                ->icon('heroicon-o-archive-box')
                ->url(route('filament.admin.resources.documents.index')),
        ];
    }
}
