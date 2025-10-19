<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Document;
use App\Models\Project;
use App\Models\Task;
use App\Models\TrelloBoard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TotalWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 5;
    }

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
            Stat::make(__('dashboard.total_tasks.title'), Task::count())
                ->description(__('dashboard.actions.view_all_tasks'))
                ->color('primary')
                ->icon('heroicon-o-rocket-launch')
                ->url(route('filament.admin.pages.action-board')),
            Stat::make(__('dashboard.total_trello_boards.title'), TrelloBoard::count())
                ->description(__('dashboard.actions.view_all_trello_boards'))
                ->color('primary')
                ->icon('heroicon-o-rectangle-stack')
                ->url(route('filament.admin.resources.trello-boards.index')),
        ];
    }
}
