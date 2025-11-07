<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
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
                ->label(__('dashboard.recent_documents.id')),

            ViewColumn::make('title')
                ->label(__('dashboard.recent_documents.document_title'))
                ->view('filament.widgets.recent-documents-title-column'),

            TextColumn::make('type')
                ->label(__('dashboard.recent_documents.type'))
                ->badge()
                ->formatStateUsing(fn (string $state): string => match ($state) {
                    'internal' => __('dashboard.recent_documents.internal'),
                    'external' => __('dashboard.recent_documents.external'),
                    default => ucfirst($state),
                }),

            TextColumn::make('file_type')
                ->label(__('dashboard.recent_documents.file_type'))
                ->badge()
                ->color(fn ($state) => $state === '-' ? 'gray' : 'primary')
                ->getStateUsing(function (Document $record): string {
                    if ($record->type === 'external') {
                        return 'URL';
                    }

                    if ($record->type === 'internal' && filled($record->file_path)) {
                        $extension = strtolower(pathinfo($record->file_path, PATHINFO_EXTENSION));

                        return match ($extension) {
                            'jpg', 'jpeg' => 'JPG',
                            'png' => 'PNG',
                            'pdf' => 'PDF',
                            'docx' => 'DOCX',
                            'doc' => 'DOC',
                            'xlsx' => 'XLSX',
                            'xls' => 'XLS',
                            'pptx' => 'PPTX',
                            'ppt' => 'PPT',
                            'csv' => 'CSV',
                            'mp4' => 'MP4',
                            default => strtoupper($extension),
                        };
                    }

                    return '-';
                }),

            TextColumn::make('created_at')
                ->label(__('dashboard.recent_documents.created_at'))
                ->dateTime('j/n/y, h:i A'),

        ];
    }

    protected function getTableActions(): array
    {
        return [

            Action::make('view')
                ->label('')
                ->icon('heroicon-o-link')
                ->url(fn (Document $record) => $record->url ?? asset('storage/'.$record->file_path))
                ->openUrlInNewTab(),

            EditAction::make()
                ->label(__('dashboard.actions.edit'))
                ->url(fn (Document $record) => route('filament.admin.resources.documents.edit', $record)),
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
