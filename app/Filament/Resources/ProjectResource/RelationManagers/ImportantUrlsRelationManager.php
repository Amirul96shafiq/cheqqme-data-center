<?php

namespace App\Filament\Resources\ProjectResource\RelationManagers;

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
        return __('project.section.important_urls');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->importantUrls()->count();

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
            ->recordUrl(fn ($record) => $record->trashed() ? null : ImportantUrlResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label(__('importanturl.table.id'))
                    ->sortable()
                    ->hidden(),
                TextColumn::make('title')
                    ->label(__('importanturl.table.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->url(fn ($record) => $record->url, true)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        $url = $record->url;

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    }),
                TextColumn::make('client.pic_name')
                    ->label(__('importanturl.table.client'))
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->client) {
                            return '-';
                        }

                        $picName = $record->client->pic_name ?: '';
                        $companyName = $record->client->company_name ?: '';

                        if ($picName && $companyName && $picName !== $companyName) {
                            return "{$picName} ({$companyName})";
                        }

                        return $picName ?: $companyName ?: '-';
                    })
                    ->sortable()
                    ->searchable()
                    ->limit(40),
                TextColumn::make('important_url')
                    ->label(__('importanturl.table.important_url'))
                    ->state(function ($record) {
                        return $record->url ?: '-';
                    })
                    ->copyable()
                    ->limit(40),
                TextColumn::make('created_at')
                    ->label(__('importanturl.table.created_at'))
                    ->dateTime('j/n/y, h:i A')
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
                SelectFilter::make('client_id')
                    ->label(__('importanturl.table.client'))
                    ->relationship('client', 'pic_name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->pic_name} ({$record->company_name})")
                    ->preload()
                    ->searchable()
                    ->multiple(),
            ])
            ->headerActions([
                // Intentionally empty to avoid creating from here unless needed
            ])
            ->actions([
                /*Tables\Actions\ViewAction::make()
          ->url(fn($record) => ProjectResource::getUrl('view', ['record' => $record])),*/
                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ImportantUrlResource::getUrl('edit', ['record' => $record]))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    ActivityLogTimelineTableAction::make('Log'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                // None for now
            ])
            ->defaultSort('created_at', 'desc');
    }
}
