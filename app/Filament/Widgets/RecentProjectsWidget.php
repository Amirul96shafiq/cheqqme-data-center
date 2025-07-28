<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;

class RecentProjectsWidget extends TableWidget
{
    protected int|string|array $columnSpan = 'full';

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
    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->url(fn(Project $record) => route('filament.admin.resources.projects.edit', $record)),
        ];
    }
}