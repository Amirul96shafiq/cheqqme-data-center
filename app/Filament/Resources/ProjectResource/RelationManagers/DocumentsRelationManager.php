<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

use App\Filament\Resources\DocumentResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('project.section.project_documents');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->documents()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // Show on both Edit and View (modal)
        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('document.table.id'))
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('document.table.title'))
                    ->sortable()
                    ->searchable()
                    ->limit(20)
                    ->tooltip(function ($record) {
                        return $record->title;
                    }),
                TextColumn::make('type')
                    ->label(__('document.table.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                        default => ucfirst($state),
                    }),
                TextColumn::make('document_url')
                    ->label(__('document.table.document_url'))
                    ->state(function ($record) {
                        if ($record->type === 'external') {
                            return $record->url ?: '-';
                        }

                        if ($record->type === 'internal') {
                            return $record->file_path ? asset('storage/'.ltrim($record->file_path, '/')) : '-';
                        }

                        return '-';
                    })
                    ->url(function ($record) {
                        if ($record->type === 'external' && $record->url) {
                            return $record->url;
                        }

                        if ($record->type === 'internal' && $record->file_path) {
                            return asset('storage/'.ltrim($record->file_path, '/'));
                        }

                        return null;
                    })
                    ->openUrlInNewTab()
                    ->copyable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        if ($record->type === 'external') {
                            return $record->url ? __('document.tooltip.external_url', ['url' => $record->url]) : __('document.tooltip.no_url');
                        }

                        if ($record->type === 'internal') {
                            return $record->file_path ? __('document.tooltip.internal_file', ['path' => $record->file_path]) : __('document.tooltip.no_file');
                        }

                        return __('document.tooltip.unknown_type');
                    }),
                TextColumn::make('created_at')
                    ->label(__('document.table.created_at'))
                    ->dateTime('j/n/y, h:i A')->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('document.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        // Show '-' if there's no update or updated_by
                        if (
                            ! $record->updated_by ||
                            $record->updated_at?->eq($record->created_at)
                        ) {
                            return '-';
                        }

                        $user = $record->updatedBy;
                        $formattedName = 'Unknown';

                        if ($user) {
                            $formattedName = $user->short_name;
                        }

                        return $state?->format('j/n/y, h:i A')." ({$formattedName})";
                    })
                    ->sortable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('document.table.type'))
                    ->options([
                        'internal' => __('document.table.internal'),
                        'external' => __('document.table.external'),
                    ])
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->headerActions([
                // Intentionally empty to avoid creating from here unless needed
            ])
            ->actions([
                Tables\Actions\Action::make('open_url')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(function ($record) {
                        if ($record->type === 'internal' && $record->file_path) {
                            // For internal documents, use the uploaded file URL
                            return asset('storage/'.$record->file_path);
                        } elseif ($record->type === 'external' && $record->url) {
                            // For external documents, use the provided URL
                            return $record->url;
                        }

                        return null;
                    })
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = '';
                        if ($record->type === 'internal' && $record->file_path) {
                            $url = asset('storage/'.$record->file_path);
                        } elseif ($record->type === 'external' && $record->url) {
                            $url = $record->url;
                        }

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    })
                    ->visible(function ($record) {
                        // Only show the action if there's a valid URL or file
                        return ($record->type === 'internal' && $record->file_path) ||
                            ($record->type === 'external' && $record->url);
                    }),
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => DocumentResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // None for now
            ])
            ->defaultSort(function ($query) {
                return $query->orderByRaw('
                    CASE
                        WHEN updated_at IS NOT NULL AND updated_at != created_at THEN updated_at
                        ELSE created_at
                    END DESC
                ');
            });
    }
}
