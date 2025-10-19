<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Document;
use App\Models\Project;
use App\Models\Task;
use App\Models\TrelloBoard;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

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
            Stat::make(__('dashboard.total_trello_boards.title'), TrelloBoard::count())
                ->description(__('dashboard.actions.view_all_trello_boards'))
                ->color('primary')
                ->icon('heroicon-o-rectangle-stack')
                ->url(route('filament.admin.resources.trello-boards.index')),
            Stat::make(__('dashboard.your_tasks.title'), $this->getUserTasksCount())
                ->description(__('dashboard.your_tasks.description', ['total' => Task::count()]))
                ->color('primary')
                ->icon('heroicon-o-rocket-launch')
                ->url(route('filament.admin.pages.action-board')),
        ];
    }

    protected function getUserTasksCount(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            return 0;
        }

        // For SQLite, we need to use a different approach since JSON_CONTAINS is not available
        // We'll use whereRaw with SQLite's JSON functions
        // Only count tasks with status: todo, in_progress, to_review (exclude completed and archived)
        return Task::where(function ($query) use ($userId) {
            $query->whereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%"'.$userId.'"%'])
                ->orWhereRaw('JSON_EXTRACT(assigned_to, "$") LIKE ?', ['%'.$userId.'%']);
        })
            ->whereIn('status', ['todo', 'in_progress', 'to_review'])
            ->count();
    }
}
