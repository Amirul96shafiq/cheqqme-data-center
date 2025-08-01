<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;

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
                ->label('ID')
                ->sortable()
                ->url(fn($record) => route('filament.admin.resources.projects.edit', $record)),
            TextColumn::make('title')->label('Project Title')->limit(10),
            TextColumn::make('status')
                ->badge()
                ->colors([
                    'primary' => 'Planning',
                    'info' => 'In Progress',
                    'success' => 'Completed',
                ]),
            TextColumn::make('created_at')->dateTime('j/n/y, h:i A'),
        ];
    }
    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->url(fn(Project $record) => route('filament.admin.resources.projects.edit', $record)),
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
                ->label('View All')
                ->url(route('filament.admin.resources.projects.index'))
                ->icon('heroicon-m-arrow-right')
                ->button()
                ->color('primary')
                ->outlined(),
        ];
    }
}