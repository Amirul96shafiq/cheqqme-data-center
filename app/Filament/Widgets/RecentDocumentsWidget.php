<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Widgets\TableWidget;
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
                ->label(__('dashboard.recent_documents.id'))
                ->sortable()
                ->url(fn ($record) => route('filament.admin.resources.documents.edit', $record)),
            ViewColumn::make('title')
                ->label(__('dashboard.recent_documents.document_title'))
                ->view('filament.widgets.recent-documents-title-column'),
            ViewColumn::make('project_id')
                ->label(__('dashboard.recent_documents.project_title'))
                ->view('filament.widgets.recent-documents-project-column'),
            TextColumn::make('created_at')
                ->label(__('dashboard.recent_documents.created_at'))
                ->dateTime('j/n/y, h:i A')
                ->sortable(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Action::make('view')
                ->label(__('dashboard.actions.view'))
                ->icon('heroicon-o-eye')
                ->url(fn (Document $record) => $record->url ?? asset('storage/'.$record->file_path))
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
                ->label(__('dashboard.actions.view_all'))
                ->url(route('filament.admin.resources.documents.index')) // adjust route name if needed
                ->icon('heroicon-m-arrow-right')
                ->button()
                ->color('gray'),
        ];
    }

    // Heading for the widget
    protected function getTableHeading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return __('dashboard.recent_documents.title');
    }
}
