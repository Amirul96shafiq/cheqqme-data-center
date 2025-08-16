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

  public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
  {
    // Show on both Edit and View (modal)
    return parent::canViewForRecord($ownerRecord, $pageClass);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordUrl(fn($record) => $record->trashed() ? null : DocumentResource::getUrl('edit', ['record' => $record]))
      ->columns([
        TextColumn::make('id')
          ->label(__('document.table.id'))
          ->sortable()
          ->hidden(),
        TextColumn::make('title')
          ->label(__('document.table.title'))
          ->sortable()
          ->searchable()
          ->limit(20),
        TextColumn::make('type')
          ->label(__('document.table.type'))
          ->badge()
          ->formatStateUsing(fn(string $state): string => match ($state) {
            'internal' => __('document.table.internal'),
            'external' => __('document.table.external'),
          }),
        TextColumn::make('document_url')
          ->label(__('document.table.doc_url'))
          ->state(function ($record) {
            if ($record->type === 'external') {
              return $record->url ?: '-';
            }

            if ($record->type === 'internal') {
              return $record->file_path ? asset('storage/' . ltrim($record->file_path, '/')) : '-';
            }

            return '-';
          })
          ->url(function ($record) {
            if ($record->type === 'external' && $record->url) {
              return $record->url;
            }

            if ($record->type === 'internal' && $record->file_path) {
              return asset('storage/' . ltrim($record->file_path, '/'));
            }

            return null;
          })
          ->openUrlInNewTab()
          ->limit(40),
        TextColumn::make('created_at')
          ->label(__('document.table.created_at'))
          ->dateTime('j/n/y, h:i A')->sortable(),
        TextColumn::make('updated_at')
          ->label(__('document.table.updated_at_by'))
          ->formatStateUsing(function ($state, $record) {
            // Show '-' if there's no update or updated_by
            if (
              !$record->updated_by ||
              $record->updated_at?->eq($record->created_at)
            ) {
              return '-';
            }

            $user = $record->updatedBy;
            $formattedName = 'Unknown';

            if ($user) {
              $formattedName = $user->short_name;
            }

            return $state?->format('j/n/y, h:i A') . " ({$formattedName})";
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
        /*Tables\Actions\ViewAction::make()
      ->url(fn($record) => DocumentResource::getUrl('view', ['record' => $record])),*/
        Tables\Actions\EditAction::make()
          ->url(fn($record) => DocumentResource::getUrl('edit', ['record' => $record]))
          ->hidden(fn($record) => $record->trashed()),

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
