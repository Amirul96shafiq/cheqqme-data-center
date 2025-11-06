<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class ProjectsRelationManager extends RelationManager
{
    protected static string $relationship = 'projects';

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $title = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('client.section.company_projects');
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->projects()->count();

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
            ->modifyQueryUsing(function ($query) {
                return $query->withCount('documents')
                    ->with('updatedBy');
            })
            ->columns([

                TextColumn::make('id')
                    ->label(__('project.table.id'))
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('project.table.title'))
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function ($record) {
                        return $record->title;
                    }),

                TextColumn::make('description')
                    ->label(__('project.table.description'))
                    ->searchable()
                    ->limit(50),

                TextColumn::make('issue_tracker_code')
                    ->label(__('project.table.issue_tracker_code'))
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->color('primary')
                    ->url(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->toggleable()
                    ->searchable()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Planning' => 'primary',
                        'In Progress' => 'info',
                        'Completed' => 'success',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('documents_count')
                    ->label(__('project.table.document_count'))
                    ->badge()
                    ->alignCenter(),

                TextColumn::make(__('created_at'))
                    ->label(__('project.table.created_at'))
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j/n/y, h:i A'))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('updated_at')
                    ->label(__('project.table.updated_at_by'))
                    ->view('filament.resources.project-resource.updated-by-column')
                    ->sortable(),

            ])
            ->filters([
                SelectFilter::make(__('project.filter.status'))
                    ->options([
                        'Planning' => __('project.filter.planning'),
                        'In Progress' => __('project.filter.in_progress'),
                        'Completed' => __('project.filter.completed'),
                    ])
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ])
            ->headerActions([
                // Intentionally empty to avoid creating from here unless needed
            ])
            ->actions([
                
                Tables\Actions\Action::make('open_issue_tracker')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        if (! $record->issue_tracker_code) {
                            return null;
                        }
                        $url = route('issue-tracker.show', ['project' => $record->issue_tracker_code]);

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    })
                    ->visible(fn ($record) => ! empty($record->issue_tracker_code)),

                /*Tables\Actions\ViewAction::make()
              ->url(fn($record) => ProjectResource::getUrl('view', ['record' => $record])),*/

                Tables\Actions\EditAction::make()
                    ->url(fn ($record) => ProjectResource::getUrl('edit', ['record' => $record]))
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
