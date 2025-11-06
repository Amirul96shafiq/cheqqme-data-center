<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Filament\Resources\ProjectResource\RelationManagers\ProjectActivityLogRelationManager;
use App\Helpers\ClientFormatter;
use App\Models\Project;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
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
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

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
                                    ->maxLength(50),

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

                        Repeater::make('tracking_tokens')
                            ->label(__('project.form.tracking_tokens'))
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('token')
                                            ->label(__('project.form.tracking_token'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(1),

                                        Forms\Components\Select::make('status')
                                            ->label(__('project.form.task_status'))
                                            ->options([
                                                'issue_tracker' => __('action.status.issue_tracker'),
                                                'todo' => __('action.status.todo'),
                                                'in_progress' => __('action.status.in_progress'),
                                                'toreview' => __('action.status.toreview'),
                                                'completed' => __('action.status.completed'),
                                                'archived' => __('action.status.archived'),
                                            ])
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('edit_url')
                                            ->label(__('project.form.edit_task'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('openEditTask')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(fn (Get $get) => $get('edit_url'))
                                                    ->openUrlInNewTab()
                                                    ->tooltip(__('project.form.open_edit_task'))
                                            )
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('status_url')
                                            ->label(__('project.form.view_status'))
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('openStatusPage')
                                                    ->icon('heroicon-o-arrow-top-right-on-square')
                                                    ->url(fn (Get $get) => $get('status_url'))
                                                    ->openUrlInNewTab()
                                                    ->tooltip(__('project.form.open_status_page'))
                                            )
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->default([])
                            ->disabled()
                            ->deletable(false)
                            ->addable(false)
                            ->reorderable(false)
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => $state['token'] ?? __('project.form.tracking_token'))
                            ->columns(1)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(true)
                    ->collapsed(),

                Section::make()
                    ->heading(function (Get $get) {
                        $count = 0;

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('project.section.extra_info');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible(true)
                    ->live()
                    ->schema([
                        RichEditor::make('notes')
                            ->label(__('project.form.notes'))
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
                            // ->maxLength(500)
                            ->extraAttributes([
                                'style' => 'resize: vertical;',
                            ])
                            ->live()
                            // Character limit helper text
                            ->helperText(function (Get $get) {
                                $raw = $get('notes') ?? '';
                                if (empty($raw)) {
                                    return __('project.form.notes_helper', ['count' => 500]);
                                }

                                // Optimized character counting - strip tags and count
                                $textOnly = strip_tags($raw);
                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                return __('project.form.notes_helper', ['count' => $remaining]);
                            })
                            // Block save if over 500 visible characters
                            ->rule(function (Get $get): Closure {
                                return function (string $attribute, $value, Closure $fail) {
                                    if (empty($value)) {
                                        return;
                                    }
                                    $textOnly = strip_tags($value);
                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                    $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                    if (mb_strlen($textOnly) > 500) {
                                        $fail(__('project.form.notes_warning'));
                                    }
                                };
                            })
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
                                            ->reactive()
                                            // Character limit reactive function
                                            ->helperText(function (Get $get) {
                                                $raw = $get('value') ?? '';
                                                if (empty($raw)) {
                                                    return __('project.form.notes_helper', ['count' => 500]);
                                                }

                                                // Optimized character counting - strip tags and count
                                                $textOnly = strip_tags($raw);
                                                $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                $remaining = max(0, 500 - mb_strlen($textOnly));

                                                return __('project.form.notes_helper', ['count' => $remaining]);
                                            })
                                            // Block save if over 500 visible characters
                                            ->rule(function (Get $get): Closure {
                                                return function (string $attribute, $value, Closure $fail) {
                                                    if (empty($value)) {
                                                        return;
                                                    }
                                                    $textOnly = strip_tags($value);
                                                    $textOnly = html_entity_decode($textOnly, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $textOnly = trim(preg_replace('/\s+/', ' ', $textOnly));
                                                    if (mb_strlen($textOnly) > 500) {
                                                        $fail(__('project.form.notes_warning'));
                                                    }
                                                };
                                            })
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                TextColumn::make('id')
                    ->label(__('project.table.id'))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('title')
                    ->label(__('project.table.title'))
                    ->view('filament.resources.project-resource.title-column')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ViewColumn::make('client_id')
                    ->label(__('project.table.client'))
                    ->view('filament.resources.project-resource.client-column')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('issue_tracker_code')
                    ->label(__('project.table.issue_tracker_code'))
                    ->searchable()
                    ->copyable()
                    ->copyableState(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->color('primary')
                    ->url(fn ($record) => $record->issue_tracker_code ? route('issue-tracker.show', ['project' => $record->issue_tracker_code]) : null)
                    ->toggleable()
                    ->alignCenter(),

                Tables\Columns\ViewColumn::make('status')
                    ->label(__('project.table.status'))
                    ->view('filament.resources.project-resource.status-column')
                    ->sortable(),

                TextColumn::make('document_count')
                    ->label(__('project.table.document_count'))
                    ->badge()
                    ->alignCenter()
                    ->toggleable(),

                TextColumn::make('important_url_count')
                    ->label(__('project.table.important_url_count'))
                    ->badge()
                    ->alignCenter()
                    ->toggleable(),

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
