<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Concerns\HasProjectShareActions;
use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectActivityLogRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TrackingTokensRelationManager;
use App\Helpers\ClientFormatter;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;
use Schmeits\FilamentCharacterCounter\Forms\Components\RichEditor;

class ProjectResource extends Resource
{
    use HasProjectShareActions;

    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?string $recordTitleAttribute = 'title'; // Use 'title' as the record title attribute

    public static function getGloballySearchableAttributes(): array // This method defines which attributes are searchable globally
    {
        return ['title', 'project_url', 'client.company_name'];
    }

    public static function getGlobalSearchResultDetails($record): array // This method defines the details shown in global search results
    {
        $statusLabels = [
            'Planning' => __('project.status.planning'),
            'In Progress' => __('project.status.in_progress'),
            'Completed' => __('project.status.completed'),
        ];

        return [
            __('project.search.title') => $record->title,
            __('project.search.client') => optional($record->client)->company_name,
            __('project.search.status') => $record->status ?? '-',
        ];
    }

    protected static function getProjectEditUrl($record): string
    {
        return self::getUrl('edit', ['record' => $record->id]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section Controls Section
                Section::make(__('project.form.section_controls'))
                    ->schema([
                        Grid::make(6)
                            ->schema([
                                // Additional Information Toggle
                                Toggle::make('enable_additional_information')
                                    ->label(__('project.form.enable_additional_information'))
                                    ->default(function (?Project $record) {
                                        // Enable if record has notes or extra_information
                                        if ($record) {
                                            $hasNotes = ! empty($record->notes);
                                            $hasExtraInfo = ! empty($record->extra_information) && is_array($record->extra_information);

                                            return $hasNotes || $hasExtraInfo;
                                        }

                                        return false;
                                    })
                                    ->live()
                                    ->dehydrated(false)
                                    ->afterStateHydrated(function (Forms\Set $set, $state, ?Project $record) {
                                        // Double-check additional information on hydration and enable toggle if needed
                                        if ($record) {
                                            $hasNotes = ! empty($record->notes);
                                            $hasExtraInfo = ! empty($record->extra_information) && is_array($record->extra_information);

                                            if ($hasNotes || $hasExtraInfo) {
                                                $set('enable_additional_information', true);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        // When toggle is disabled, clear all additional information
                                        if (! $state) {
                                            $set('notes', null);
                                            $set('extra_information', []);
                                        }
                                    }),
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make(__('project.section.project_info'))
                    ->schema([

                        Grid::make([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 3,
                        ])
                            ->schema([

                                TextInput::make('title')
                                    ->label(__('project.form.project_title'))
                                    ->required()
                                    ->maxLength(100),

                                Select::make('client_id')
                                    ->label(__('project.form.client'))
                                    ->relationship('client', 'pic_name')
                                    ->getOptionLabelFromRecordUsing(function ($record) {
                                        return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->dehydrated()
                                    ->live()
                                    ->prefixAction(
                                        // Open the client in a new tab
                                        Action::make('openClient')
                                            ->icon('heroicon-o-pencil-square')
                                            ->url(function (Get $get) {
                                                $clientId = $get('client_id');
                                                if (! $clientId) {
                                                    return null;
                                                }

                                                return \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $clientId]);
                                            })
                                            ->openUrlInNewTab()
                                            ->visible(fn (Get $get) => (bool) $get('client_id'))
                                    )
                                    ->suffixAction(
                                        Action::make('createClient')
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                            ->label(__('project.form.create_client'))
                                    )
                                    ->nullable(),

                                Select::make('status')
                                    ->label(__('project.form.project_status'))
                                    ->options(['Planning' => __('project.form.planning'), 'In Progress' => __('project.form.in_progress'), 'Completed' => __('project.form.completed')])
                                    ->searchable()
                                    ->nullable(),

                            ]),

                        TextInput::make('project_url')
                            ->label(__('project.form.project_url'))
                            ->url()
                            ->nullable(),

                        Textarea::make('description')
                            ->label(__('project.form.project_description'))
                            ->rows(3)
                            ->nullable()
                            ->maxLength(200),

                    ]),

                Section::make(__('project.section.issue_tracker_info'))
                    ->schema([

                        Repeater::make('issue_tracker_info')
                            ->label('')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(__('project.form.issue_tracker_code'))
                                            ->disabled(fn ($record) => $record !== null)
                                            ->maxLength(6)
                                            ->helperText(fn ($record) => $record && $record->issue_tracker_code
                                                ? __('project.form.issue_tracker_code_helper_new')
                                                : __('project.form.issue_tracker_code_helper_new'))
                                            ->columnSpan(1)
                                            ->dehydrated(false),

                                        TextInput::make('url')
                                            ->label(__('project.form.issue_tracker_url'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('openIssueTracker')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(fn (Get $get) => $get('url'))
                                                    ->openUrlInNewTab()
                                                    ->tooltip(__('project.form.open_issue_tracker'))
                                                    ->visible(fn (Get $get) => ! empty($get('url')))
                                            )
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->default(function ($record) {
                                if (! $record || ! $record->issue_tracker_code) {
                                    return [];
                                }

                                return [[
                                    'code' => $record->issue_tracker_code,
                                    'url' => route('issue-tracker.show', ['project' => $record->issue_tracker_code]),
                                ]];
                            })
                            ->disabled()
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->collapsible(false)
                            ->itemLabel('')
                            ->columns(1)
                            ->columnSpanFull(),

                    ])
                    ->visible(fn (?Project $record): bool => $record !== null)
                    ->collapsible(true),

                Section::make(__('project.section.wishlist_tracker_info'))
                    ->schema([

                        Repeater::make('wishlist_tracker_info')
                            ->label('')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('code')
                                            ->label(__('project.form.wishlist_tracker_code'))
                                            ->disabled(fn ($record) => $record !== null)
                                            ->maxLength(6)
                                            ->helperText(fn ($record) => $record && $record->wishlist_tracker_code
                                                ? __('project.form.wishlist_tracker_code_helper_existing')
                                                : __('project.form.wishlist_tracker_code_helper_new'))
                                            ->columnSpan(1)
                                            ->dehydrated(false),

                                        TextInput::make('url')
                                            ->label(__('project.form.wishlist_tracker_url'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('openWishlistTracker')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(fn (Get $get) => $get('url'))
                                                    ->openUrlInNewTab()
                                                    ->tooltip(__('project.form.open_wishlist_tracker'))
                                                    ->visible(fn (Get $get) => ! empty($get('url')))
                                            )
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->default(function ($record) {
                                if (! $record || ! $record->wishlist_tracker_code) {
                                    return [];
                                }

                                return [[
                                    'code' => $record->wishlist_tracker_code,
                                    'url' => route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]),
                                ]];
                            })
                            ->disabled()
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->collapsible(false)
                            ->itemLabel('')
                            ->columns(1)
                            ->columnSpanFull(),

                    ])
                    ->visible(fn (?Project $record): bool => $record !== null)
                    ->collapsible(true),

                Section::make()
                    ->heading(__('project.section.extra_info'))
                    ->visible(fn (Get $get) => $get('enable_additional_information'))
                    ->collapsible(true)
                    ->collapsed(false)
                    ->live()
                    ->schema([

                        RichEditor::make('notes')
                            ->label(__('project.form.notes'))
                            ->maxLength(500)
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'bulletList',
                                'codeBlock',
                            ])
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->live()
                            ->nullable(),

                        Repeater::make('extra_information')
                            ->label(__('project.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Grid::make()
                                    ->schema([
                                        TextInput::make('title')
                                            ->label(__('project.form.extra_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        RichEditor::make('value')
                                            ->label(__('project.form.extra_value'))
                                            ->maxLength(500)
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'bulletList',
                                                'codeBlock',
                                            ])
                                            ->extraAttributes([
                                                'style' => 'resize: vertical;',
                                            ])
                                            ->live()
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('project.form.add_extra_info'))
                            ->addActionAlignment(Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('project.form.title_placeholder_short'))
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),

                    ])
                    ->collapsible(),

                Section::make(__('project.section.visibility_status'))
                    ->schema(function (Get $get) {
                        // Check if we're in edit mode by looking for record in route
                        $recordId = request()->route('record');
                        $isEditMode = $recordId !== null;
                        $canEditVisibility = true;

                        if ($isEditMode) {
                            // We're editing - get the record from route
                            $record = Project::find($recordId);
                            $canEditVisibility = $record && $record->created_by === auth()->id();
                        }

                        if ($canEditVisibility) {
                            // User can edit visibility - show radio field
                            return [
                                \Filament\Forms\Components\Radio::make('visibility_status')
                                    ->label(__('project.form.visibility_status'))
                                    ->options([
                                        'active' => __('project.form.visibility_status_active'),
                                        'draft' => __('project.form.visibility_status_draft'),
                                    ])
                                    ->default('active')
                                    ->inline()
                                    ->required()
                                    ->helperText(__('project.form.visibility_status_helper')),
                            ];
                        } else {
                            // User cannot edit visibility - show message with clickable creator name
                            $creator = null;
                            if ($isEditMode && $record) {
                                $creator = $record->createdBy;
                            }

                            return [
                                \Filament\Forms\Components\Placeholder::make('visibility_status_readonly')
                                    ->label(__('project.form.visibility_status'))
                                    ->content(new HtmlString(
                                        __('project.form.visibility_status_helper_readonly').' '.
                                        \Blade::render('<x-clickable-creator-name :user="$user" />', ['user' => $creator]).
                                        '.'
                                    )),
                            ];
                        }
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['client', 'createdBy', 'updatedBy'])
                    ->withCount(['documents', 'importantUrls'])
                    ->visibleToUser()
            )
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

                SelectFilter::make('client_id')
                    ->label(__('project.table.client'))
                    ->relationship('client', 'pic_name')
                    ->getOptionLabelFromRecordUsing(function ($record) {
                        return ClientFormatter::formatClientDisplay($record->pic_name, $record->company_name);
                    })
                    ->preload()
                    ->searchable()
                    ->multiple(),

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

                TrashedFilter::make()
                    ->label(__('project.filter.trashed'))
                    ->searchable(), // To show trashed or only active

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

                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([

                    static::getShareIssueTrackerLinkAction(fn ($record) => self::getUrl('edit', ['record' => $record->id])),

                    static::getShareAllIssueStatusLinksAction(fn ($record) => self::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0])),

                    static::getShareWishlistTrackerLinkAction(fn ($record) => self::getUrl('edit', ['record' => $record->id])),

                    static::getShareAllWishlistStatusLinksAction(fn ($record) => self::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0])),

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

    public static function infolist(Infolist $infolist): Infolist
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

    public static function getRelations(): array
    {
        return [
            TrackingTokensRelationManager::class,
            RelationManagers\DocumentsRelationManager::class,
            RelationManagers\ImportantUrlsRelationManager::class,
            ProjectActivityLogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return __('project.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('project.labels.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('project.labels.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('project.navigation_group'); // Grouping projects under Resources
    }

    public static function getNavigationSort(): ?int
    {
        return 22; // Adjust the navigation sort order as needed
    }
}
