<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Widgets\TableWidget;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;


class RecentDocumentsWidget extends TableWidget
{
    protected function getTableQuery(): Builder
    {
        return Document::latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')
                ->label('ID')
                ->sortable()
                ->url(fn($record) => route('filament.admin.resources.documents.edit', $record)),
            TextColumn::make('title')->label('Document Title')->limit(10),
            TextColumn::make('project.title')->label('Project Title')->limit(10),
            TextColumn::make('created_at')->dateTime('j/n/y, h:i A'),
        ];
    }
    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label('View')
                ->icon('heroicon-o-eye')
                ->url(fn(Document $record) => $record->url ?? asset('storage/' . $record->file_path))
                ->openUrlInNewTab(),
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
                ->url(route('filament.admin.resources.documents.index')) // adjust route name if needed
                ->icon('heroicon-m-arrow-right')
                ->button()
                ->color('gray'),
        ];
    }
}