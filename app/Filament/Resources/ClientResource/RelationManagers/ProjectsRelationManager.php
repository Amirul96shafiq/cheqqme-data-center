<?php

namespace App\Filament\Resources\ClientResource\RelationManagers;

use App\Filament\Resources\ProjectResource;
use App\Filament\Resources\ProjectResource\Concerns\HasProjectShareActions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class ProjectsRelationManager extends RelationManager
{
    use HasProjectShareActions;

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

                    static::getShareIssueTrackerLinkAction(fn ($record) => ProjectResource::getUrl('edit', ['record' => $record->id])),

                    static::getShareAllIssueStatusLinksAction(fn ($record) => ProjectResource::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0])),

                    static::getShareWishlistTrackerLinkAction(fn ($record) => ProjectResource::getUrl('edit', ['record' => $record->id])),

                    static::getShareAllWishlistStatusLinksAction(fn ($record) => ProjectResource::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0])),

                    Tables\Actions\Action::make('toggle_visibility_status')
                        ->label(fn ($record) => $record->visibility_status === 'active'
                            ? __('project.actions.make_draft')
                            : __('project.actions.make_active'))
                        ->icon(fn ($record) => $record->visibility_status === 'active' ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn ($record) => $record->visibility_status === 'active' ? 'warning' : 'success')
                        ->action(function ($record) {
                            $newStatus = $record->visibility_status === 'active' ? 'draft' : 'active';

                            $record->update([
                                'visibility_status' => $newStatus,
                                'updated_by' => auth()->id(),
                            ]);

                            // Show success notification
                            \Filament\Notifications\Notification::make()
                                ->title(__('project.actions.visibility_status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('project.actions.project_activated')
                                    : __('project.actions.project_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->visibility_status === 'active'
                            ? __('project.actions.make_draft_tooltip')
                            : __('project.actions.make_active_tooltip'))
                        ->hidden(fn ($record) => $record->trashed() || $record->created_by !== auth()->id()),

                    ActivityLogTimelineTableAction::make('Log'),

                    Tables\Actions\DeleteAction::make(),

                    Tables\Actions\RestoreAction::make(),

                    Tables\Actions\ForceDeleteAction::make(),

                ]),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Project Information Section (matches first section in form)
                Infolists\Components\Section::make(__('project.section.project_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('project.form.project_title'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('client.company_name')
                            ->label(__('project.form.client'))
                            ->placeholder(__('No client assigned')),

                        Infolists\Components\TextEntry::make('status')
                            ->label(__('project.form.project_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Planning' => 'warning',
                                'In Progress' => 'primary',
                                'Completed' => 'success',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'Planning' => __('project.status.planning'),
                                'In Progress' => __('project.status.in_progress'),
                                'Completed' => __('project.status.completed'),
                                default => $state,
                            }),

                        Infolists\Components\TextEntry::make('project_url')
                            ->label(__('project.form.project_url'))
                            ->copyable()
                            ->url(fn ($record) => $record->project_url)
                            ->openUrlInNewTab()
                            ->placeholder(__('No project URL'))
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('description')
                            ->label(__('project.form.project_description'))
                            ->markdown()
                            ->placeholder(__('No description'))
                            ->columnSpanFull(),
                    ]),

                // Issue Tracker Information Section (matches second section in form)
                Infolists\Components\Section::make(__('project.section.issue_tracker_info'))
                    ->visible(fn ($record) => ! empty($record->issue_tracker_code))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('issue_tracker_info')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('code')
                                            ->label(__('project.form.issue_tracker_code')),
                                        Infolists\Components\TextEntry::make('url')
                                            ->label(__('project.form.issue_tracker_url'))
                                            ->copyable()
                                            ->url(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                                            ->openUrlInNewTab(),
                                    ]),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Wishlist Tracker Information Section (matches third section in form)
                Infolists\Components\Section::make(__('project.section.wishlist_tracker_info'))
                    ->visible(fn ($record) => ! empty($record->wishlist_tracker_code))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('wishlist_tracker_info')
                            ->label('')
                            ->schema([
                                Infolists\Components\Grid::make(2)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('code')
                                            ->label(__('project.form.wishlist_tracker_code')),
                                        Infolists\Components\TextEntry::make('url')
                                            ->label(__('project.form.wishlist_tracker_url'))
                                            ->copyable()
                                            ->url(fn ($record) => $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : null)
                                            ->openUrlInNewTab(),
                                    ]),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Additional Information Section (matches fourth section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        $count = count($record->extra_information ?? []);

                        $title = __('project.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label(__('project.form.notes'))
                            ->markdown()
                            ->placeholder(__('No notes'))
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('extra_information')
                            ->label(__('project.form.extra_information'))
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('project.form.extra_title')),
                                Infolists\Components\TextEntry::make('value')
                                    ->label(__('project.form.extra_value'))
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Visibility Status Information Section (matches fifth section in form)
                Infolists\Components\Section::make(__('project.section.visibility_status'))
                    ->schema([
                        Infolists\Components\TextEntry::make('visibility_status')
                            ->label(__('project.form.visibility_status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'gray',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => __('project.form.visibility_status_active'),
                                'draft' => __('project.form.visibility_status_draft'),
                                default => $state,
                            }),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label(__('Created by'))
                                    ->placeholder(__('Unknown')),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('Created at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label(__('Updated by'))
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label(__('Updated at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }

    protected static function getProjectEditUrl($record): string
    {
        return ProjectResource::getUrl('edit', ['record' => $record->id]);
    }
}
