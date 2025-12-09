<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
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
                return $query
                    ->with(['client', 'createdBy', 'updatedBy'])
                    ->withCount(['documents', 'importantUrls'])
                    ->visibleToUser();
            })
            ->columns([

                TextColumn::make('id')
                    ->label(__('project.table.id'))
                    ->url(fn ($record) => $record->trashed() ? null : route('filament.admin.resources.projects.edit', $record->id))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('title')
                    ->label(__('project.table.title'))
                    ->view('filament.resources.project-resource.title-column')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\ViewColumn::make('client_id')
                    ->label(__('project.table.client'))
                    ->view('filament.resources.project-resource.client-column')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('issue_tracker_code')
                    ->label(__('project.table.issue_tracker_code'))
                    ->copyable()
                    ->copyableState(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->color('primary')
                    ->url(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->searchable()
                    ->toggleable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('wishlist_tracker_code')
                    ->label(__('project.table.wishlist_tracker_code'))
                    ->copyable()
                    ->copyableState(fn ($record) => $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : null)
                    ->color('success')
                    ->url(fn ($record) => $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : null)
                    ->searchable()
                    ->toggleable()
                    ->alignCenter(),

                TextColumn::make('tracking_tokens_count')
                    ->label(__('project.table.tracking_tokens_count'))
                    ->badge()
                    ->color('primary')
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('wishlist_tokens_count')
                    ->label(__('project.table.wishlist_tokens_count'))
                    ->badge()
                    ->color('success')
                    ->alignCenter()
                    ->toggleable(),

                Tables\Columns\ViewColumn::make('status')
                    ->label(__('project.table.status'))
                    ->view('filament.resources.project-resource.status-column')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('visibility_status')
                    ->label(__('project.table.visibility_status'))
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => __('project.table.visibility_status_active'),
                        'draft' => __('project.table.visibility_status_draft'),
                        default => $state,
                    })
                    ->toggleable()
                    ->visible(true)
                    ->alignment(Alignment::Center),

                TextColumn::make('document_count')
                    ->label(__('project.table.document_count'))
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('important_url_count')
                    ->label(__('project.table.important_url_count'))
                    ->badge()
                    ->alignCenter()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('project.table.created_at_by'))
                    ->since()
                    ->tooltip(function ($record) {
                        $createdAt = $record->created_at;

                        if (! $createdAt) {
                            return null;
                        }

                        $formatted = $createdAt->format('j/n/y, h:i A');

                        $creatorName = null;

                        if (method_exists($record, 'createdBy')) {
                            $creator = $record->createdBy;
                            $creatorName = $creator?->short_name ?? $creator?->name;
                        }

                        return $creatorName ? $formatted.' ('.$creatorName.')' : $formatted;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Filters\SelectFilter::make('visibility_status')
                    ->label(__('project.table.visibility_status'))
                    ->options([
                        'active' => __('project.table.visibility_status_active'),
                        'draft' => __('project.table.visibility_status_draft'),
                    ])
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

                Tables\Actions\Action::make('open_wishlist_tracker')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->url(fn ($record) => $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : null)
                    ->openUrlInNewTab()
                    ->tooltip(function ($record) {
                        if (! $record->wishlist_tracker_code) {
                            return null;
                        }
                        $url = route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]);

                        return strlen($url) > 50 ? substr($url, 0, 47).'...' : $url;
                    })
                    ->visible(fn ($record) => ! empty($record->wishlist_tracker_code)),

                Tables\Actions\ViewAction::make()
                    ->slideOver(),

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
            ->defaultSort('updated_at', 'desc');
    }
}
