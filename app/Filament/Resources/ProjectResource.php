<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectActivityLogRelationManager;
use App\Filament\Resources\ProjectResource\RelationManagers\TrackingTokensRelationManager;
use App\Helpers\ClientFormatter;
use App\Models\Project;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
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

    protected static function getIssueTrackerTextForCopy($record): string
    {
        $issueTrackerUrl = $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : 'TBD';
        $projectTitle = $record->title ?? 'TBD';

        return "Good day everyone ✨,\n\nHere's the issue tracker link for {$projectTitle} project.\n\n👉 {$issueTrackerUrl}\n\nPlease use this link to submit any issues or feedback related to this project.\n\nThank you! ☺️";
    }

    protected static function getAllIssueStatusLinksForCopy($record): string
    {
        $projectTitle = $record->title ?? 'TBD';
        $projectId = $record->id;

        $trackingTasks = Task::query()
            ->whereNotNull('tracking_token')
            ->with('updatedBy')
            ->where(function ($query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($trackingTasks->isEmpty()) {
            return "Hi team 👋,\n\nThere are currently no active issue tracking tokens for the {$projectTitle} project.\n\nWhen issues are submitted, their status links will appear here.\n\nThank you!";
        }

        $linksText = "Hi team 👋,\n\nHere are all the current issue status links for the {$projectTitle} project:\n\n";

        foreach ($trackingTasks as $task) {
            $issueTitle = $task->title ?: __('project.actions.issue_status_no_title');
            $statusLabel = self::formatIssueStatusLabel($task->status);
            $statusUrl = route('issue-tracker.status', ['token' => $task->tracking_token]);
            $submittedAt = $task->created_at?->format('j/n/y, h:i A');

            $linksText .= "🔹 {$issueTitle}\n";
            $linksText .= "   Status: {$statusLabel}\n";
            $linksText .= "   Link: {$statusUrl}\n";
            if ($submittedAt) {
                $linksText .= "   Submitted: {$submittedAt}\n";
            }
            $linksText .= "\n";
        }

        $linksText .= "Please use these links to check the latest status of each issue.\n\nThank you!";

        return $linksText;
    }

    protected static function formatIssueStatusLabel(?string $status): string
    {
        if (empty($status)) {
            return __('project.actions.issue_status_unknown');
        }

        $translationKey = "action.status.{$status}";
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    protected static function getWishlistTrackerTextForCopy($record): string
    {
        $wishlistTrackerUrl = $record->wishlist_tracker_code ? route('wishlist-tracker.show', ['project' => $record->wishlist_tracker_code]) : 'TBD';
        $projectTitle = $record->title ?? 'TBD';

        return "Good day everyone ✨,\n\nHere's the wishlist tracker link for {$projectTitle} project.\n\n👉 {$wishlistTrackerUrl}\n\nPlease use this link to submit any wishlist items or feature requests related to this project.\n\nThank you! ☺️";
    }

    protected static function getAllWishlistStatusLinksForCopy($record): string
    {
        $projectTitle = $record->title ?? 'TBD';
        $projectId = $record->id;

        $wishlistTasks = Task::wishlistTokens()
            ->with('updatedBy')
            ->where(function ($query) use ($projectId) {
                $query
                    ->whereJsonContains('project', $projectId)
                    ->orWhereJsonContains('project', (string) $projectId)
                    ->orWhere('project', 'like', '%"'.$projectId.'"%')
                    ->orWhere('project', 'like', '%['.$projectId.']%');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($wishlistTasks->isEmpty()) {
            return "Hi team 👋,\n\nThere are currently no active wishlist tracking tokens for the {$projectTitle} project.\n\nWhen wishlist items are submitted, their status links will appear here.\n\nThank you!";
        }

        $linksText = "Hi team 👋,\n\nHere are all the current wishlist status links for the {$projectTitle} project:\n\n";

        foreach ($wishlistTasks as $task) {
            $wishlistTitle = $task->title ?: __('project.actions.wishlist_status_no_title');
            $statusLabel = self::formatWishlistStatusLabel($task->status);
            $statusUrl = route('wishlist-tracker.status', ['token' => $task->tracking_token]);
            $submittedAt = $task->created_at?->format('j/n/y, h:i A');

            $linksText .= "🔹 {$wishlistTitle}\n";
            $linksText .= "   Status: {$statusLabel}\n";
            $linksText .= "   Link: {$statusUrl}\n";
            if ($submittedAt) {
                $linksText .= "   Submitted: {$submittedAt}\n";
            }
            $linksText .= "\n";
        }

        $linksText .= "Please use these links to check the latest status of each wishlist item.\n\nThank you!";

        return $linksText;
    }

    protected static function formatWishlistStatusLabel(?string $status): string
    {
        if (empty($status)) {
            return __('project.actions.wishlist_status_unknown');
        }

        $translationKey = "action.status.{$status}";
        $translated = __($translationKey);

        if ($translated !== $translationKey) {
            return $translated;
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                    ->heading(function (Get $get) {
                        $count = 0;

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('project.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new HtmlString($title.' '.$badge);
                    })
                    ->collapsible(true)
                    ->collapsed()
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
                        'draft' => 'warning',
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

                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([

                    Tables\Actions\Action::make('share_issue_tracker_link')
                        ->label(__('project.actions.share_issue_tracker_link'))
                        ->icon('heroicon-o-share')
                        ->color('primary')
                        ->visible(fn ($record) => ! $record->trashed() && $record->issue_tracker_code)
                        ->modalWidth('2xl')
                        ->modalHeading(__('project.actions.share_issue_tracker_link'))
                        ->modalDescription(__('project.actions.share_issue_tracker_link_description'))
                        ->form(function ($record) {
                            $issueTrackerText = self::getIssueTrackerTextForCopy($record);

                            return [
                                Forms\Components\Textarea::make('issue_tracker_preview')
                                    ->label(__('project.actions.issue_tracker_preview'))
                                    ->default($issueTrackerText)
                                    ->disabled()
                                    ->rows(12)
                                    ->extraInputAttributes([
                                        'class' => 'font-mono text-sm !resize-none',
                                        'style' => 'resize: none !important; max-height: none !important;',
                                        'x-init' => '$el.style.resize = "none"',
                                    ])
                                    ->columnSpanFull(),
                            ];
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->extraModalFooterActions(function ($record, $livewire) {
                            $actions = [];

                            // Detect mobile device and hide copy button on mobile
                            $userAgent = request()->userAgent() ?? '';
                            $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

                            // Only show copy button on desktop, hide on mobile so users can manually copy from preview
                            if (! $isMobile) {
                                $actions[] = Tables\Actions\Action::make('copy_to_clipboard')
                                    ->label(__('project.actions.copy_to_clipboard'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->color('primary')
                                    ->extraAttributes([
                                        'x-data' => '{}',
                                        'x-on:copy-success.window' => 'showCopiedBubble($el)',
                                    ])
                                    ->action(function () use ($record, $livewire) {
                                        $issueTrackerText = self::getIssueTrackerTextForCopy($record);

                                        // Dispatch browser event with the text to copy and success callback
                                        $livewire->dispatch('copy-to-clipboard-with-callback', text: $issueTrackerText);
                                    });
                            }

                            $actions[] = Tables\Actions\Action::make('edit_project')
                                ->label(__('project.actions.edit_project'))
                                ->icon('heroicon-o-pencil-square')
                                ->color('gray')
                                ->url(fn ($record) => self::getUrl('edit', ['record' => $record->id]))
                                ->close();

                            return $actions;
                        }),

                    Tables\Actions\Action::make('share_all_issue_status_link')
                        ->label(__('project.actions.share_all_issue_status_link'))
                        ->icon('heroicon-o-share')
                        ->color('primary')
                        ->visible(fn ($record) => ! $record->trashed() && $record->issue_tracker_code)
                        ->modalWidth('2xl')
                        ->modalHeading(__('project.actions.share_all_issue_status_link'))
                        ->modalDescription(__('project.actions.share_all_issue_status_link_description'))
                        ->form(function ($record) {
                            $allIssueStatusText = self::getAllIssueStatusLinksForCopy($record);

                            return [
                                Forms\Components\Textarea::make('all_issue_status_preview')
                                    ->label(__('project.actions.all_issue_status_preview'))
                                    ->default($allIssueStatusText)
                                    ->disabled()
                                    ->rows(12)
                                    ->extraInputAttributes([
                                        'class' => 'font-mono text-sm !resize-none',
                                        'style' => 'resize: none !important; max-height: none !important;',
                                        'x-init' => '$el.style.resize = "none"',
                                    ])
                                    ->columnSpanFull(),
                            ];
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->extraModalFooterActions(function ($record, $livewire) {
                            $actions = [];

                            // Detect mobile device and hide copy button on mobile
                            $userAgent = request()->userAgent() ?? '';
                            $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

                            // Only show copy button on desktop, hide on mobile so users can manually copy from preview
                            if (! $isMobile) {
                                $actions[] = Tables\Actions\Action::make('copy_to_clipboard')
                                    ->label(__('project.actions.copy_to_clipboard'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->color('primary')
                                    ->extraAttributes([
                                        'x-data' => '{}',
                                        'x-on:copy-success.window' => 'showCopiedBubble($el)',
                                    ])
                                    ->action(function () use ($record, $livewire) {
                                        $allIssueStatusText = self::getAllIssueStatusLinksForCopy($record);

                                        // Dispatch browser event with the text to copy and success callback
                                        $livewire->dispatch('copy-to-clipboard-with-callback', text: $allIssueStatusText);
                                    });
                            }

                            $actions[] = Tables\Actions\Action::make('edit_project')
                                ->label(__('project.actions.view_tracking_tokens'))
                                ->icon('heroicon-o-eye')
                                ->color('gray')
                                ->url(fn ($record) => self::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0]))
                                ->close();

                            return $actions;
                        }),

                    Tables\Actions\Action::make('share_wishlist_tracker_link')
                        ->label(__('project.actions.share_wishlist_tracker_link'))
                        ->icon('heroicon-o-share')
                        ->color('success')
                        ->visible(fn ($record) => ! $record->trashed() && $record->wishlist_tracker_code)
                        ->modalWidth('2xl')
                        ->modalHeading(__('project.actions.share_wishlist_tracker_link'))
                        ->modalDescription(__('project.actions.share_wishlist_tracker_link_description'))
                        ->form(function ($record) {
                            $wishlistTrackerText = self::getWishlistTrackerTextForCopy($record);

                            return [
                                Forms\Components\Textarea::make('wishlist_tracker_preview')
                                    ->label(__('project.actions.wishlist_tracker_preview'))
                                    ->default($wishlistTrackerText)
                                    ->disabled()
                                    ->rows(12)
                                    ->extraInputAttributes([
                                        'class' => 'font-mono text-sm !resize-none',
                                        'style' => 'resize: none !important; max-height: none !important;',
                                        'x-init' => '$el.style.resize = "none"',
                                    ])
                                    ->columnSpanFull(),
                            ];
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->extraModalFooterActions(function ($record, $livewire) {
                            $actions = [];

                            // Detect mobile device and hide copy button on mobile
                            $userAgent = request()->userAgent() ?? '';
                            $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

                            // Only show copy button on desktop, hide on mobile so users can manually copy from preview
                            if (! $isMobile) {
                                $actions[] = Tables\Actions\Action::make('copy_wishlist_to_clipboard')
                                    ->label(__('project.actions.copy_to_clipboard'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->color('success')
                                    ->extraAttributes([
                                        'x-data' => '{}',
                                        'x-on:copy-success.window' => 'showCopiedBubble($el)',
                                    ])
                                    ->action(function () use ($record, $livewire) {
                                        $wishlistTrackerText = self::getWishlistTrackerTextForCopy($record);

                                        // Dispatch browser event with the text to copy and success callback
                                        $livewire->dispatch('copy-to-clipboard-with-callback', text: $wishlistTrackerText);
                                    });
                            }

                            $actions[] = Tables\Actions\Action::make('edit_project')
                                ->label(__('project.actions.edit_project'))
                                ->icon('heroicon-o-pencil-square')
                                ->color('gray')
                                ->url(fn ($record) => self::getUrl('edit', ['record' => $record->id]))
                                ->close();

                            return $actions;
                        }),

                    Tables\Actions\Action::make('share_all_wishlist_status_link')
                        ->label(__('project.actions.share_all_wishlist_status_link'))
                        ->icon('heroicon-o-share')
                        ->color('success')
                        ->visible(fn ($record) => ! $record->trashed() && $record->wishlist_tracker_code)
                        ->modalWidth('2xl')
                        ->modalHeading(__('project.actions.share_all_wishlist_status_link'))
                        ->modalDescription(__('project.actions.share_all_wishlist_status_link_description'))
                        ->form(function ($record) {
                            $allWishlistStatusText = self::getAllWishlistStatusLinksForCopy($record);

                            return [
                                Forms\Components\Textarea::make('all_wishlist_status_preview')
                                    ->label(__('project.actions.all_wishlist_status_preview'))
                                    ->default($allWishlistStatusText)
                                    ->disabled()
                                    ->rows(12)
                                    ->extraInputAttributes([
                                        'class' => 'font-mono text-sm !resize-none',
                                        'style' => 'resize: none !important; max-height: none !important;',
                                        'x-init' => '$el.style.resize = "none"',
                                    ])
                                    ->columnSpanFull(),
                            ];
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->extraModalFooterActions(function ($record, $livewire) {
                            $actions = [];

                            // Detect mobile device and hide copy button on mobile
                            $userAgent = request()->userAgent() ?? '';
                            $isMobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent);

                            // Only show copy button on desktop, hide on mobile so users can manually copy from preview
                            if (! $isMobile) {
                                $actions[] = Tables\Actions\Action::make('copy_all_wishlist_to_clipboard')
                                    ->label(__('project.actions.copy_to_clipboard'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->color('success')
                                    ->extraAttributes([
                                        'x-data' => '{}',
                                        'x-on:copy-success.window' => 'showCopiedBubble($el)',
                                    ])
                                    ->action(function () use ($record, $livewire) {
                                        $allWishlistStatusText = self::getAllWishlistStatusLinksForCopy($record);

                                        // Dispatch browser event with the text to copy and success callback
                                        $livewire->dispatch('copy-to-clipboard-with-callback', text: $allWishlistStatusText);
                                    });
                            }

                            $actions[] = Tables\Actions\Action::make('view_wishlists')
                                ->label(__('project.actions.view_wishlists'))
                                ->icon('heroicon-o-eye')
                                ->color('gray')
                                ->url(fn ($record) => self::getUrl('edit', ['record' => $record->id, 'activeRelationManager' => 0]))
                                ->close();

                            return $actions;
                        }),

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
