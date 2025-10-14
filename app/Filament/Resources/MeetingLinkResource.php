<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingLinkResource\Pages;
use App\Models\Client;
use App\Models\Document;
use App\Models\MeetingLink;
use App\Services\GoogleMeetService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MeetingLinkResource extends Resource
{
    protected static ?string $model = MeetingLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 5;

    protected static function generateMeetingTitle(string $platform, string $startTime, int $duration): string
    {
        $date = \Carbon\Carbon::parse($startTime);
        $formattedDate = $date->format('j/n/y - h:i A');

        $durationText = match ($duration) {
            30 => '30 minutes',
            60 => '1 hour',
            90 => '1 hour 30 minutes',
            120 => '2 hours',
            default => $duration.' minutes'
        };

        return "CheQQMeeting - {$platform} - {$formattedDate} - {$durationText}";
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make([
                'default' => 1,
                'sm' => 1,
                'md' => 1,
                'lg' => 1,
                'xl' => 1,
                '2xl' => 5,
            ])
                ->schema([
                    // Main content (left side) - spans 5 columns
                    Forms\Components\Grid::make(1)
                        ->schema([
                            Forms\Components\Tabs::make('meetingTabs')
                                ->tabs([
                                    // -----------------------------
                                    // Meeting Information
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make('Meeting Information')
                                        ->schema([
                                            Forms\Components\TextInput::make('title')
                                                ->label('Meeting Title')
                                                ->required()
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateHydrated(function (Forms\Components\TextInput $component, Forms\Get $get, Forms\Set $set) {
                                                    // Generate title on form load if title is empty
                                                    if (empty($component->getState())) {
                                                        $platform = $get('meeting_platform') ?: 'Google Meet';
                                                        $startTime = $get('meeting_start_time') ?: now()->format('Y-m-d H:i:s');
                                                        $duration = $get('meeting_duration') ?: 60;

                                                        $generatedTitle = static::generateMeetingTitle($platform, $startTime, $duration);
                                                        $component->state($generatedTitle);
                                                    }
                                                })
                                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                    $platform = $get('meeting_platform');
                                                    $startTime = $get('meeting_start_time');
                                                    $duration = $get('meeting_duration');

                                                    if ($platform && $startTime && $duration) {
                                                        $generatedTitle = static::generateMeetingTitle($platform, $startTime, $duration);
                                                        $set('title', $generatedTitle);
                                                    }
                                                })
                                                ->helperText('Automatically generated meeting title based on the platform, start time, and duration.')
                                                ->columnSpanFull(),

                                            Forms\Components\Grid::make(3)
                                                ->schema([
                                                    Forms\Components\Select::make('meeting_platform')
                                                        ->label('Platform')
                                                        ->options([
                                                            'Google Meet' => 'Google Meet',
                                                            'Zoom Meeting' => 'Zoom Meeting (Coming Soon)',
                                                            'Teams Meeting' => 'Teams Meeting (Coming Soon)',
                                                        ])
                                                        ->required()
                                                        ->live()
                                                        ->searchable()
                                                        ->default('Google Meet')
                                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                            $platform = $get('meeting_platform');
                                                            $startTime = $get('meeting_start_time');
                                                            $duration = $get('meeting_duration');

                                                            if ($platform && $startTime && $duration) {
                                                                $generatedTitle = static::generateMeetingTitle($platform, $startTime, $duration);
                                                                $set('title', $generatedTitle);
                                                            }
                                                        })
                                                        ->columnSpan(1),

                                                    Forms\Components\DateTimePicker::make('meeting_start_time')
                                                        ->label('Meeting Start')
                                                        ->seconds(false)
                                                        ->native(false)
                                                        ->minutesStep(5)
                                                        ->displayFormat('j/n/y, h:i A')
                                                        ->default(now())
                                                        ->required()
                                                        ->live()
                                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                            $platform = $get('meeting_platform');
                                                            $startTime = $get('meeting_start_time');
                                                            $duration = $get('meeting_duration');

                                                            if ($platform && $startTime && $duration) {
                                                                $generatedTitle = static::generateMeetingTitle($platform, $startTime, $duration);
                                                                $set('title', $generatedTitle);
                                                            }
                                                        })
                                                        ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Google Meet')
                                                        ->columnSpan(1),

                                                    Forms\Components\Select::make('meeting_duration')
                                                        ->label('Duration')
                                                        ->options([
                                                            30 => '30 minutes',
                                                            60 => '1 hour',
                                                            90 => '1 hour 30 minutes',
                                                            120 => '2 hours',
                                                        ])
                                                        ->default(60)
                                                        ->required()
                                                        ->live()
                                                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                                            $platform = $get('meeting_platform');
                                                            $startTime = $get('meeting_start_time');
                                                            $duration = $get('meeting_duration');

                                                            if ($platform && $startTime && $duration) {
                                                                $generatedTitle = static::generateMeetingTitle($platform, $startTime, $duration);
                                                                $set('title', $generatedTitle);
                                                            }
                                                        })
                                                        ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Google Meet')
                                                        ->columnSpan(1),
                                                ])
                                                ->columns(3),

                                            Forms\Components\Grid::make(2)
                                                ->schema([

                                                    Forms\Components\TextInput::make('meeting_url')
                                                        ->label('Meeting URL')
                                                        ->disabled()
                                                        ->dehydrated()
                                                        ->placeholder('No meeting link generated')
                                                        ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Google Meet')
                                                        ->columnSpan(2),
                                                ]),

                                            // Hidden field to track meeting ID
                                            Forms\Components\Hidden::make('meeting_id'),

                                            // Hidden field to track unsaved changes
                                            Forms\Components\Hidden::make('has_unsaved_meeting')
                                                ->dehydrated(false),

                                            // Actions for Google Meet
                                            Forms\Components\Actions::make([
                                                Forms\Components\Actions\Action::make('generate_meet_link')
                                                    ->label('Generate Google Meet URL')
                                                    ->icon('heroicon-o-video-camera')
                                                    ->color('primary')
                                                    ->visible(fn (Forms\Get $get) => ! $get('meeting_url'))
                                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                                        $user = Auth::user();
                                                        $token = $user?->google_calendar_token;

                                                        if (! $token) {
                                                            // Redirect to Google Calendar OAuth
                                                            Notification::make()
                                                                ->title('Google Calendar Access Required')
                                                                ->body('Please connect your Google Calendar account to generate meeting links.')
                                                                ->warning()
                                                                ->actions([
                                                                    \Filament\Notifications\Actions\Action::make('connect')
                                                                        ->label('Connect Google Calendar')
                                                                        ->url('/auth/google/calendar?state=meeting_link')
                                                                        ->openUrlInNewTab(),
                                                                ])
                                                                ->send();

                                                            return;
                                                        }

                                                        try {
                                                            $googleMeetService = app(GoogleMeetService::class);
                                                            $googleMeetService->setAccessToken(json_decode($token, true));

                                                            $title = $get('title') ?: 'Meeting';
                                                            $startTime = $get('meeting_start_time');
                                                            $duration = (int) $get('meeting_duration') ?: 60;

                                                            // Calculate end time
                                                            $endTime = $startTime
                                                                ? \Carbon\Carbon::parse($startTime)->addMinutes($duration)->toIso8601String()
                                                                : null;
                                                            $startTime = $startTime
                                                                ? \Carbon\Carbon::parse($startTime)->toIso8601String()
                                                                : null;

                                                            $result = $googleMeetService->generateMeetLink($title, $startTime, $endTime);

                                                            if ($result) {
                                                                $set('meeting_url', $result['meeting_url']);
                                                                $set('meeting_id', $result['meeting_id']);
                                                                $set('has_unsaved_meeting', true);

                                                                Notification::make()
                                                                    ->title('Google Meet link generated successfully!')
                                                                    ->body('Don\'t forget to save the meeting link.')
                                                                    ->success()
                                                                    ->send();
                                                            } else {
                                                                Notification::make()
                                                                    ->title('Failed to generate Google Meet link')
                                                                    ->body('Please try again or reconnect your Google Calendar account.')
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        } catch (\Exception $e) {
                                                            Notification::make()
                                                                ->title('Error generating Google Meet link')
                                                                ->body('Please reconnect your Google Calendar account.')
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    }),

                                                Forms\Components\Actions\Action::make('delete_meet_link')
                                                    ->label('Delete Link')
                                                    ->icon('heroicon-o-trash')
                                                    ->color('danger')
                                                    ->outlined()
                                                    ->visible(fn (Forms\Get $get) => (bool) $get('meeting_url'))
                                                    ->requiresConfirmation()
                                                    ->modalHeading('Delete Google Meet Link')
                                                    ->modalDescription('Are you sure you want to delete this meeting link? This action cannot be undone.')
                                                    ->modalSubmitActionLabel('Delete')
                                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                                        $user = Auth::user();
                                                        $token = $user?->google_calendar_token;
                                                        $meetingId = $get('meeting_id');

                                                        if ($token && $meetingId) {
                                                            try {
                                                                $googleMeetService = app(GoogleMeetService::class);
                                                                $googleMeetService->setAccessToken(json_decode($token, true));
                                                                $googleMeetService->deleteMeetEvent($meetingId);
                                                            } catch (\Exception $e) {
                                                                // Log error but continue with form update
                                                                \Log::error('Failed to delete Google Meet event: '.$e->getMessage());
                                                            }
                                                        }

                                                        $set('meeting_url', null);
                                                        $set('meeting_id', null);
                                                        $set('has_unsaved_meeting', false);

                                                        Notification::make()
                                                            ->title('Meeting link deleted')
                                                            ->success()
                                                            ->send();
                                                    }),
                                            ])
                                                ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Google Meet')
                                                ->columnSpanFull(),
                                        ]),

                                    // -----------------------------
                                    // Meeting Resources
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make('Meeting Resources')
                                        ->badge(function (Forms\Get $get) {
                                            // Count the number of resources selected
                                            $clients = $get('client_ids') ?? [];
                                            $projects = $get('project_ids') ?? [];
                                            $documents = $get('document_ids') ?? [];
                                            $importantUrls = $get('important_url_ids') ?? [];

                                            return count($clients) + count($projects) + count($documents) + count($importantUrls) ?: null;
                                        })
                                        ->schema([
                                            // Client
                                            Forms\Components\Select::make('client_ids')
                                                ->label('Client(s)')
                                                ->options(function () {
                                                    return Client::withTrashed()
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
                                                ->prefixAction(
                                                    Forms\Components\Actions\Action::make('openClient')
                                                        ->icon('heroicon-o-pencil-square')
                                                        ->url(function (Forms\Get $get) {
                                                            $clientIds = $get('client_ids');
                                                            if (! $clientIds || empty($clientIds)) {
                                                                return null;
                                                            }

                                                            return \App\Filament\Resources\ClientResource::getUrl('edit', ['record' => $clientIds[0]]);
                                                        })
                                                        ->openUrlInNewTab()
                                                        ->visible(fn (Forms\Get $get) => ! empty($get('client_ids')))
                                                )
                                                ->suffixAction(
                                                    Forms\Components\Actions\Action::make('createClient')
                                                        ->icon('heroicon-o-plus')
                                                        ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                                        ->openUrlInNewTab()
                                                        ->label('Create Client')
                                                )
                                                ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                    if ($state) {
                                                        // Get documents for selected clients
                                                        $documents = Document::whereHas('project', function ($query) use ($state) {
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
                                                                ->label('Create Project')
                                                        )
                                                        ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                                            $selectedProjects = $state ?? [];
                                                            $currentDocuments = $get('document_ids') ?? [];

                                                            if (empty($selectedProjects)) {
                                                                $set('document_ids', []);

                                                                return;
                                                            }

                                                            // Get all documents for the selected projects
                                                            $availableDocuments = Document::whereIn('project_id', $selectedProjects)
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
                                                                return Document::whereIn('project_id', $projectIds)
                                                                    ->withTrashed()
                                                                    ->orderBy('title')
                                                                    ->get()
                                                                    ->mapWithKeys(fn ($d) => [
                                                                        $d->id => str($d->title)->limit(20).($d->deleted_at ? ' (deleted)' : ''),
                                                                    ])
                                                                    ->toArray();
                                                            }

                                                            if (! empty($clientIds)) {
                                                                return Document::whereHas('project', function ($query) use ($clientIds) {
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
                                                                ->label('Create Document')
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
                                                                ->label('Create Important URL')
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

                                    // -----------------------------
                                    // Meeting Additional Information
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make('Additional Information')
                                        ->badge(function (Forms\Get $get) {
                                            $extraInfo = $get('extra_information') ?? [];

                                            return count($extraInfo) ?: null;
                                        })
                                        ->schema([
                                            Forms\Components\RichEditor::make('notes')
                                                ->label('Description')
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
                                                ->reactive()
                                                ->helperText(function (Forms\Get $get) {
                                                    $raw = $get('notes') ?? '';
                                                    $noHtml = strip_tags($raw);
                                                    $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                    $remaining = 500 - mb_strlen($decoded);

                                                    return 'Characters remaining: '.$remaining;
                                                })
                                                ->rule(function (Forms\Get $get): \Closure {
                                                    return function (string $attribute, $value, \Closure $fail) {
                                                        $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                        if (mb_strlen($textOnly) > 500) {
                                                            $fail('Description must not exceed 500 characters.');
                                                        }
                                                    };
                                                })
                                                ->nullable()
                                                ->columnSpanFull(),

                                            Forms\Components\Repeater::make('extra_information')
                                                ->label('Extra Information')
                                                ->schema([
                                                    Forms\Components\TextInput::make('title')
                                                        ->label('Title')
                                                        ->maxLength(100)
                                                        ->columnSpanFull(),
                                                    Forms\Components\RichEditor::make('value')
                                                        ->label('Value')
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
                                                        ->reactive()
                                                        ->helperText(function (Forms\Get $get) {
                                                            $raw = $get('value') ?? '';
                                                            $noHtml = strip_tags($raw);
                                                            $decoded = html_entity_decode($noHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                                            $remaining = 500 - mb_strlen($decoded);

                                                            return 'Characters remaining: '.$remaining;
                                                        })
                                                        ->rule(function (Forms\Get $get): \Closure {
                                                            return function (string $attribute, $value, \Closure $fail) {
                                                                $textOnly = trim(preg_replace('/\s+/', ' ', strip_tags($value ?? '')));
                                                                if (mb_strlen($textOnly) > 500) {
                                                                    $fail('Value must not exceed 500 characters.');
                                                                }
                                                            };
                                                        })
                                                        ->columnSpanFull(),
                                                ])
                                                ->defaultItems(1)
                                                ->addActionLabel('Add Extra Info')
                                                ->cloneable()
                                                ->reorderable()
                                                ->collapsible(true)
                                                ->collapsed()
                                                ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : 'Extra Information')
                                                ->live()
                                                ->columnSpanFull()
                                                ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                                        ]),

                                    // -----------------------------
                                    // Invite Users
                                    // -----------------------------
                                    Forms\Components\Tabs\Tab::make('Invite')
                                        ->badge(function (Forms\Get $get) {
                                            $userIds = $get('user_ids') ?? [];

                                            return count($userIds) ?: null;
                                        })
                                        ->schema([
                                            Forms\Components\Select::make('user_ids')
                                                ->label('User(s)')
                                                ->options(function () {
                                                    return \App\Models\User::withTrashed()
                                                        ->orderBy('username')
                                                        ->get()
                                                        ->mapWithKeys(fn ($u) => [
                                                            $u->id => ($u->username ?: 'User #'.$u->id).($u->deleted_at ? ' (deleted)' : ''),
                                                        ])
                                                        ->toArray();
                                                })
                                                ->searchable()
                                                ->preload()
                                                ->native(false)
                                                ->nullable()
                                                ->multiple()
                                                ->columnSpanFull(),
                                        ]),
                                ]),
                        ])
                        ->columnSpan([
                            'default' => 1,
                            'sm' => 1,
                            'md' => 1,
                            'lg' => 1,
                            'xl' => 1,
                            '2xl' => 5,
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // Disable record URL and record action for all records
            ->recordUrl(null)
            ->recordAction(null)
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->title),

                Tables\Columns\TextColumn::make('meeting_platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Google Meet' => 'success',
                        'Zoom Meeting' => 'info',
                        'Teams Meeting' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('meeting_url')
                    ->label('Meeting Link')
                    ->limit(30)
                    ->copyable()
                    ->tooltip(fn ($record) => $record->meeting_url)
                    ->url(fn ($record) => $record->meeting_url, true)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ViewColumn::make('updated_at')
                    ->label('Updated At (By)')
                    ->view('filament.resources.meeting-link-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meeting_platform')
                    ->options([
                        'Google Meet' => 'Google Meet',
                        'Zoom Meeting' => 'Zoom Meeting',
                        'Teams Meeting' => 'Teams Meeting',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeetingLinks::route('/'),
            'create' => Pages\CreateMeetingLink::route('/create'),
            'edit' => Pages\EditMeetingLink::route('/{record}/edit'),
        ];
    }
}
