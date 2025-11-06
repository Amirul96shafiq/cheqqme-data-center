<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ImportantUrlResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class ImportantUrlsRelationManager extends RelationManager
{
    protected static string $relationship = 'importantUrls';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('client.section.important_urls');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->importantUrls()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('importanturl.table.id'))
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('importanturl.table.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->title;
                    }),
                TextColumn::make('project.title')
                    ->label(__('importanturl.table.project'))
                    ->sortable()
                    ->searchable()
                    ->limit(20)
                    ->getStateUsing(function ($record) {
                        return $record->project?->title ?? '-';
                    })
                    ->tooltip(function ($record) {
                        return $record->project?->title ?? '';
                    }),
                TextColumn::make('url')
                    ->label(__('importanturl.table.important_url'))
                    ->state(function ($record) {
                        return $record->url ?: '-';
                    })
                    ->copyable()
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->url ?: '';
                    })
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('importanturl.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('importanturl.table.updated_at_by'))
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->updated_by || $record->updated_at?->eq($record->created_at)) {
                            return '-';
                        }

                        $user = $record->updatedBy;
                        $formattedName = $user ? $user->short_name : 'Unknown';

                        return $state?->format('j/n/y, h:i A')." ({$formattedName})";
                    })
                    ->sortable()
                    ->limit(30),
            ])
            ->filters([
                SelectFilter::make('project_id')
                    ->label(__('importanturl.table.project'))
                    ->relationship('project', 'title')
                    ->preload()
                    ->searchable()
                    ->multiple(),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('open_url')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn ($record) => $record->url)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = $record->url;

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    }),
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ImportantUrlResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([])
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
