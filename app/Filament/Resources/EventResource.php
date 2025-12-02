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

    public static function generatePreviewTitleFromValues(string $title, string $eventType, string $startDateTime, string $endDateTime): string
    {
        // Format the start date/time
        $formattedStartDate = 'Start Time';
        if ($startDateTime) {
            try {
                $startDate = \Carbon\Carbon::parse($startDateTime);
                $formattedStartDate = $startDate->format('j/n/y - h:i A');
            } catch (\Exception $e) {
                $formattedStartDate = 'Invalid Start Date';
            }
        }

        // Format the end date/time
        $formattedEndDate = 'End Time';
        if ($endDateTime) {
            try {
                $endDate = \Carbon\Carbon::parse($endDateTime);
                $formattedEndDate = $endDate->format('j/n/y - h:i A');
            } catch (\Exception $e) {
                $formattedEndDate = 'Invalid End Date';
            }
        }

        // Format the event type
        $formattedEventType = match ($eventType) {
            'online' => 'Online',
            'offline' => 'Offline',
            default => $eventType
        };

        return "{$title} - {$formattedEventType} - {$formattedStartDate} - {$formattedEndDate}";
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
                                            ->default('offline')
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

                                // Offline event location method selection
                                Forms\Components\Hidden::make('location_method')
                                    ->default('picker')
                                    ->live(),

                                Forms\Components\ViewField::make('location_method_cards')
                                    ->label(__('event.form.location_method'))
                                    ->view('components.location-method-cards')
                                    ->viewData(function (Forms\Get $get) {
                                        return [
                                            'selectedMethod' => $get('location_method') ?: 'picker',
                                            'urlLabel' => __('event.form.location_method_url'),
                                            'pickerLabel' => __('event.form.location_method_picker'),
                                            'urlDescription' => __('event.form.maps_share_url_help'),
                                            'pickerDescription' => __('event.form.location_picker_help'),
                                        ];
                                    })
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline'),

                                // Option 1: Google Maps Share URL
                                Forms\Components\Grid::make(1)
                                    ->schema([
                                        Forms\Components\TextInput::make('maps_share_url')
                                            ->label(__('event.form.maps_share_url'))
                                            ->placeholder('https://maps.app.goo.gl/... or https://goo.gl/maps/...')
                                            ->helperText(__('event.form.maps_share_url_help'))
                                            ->url()
                                            ->nullable()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, $state) {
                                                if (! $state) {
                                                    return;
                                                }

                                                try {
                                                    $mapsService = new \App\Services\GoogleMapsService;
                                                    $locationData = $mapsService->resolveShareUrl($state);

                                                    if ($locationData) {
                                                        $set('location_title', $locationData['title']);
                                                        $set('location_full_address', $locationData['address']);
                                                        if (isset($locationData['place_id'])) {
                                                            $set('location_place_id', $locationData['place_id']);
                                                        }

                                                        \Filament\Notifications\Notification::make()
                                                            ->title('Location loaded successfully')
                                                            ->body('Location details have been filled from the shared URL.')
                                                            ->success()
                                                            ->send();
                                                    } else {
                                                        \Filament\Notifications\Notification::make()
                                                            ->title('URL Resolution Failed')
                                                            ->body('Could not extract location data from the URL.')
                                                            ->warning()
                                                            ->send();
                                                    }
                                                } catch (\Exception $e) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Error processing URL')
                                                        ->body('Could not process the Google Maps URL.')
                                                        ->warning()
                                                        ->send();
                                                }
                                            })
                                            ->suffixAction(
                                                \Filament\Forms\Components\Actions\Action::make('clearUrl')
                                                    ->label(__('Clear'))
                                                    ->icon('heroicon-o-x-mark')
                                                    ->color('gray')
                                                    ->action(function (Forms\Set $set) {
                                                        $set('maps_share_url', null);
                                                        $set('location_title', null);
                                                        $set('location_full_address', null);
                                                        $set('location_place_id', null);
                                                    })
                                                    ->visible(fn (Forms\Get $get) => ! empty($get('maps_share_url')))
                                            ),
                                    ])
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline' && $get('location_method') === 'url'),

                                // Option 2: Google Maps Location Picker
                                Forms\Components\Section::make(__('event.form.location_picker'))
                                    ->schema([
                                        Forms\Components\ViewField::make('location_picker')
                                            ->view('components.google-maps-location-picker')
                                            ->viewData(function (Forms\Get $get) {
                                                return [
                                                    'title' => $get('location_title'),
                                                    'address' => $get('location_full_address'),
                                                    'id' => 'google-map-location-picker-event-form',
                                                ];
                                            }),
                                    ])
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline' && $get('location_method') === 'picker')
                                    ->columnSpanFull(),

                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('location_title')
                                            ->label(__('event.form.location_title'))
                                            ->nullable()
                                            ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline')
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('location_full_address')
                                            ->label(__('event.form.location_full_address'))
                                            ->nullable()
                                            ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline')
                                            ->columnSpan(2),
                                    ]),

                                // Hidden field for Google Places API place_id
                                Forms\Components\Hidden::make('location_place_id')
                                    ->nullable()
                                    ->visible(fn (Forms\Get $get) => $get('event_type') === 'offline'),

                            ]),

                        Forms\Components\Tabs\Tab::make(__('event.form.featured_image'))
                            ->schema([
                                Forms\Components\FileUpload::make('featured_image')
                                    ->label(__('event.form.featured_image'))
                                    ->preserveFilenames()
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

                            ]),
                    ]),

                // 2nd section (full span width) (able to collapsed) (default collapsed)
                Forms\Components\Section::make()
                    ->heading(__('event.form.event_resources'))
                    ->collapsible()
                    ->collapsed(
                        fn (Forms\Get $get) => empty($get('project_ids')) &&
                            empty($get('document_ids')) &&
                            empty($get('important_url_ids'))
                    )
                    ->live()
                    ->schema([
                        // Projects
                        Forms\Components\Select::make('project_ids')
                            ->label('Project(s)')
                            ->options(function () {
                                return \App\Models\Project::orderBy('title')
                                    ->get()
                                    ->mapWithKeys(fn ($p) => [
                                        $p->id => str($p->title)->limit(120),
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
                            ),

                        // Documents
                        Forms\Components\Select::make('document_ids')
                            ->label('Document(s)')
                            ->options(function () {
                                return \App\Models\Document::orderBy('title')
                                    ->get()
                                    ->mapWithKeys(fn ($d) => [
                                        $d->id => str($d->title)->limit(120),
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
                                Forms\Components\Actions\Action::make('createDocument')
                                    ->icon('heroicon-o-plus')
                                    ->url(\App\Filament\Resources\DocumentResource::getUrl('create'))
                                    ->openUrlInNewTab()
                                    ->label(__('event.actions.create_document'))
                            ),

                        // Important URLs
                        Forms\Components\Select::make('important_url_ids')
                            ->label('Important URL(s)')
                            ->options(function () {
                                return \App\Models\ImportantUrl::orderBy('title')
                                    ->get()
                                    ->mapWithKeys(fn ($i) => [
                                        $i->id => str($i->title)->limit(120),
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
                                $selectedProjects = $get('project_ids') ?? [];
                                $selectedDocuments = $get('document_ids') ?? [];
                                $selectedUrls = $get('important_url_ids') ?? [];

                                // Filter out documents that no longer exist
                                if (! empty($selectedDocuments)) {
                                    $existingDocuments = \App\Models\Document::whereIn('id', $selectedDocuments)->pluck('id')->toArray();
                                    $selectedDocuments = array_intersect($selectedDocuments, $existingDocuments);
                                }

                                // Filter out important URLs that no longer exist
                                if (! empty($selectedUrls)) {
                                    $existingUrls = \App\Models\ImportantUrl::whereIn('id', $selectedUrls)->pluck('id')->toArray();
                                    $selectedUrls = array_intersect($selectedUrls, $existingUrls);
                                }

                                return [
                                    'clientIds' => [],
                                    'selectedProjects' => $selectedProjects,
                                    'selectedDocuments' => $selectedDocuments,
                                    'selectedUrls' => $selectedUrls,
                                ];
                            })
                            ->visible(function (Forms\Get $get) {
                                $selectedProjects = $get('project_ids') ?? [];
                                $selectedDocuments = $get('document_ids') ?? [];
                                $selectedUrls = $get('important_url_ids') ?? [];

                                // Filter out documents that no longer exist
                                if (! empty($selectedDocuments)) {
                                    $existingDocuments = \App\Models\Document::whereIn('id', $selectedDocuments)->pluck('id')->toArray();
                                    $selectedDocuments = array_intersect($selectedDocuments, $existingDocuments);
                                }

                                // Filter out important URLs that no longer exist
                                if (! empty($selectedUrls)) {
                                    $existingUrls = \App\Models\ImportantUrl::whereIn('id', $selectedUrls)->pluck('id')->toArray();
                                    $selectedUrls = array_intersect($selectedUrls, $existingUrls);
                                }

                                return ! empty($selectedProjects) || ! empty($selectedDocuments) || ! empty($selectedUrls);
                            })
                            ->live()
                            ->columnSpanFull(),
                    ]),

                // 3rd section (full span width) (able to collapsed) (default collapsed)
                Forms\Components\Section::make()
                    ->heading(__('event.form.additional_information'))
                    ->collapsible(true)
                    ->collapsed(fn ($get) => empty($get('description')))
                    ->live()
                    ->schema([
                        Forms\Components\RichEditor::make('description')
                            ->label(__('event.form.notes'))
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

                Tables\Columns\TextColumn::make('location')
                    ->label(__('event.table.location'))
                    ->getStateUsing(function ($record) {
                        return $record->location_title ?: $record->location_full_address;
                    })
                    ->url(fn ($record) => $record && $record->event_type === 'offline' ? $record->getGoogleMapsUrl() : null)
                    ->openUrlInNewTab()
                    ->placeholder(__('No location'))
                    ->limit(30)
                    ->tooltip(function ($record) {
                        $location = $record->location_title ?: $record->location_full_address;

                        return $location ? 'Click to open in Google Maps' : null;
                    })
                    ->visible(fn ($record) => $record && $record->event_type === 'offline'),

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

                // Featured Image Section (matches third tab in form)
                Infolists\Components\Section::make(__('event.form.featured_image'))
                    ->schema([
                        Infolists\Components\ViewEntry::make('featured_image')
                            ->view('components.featured-image-entry')
                            ->viewData(function ($record) {
                                return [
                                    'featuredImage' => $record->featured_image,
                                    'placeholder' => __('No featured image'),
                                ];
                            })
                            ->columnSpanFull(),
                    ]),

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
                            ->formatStateUsing(function ($state, $record) {
                                // Get the value directly from the record to ensure casting is applied
                                $userIds = $record->invited_user_ids;

                                if (empty($userIds) || ! is_array($userIds)) {
                                    return __('No invited users');
                                }

                                $users = \App\Models\User::whereIn('id', $userIds)->pluck('username')->toArray();

                                return implode(', ', $users);
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No invited users')),
                    ]),

                // Meeting Location Section (matches second tab in form)
                Infolists\Components\Section::make(__('event.form.meeting_location'))
                    ->visible(fn ($record) => $record && ! empty($record->event_type))
                    ->schema([
                        // Online event fields
                        Infolists\Components\TextEntry::make('meeting_link_id')
                            ->label(__('event.form.meeting_link'))
                            ->formatStateUsing(function ($state, $record) {
                                if (! $record->meetingLink) {
                                    return __('No meeting link');
                                }

                                $meetingLink = $record->meetingLink;
                                $url = \App\Filament\Resources\MeetingLinkResource::getUrl('edit', ['record' => $meetingLink->id]);

                                return new \Illuminate\Support\HtmlString(
                                    '<a href="'.$url.'" class="hover:underline" target="_blank" rel="noopener noreferrer">'.
                                    e($meetingLink->title).' ('.$meetingLink->meeting_platform.')'.
                                    '</a>'
                                );
                            })
                            ->color('primary')
                            ->placeholder(__('No meeting link'))
                            ->visible(fn ($record) => $record && $record->event_type === 'online'),

                        Infolists\Components\TextEntry::make('meetingLink.meeting_url')
                            ->label(__('Meeting URL'))
                            ->color('primary')
                            ->copyable()
                            ->url(fn ($record) => $record->meetingLink?->meeting_url)
                            ->openUrlInNewTab()
                            ->placeholder(__('No meeting URL'))
                            ->visible(fn ($record) => $record && $record->event_type === 'online'),

                        // Offline event fields
                        Infolists\Components\TextEntry::make('location_title')
                            ->label(__('event.form.location_title'))
                            ->placeholder(__('No location title'))
                            ->visible(fn ($record) => $record && $record->event_type === 'offline'),

                        Infolists\Components\TextEntry::make('location_full_address')
                            ->label(__('event.form.location_full_address'))
                            ->placeholder(__('No location address'))
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->event_type === 'offline'),

                        // Google Maps with pinned location
                        Infolists\Components\ViewEntry::make('location_map')
                            ->view('components.google-maps-location-viewer')
                            ->viewData(function ($record) {
                                return [
                                    'title' => $record->location_title,
                                    'address' => $record->location_full_address,
                                    'url' => $record->getGoogleMapsUrl(),
                                    'id' => 'google-map-location-viewer-'.$record->id,
                                ];
                            })
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record && $record->event_type === 'offline' && (! empty($record->location_title) || ! empty($record->location_full_address))),
                    ]),

                // Event Resources Section (matches fourth section in form)
                Infolists\Components\Section::make()
                    ->heading(function ($record) {
                        // Count the number of resources selected
                        $project = $record->project_ids ?? [];
                        $document = $record->document_ids ?? [];
                        $importantUrl = $record->important_url_ids ?? [];

                        // Ensure arrays are countable (handle corrupted data)
                        $projectCount = is_array($project) ? count($project) : 0;
                        $documentCount = is_array($document) ? count($document) : 0;
                        $importantUrlCount = is_array($importantUrl) ? count($importantUrl) : 0;

                        $count = $projectCount + $documentCount + $importantUrlCount;

                        $title = __('event.form.event_resources');
                        $badge = '<span style="color: #FBB43E; font-weight: 700;">('.$count.')</span>';

                        return new \Illuminate\Support\HtmlString($title.' '.$badge);
                    })
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Infolists\Components\TextEntry::make('project_ids')
                            ->label('Project(s)')
                            ->formatStateUsing(function ($state, $record) {
                                // Get the value directly from the record to ensure casting is applied
                                $projectIds = $record->project_ids;

                                if (empty($projectIds) || ! is_array($projectIds)) {
                                    return __('No projects selected');
                                }

                                $projects = \App\Models\Project::withTrashed()->whereIn('id', $projectIds)->get(['id', 'title']);

                                $links = $projects->map(function ($project) {
                                    $url = $project->trashed() ? null : route('filament.admin.resources.projects.edit', $project->id);

                                    return $url ? '<a href="'.$url.'" class="text-primary-600 hover:text-primary-700 underline" target="_blank" rel="noopener noreferrer">'.e($project->title).'</a>' : e($project->title);
                                });

                                return new \Illuminate\Support\HtmlString(implode(', ', $links->toArray()));
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No projects selected')),

                        Infolists\Components\TextEntry::make('document_ids')
                            ->label('Document(s)')
                            ->formatStateUsing(function ($state, $record) {
                                // Get the value directly from the record to ensure casting is applied
                                $documentIds = $record->document_ids;

                                if (empty($documentIds) || ! is_array($documentIds)) {
                                    return __('No documents selected');
                                }

                                $documents = \App\Models\Document::withTrashed()->whereIn('id', $documentIds)->get(['id', 'title', 'type', 'file_path', 'url']);

                                $links = $documents->map(function ($document) {
                                    $url = null;

                                    if (! $document->trashed()) {
                                        if ($document->type === 'internal' && $document->file_path) {
                                            // For internal documents, link to the uploaded file
                                            $url = asset('storage/'.$document->file_path);
                                        } elseif ($document->type === 'external' && $document->url) {
                                            // For external documents, use the provided URL
                                            $url = $document->url;
                                        } else {
                                            // Fall back to edit page
                                            $url = route('filament.admin.resources.documents.edit', $document->id);
                                        }
                                    }

                                    return $url ? '<a href="'.$url.'" class="text-primary-600 hover:text-primary-700 underline"'.($document->type === 'internal' && $document->file_path ? ' target="_blank" rel="noopener noreferrer"' : '').'>'.e($document->title).'</a>' : e($document->title);
                                });

                                return new \Illuminate\Support\HtmlString(implode(', ', $links->toArray()));
                            })
                            ->columnSpanFull()
                            ->placeholder(__('No documents selected')),

                        Infolists\Components\TextEntry::make('important_url_ids')
                            ->label('Important URL(s)')
                            ->formatStateUsing(function ($state, $record) {
                                // Get the value directly from the record to ensure casting is applied
                                $urlIds = $record->important_url_ids;

                                if (empty($urlIds) || ! is_array($urlIds)) {
                                    return __('No important URLs selected');
                                }

                                $urls = \App\Models\ImportantUrl::withTrashed()->whereIn('id', $urlIds)->get(['id', 'title']);

                                $links = $urls->map(function ($url) {
                                    $editUrl = $url->trashed() ? null : route('filament.admin.resources.important-urls.edit', $url->id);

                                    return $editUrl ? '<a href="'.$editUrl.'" class="text-primary-600 hover:text-primary-700 underline" target="_blank" rel="noopener noreferrer">'.e($url->title).'</a>' : e($url->title);
                                });

                                return new \Illuminate\Support\HtmlString(implode(', ', $links->toArray()));
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
