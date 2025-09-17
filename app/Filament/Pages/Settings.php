<?php

namespace App\Filament\Pages;

use App\Forms\Components\TimezoneField;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    protected static ?string $navigationLabel = null;

    protected static ?string $title = null;

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 99;

    // Disable navigation
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    // Get navigation label and title
    public static function getNavigationLabel(): string
    {
        return __('settings.page.navigation_label');
    }

    // Get title
    public function getTitle(): string
    {
        return __('settings.page.title');
    }

    // Get data
    public ?array $data = [];

    // Mount the page
    public function mount(): void
    {
        $user = Auth::user();
        $data = $user->toArray();

        // Set the current API key display value
        $data['current_api_key'] = $user->getMaskedApiKey();

        // Set default timezone if not set
        if (empty($data['timezone'])) {
            $data['timezone'] = config('app.timezone', 'UTC');
        }

        // Set default location values if not set
        if (empty($data['city'])) {
            $data['city'] = config('weather.default_location.city', 'Kuala Lumpur');
        }
        if (empty($data['country'])) {
            $data['country'] = config('weather.default_location.country', 'MY');
        }
        if (empty($data['latitude'])) {
            $data['latitude'] = config('weather.default_location.latitude', 3.1390);
        }
        if (empty($data['longitude'])) {
            $data['longitude'] = config('weather.default_location.longitude', 101.6869);
        }

        // Set initial timezone preview
        if (! empty($data['timezone'])) {
            try {
                $timezone = new \DateTimeZone($data['timezone']);
                $now = new \DateTime('now', $timezone);
                $data['timezone_preview'] = $now->format('Y-m-d H:i:s T');
            } catch (\Exception $e) {
                $data['timezone_preview'] = 'Invalid timezone';
            }
        }

        $this->form->fill($data);
    }

    // Get listeners
    public function getListeners(): array
    {
        return [
            'clipboard-copy-success' => 'handleClipboardCopySuccess',
            'clipboard-copy-failure' => 'handleClipboardCopyFailure',
            'location-detected' => 'handleLocationDetected',
            'location-detection-failed' => 'handleLocationDetectionFailed',
        ];
    }

    // Handle clipboard copy success
    public function handleClipboardCopySuccess(): void
    {
        Notification::make()
            ->title(__('settings.notifications.api_key_copied'))
            ->body(__('settings.notifications.api_key_copied_body'))
            ->success()
            ->send();
    }

    // Handle clipboard copy failure
    public function handleClipboardCopyFailure(): void
    {
        Notification::make()
            ->title(__('settings.notifications.api_key_copy_failed'))
            ->body(__('settings.notifications.api_key_copy_failed_body'))
            ->danger()
            ->send();
    }

    // Handle location detected
    public function handleLocationDetected($latitude, $longitude, $city = null, $country = null): void
    {
        // Get current form state to preserve existing data
        $currentData = $this->form->getState();

        // Debug: Log current timezone before update
        \Log::info('Location detected - Current timezone before update:', [
            'timezone' => $currentData['timezone'] ?? 'not set',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'city' => $city,
            'country' => $country,
        ]);

        // Update only specific location fields without affecting other form data
        $this->data['latitude'] = $latitude;
        $this->data['longitude'] = $longitude;
        $this->data['city'] = $city ?? '';
        $this->data['country'] = $country ?? '';

        // Preserve all other existing data
        foreach ($currentData as $key => $value) {
            if (! in_array($key, ['latitude', 'longitude', 'city', 'country'])) {
                $this->data[$key] = $value;
            }
        }

        // Update the form with the modified data
        $this->form->fill($this->data);

        // Debug: Log timezone after update
        \Log::info('Location detected - Timezone after update:', [
            'timezone' => $this->data['timezone'] ?? 'not set',
        ]);

        Notification::make()
            ->title(__('settings.notifications.location_detected'))
            ->body(__('settings.notifications.location_detected_body', [
                'city' => $city ?? 'Unknown',
                'country' => $country ?? 'Unknown',
            ]))
            ->success()
            ->send();
    }

    // Handle location detection failed
    public function handleLocationDetectionFailed(): void
    {
        Notification::make()
            ->title(__('settings.notifications.location_detection_failed'))
            ->body(__('settings.notifications.location_detection_failed_body'))
            ->danger()
            ->send();
    }

    // Get form
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    // Get form schema
    protected function getFormSchema(): array
    {
        $schema = [
            // API section
            Forms\Components\Section::make(__('settings.sections.api'))
                ->description(__('settings.sections.api_description'))
                ->collapsible()
                ->schema([
                    // Current API key row
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\Placeholder::make('current_api_key_label')
                                ->label(__('settings.api.current_key'))
                                ->content('')
                                ->columnSpan(4),

                            Forms\Components\Grid::make(8)
                                ->schema([
                                    Forms\Components\TextInput::make('current_api_key')
                                        ->label('')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder(__('settings.api.no_key'))
                                        ->helperText(__('settings.api.helper'))
                                        ->prefixAction(
                                            \Filament\Forms\Components\Actions\Action::make('regenerate_api_key')
                                                ->label(__('settings.api.regenerate'))
                                                ->icon('heroicon-o-arrow-path')
                                                ->color('gray')
                                                ->size('sm')
                                                ->visible(fn () => auth()->user()->hasApiKey())
                                                ->requiresConfirmation()
                                                ->modalHeading(__('settings.api.confirm_regenerate'))
                                                ->modalDescription(__('settings.api.confirm_regenerate_description'))
                                                ->modalSubmitActionLabel(__('settings.api.regenerate_action'))
                                                ->action(function ($set) {
                                                    $user = auth()->user();
                                                    $apiKey = $user->generateApiKey();
                                                    $set('current_api_key', $user->getMaskedApiKey());

                                                    Notification::make()
                                                        ->title(__('settings.notifications.api_key_regenerated'))
                                                        ->body(__('settings.notifications.api_key_regenerated_body'))
                                                        ->warning()
                                                        ->send();
                                                })
                                        )
                                        ->suffixAction(
                                            \Filament\Forms\Components\Actions\Action::make('copy_api_key')
                                                ->label(__('settings.api.copy'))
                                                ->icon('heroicon-o-square-2-stack')
                                                ->color('gray')
                                                ->size('sm')
                                                ->visible(fn () => auth()->user()->hasApiKey())
                                                ->action(function () {
                                                    $user = auth()->user();

                                                    // Dispatch browser event to copy API key to clipboard
                                                    $this->dispatch('copy-api-key', apiKey: $user->api_key);

                                                    // Show notification that copy operation was initiated
                                                    Notification::make()
                                                        ->title(__('settings.notifications.api_key_copying'))
                                                        ->body(__('settings.notifications.api_key_copying_body'))
                                                        ->info()
                                                        ->send();
                                                })
                                        )
                                        ->columnSpan(8),
                                ])
                                ->columnSpan(8),
                        ]),

                    // Actions row
                    Forms\Components\Grid::make(12)
                        ->schema([
                            // Actions label
                            Forms\Components\Placeholder::make('actions_label')
                                ->label('')
                                ->columnSpan(4),

                            // Actions: Generate, Delete
                            Forms\Components\Actions::make([
                                // Generate API key
                                \Filament\Forms\Components\Actions\Action::make('generate_api_key')
                                    ->label(__('settings.api.generate'))
                                    ->icon('heroicon-o-key')
                                    ->color('gray')
                                    ->visible(fn () => ! auth()->user()->hasApiKey())
                                    ->action(function ($set) {
                                        $user = auth()->user();
                                        $apiKey = $user->generateApiKey();
                                        $set('current_api_key', $user->getMaskedApiKey());

                                        // Notification
                                        Notification::make()
                                            ->title(__('settings.notifications.api_key_generated'))
                                            ->body(__('settings.notifications.api_key_generated_body'))
                                            ->success()
                                            ->send();
                                    }),

                                // Delete API key
                                \Filament\Forms\Components\Actions\Action::make('delete_api_key')
                                    ->label(__('settings.api.delete'))
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->outlined()
                                    ->visible(fn () => auth()->user()->hasApiKey())
                                    ->requiresConfirmation()
                                    ->modalHeading(__('settings.api.confirm_delete'))
                                    ->modalDescription(__('settings.api.confirm_delete_description'))
                                    ->modalSubmitActionLabel(__('settings.api.delete_action'))
                                    ->action(function ($set) {
                                        $user = auth()->user();
                                        $user->update([
                                            'api_key' => null,
                                            'api_key_generated_at' => null,
                                        ]);
                                        $set('current_api_key', '');

                                        Notification::make()
                                            ->title(__('settings.notifications.api_key_deleted'))
                                            ->body(__('settings.notifications.api_key_deleted_body'))
                                            ->success()
                                            ->send();
                                    }),
                            ])
                                ->columns(3)
                                ->columnSpan(8),
                        ]),
                ]),

            // Location section
            Forms\Components\Section::make(__('settings.sections.location'))
                ->description(__('settings.sections.location_description'))
                ->collapsible()
                ->schema([
                    // Location actions
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\Placeholder::make('location_actions_label')
                                ->label('')
                                ->columnSpan(4),

                            Forms\Components\Actions::make([
                                \Filament\Forms\Components\Actions\Action::make('detect_location')
                                    ->label(__('settings.location.detect'))
                                    ->icon('heroicon-o-map-pin')
                                    ->color('gray')
                                    ->action(function ($set) {
                                        // Dispatch browser event to detect location
                                        $this->dispatch('detect-user-location');

                                        Notification::make()
                                            ->title(__('settings.notifications.location_detection_started'))
                                            ->body(__('settings.notifications.location_detection_started_body'))
                                            ->info()
                                            ->send();
                                    }),

                                \Filament\Forms\Components\Actions\Action::make('clear_location')
                                    ->label(__('settings.location.clear'))
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->outlined()
                                    ->visible(function ($get) {
                                        return ! empty($get('city')) ||
                                            ! empty($get('country')) ||
                                            ! empty($get('latitude')) ||
                                            ! empty($get('longitude'));
                                    })
                                    ->action(function ($set) {
                                        $set('city', '');
                                        $set('country', '');
                                        $set('latitude', '');
                                        $set('longitude', '');

                                        Notification::make()
                                            ->title(__('settings.notifications.location_cleared'))
                                            ->body(__('settings.notifications.location_cleared_body'))
                                            ->success()
                                            ->send();
                                    }),
                            ])
                                ->columns(2)
                                ->columnSpan(8),
                        ]),
                    // Location fields
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\Placeholder::make('location_label')
                                ->label(__('settings.location.settings'))
                                ->content('')
                                ->columnSpan(4),

                            Forms\Components\Grid::make(8)
                                ->schema([
                                    Forms\Components\TextInput::make('city')
                                        ->label(__('settings.location.city'))
                                        ->placeholder('e.g., Kuala Lumpur')
                                        ->live()
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('country')
                                        ->label(__('settings.location.country'))
                                        ->placeholder('e.g., MY')
                                        ->live()
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('latitude')
                                        ->label(__('settings.location.latitude'))
                                        ->placeholder('e.g., 3.1390')
                                        ->numeric()
                                        ->step(0.0000001)
                                        ->minValue(-90)
                                        ->maxValue(90)
                                        ->columnSpan(4),

                                    Forms\Components\TextInput::make('longitude')
                                        ->label(__('settings.location.longitude'))
                                        ->placeholder('e.g., 101.6869')
                                        ->numeric()
                                        ->step(0.0000001)
                                        ->minValue(-180)
                                        ->maxValue(180)
                                        ->columnSpan(4),
                                ])
                                ->columnSpan(8),
                        ]),

                    // Weather preview section
                    Forms\Components\Section::make(__('settings.weather.preview'))
                        ->collapsible()
                        ->collapsed()
                        ->description(__('settings.weather.preview_description'))
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->schema([
                                    Forms\Components\Placeholder::make('weather_preview')
                                        ->label('')
                                        ->live()
                                        ->content(function ($get) {
                                            $city = $get('city');
                                            $country = $get('country');

                                            if (empty($city) || empty($country)) {
                                                $html = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                                    <div class="text-2xl font-bold">null</div>
                                                    <div class="text-sm mt-2">'.__('settings.weather.no_location_data_available').'</div>
                                                </div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            }

                                            try {
                                                $weatherData = $this->getWeatherPreviewData($city, $country);

                                                if (isset($weatherData['error']) && $weatherData['error']) {
                                                    $html = '<div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">';
                                                    $html .= '<div class="flex items-center">';
                                                    $html .= '<div class="flex-shrink-0">';
                                                    $html .= '<svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">';
                                                    $html .= '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
                                                    $html .= '</svg>';
                                                    $html .= '</div>';
                                                    $html .= '<div class="ml-3">';
                                                    $html .= '<p class="text-sm font-medium text-red-800 dark:text-red-200">'.$weatherData['message'].'</p>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';

                                                    return new \Illuminate\Support\HtmlString($html);
                                                }

                                                $current = $weatherData['current'];
                                                $location = $weatherData['location'];

                                                $html = '<div class="bg-gradient-to-r from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/20 p-6 rounded-lg text-teal-700 dark:text-teal-100">';
                                                $html .= '<div class="flex items-center justify-between">';

                                                // Left side - Weather icon and condition
                                                $html .= '<div class="flex items-center space-x-4">';
                                                $html .= '<div class="flex-shrink-0">';
                                                $html .= '<div class="w-16 h-16 bg-gray-100 dark:bg-gray-700/30 rounded-full flex items-center justify-center">';
                                                $html .= '<svg class="w-8 h-8 text-gray-500 dark:text-white" fill="currentColor" viewBox="0 0 20 20">';
                                                $html .= '<path d="M5.5 16a3.5 3.5 0 01-.369-6.98 4 4 0 117.753-1.977A4.5 4.5 0 1113.5 16h-8z" />';
                                                $html .= '</svg>';
                                                $html .= '</div>';
                                                $html .= '</div>';
                                                $html .= '<div>';
                                                $html .= '<h3 class="text-lg font-semibold">'.ucfirst($current['condition']).'</h3>';
                                                $html .= '<p class="text-gray-600 dark:text-gray-400 text-sm">'.ucfirst($current['description']).'</p>';
                                                $html .= '</div>';
                                                $html .= '</div>';

                                                // Right side - Temperature and location
                                                $html .= '<div class="text-right">';
                                                $html .= '<div class="text-3xl font-bold">'.$current['temperature'].'°C</div>';
                                                $html .= '<div class="text-gray-600 dark:text-gray-400 text-sm">'.__('settings.weather.feels_like').' '.$current['feels_like'].'°C</div>';
                                                $html .= '<div class="text-gray-600 dark:text-gray-400 text-sm mt-1">'.$location['city'].', '.$location['country'].'</div>';
                                                $html .= '</div>';

                                                $html .= '</div>';
                                                $html .= '</div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            } catch (\Exception $e) {
                                                \Log::error('Weather preview error: '.$e->getMessage());
                                                $html = '<div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">';
                                                $html .= '<div class="flex items-center">';
                                                $html .= '<div class="flex-shrink-0">';
                                                $html .= '<svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">';
                                                $html .= '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />';
                                                $html .= '</svg>';
                                                $html .= '</div>';
                                                $html .= '<div class="ml-3">';
                                                $html .= '<p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">'.__('settings.weather.error').'</p>';
                                                $html .= '</div>';
                                                $html .= '</div>';
                                                $html .= '</div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            }
                                        })
                                        ->columnSpan(12),
                                ]),
                        ]),
                ]),

            // Timezone section
            Forms\Components\Section::make(__('settings.sections.timezone'))
                ->description(__('settings.sections.timezone_description'))
                ->collapsible()
                ->schema([
                    // Timezone row with label and field
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\Placeholder::make('timezone_label')
                                ->label(__('settings.timezone.settings'))
                                ->content('')
                                ->columnSpan(4),

                            TimezoneField::make('timezone')
                                ->label('')
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $set) {
                                    if ($state) {
                                        try {
                                            $timezone = new \DateTimeZone($state);
                                            $now = new \DateTime('now', $timezone);
                                            $set('timezone_preview', $now->format('Y-m-d H:i:s T'));
                                        } catch (\Exception $e) {
                                            $set('timezone_preview', 'Invalid timezone');
                                        }
                                    } else {
                                        $set('timezone_preview', '');
                                    }
                                })
                                ->columnSpan(8),
                        ]),

                    // Timezone preview section
                    Forms\Components\Section::make(__('settings.timezone.current_time_preview'))
                        ->collapsible()
                        ->collapsed()
                        ->description(__('settings.timezone.preview_description'))
                        ->schema([
                            Forms\Components\Grid::make(12)
                                ->schema([
                                    Forms\Components\Placeholder::make('timezone_preview')
                                        ->label('')
                                        ->content(function ($get) {
                                            $timezone = $get('timezone');
                                            if (! $timezone) {
                                                return __('settings.timezone.select_to_preview');
                                            }

                                            try {
                                                $tz = new \DateTimeZone($timezone);
                                                $now = new \DateTime('now', $tz);

                                                // Create timestamps based on current time in selected timezone
                                                $now = new \DateTime('now', $tz);
                                                $createdAt = clone $now;
                                                $updatedAt = clone $now;
                                                $updatedAt->modify('+30 minutes');

                                                // Get the current logged-in user
                                                $currentUser = Auth::user();
                                                $userName = $currentUser ? $currentUser->name : 'System User';

                                                $html = '<div class="grid grid-cols-2 gap-6">';

                                                // Current time - Left side
                                                $html .= '<div class="bg-teal-50 dark:bg-teal-900/20 p-4 rounded-lg">';
                                                $html .= '<h4 class="font-medium text-teal-900 dark:text-teal-100 mb-3">'.__('settings.timezone.current_time').'</h4>';
                                                $html .= '<div class="text-center">';
                                                $html .= '<div class="text-2xl font-bold text-teal-700 dark:text-teal-300 mb-2">'.$now->format('g:i A').'</div>';
                                                $html .= '<div class="text-sm text-teal-600 dark:text-teal-400">'.$this->getLocalizedDate($now).'</div>';
                                                $html .= '<div class="text-xs text-teal-500 dark:text-teal-500 mt-1">'.$now->format('P').'</div>';
                                                $html .= '</div>';
                                                $html .= '</div>';

                                                // Timezone information - Right side
                                                $html .= '<div class="bg-amber-50 dark:bg-amber-900/20 p-4 rounded-lg">';
                                                $html .= '<h4 class="font-medium text-amber-900 dark:text-amber-100 mb-3">'.__('settings.timezone.information').'</h4>';
                                                $html .= '<div class="space-y-2 text-sm">';
                                                $html .= '<div class="flex justify-between"><span class="font-medium text-amber-700 dark:text-amber-300">'.__('settings.timezone.identifier_name').':</span> <span class="text-amber-600 dark:text-amber-400">'.$timezone.'</span></div>';
                                                $html .= '<div class="flex justify-between"><span class="font-medium text-amber-700 dark:text-amber-300">'.__('settings.timezone.country_code').':</span> <span class="text-amber-600 dark:text-amber-400">'.$this->getCountryFromTimezone($timezone).'</span></div>';
                                                $html .= '<div class="flex justify-between"><span class="font-medium text-amber-700 dark:text-amber-300">'.__('settings.timezone.utc_offset').':</span> <span class="text-amber-600 dark:text-amber-400">'.$now->format('P').'</span></div>';
                                                $html .= '<div class="flex justify-between"><span class="font-medium text-amber-700 dark:text-amber-300">'.__('settings.timezone.abbreviation').':</span> <span class="text-amber-600 dark:text-amber-400">'.$now->format('T').'</span></div>';
                                                $html .= '</div>';
                                                $html .= '</div>';

                                                $html .= '</div>';

                                                // Sample data table - Full width below
                                                $html .= '<div class="mt-6 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">';
                                                $html .= '<h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">'.__('settings.timezone.sample_data_preview').'</h4>';
                                                $html .= '<div class="overflow-x-auto">';
                                                $html .= '<table class="min-w-full text-sm">';
                                                $html .= '<thead class="border-b border-gray-200 dark:border-gray-700">';
                                                $html .= '<tr>';
                                                $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">'.__('settings.timezone.id').'</th>';
                                                $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">'.__('settings.timezone.title').'</th>';
                                                $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">'.__('settings.timezone.created_at').'</th>';
                                                $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">'.__('settings.timezone.updated_at').'</th>';
                                                $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">'.__('settings.timezone.by').'</th>';
                                                $html .= '</tr>';
                                                $html .= '</thead>';
                                                $html .= '<tbody class="divide-y divide-gray-200 dark:divide-gray-700">';

                                                // Sample rows with current time-based timestamps
                                                $sampleData = [
                                                    ['id' => 1, 'title' => __('settings.timezone.sample_project_alpha'), 'created' => clone $createdAt, 'updated' => clone $updatedAt, 'by' => $userName],
                                                    ['id' => 2, 'title' => __('settings.timezone.sample_task_review'), 'created' => (clone $createdAt)->modify('-1 day'), 'updated' => (clone $updatedAt)->modify('-1 day'), 'by' => $userName],
                                                    ['id' => 3, 'title' => 'Meeting Notes', 'created' => (clone $createdAt)->modify('-3 days'), 'updated' => (clone $updatedAt)->modify('-2 days'), 'by' => $userName],
                                                ];

                                                foreach ($sampleData as $row) {
                                                    $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">';
                                                    $html .= '<td class="py-2 px-3 text-gray-900 dark:text-gray-100">'.$row['id'].'</td>';
                                                    $html .= '<td class="py-2 px-3 text-gray-900 dark:text-gray-100">'.$row['title'].'</td>';
                                                    $html .= '<td class="py-2 px-3 text-gray-600 dark:text-gray-400">'.$row['created']->format('j/n/y, h:i A').'</td>';
                                                    $html .= '<td class="py-2 px-3 text-gray-600 dark:text-gray-400">'.$row['updated']->format('j/n/y, h:i A').'</td>';
                                                    $html .= '<td class="py-2 px-3 text-gray-600 dark:text-gray-400">'.$row['by'].'</td>';
                                                    $html .= '</tr>';
                                                }

                                                $html .= '</tbody>';
                                                $html .= '</table>';
                                                $html .= '</div>';
                                                $html .= '</div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            } catch (\Exception $e) {
                                                return 'Invalid timezone';
                                            }
                                        })
                                        ->visible(fn ($get) => ! empty($get('timezone')))
                                        ->extraAttributes(['class' => 'text-sm text-gray-600 dark:text-gray-400'])
                                        ->columnSpan(12),
                                ]),
                        ]),
                ]),

            // Chatbot History section
            Forms\Components\Section::make(__('settings.sections.chatbot_history'))
                ->description(__('settings.sections.chatbot_history_description'))
                ->collapsible()
                ->schema([
                    // Backups table - Livewire component
                    Forms\Components\Grid::make(12)
                        ->schema([
                            Forms\Components\ViewField::make('chatbot_backups')
                                ->label('')
                                ->view('components.livewire-wrapper', [
                                    'component' => 'chatbot-backups-table',
                                ])
                                ->columnSpanFull(),
                        ]),
                ]),
        ];

        return $schema;
    }

    // Save form data
    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        // Update user data
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'timezone' => $data['timezone'],
            'city' => $data['city'],
            'country' => $data['country'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
        ]);

        // Update API key if provided
        if (! empty($data['api_key'])) {
            $user->update(['api_key' => $data['api_key']]);
        }

        Notification::make()
            ->title(__('settings.notifications.saved'))
            ->success()
            ->send();
    }

    // Get masked API key for display
    private function getMaskedApiKey(string $apiKey): string
    {
        $length = strlen($apiKey);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        // Show first 4 and last 4 characters, mask the rest
        $first = substr($apiKey, 0, 4);
        $last = substr($apiKey, -4);
        $masked = str_repeat('*', $length - 8);

        return $first.$masked.$last;
    }

    // Get weather preview data
    private function getWeatherPreviewData(string $city, string $country): array
    {
        try {
            // Generate mock weather data based on location
            $mockWeatherData = $this->generateMockWeatherData($city, $country);

            return $mockWeatherData;
        } catch (\Exception $e) {
            \Log::error('Weather preview error: '.$e->getMessage());

            return [
                'error' => true,
                'message' => __('settings.weather.error'),
                'location' => [
                    'city' => $city,
                    'country' => $country,
                ],
                'current' => [
                    'temperature' => '-',
                    'feels_like' => '-',
                    'condition' => 'Unknown',
                    'description' => __('settings.weather.data_unavailable'),
                    'icon' => '01d',
                ],
            ];
        }
    }

    // Generate mock weather data based on location
    private function generateMockWeatherData(string $city, string $country): array
    {
        // Mock weather conditions with localized names
        $conditions = [
            'clear' => [
                'en' => 'Clear',
                'ms' => 'Cerah',
                'description_en' => 'clear sky',
                'description_ms' => 'langit cerah',
            ],
            'cloudy' => [
                'en' => 'Cloudy',
                'ms' => 'Mendung',
                'description_en' => 'scattered clouds',
                'description_ms' => 'awan berterabur',
            ],
            'rainy' => [
                'en' => 'Rainy',
                'ms' => 'Hujan',
                'description_en' => 'light rain',
                'description_ms' => 'hujan ringan',
            ],
            'sunny' => [
                'en' => 'Sunny',
                'ms' => 'Panas',
                'description_en' => 'sunny',
                'description_ms' => 'panas terik',
            ],
            'overcast' => [
                'en' => 'Overcast',
                'ms' => 'Mendung',
                'description_en' => 'overcast clouds',
                'description_ms' => 'awan tebal',
            ],
            'drizzle' => [
                'en' => 'Drizzle',
                'ms' => 'Gerimis',
                'description_en' => 'light drizzle',
                'description_ms' => 'gerimis ringan',
            ],
        ];

        // Get current locale
        $locale = app()->getLocale();
        $isMalay = $locale === 'ms';

        // Select random condition
        $conditionKeys = array_keys($conditions);
        $selectedKey = $conditionKeys[mt_rand(0, count($conditionKeys) - 1)];
        $selectedCondition = $conditions[$selectedKey];

        $condition = $isMalay ? $selectedCondition['ms'] : $selectedCondition['en'];
        $description = $isMalay ? $selectedCondition['description_ms'] : $selectedCondition['description_en'];

        // Generate temperature based on country (rough climate zones)
        $baseTemp = $this->getBaseTemperatureForCountry($country);
        $temperature = $baseTemp + mt_rand(-5, 10);
        $feelsLike = $temperature + mt_rand(-2, 3);

        return [
            'location' => [
                'city' => $city,
                'country' => $country,
            ],
            'current' => [
                'temperature' => $temperature,
                'feels_like' => $feelsLike,
                'condition' => $condition,
                'description' => $description,
                'icon' => '01d',
                'humidity' => mt_rand(40, 80),
                'pressure' => mt_rand(1000, 1020),
                'wind_speed' => mt_rand(5, 25),
                'visibility' => mt_rand(8, 15),
            ],
        ];
    }

    // Get base temperature for country (rough climate estimation)
    private function getBaseTemperatureForCountry(string $countryCode): int
    {
        $climateMap = [
            'MY' => 28, // Malaysia - tropical
            'SG' => 28, // Singapore - tropical
            'TH' => 30, // Thailand - tropical
            'ID' => 27, // Indonesia - tropical
            'PH' => 27, // Philippines - tropical
            'VN' => 26, // Vietnam - subtropical/tropical
            'US' => 20, // USA - temperate (average)
            'CA' => 15, // Canada - cool temperate
            'AU' => 22, // Australia - varied (average)
            'JP' => 18, // Japan - temperate
            'KR' => 16, // South Korea - temperate
            'CN' => 18, // China - varied (average)
            'IN' => 28, // India - tropical/subtropical
            'GB' => 12, // UK - cool temperate
            'DE' => 10, // Germany - temperate
            'FR' => 14, // France - temperate
            'IT' => 18, // Italy - Mediterranean
            'ES' => 20, // Spain - Mediterranean
            'BR' => 25, // Brazil - tropical
            'AR' => 18, // Argentina - temperate
            'MX' => 22, // Mexico - varied
            'EG' => 25, // Egypt - desert
            'ZA' => 20, // South Africa - temperate
        ];

        return $climateMap[$countryCode] ?? 20; // Default to 20°C
    }

    // Get localized date string
    private function getLocalizedDate(\DateTime $date): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ms') {
            // Malay date format
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
                5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember',
            ];

            return $date->format('j').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
        }

        // English date format
        return $date->format('F j, Y');
    }

    // Get country code from timezone
    private function getCountryFromTimezone(string $timezone): string
    {
        $timezoneMap = [
            'Asia/Kuala_Lumpur' => 'MY',
            'Asia/Singapore' => 'SG',
            'Asia/Bangkok' => 'TH',
            'Asia/Jakarta' => 'ID',
            'Asia/Manila' => 'PH',
            'Asia/Ho_Chi_Minh' => 'VN',
            'America/New_York' => 'US',
            'America/Los_Angeles' => 'US',
            'America/Chicago' => 'US',
            'America/Denver' => 'US',
            'America/Toronto' => 'CA',
            'America/Vancouver' => 'CA',
            'Australia/Sydney' => 'AU',
            'Australia/Melbourne' => 'AU',
            'Asia/Tokyo' => 'JP',
            'Asia/Seoul' => 'KR',
            'Asia/Shanghai' => 'CN',
            'Asia/Kolkata' => 'IN',
            'Europe/London' => 'GB',
            'Europe/Berlin' => 'DE',
            'Europe/Paris' => 'FR',
            'Europe/Rome' => 'IT',
            'Europe/Madrid' => 'ES',
            'America/Sao_Paulo' => 'BR',
            'America/Buenos_Aires' => 'AR',
            'America/Mexico_City' => 'MX',
            'Africa/Cairo' => 'EG',
            'Africa/Johannesburg' => 'ZA',
        ];

        return $timezoneMap[$timezone] ?? 'Unknown';
    }
}
