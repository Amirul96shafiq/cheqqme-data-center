<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeetingLinkResource\Pages;
use App\Models\Client;
use App\Models\Document;
use App\Models\MeetingLink;
use App\Services\GoogleMeetService;
use App\Services\ZoomMeetingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class MeetingLinkResource extends Resource
{
    protected static ?string $model = MeetingLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    public static function getNavigationGroup(): ?string
    {
        return __('meetinglink.navigation_group');
    }

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return __('navigation.meeting_links');
    }

    public static function getModelLabel(): string
    {
        return __('navigation.meeting_link');
    }

    public static function getPluralModelLabel(): string
    {
        return __('navigation.meeting_links');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'meeting_url', 'meeting_platform'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            __('meetinglink.table.platform') => $record->meeting_platform,
            __('meetinglink.table.created_by') => optional($record->createdBy)->name,
            __('meetinglink.table.start_time') => $record->meeting_start_time,
            __('meetinglink.form.meeting_url') => $record->meeting_url,
        ];
    }

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

    protected static function getMeetingTextForCopy($record): string
    {
        $meetingUrl = $record->meeting_url ?? 'TBD';
        $platform = $record->meeting_platform ?? 'TBD';
        $startTime = $record->meeting_start_time ? $record->meeting_start_time->format('j/n/y, h:i A') : 'TBD';
        $duration = $record->meeting_duration ? match ($record->meeting_duration) {
            30 => '30 minutes',
            60 => '1 hour',
            90 => '1 hour 30 minutes',
            120 => '2 hours',
            default => $record->meeting_duration.' minutes'
        } : 'TBD';

        return "Good day everyone ✨,\n\nHere's the meeting link for the upcoming session yea!\n\n👉 {$meetingUrl}\n\n💻 Platform: {$platform}\n📅 Date & Time: {$startTime} GMT+8\n⏰ Duration: {$duration}\n\nCan't wait to catch up with you all soon! ☺️";
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
                    // Main content with responsive 60/40 (lg+) and 100/100 (below lg)
                    Forms\Components\Grid::make([
                        'default' => 1,
                        'xl' => 5,
                    ])
                        ->schema([
                            // -----------------------------
                            // Meeting Information Section (60% - 3 columns)
                            // -----------------------------
                            Forms\Components\Section::make(__('meetinglink.form.meeting_information'))
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label(__('meetinglink.form.title'))
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
                                        ->helperText(__('meetinglink.form.title_helper'))
                                        ->columnSpanFull(),

                                    Forms\Components\Grid::make(3)
                                        ->schema([
                                            Forms\Components\Select::make('meeting_platform')
                                                ->label(__('meetinglink.form.meeting_platform'))
                                                ->options([
                                                    'Google Meet' => __('meetinglink.platform.google_meet'),
                                                    'Zoom Meeting' => __('meetinglink.platform.zoom_meeting'),
                                                    'Teams Meeting' => __('meetinglink.platform.teams_meeting').' (Coming Soon)',
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
                                                ->label(__('meetinglink.form.meeting_start_time'))
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
                                                ->visible(fn (Forms\Get $get) => in_array($get('meeting_platform'), ['Google Meet', 'Zoom Meeting']))
                                                ->columnSpan(1),

                                            Forms\Components\Select::make('meeting_duration')
                                                ->label(__('meetinglink.form.meeting_duration'))
                                                ->searchable()
                                                ->options([
                                                    30 => __('meetinglink.duration.30_minutes'),
                                                    60 => __('meetinglink.duration.1_hour'),
                                                    90 => __('meetinglink.duration.1_hour_30_minutes'),
                                                    120 => __('meetinglink.duration.2_hours'),
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
                                                ->visible(fn (Forms\Get $get) => in_array($get('meeting_platform'), ['Google Meet', 'Zoom Meeting']))
                                                ->columnSpan(1),
                                        ])
                                        ->columns(3),

                                    Forms\Components\Grid::make(2)
                                        ->schema([

                                            Forms\Components\TextInput::make('meeting_url')
                                                ->label(__('meetinglink.form.meeting_url'))
                                                ->disabled()
                                                ->dehydrated()
                                                ->placeholder(__('meetinglink.notifications.no_meeting_url'))
                                                ->prefixAction(
                                                    Forms\Components\Actions\Action::make('regenerate_meet_link')
                                                        ->label(__('meetinglink.actions.regenerate_meet_link'))
                                                        ->icon('heroicon-o-arrow-path')
                                                        ->color('warning')
                                                        ->requiresConfirmation()
                                                        ->modalHeading(__('meetinglink.notifications.regenerate_link_heading'))
                                                        ->modalDescription(__('meetinglink.notifications.regenerate_link_description'))
                                                        ->modalSubmitActionLabel(__('meetinglink.actions.regenerate_meet_link'))
                                                        ->visible(fn (Forms\Get $get) => (bool) $get('meeting_url'))
                                                        ->action(function (Forms\Set $set, Forms\Get $get) {
                                                            $platform = $get('meeting_platform');
                                                            $user = Auth::user();

                                                            // Check platform and token
                                                            if ($platform === 'Google Meet') {
                                                                $token = $user?->google_calendar_token;

                                                                if (! $token) {
                                                                    Notification::make()
                                                                        ->title(__('meetinglink.notifications.google_meet_required'))
                                                                        ->body(__('meetinglink.notifications.google_meet_required_body'))
                                                                        ->warning()
                                                                        ->actions([
                                                                            \Filament\Notifications\Actions\Action::make('connect')
                                                                                ->label(__('meetinglink.actions.connect_google_calendar'))
                                                                                ->url('/auth/google/calendar?state=meeting_link'),
                                                                        ])
                                                                        ->send();

                                                                    return;
                                                                }
                                                            } elseif ($platform === 'Zoom Meeting') {
                                                                $token = $user?->zoom_token;

                                                                if (! $token) {
                                                                    Notification::make()
                                                                        ->title(__('meetinglink.notifications.zoom_access_required'))
                                                                        ->body(__('meetinglink.notifications.zoom_access_required_body'))
                                                                        ->warning()
                                                                        ->actions([
                                                                            \Filament\Notifications\Actions\Action::make('connect')
                                                                                ->label(__('meetinglink.actions.connect_zoom'))
                                                                                ->url('/auth/zoom?state=meeting_link'),
                                                                        ])
                                                                        ->send();

                                                                    return;
                                                                }
                                                            }

                                                            try {
                                                                $title = $get('title') ?: 'Meeting';
                                                                $startTime = $get('meeting_start_time');
                                                                $duration = (int) $get('meeting_duration') ?: 60;

                                                                if ($platform === 'Google Meet') {
                                                                    $googleMeetService = app(GoogleMeetService::class);
                                                                    $googleMeetService->setAccessToken(json_decode($token, true));

                                                                    // Delete old meeting
                                                                    $oldMeetingId = $get('meeting_id');
                                                                    if ($oldMeetingId) {
                                                                        try {
                                                                            $googleMeetService->deleteMeetEvent($oldMeetingId);
                                                                        } catch (\Exception $e) {
                                                                            \Log::error('Failed to delete old Google Meet event: '.$e->getMessage());
                                                                        }
                                                                    }

                                                                    $endTime = $startTime
                                                                        ? \Carbon\Carbon::parse($startTime)->addMinutes($duration)->toIso8601String()
                                                                        : null;
                                                                    $startTime = $startTime
                                                                        ? \Carbon\Carbon::parse($startTime)->toIso8601String()
                                                                        : null;

                                                                    $result = $googleMeetService->generateMeetLink($title, $startTime, $endTime);
                                                                } elseif ($platform === 'Zoom Meeting') {
                                                                    $zoomMeetingService = app(ZoomMeetingService::class);
                                                                    $zoomMeetingService->setAccessToken(json_decode($token, true));

                                                                    // Delete old meeting
                                                                    $oldMeetingId = $get('meeting_id');
                                                                    if ($oldMeetingId) {
                                                                        try {
                                                                            $zoomMeetingService->deleteMeeting($oldMeetingId);
                                                                        } catch (\Exception $e) {
                                                                            \Log::error('Failed to delete old Zoom meeting: '.$e->getMessage());
                                                                        }
                                                                    }

                                                                    $startTime = $startTime
                                                                        ? \Carbon\Carbon::parse($startTime)->toIso8601String()
                                                                        : null;

                                                                    $result = $zoomMeetingService->generateMeetingLink($title, $startTime, $duration);
                                                                } else {
                                                                    $result = null;
                                                                }

                                                                if ($result) {
                                                                    $set('meeting_url', $result['meeting_url']);
                                                                    $set('meeting_id', $result['meeting_id']);
                                                                    $set('has_unsaved_meeting', true);

                                                                    // Set meeting passcode for Zoom meetings
                                                                    if ($platform === 'Zoom Meeting' && isset($result['password'])) {
                                                                        $set('meeting_passcode', $result['password']);
                                                                    }

                                                                    $platformName = $platform === 'Google Meet' ? 'Google Meet' : 'Zoom';
                                                                    Notification::make()
                                                                        ->title("{$platformName} link regenerated successfully")
                                                                        ->body("Your {$platformName} meeting link has been updated.")
                                                                        ->success()
                                                                        ->send();
                                                                } else {
                                                                    $platformName = $platform === 'Google Meet' ? 'Google Meet' : 'Zoom';
                                                                    Notification::make()
                                                                        ->title("Failed to generate {$platformName} link")
                                                                        ->body("Please try again or reconnect your {$platformName} account.")
                                                                        ->danger()
                                                                        ->send();
                                                                }
                                                            } catch (\Exception $e) {
                                                                $platformName = $platform === 'Google Meet' ? 'Google Meet' : 'Zoom';
                                                                Notification::make()
                                                                    ->title("Error generating {$platformName} link")
                                                                    ->body("An error occurred while generating the {$platformName} link. Please try again.")
                                                                    ->danger()
                                                                    ->send();
                                                            }
                                                        })
                                                )
                                                ->visible(fn (Forms\Get $get) => in_array($get('meeting_platform'), ['Google Meet', 'Zoom Meeting']))
                                                ->columnSpan(2),
                                        ]),

                                    // Hidden field to track unsaved changes
                                    Forms\Components\Hidden::make('has_unsaved_meeting')
                                        ->dehydrated(false),

                                    // Actions for Google Meet and Zoom
                                    Forms\Components\Actions::make([
                                        Forms\Components\Actions\Action::make('generate_meet_link')
                                            ->label(__('meetinglink.actions.generate_meet_link'))
                                            ->icon('heroicon-o-video-camera')
                                            ->color('primary')
                                            ->visible(fn (Forms\Get $get) => ! $get('meeting_url'))
                                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                                $platform = $get('meeting_platform');
                                                $user = Auth::user();

                                                // Check platform and token
                                                if ($platform === 'Google Meet') {
                                                    $token = $user?->google_calendar_token;

                                                    if (! $token) {
                                                        Notification::make()
                                                            ->title(__('meetinglink.notifications.google_meet_required'))
                                                            ->body(__('meetinglink.notifications.google_meet_required_body'))
                                                            ->warning()
                                                            ->actions([
                                                                \Filament\Notifications\Actions\Action::make('connect')
                                                                    ->label(__('meetinglink.actions.connect_google_calendar'))
                                                                    ->url('/auth/google/calendar?state=meeting_link'),
                                                            ])
                                                            ->send();

                                                        return;
                                                    }
                                                } elseif ($platform === 'Zoom Meeting') {
                                                    $token = $user?->zoom_token;

                                                    if (! $token) {
                                                        Notification::make()
                                                            ->title(__('meetinglink.notifications.zoom_access_required'))
                                                            ->body(__('meetinglink.notifications.zoom_access_required_body'))
                                                            ->warning()
                                                            ->actions([
                                                                \Filament\Notifications\Actions\Action::make('connect')
                                                                    ->label(__('meetinglink.actions.connect_zoom'))
                                                                    ->url('/auth/zoom?state=meeting_link'),
                                                            ])
                                                            ->send();

                                                        return;
                                                    }
                                                } else {
                                                    return;
                                                }

                                                try {
                                                    $title = $get('title') ?: 'Meeting';
                                                    $startTime = $get('meeting_start_time');
                                                    $duration = (int) $get('meeting_duration') ?: 60;

                                                    if ($platform === 'Google Meet') {
                                                        $googleMeetService = app(GoogleMeetService::class);
                                                        $googleMeetService->setAccessToken(json_decode($token, true));

                                                        // Calculate end time
                                                        $endTime = $startTime
                                                            ? \Carbon\Carbon::parse($startTime)->addMinutes($duration)->toIso8601String()
                                                            : null;
                                                        $startTime = $startTime
                                                            ? \Carbon\Carbon::parse($startTime)->toIso8601String()
                                                            : null;

                                                        $result = $googleMeetService->generateMeetLink($title, $startTime, $endTime);
                                                    } elseif ($platform === 'Zoom Meeting') {
                                                        $zoomMeetingService = app(ZoomMeetingService::class);
                                                        $zoomMeetingService->setAccessToken(json_decode($token, true));

                                                        $startTime = $startTime
                                                            ? \Carbon\Carbon::parse($startTime)->toIso8601String()
                                                            : null;

                                                        $result = $zoomMeetingService->generateMeetingLink($title, $startTime, $duration);
                                                    } else {
                                                        $result = null;
                                                    }

                                                    if ($result) {
                                                        $set('meeting_url', $result['meeting_url']);
                                                        $set('meeting_id', $result['meeting_id']);
                                                        $set('has_unsaved_meeting', true);

                                                        // Set meeting passcode for Zoom meetings
                                                        if ($platform === 'Zoom Meeting' && isset($result['password'])) {
                                                            $set('meeting_passcode', $result['password']);
                                                        }

                                                        $platformName = $platform === 'Google Meet' ? 'Google Meet' : 'Zoom';
                                                        Notification::make()
                                                            ->title("{$platformName} link generated successfully")
                                                            ->body("Your {$platformName} meeting link has been created.")
                                                            ->success()
                                                            ->send();
                                                    } else {
                                                        $platformName = $platform === 'Google Meet' ? 'Google Meet' : 'Zoom';
                                                        Notification::make()
                                                            ->title("Failed to generate {$platformName} link")
                                                            ->body("Please try again or reconnect your {$platformName} account.")
                                                            ->danger()
                                                            ->send();
                                                    }
                                                } catch (\Exception $e) {
                                                    $platformName = $platform === 'Google Meet' ? 'Google Meet' : 'Zoom';
                                                    Notification::make()
                                                        ->title("Error generating {$platformName} link")
                                                        ->body("An error occurred while generating the {$platformName} link. Please try again.")
                                                        ->danger()
                                                        ->send();
                                                }
                                            }),
                                    ])
                                        ->visible(fn (Forms\Get $get) => in_array($get('meeting_platform'), ['Google Meet', 'Zoom Meeting']))
                                        ->columnSpanFull(),
                                ])
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 3,
                                ]),

                            // -----------------------------
                            // Meeting Settings Section (40% - 2 columns)
                            // -----------------------------
                            Forms\Components\Section::make(__('meetinglink.form.meeting_settings'))
                                ->schema([
                                    Forms\Components\Select::make('user_ids')
                                        ->label(__('meetinglink.form.users'))
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

                                    Forms\Components\TextInput::make('meeting_id')
                                        ->label(__('meetinglink.form.meeting_id'))
                                        ->disabled()
                                        ->dehydrated()
                                        ->placeholder(__('meetinglink.form.meeting_id_placeholder'))
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('meeting_passcode')
                                        ->label(__('meetinglink.form.meeting_passcode'))
                                        ->disabled()
                                        ->dehydrated()
                                        ->placeholder(__('meetinglink.form.meeting_passcode_placeholder'))
                                        ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Zoom Meeting')
                                        ->columnSpanFull(),

                                    Forms\Components\Section::make(__('meetinglink.form.google_meet_guide'))
                                        ->collapsible()
                                        ->collapsed()
                                        ->schema([
                                            Forms\Components\Placeholder::make('google_meet_guide_content')
                                                ->label('')
                                                ->content(function () {
                                                    $html = '<div class="space-y-4 text-sm font-mono">';

                                                    $html .= '<ul class="list-disc list-outside text-gray-500 dark:text-gray-400 space-y-1 ml-4">';
                                                    $html .= '<li>'.__('meetinglink.google_meet_guide.host_controls_instruction').'</li>';
                                                    $html .= '</ul>';

                                                    // Host Controls Setup Image
                                                    $html .= '<div class="mt-4">';
                                                    $html .= '<a href="/images/google-meet-setup-02.png" target="_blank" rel="noopener noreferrer" class="block">';
                                                    $html .= '<img src="/images/google-meet-setup-02.png" alt="'.__('meetinglink.google_meet_guide.image_alt').'" class="w-full rounded-lg hover:opacity-60 transition-opacity cursor-pointer">';
                                                    $html .= '</a>';
                                                    $html .= '</div>';

                                                    $html .= '</div>';

                                                    return new \Illuminate\Support\HtmlString($html);
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Google Meet')
                                        ->columnSpanFull(),

                                    Forms\Components\Section::make(__('meetinglink.form.zoom_meeting_guide'))
                                        ->collapsible()
                                        ->collapsed()
                                        ->schema([
                                            Forms\Components\Placeholder::make('zoom_meeting_guide_content')
                                                ->label('')
                                                ->content(function () {
                                                    $html = '<div class="space-y-4 text-sm font-mono">';

                                                    $html .= '<ul class="list-disc list-outside text-gray-500 dark:text-gray-400 space-y-1 ml-4">';
                                                    $html .= '<li>'.__('meetinglink.zoom_meeting_guide.host_controls_instruction').'</li>';
                                                    $html .= '</ul>';

                                                    // Zoom Meeting Setup Image
                                                    $html .= '<div class="mt-4">';
                                                    $html .= '<a href="/images/zoom-meet-setup-02.png" target="_blank" rel="noopener noreferrer" class="block">';
                                                    $html .= '<img src="/images/zoom-meet-setup-02.png" alt="'.__('meetinglink.zoom_meeting_guide.image_alt').'" class="w-full rounded-lg hover:opacity-60 transition-opacity cursor-pointer">';
                                                    $html .= '</a>';
                                                    $html .= '</div>';

                                                    $html .= '</div>';

                                                    return new \Illuminate\Support\HtmlString($html);
                                                })
                                                ->columnSpanFull(),
                                        ])
                                        ->visible(fn (Forms\Get $get) => $get('meeting_platform') === 'Zoom Meeting')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible()
                                ->collapsed(false)
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 2,
                                ]),

                            // -----------------------------
                            // Meeting Resources Section (Full Width)
                            // -----------------------------
                            Forms\Components\Section::make(__('meetinglink.form.meeting_resources'))
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
                                        ->suffixAction(
                                            Forms\Components\Actions\Action::make('createClient')
                                                ->icon('heroicon-o-plus')
                                                ->url(\App\Filament\Resources\ClientResource::getUrl('create'))
                                                ->openUrlInNewTab()
                                                ->label(__('meetinglink.actions.create_client'))
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
                                                        ->label(__('meetinglink.actions.create_project'))
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
                                                        ->label(__('meetinglink.actions.create_document'))
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
                                                        ->label(__('meetinglink.actions.create_important_url'))
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
                                ])
                                ->collapsible()
                                ->collapsed()
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 5,
                                ]),

                            // -----------------------------
                            // Additional Information Section (Full Width)
                            // -----------------------------
                            Forms\Components\Section::make(__('meetinglink.form.additional_information'))
                                ->schema([
                                    Forms\Components\RichEditor::make('notes')
                                        ->label(__('meetinglink.form.description'))
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
                                        ->label(__('meetinglink.form.extra_information'))
                                        ->schema([
                                            Forms\Components\TextInput::make('title')
                                                ->label(__('meetinglink.form.extra_info_title'))
                                                ->maxLength(100)
                                                ->columnSpanFull(),
                                            Forms\Components\RichEditor::make('value')
                                                ->label(__('meetinglink.form.extra_info_value'))
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
                                        ->addActionLabel(__('meetinglink.form.add_extra_info'))
                                        ->cloneable()
                                        ->reorderable()
                                        ->collapsible(true)
                                        ->collapsed()
                                        ->itemLabel(fn (array $state): string => ! empty($state['title']) ? $state['title'] : __('meetinglink.form.extra_information'))
                                        ->live()
                                        ->columnSpanFull()
                                        ->extraAttributes(['class' => 'no-repeater-collapse-toolbar']),
                                ])
                                ->collapsible()
                                ->collapsed()
                                ->columnSpan([
                                    'default' => 1,
                                    'xl' => 5,
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
                    ->label(__('meetinglink.table.id')),

                Tables\Columns\TextColumn::make('title')
                    ->label(__('meetinglink.table.title'))
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->title),

                Tables\Columns\TextColumn::make('meeting_platform')
                    ->label(__('meetinglink.table.platform'))
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Google Meet', 'Zoom Meeting', 'Teams Meeting' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('meeting_url')
                    ->label(__('meetinglink.table.url_link'))
                    ->searchable()
                    ->limit(40)
                    ->copyable()
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => $state ? str_replace(['https://', 'http://'], '', $state) : null)
                    ->url(fn ($record) => $record->meeting_url)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('meeting_start_time')
                    ->label(__('meetinglink.table.start_time'))
                    ->sortable()
                    ->dateTime('j/n/y, h:i A')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('attendees_count')
                    ->label(__('meetinglink.table.attendees'))
                    ->badge()
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $userIds = $record->user_ids;

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
                        $userIds = $record->user_ids;

                        // Handle both array and JSON string cases for color
                        if (is_array($userIds)) {
                            return count($userIds) > 0 ? 'primary' : 'gray';
                        } elseif (is_string($userIds)) {
                            $decoded = json_decode($userIds, true);

                            return is_array($decoded) && count($decoded) > 0 ? 'primary' : 'gray';
                        }

                        return 'gray';
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('meetinglink.table.created_at'))
                    ->dateTime('j/n/y, h:i A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\ViewColumn::make('updated_by')
                    ->label(__('meetinglink.table.updated_at_by'))
                    ->view('filament.resources.meeting-link-resource.updated-by-column')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('meeting_platform')
                    ->label(__('meetinglink.form.meeting_platform'))
                    ->searchable()
                    ->options([
                        'Google Meet' => __('meetinglink.platform.google_meet'),
                        'Zoom Meeting' => __('meetinglink.platform.zoom_meeting'),
                        'Teams Meeting' => __('meetinglink.platform.teams_meeting'),
                    ]),

                Tables\Filters\Filter::make('meeting_start_time')
                    ->form([
                        Forms\Components\DatePicker::make('start_date_from')
                            ->label(__('meetinglink.filters.start_date_from'))
                            ->native(false)
                            ->displayFormat('j/n/y'),
                        Forms\Components\DatePicker::make('start_date_until')
                            ->label(__('meetinglink.filters.start_date_until'))
                            ->native(false)
                            ->displayFormat('j/n/y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when(
                                $data['start_date_from'] ?? null,
                                fn ($query, $date) => $query->whereDate('meeting_start_time', '>=', $date)
                            )
                            ->when(
                                $data['start_date_until'] ?? null,
                                fn ($query, $date) => $query->whereDate('meeting_start_time', '<=', $date)
                            );
                    }),

                Tables\Filters\TrashedFilter::make()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\Action::make('open_url')
                    ->label('')
                    ->icon('heroicon-o-link')
                    ->color('primary')
                    ->url(fn ($record) => $record->meeting_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => ! empty($record->meeting_url)),
                Tables\Actions\ViewAction::make()
                    ->label(__('meetinglink.actions.view')),
                Tables\Actions\EditAction::make()
                    ->label(__('meetinglink.actions.edit'))
                    ->hidden(fn ($record) => $record->trashed()),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('share_meeting_link')
                        ->label(__('meetinglink.actions.share_meeting_link'))
                        ->icon('heroicon-o-share')
                        ->color('primary')
                        ->visible(fn ($record) => ! $record->trashed() && $record->meeting_url)
                        ->modalWidth('2xl')
                        ->modalHeading(__('meetinglink.actions.share_meeting_link'))
                        ->modalDescription(__('meetinglink.actions.share_meeting_link_description'))
                        ->form(function ($record) {
                            $meetingText = self::getMeetingTextForCopy($record);

                            return [
                                Forms\Components\Textarea::make('meeting_preview')
                                    ->label(__('meetinglink.actions.meeting_preview'))
                                    ->default($meetingText)
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
                            return [
                                Tables\Actions\Action::make('copy_to_clipboard')
                                    ->label(__('meetinglink.actions.copy_to_clipboard'))
                                    ->icon('heroicon-o-clipboard-document')
                                    ->color('primary')
                                    ->extraAttributes([
                                        'x-data' => '{}',
                                        'x-on:copy-success.window' => 'showCopiedBubble($el)',
                                    ])
                                    ->action(function () use ($record, $livewire) {
                                        $meetingText = self::getMeetingTextForCopy($record);

                                        // Dispatch browser event with the text to copy and success callback
                                        $livewire->dispatch('copy-to-clipboard-with-callback', text: $meetingText);
                                    }),
                                Tables\Actions\Action::make('edit_meeting_link')
                                    ->label(__('meetinglink.actions.edit_meeting_link'))
                                    ->icon('heroicon-o-pencil-square')
                                    ->color('gray')
                                    ->url(fn ($record) => self::getUrl('edit', ['record' => $record->id]))
                                    ->close(),
                            ];
                        }),
                    ActivityLogTimelineTableAction::make(__('meetinglink.actions.activity_log'))
                        ->label(__('meetinglink.actions.activity_log')),
                    Tables\Actions\DeleteAction::make()
                        ->label(__('meetinglink.actions.delete')),
                    Tables\Actions\RestoreAction::make()
                        ->label(__('meetinglink.actions.restore')),
                    Tables\Actions\ForceDeleteAction::make()
                        ->label(__('meetinglink.actions.force_delete')),
                ]),
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make(__('meetinglink.infolist.meeting_details'))
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('title')
                                    ->label(__('meetinglink.infolist.meeting_title')),
                                Infolists\Components\TextEntry::make('meeting_platform')
                                    ->label(__('meetinglink.infolist.platform'))
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Google Meet', 'Zoom Meeting', 'Teams Meeting' => 'primary',
                                        default => 'gray',
                                    }),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('meeting_start_time')
                                    ->label(__('meetinglink.infolist.start_time'))
                                    ->dateTime('j/n/y - h:i A'),
                                Infolists\Components\TextEntry::make('meeting_duration')
                                    ->label(__('meetinglink.infolist.duration'))
                                    ->formatStateUsing(fn (int $state): string => match ($state) {
                                        30 => __('meetinglink.duration.30_minutes'),
                                        60 => __('meetinglink.duration.1_hour'),
                                        90 => __('meetinglink.duration.1_hour_30_minutes'),
                                        120 => __('meetinglink.duration.2_hours'),
                                        default => $state.' minutes'
                                    }),
                            ]),
                        Infolists\Components\TextEntry::make('meeting_url')
                            ->label(__('meetinglink.infolist.meeting_url'))
                            ->copyable()
                            ->url(fn ($record) => $record->meeting_url)
                            ->openUrlInNewTab()
                            ->placeholder(__('meetinglink.infolist.no_meeting_url')),
                    ]),

                Infolists\Components\Section::make(__('meetinglink.infolist.additional_information'))
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('createdBy.name')
                                    ->label(__('meetinglink.infolist.created_by'))
                                    ->placeholder(__('meetinglink.infolist.unknown')),
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label(__('meetinglink.infolist.created_at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('updatedBy.name')
                                    ->label(__('meetinglink.infolist.updated_by'))
                                    ->placeholder('-'),
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label(__('meetinglink.infolist.updated_at'))
                                    ->dateTime('j/n/y, h:i A'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
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
