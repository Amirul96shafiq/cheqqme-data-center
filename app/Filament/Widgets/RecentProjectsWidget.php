<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Filament\Tables\Actions\Action;
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
            TextColumn::make('title')->label('Project Title')->limit(10),
            TextColumn::make('client.company_name')->label('Company Name')->limit(10),
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