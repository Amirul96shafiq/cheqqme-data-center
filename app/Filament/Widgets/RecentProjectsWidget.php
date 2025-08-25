<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentProjectsWidget extends TableWidget
{
    protected function getTableQuery(): Builder
    {
        return Project::latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label(__('dashboard.recent_projects.id'))
                ->sortable()
                ->url(fn ($record) => route('filament.admin.resources.projects.edit', $record)),
            TextColumn::make('title')->label(__('dashboard.recent_projects.project_title'))->limit(10),
            TextColumn::make('status')
                ->badge()
                ->colors([
                    'primary' => 'Planning',
                    'info' => 'In Progress',
                    'success' => 'Completed',
                ])
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'Planning' => __('dashboard.recent_projects.planning'),
                    'In Progress' => __('dashboard.recent_projects.in_progress'),
                    'Completed' => __('dashboard.recent_projects.completed'),
                    default => $state,
                }),
            TextColumn::make('created_at')->label(__('dashboard.recent_projects.created_at'))->dateTime('j/n/y, h:i A'),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label(__('dashboard.actions.edit'))
                ->url(fn (Project $record) => route('filament.admin.resources.projects.edit', $record)),
        ];
    }

    protected function isTablePaginationEnabled(): bool
    {
        return false;
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('viewAll')
                ->label(label: __('dashboard.actions.view_all'))
                ->url(route('filament.admin.resources.projects.index'))
                ->icon('heroicon-m-arrow-right')
                ->button()
                ->color('gray'),
        ];
    }

    // Heading for the widget
    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('dashboard.recent_projects.title');
    }
}
