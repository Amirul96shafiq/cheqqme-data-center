<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    public static function getNavigationGroup(): ?string
    {
        return __('event.navigation_group');
    }

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('event.navigation.events');
    }

    public static function getModelLabel(): string
    {
        return __('event.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('event.plural_model_label');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('event.table.event_type') => $record->event_type,
            __('event.table.start_datetime') => $record->start_datetime?->format('M j, Y g:i A'),
            __('event.table.created_by') => optional($record->createdBy)->name,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1st section (tabs section)
                Forms\Components\Tabs::make('eventTabs')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('event.form.event_information'))
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label(__('event.form.title'))
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('event_type')
                                            ->label(__('event.form.event_type'))
                                            ->options([
                                                'online' => __('event.type.online'),
                                                'offline' => __('event.type.offline'),
                                            ])
                                            ->required()
                                            ->searchable()
                                            ->live()
                                            ->default('online')
                                            ->columnSpan(1),

                                        Forms\Components\DateTimePicker::make('start_datetime')
                                            ->label(__('event.form.start_datetime'))
                                            ->seconds(false)
                                            ->native(false)
                                            ->displayFormat('j/n/y, h:i A')
                                            ->required()
                                            ->default(now()->addHour())
                                            ->live()
                                            ->extraInputAttributes(['formnovalidate' => true])
                                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                $startDateTime = $get('start_datetime');
                                                if ($startDateTime) {
                                                    $endDateTime = \Carbon\Carbon::parse($startDateTime)->addHour();
                                                    $set('end_datetime', $endDateTime->format('Y-m-d H:i:s'));
                                                }
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\DateTimePicker::make('end_datetime')
                                            ->label(__('event.form.end_datetime'))
                                            ->seconds(false)
                                            ->native(false)
                                            ->displayFormat('j/n/y, h:i A')
                                            ->required()
                                            ->default(now()->addHours(2))
                                            ->live()
                                            ->extraInputAttributes(['formnovalidate' => true])
                                            ->columnSpan(1),
                                    ]),

                                Forms\Components\Select::make('invited_user_ids')
                                    ->label(__('event.form.invited_users'))
                                    ->multiple()
                                    ->options(\App\Models\User::getUserSelectOptions())
                                    ->searchable()
                                    ->nullable()
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('event.form.meeting_location'))
                            ->visible(fn (Forms\Get $get) => $get('event_type'))
                            ->schema([
                                // Online event fields
                                Forms\Components\Select::make('meeting_link_id')
                                    ->label(__('event.form.meeting_link'))
                                    ->options(\App\Models\MeetingLink::orderBy('title')
                                        ->get()
                                        ->mapWithKeys(fn ($link) => [
                                            $link->id => $link->title.' ('.$link->meeting_platform.')',
                                        ])
                                        ->toArray())
                                    ->searchable()
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'online')
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('createMeetingLink')
                                            ->label(__('event.actions.create_meeting_link'))
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\MeetingLinkResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                    ),

                                // Offline event fields
                                Forms\Components\TextInput::make('location_address')
                                    ->label(__('event.form.location_address'))
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline'),

                                Forms\Components\TextInput::make('location_latitude')
                                    ->label(__('event.form.location_latitude'))
                                    ->numeric()
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline'),

                                Forms\Components\TextInput::make('location_longitude')
                                    ->label(__('event.form.location_longitude'))
                                    ->numeric()
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline'),
                            ]),

                        Forms\Components\Tabs\Tab::make(__('event.form.featured_image'))
                            ->schema([
                                Forms\Components\FileUpload::make('featured_image')
                                    ->label(__('event.form.featured_image'))
                                    ->directory('events')
                                    ->image()
                                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120) // 5MB
                                    ->nullable()
                                    ->afterStateUpdated(function ($state) {
                                        // Convert image files to WebP format for better compression
                                        if (! empty($state) && is_array($state)) {
                                            $conversionService = new \App\Services\ImageConversionService;
                                            foreach ($state as $file) {
                                                if ($file instanceof \Livewire\TemporaryUploadedFile) {
                                                    $conversionService->convertTemporaryFile($file, 85);
                                                }
                                            }
                                        }
                                    }),

                                Forms\Components\Select::make('featured_image_source')
                                    ->label(__('event.form.featured_image_source'))
                                    ->options([
                                        'manual' => __('event.image_source.manual'),
                                        'places_api' => __('event.image_source.places_api'),
                                    ])
                                    ->nullable(),
                            ]),
                    ]),

                // 2nd section (full span width) (able to collapsed) (default collapsed)
                Forms\Components\Section::make(__('event.form.event_resources'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        // Client
                        Forms\Components\Select::make('client_ids')
                            ->label('Client(s)')
                            ->options(function () {
                                return \App\Models\Client::withTrashed()
                                    ->orderBy('company_name')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => $c->pic_name.' ('.($c->company_name ?: 'Company #'.$c->id).')'.($c->deleted_at ? ' (deleted)' : ''),
                                    ])
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->nullable()
                            ->multiple()
                            ->live()
                            ->reactive()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('createClient')
                                    ->icon('heroicon-o-plus')
                                    ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                    ->openUrlInNewTab()
                                    ->label(__('event.actions.create_client'))
                            )
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                if ($state) {
                                    // Get documents for selected clients
                                    $documents = \App\Models\Document::whereHas('project', function ($query) use ($state) {
                                        $query->whereIn('client_id', $state);
                                    })
                                        ->orderBy('title')
                                        ->pluck('id')
                                        ->toArray();

                                    $set('document_ids', $documents);
                                }
                            }),

                        // Projects
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Select::make('project_ids')
                                    ->label('Project(s)')
                                    ->options(function (Forms\Get $get) {
                                        $clientIds = $get('client_ids') ?? [];
                                        if (empty($clientIds)) {
                                            return [];
                                        }

                                        return \App\Models\Project::whereIn('client_id', $clientIds)
                                            ->withTrashed()
                                            ->orderBy('title')
                                            ->get()
                                            ->mapWithKeys(fn ($p) => [
                                                $p->id => str($p->title)->limit(20).($p->deleted_at ? ' (deleted)' : ''),
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->nullable()
                                    ->multiple()
                                    ->live()
                                    ->reactive()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('createProject')
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\ProjectResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                            ->label(__('event.actions.create_project'))
                                    )
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        $selectedProjects = $state ?? [];
                                        $currentDocuments = $get('document_ids') ?? [];

                                        if (empty($selectedProjects)) {
                                            $set('document_ids', []);

                                            return;
                                        }

                                        // Get all documents for the selected projects
                                        $availableDocuments = \App\Models\Document::whereIn('project_id', $selectedProjects)
                                            ->withTrashed()
                                            ->pluck('id')
                                            ->toArray();

                                        // Keep existing documents that are still valid + add new ones for newly selected projects
                                        $validCurrentDocuments = array_intersect($currentDocuments, $availableDocuments);
                                        $newDocuments = array_diff($availableDocuments, $currentDocuments);
                                        $finalDocuments = array_unique(array_merge($validCurrentDocuments, $newDocuments));

                                        $set('document_ids', $finalDocuments);
                                    }),

                                // Documents
                                Forms\Components\Select::make('document_ids')
                                    ->label('Document(s)')
                                    ->options(function (Forms\Get $get) {
                                        $clientIds = $get('client_ids') ?? [];
                                        $projectIds = $get('project_ids') ?? [];

                                        if (! empty($projectIds)) {
                                            return \App\Models\Document::whereIn('project_id', $projectIds)
                                                ->withTrashed()
                                                ->orderBy('title')
                                                ->get()
                                                ->mapWithKeys(fn ($d) => [
                                                    $d->id => str($d->title)->limit(20).($d->deleted_at ? ' (deleted)' : ''),
                                                ])
                                                ->toArray();
                                        }

                                        if (! empty($clientIds)) {
                                            return \App\Models\Document::whereHas('project', function ($query) use ($clientIds) {
                                                $query->whereIn('client_id', $clientIds);
                                            })
                                                ->withTrashed()
                                                ->orderBy('title')
                                                ->get()
                                                ->mapWithKeys(fn ($d) => [
                                                    $d->id => str($d->title)->limit(20).($d->deleted_at ? ' (deleted)' : ''),
                                                ])
                                                ->toArray();
                                        }

                                        return [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->nullable()
                                    ->multiple()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('createDocument')
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\DocumentResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                            ->label(__('event.actions.create_document'))
                                    ),

                                // Important URLs
                                Forms\Components\Select::make('important_url_ids')
                                    ->label('Important URL(s)')
                                    ->options(function (Forms\Get $get) {
                                        $clientIds = $get('client_ids') ?? [];
                                        if (empty($clientIds)) {
                                            return [];
                                        }

                                        return \App\Models\ImportantUrl::whereHas('project', function ($query) use ($clientIds) {
                                            $query->whereIn('client_id', $clientIds);
                                        })
                                            ->withTrashed()
                                            ->orderBy('title')
                                            ->get()
                                            ->mapWithKeys(fn ($i) => [
                                                $i->id => str($i->title)->limit(20).($i->deleted_at ? ' (deleted)' : ''),
                                            ])
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->nullable()
                                    ->multiple()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('createImportantUrl')
                                            ->icon('heroicon-o-plus')
                                            ->url(\App\Filament\Resources\ImportantUrlResource::getUrl('create'))
                                            ->openUrlInNewTab()
                                            ->label(__('event.actions.create_important_url'))
                                    ),

                                // Display selected items with clickable links
                                Forms\Components\ViewField::make('selected_items_links')
                                    ->view('filament.components.selected-items-links')
                                    ->viewData(function (Forms\Get $get) {
                                        $clientIds = $get('client_ids') ?? [];
                                        $selectedProjects = $get('project_ids') ?? [];
                                        $selectedDocuments = $get('document_ids') ?? [];
                                        $selectedUrls = $get('important_url_ids') ?? [];

                                        return [
                                            'clientIds' => $clientIds,
                                            'selectedProjects' => $selectedProjects,
                                            'selectedDocuments' => $selectedDocuments,
                                            'selectedUrls' => $selectedUrls,
                                        ];
                                    })
                                    ->visible(
                                        fn (Forms\Get $get) => ! empty($get('project_ids')) ||
                                        ! empty($get('document_ids')) ||
                                        ! empty($get('important_url_ids'))
                                    )
                                    ->live()
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // 3rd section (full span width) (able to collapsed) (default collapsed)
                Forms\Components\Section::make()
                    ->heading(function (Forms\Get $get) {
                        $count = 0;

                        // Add count of extra_information items
                        $extraInfo = $get('extra_information') ?? [];
                        $count += count($extraInfo);

                        $title = __('event.form.additional_information');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible(true)
                    ->collapsed()
                    ->live()
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label(__('event.form.description'))
                            ->maxLength(500)
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'link',
                                'codeBlock',
                            ])
                            ->extraAttributes(['style' => 'resize: vertical;'])
                            ->live()
                            ->nullable(),

                        Forms\Components\Repeater::make('extra_information')
                            ->label(__('event.form.extra_information'))
                            // ->relationship('extra_information')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->label(__('event.form.extra_info_title'))
                                            ->maxLength(100)
                                            ->debounce(1000)
                                            ->columnSpanFull(),
                                        Forms\Components\RichEditor::make('value')
                                            ->label(__('event.form.extra_info_value'))
                                            ->maxLength(500)
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'strike',
                                                'bulletList',
                                                'orderedList',
                                                'link',
                                                'codeBlock',
                                            ])
                                            ->extraAttributes(['style' => 'resize: vertical;'])
                                            ->live()
                                            ->nullable()
                                            ->columnSpanFull(),
                                    ]),
                            ])
                            ->columns(1)
                            ->defaultItems(1)
                            ->addActionLabel(__('event.form.add_extra_info'))
                            ->addActionAlignment(\Filament\Support\Enums\Alignment::Start)
                            ->cloneable()
                            ->reorderable()
                            ->collapsible(true)
                            ->collapsed()
                            ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('event.form.extra_information'))
                            ->live(onBlur: true)
                            ->columnSpanFull()
                            ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                    ]),

                // 4th section (full span width) (not able to collapsed) (default uncollapsed)
                Forms\Components\Section::make(__('event.form.visibility_info'))
                    ->schema(function (Forms\Get $get) {
                        // Check if we're in edit mode by looking for record in route
                        $recordId = request()->route('record');
                        $isEditMode = $recordId !== null;
                        $canEditVisibility = true;

                        if ($isEditMode) {
                            // We're editing - get the record from route
                            $record = Event::find($recordId);
                            $canEditVisibility = $record && $record->created_by === auth()->id();
                        }

                        if ($canEditVisibility) {
                            // User can edit visibility - show radio field
                            return [
                                \Filament\Forms\Components\Radio::make('visibility_status')
                                    ->label(__('event.form.status'))
                                    ->options([
                                        'active' => __('event.form.status_active'),
                                        'draft' => __('event.form.status_draft'),
                                    ])
                                    ->default('active')
                                    ->inline()
                                    ->required()
                                    ->helperText(__('event.form.status_helper')),
                            ];
                        } else {
                            // User cannot edit visibility - show message with clickable creator name
                            $creator = null;
                            if ($isEditMode && $record) {
                                $creator = $record->createdBy;
                            }

                            return [
                                \Filament\Forms\Components\Placeholder::make('visibility_status_readonly')
                                    ->label(__('event.form.status'))
                                    ->content(new \Illuminate\Support\HtmlString(
                                        __('event.form.status_helper_readonly').' '.
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
                    ->with(['createdBy', 'updatedBy'])
            )
            ->modifyQueryUsing(
                fn (Builder $query) => $query->visibleToUser()
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label(__('event.table.id'))
                    ->url(fn ($record) => $record->trashed() ? null : route('filament.admin.resources.events.edit', $record->id))
                    ->sortable(),

                Tables\Columns\ViewColumn::make('title')
                    ->label(__('event.table.title'))
                    ->view('filament.resources.event-resource.title-column')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('event_type')
                    ->label(__('event.table.event_type'))
                    ->colors([
                        'warning' => [
                            'online',
                            'offline',
                        ],
                    ])
                    ->formatStateUsing(fn (string $state): string => __('event.type.'.$state)),

                Tables\Columns\TextColumn::make('start_datetime')
                    ->label(__('event.table.start_datetime'))
                    ->dateTime('j/n/y, h:i A'),

                Tables\Columns\TextColumn::make('end_datetime')
                    ->label(__('event.table.end_datetime'))
                    ->dateTime('j/n/y, h:i A'),

                Tables\Columns\TextColumn::make('attendees_count')
                    ->label(__('event.table.attendees'))
                    ->badge()
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $userIds = $record->invited_user_ids;

                        // Handle both array and JSON string cases
                        if (is_array($userIds)) {
                            $count = count($userIds);
                        } elseif (is_string($userIds)) {
                            $decoded = json_decode($userIds, true);
                            $count = is_array($decoded) ? count($decoded) : 0;
                        } else {
                            $count = 0;
                        }

                        return $count;
                    })
                    ->color(function ($record) {
                        $userIds = $record->invited_user_ids;

                        // Handle both array and JSON string cases for color
                        if (is_array($userIds)) {
                            return count($userIds) > 0 ? 'primary' : 'gray';
                        } elseif (is_string($userIds)) {
                            $decoded = json_decode($userIds, true);

                            return is_array($decoded) && count($decoded) > 0 ? 'primary' : 'gray';
                        }

                        return 'gray';
                    })
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('visibility_status')
                    ->label(__('event.table.status'))
                    ->colors([
                        'success' => 'active',
                        'gray' => 'draft',
                    ])
                    ->formatStateUsing(fn (string $state): string => __('event.table.status_'.$state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('event.table.created_at_by'))
                    ->since()
                    ->tooltip(function ($record) {
                        $createdAt = $record->created_at;

                        if (! $createdAt) {
                            return null;
                        }

                        $formatted = $createdAt->format('j/n/y, h:i A');
                        $shortName = $record->createdBy?->short_name;

                        return $shortName ? $formatted.' ('.$shortName.')' : $formatted;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ViewColumn::make('updated_by')
                    ->label(__('event.table.updated_at_by'))
                    ->view('filament.resources.event-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_type')
                    ->label(__('event.table.event_type'))
                    ->options([
                        'online' => __('event.type.online'),
                        'offline' => __('event.type.offline'),
                    ])
                    ->searchable(),

                Tables\Filters\SelectFilter::make('visibility_status')
                    ->label(__('event.table.status'))
                    ->options([
                        'active' => __('event.table.status_active'),
                        'draft' => __('event.table.status_draft'),
                    ])
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make()->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('toggle_visibility_status')
                        ->label(fn ($record) => $record->visibility_status === 'active'
                            ? __('event.actions.make_draft')
                            : __('event.actions.make_active'))
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
                                ->title(__('event.actions.status_updated'))
                                ->body($newStatus === 'active'
                                    ? __('event.actions.event_activated')
                                    : __('event.actions.event_made_draft'))
                                ->success()
                                ->send();
                        })
                        ->tooltip(fn ($record) => $record->visibility_status === 'active'
                            ? __('event.actions.make_draft_tooltip')
                            : __('event.actions.make_active_tooltip'))
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
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Event Information Section (matches first tab in form)
                Infolists\Components\Section::make(__('event.form.event_information'))
                    ->schema([
                        Infolists\Components\TextEntry::make('title')
                            ->label(__('event.form.title'))
                            ->columnSpanFull(),
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('event_type')
                                    ->label(__('event.form.event_type'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'online' => 'success',
                                        'offline' => 'warning',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => __('event.type.'.$state)),
                                Infolists\Components\TextEntry::make('start_datetime')
                                    ->label(__('event.form.start_datetime'))
                                    ->dateTime('j/n/y - h:i A'),
                                Infolists\Components\TextEntry::make('end_datetime')
                                    ->label(__('event.form.end_datetime'))
                                    ->dateTime('j/n/y - h:i A'),
                            ]),
                        Infolists\Components\TextEntry::make('invited_user_ids')
                            ->label(__('event.form.invited_users'))
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return __('No invited users');
                                }

                                $userIds = is_array($state) ? $state : json_decode($state, true);
                                if (! is_array($userIds)) {
                                    return __('No invited users');
                                }

                                $users = \App\Models\User::whereIn('id', $userIds)->pluck('name')->toArray();

                                return implode(', ', $users);
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No invited users')),
                    ]),

                // Meeting Location Section (matches second tab in form)
                Infolists\Components\Section::make(__('event.form.meeting_location'))
                    ->visible(fn ($record) => ! empty($record->event_type))
                    ->schema([
                        // Online event fields
                        Infolists\Components\TextEntry::make('meeting_link.title')
                            ->label(__('event.form.meeting_link'))
                            ->formatStateUsing(fn ($record) => $record->meeting_link ? $record->meeting_link->title.' ('.$record->meeting_link->meeting_platform.')' : null)
                            ->placeholder(__('No meeting link'))
                            ->visible(fn ($record) => $record->event_type === 'online'),

                        Infolists\Components\TextEntry::make('meeting_link.meeting_url')
                            ->label(__('Meeting URL'))
                            ->copyable()
                            ->url(fn ($record) => $record->meeting_link?->meeting_url)
                            ->openUrlInNewTab()
                            ->placeholder(__('No meeting URL'))
                            ->visible(fn ($record) => $record->event_type === 'online'),

                        // Offline event fields
                        Infolists\Components\TextEntry::make('location_address')
                            ->label(__('event.form.location_address'))
                            ->placeholder(__('No location address'))
                            ->visible(fn ($record) => $record->event_type === 'offline'),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('location_latitude')
                                    ->label(__('event.form.location_latitude'))
                                    ->placeholder(__('Not set'))
                                    ->visible(fn ($record) => $record->event_type === 'offline'),
                                Infolists\Components\TextEntry::make('location_longitude')
                                    ->label(__('event.form.location_longitude'))
                                    ->placeholder(__('Not set'))
                                    ->visible(fn ($record) => $record->event_type === 'offline'),
                            ])
                            ->visible(fn ($record) => $record->event_type === 'offline'),
                    ]),

                // Featured Image Section (matches third tab in form)
                Infolists\Components\Section::make(__('event.form.featured_image'))
                    ->schema([
                        Infolists\Components\ImageEntry::make('featured_image')
                            ->label(__('event.form.featured_image'))
                            ->disk('public')
                            ->placeholder(__('No featured image')),
                        Infolists\Components\TextEntry::make('featured_image_source')
                            ->label(__('event.form.featured_image_source'))
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'manual' => __('event.image_source.manual'),
                                'places_api' => __('event.image_source.places_api'),
                                default => $state,
                            })
                            ->placeholder(__('Not set')),
                    ]),

                // Event Resources Section (matches fourth section in form)
                Infolists\Components\Section::make(__('event.form.event_resources'))
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('client_ids')
                            ->label('Client(s)')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return __('No clients selected');
                                }

                                $clientIds = is_array($state) ? $state : json_decode($state, true);
                                if (! is_array($clientIds)) {
                                    return __('No clients selected');
                                }

                                $clients = \App\Models\Client::withTrashed()->whereIn('id', $clientIds)->pluck('pic_name')->toArray();

                                return implode(', ', $clients);
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No clients selected')),

                        Infolists\Components\TextEntry::make('project_ids')
                            ->label('Project(s)')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return __('No projects selected');
                                }

                                $projectIds = is_array($state) ? $state : json_decode($state, true);
                                if (! is_array($projectIds)) {
                                    return __('No projects selected');
                                }

                                $projects = \App\Models\Project::withTrashed()->whereIn('id', $projectIds)->pluck('title')->toArray();

                                return implode(', ', $projects);
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No projects selected')),

                        Infolists\Components\TextEntry::make('document_ids')
                            ->label('Document(s)')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return __('No documents selected');
                                }

                                $documentIds = is_array($state) ? $state : json_decode($state, true);
                                if (! is_array($documentIds)) {
                                    return __('No documents selected');
                                }

                                $documents = \App\Models\Document::withTrashed()->whereIn('id', $documentIds)->pluck('title')->toArray();

                                return implode(', ', $documents);
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No documents selected')),

                        Infolists\Components\TextEntry::make('important_url_ids')
                            ->label('Important URL(s)')
                            ->formatStateUsing(function ($state) {
                                if (empty($state)) {
                                    return __('No important URLs selected');
                                }

                                $urlIds = is_array($state) ? $state : json_decode($state, true);
                                if (! is_array($urlIds)) {
                                    return __('No important URLs selected');
                                }

                                $urls = \App\Models\ImportantUrl::withTrashed()->whereIn('id', $urlIds)->pluck('title')->toArray();

                                return implode(', ', $urls);
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No important URLs selected')),
                    ]),

                // Additional Information Section (matches fifth section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        $count = count($record->extra_information ?? []);

                        $title = __('event.form.additional_information');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label(__('event.form.description'))
                            ->markdown()
                            ->placeholder(__('No description'))
                            ->columnSpanFull(),

                        Infolists\Components\RepeatableEntry::make('extra_information')
                            ->label(__('event.form.extra_information'))
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('event.form.extra_info_title')),
                                Infolists\Components\TextEntry::make('value')
                                    ->label(__('event.form.extra_info_value'))
                                    ->markdown(),
                            ])
                            ->columns(1)
                            ->columnSpanFull(),
                    ]),

                // Visibility Information Section (matches sixth section in form)
                Infolists\Components\Section::make(__('event.form.visibility_info'))
                    ->schema([
                        Infolists\Components\TextEntry::make('visibility_status')
                            ->label(__('event.form.status'))
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'draft' => 'gray',
                            })
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => __('event.form.status_active'),
                                'draft' => __('event.form.status_draft'),
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
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
