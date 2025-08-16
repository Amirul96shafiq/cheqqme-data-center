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

  public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
  {
    return parent::canViewForRecord($ownerRecord, $pageClass);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordUrl(fn($record) => $record->trashed() ? null : ImportantUrlResource::getUrl('edit', ['record' => $record]))
      ->columns([
        TextColumn::make('id')
          ->label(__('importanturl.table.id'))
          ->sortable()
          ->hidden(),
        TextColumn::make('title')
          ->label(__('importanturl.table.title'))
          ->searchable()
          ->sortable()
          ->limit(30),
        TextColumn::make('project.title')
          ->label(__('importanturl.table.project'))
          ->sortable()
          ->searchable()
          ->limit(20),
        TextColumn::make('important_url')
          ->label(__('importanturl.table.important_url'))
          ->state(function ($record) {
            return $record->url ?: '-';
          })
          ->url(function ($record) {
            return $record->url;
          })
          ->openUrlInNewTab()
          ->limit(40),
        TextColumn::make('created_at')
          ->label(__('importanturl.table.created_at'))
          ->dateTime('j/n/y, h:i A')
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
          ->dateTime('j/n/y, h:i A')
          ->sortable(),
        TextColumn::make('updated_at')
          ->label(__('importanturl.table.updated_at_by'))
          ->formatStateUsing(function ($state, $record) {
            if (!$record->updated_by || $record->updated_at?->eq($record->created_at)) {
              return '-';
            }

            $user = $record->updatedBy;
            $formattedName = $user ? $user->short_name : 'Unknown';

            return $state?->format('j/n/y, h:i A') . " ({$formattedName})";
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
        Tables\Actions\EditAction::make()
          ->url(fn($record) => ImportantUrlResource::getUrl('edit', ['record' => $record]))
          ->hidden(fn($record) => $record->trashed()),

        Tables\Actions\ActionGroup::make([
          ActivityLogTimelineTableAction::make('Log'),
          Tables\Actions\DeleteAction::make(),
        ]),
      ])
      ->bulkActions([])
      ->defaultSort('created_at', 'desc');
  }
}
