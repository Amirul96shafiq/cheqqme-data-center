<?php

namespace App\Filament\Pages;

use App\Forms\Components\TimezoneField;
use App\Helpers\TimezoneHelper;
use App\Services\ChatbotBackupService;
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
            ->schema([
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

                        // API documentation section
                        Forms\Components\Section::make(__('settings.api.documentation'))
                            ->collapsible()
                            ->collapsed()
                            ->description(__('settings.api.documentation_description'))
                            ->schema([
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        Forms\Components\Placeholder::make('api_documentation')
                                            ->label('')
                                            ->content(function () {
                                                $user = Auth::user();
                                                $baseUrl = config('app.url').'/api';
                                                $apiDocsUrl = route('api.documentation', [], false);
                                                $apiKey = $user?->api_key ?? 'YOUR_API_KEY';
                                                $maskedApiKey = $this->getMaskedApiKey($apiKey);

                                                $html = '<div class="space-y-4">';

                                                // Base URL
                                                $html .= '<div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">';
                                                $html .= '<div class="flex-1">';
                                                $html .= '<p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">'.__('settings.api.documentation_content.base_url').':</p>';
                                                $html .= '<code class="text-sm text-gray-600 dark:text-gray-400">'.$baseUrl.'</code>';
                                                $html .= '</div>';
                                                $html .= '<button type="button" onclick="copyWithFeedback(this, \''.addslashes($baseUrl).'\')" class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                $html .= '<svg class="copy-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                $html .= '<svg class="check-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                $html .= '</button>';
                                                $html .= '</div>';

                                                // API Header
                                                $html .= '<div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">';
                                                $html .= '<div class="flex-1">';
                                                $html .= '<p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">'.__('settings.api.documentation_content.api_header').':</p>';
                                                $html .= '<code class="text-sm text-gray-600 dark:text-gray-400">Accept: application/json</code>';
                                                $html .= '</div>';
                                                $html .= '<button type="button" onclick="copyWithFeedback(this, \'Accept: application/json\')" class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                $html .= '<svg class="copy-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                $html .= '<svg class="check-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                $html .= '</button>';
                                                $html .= '</div>';

                                                // Authentication
                                                $html .= '<div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">';
                                                $html .= '<div class="flex-1">';
                                                $html .= '<p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">'.__('settings.api.documentation_content.authentication').':</p>';
                                                $html .= '<code class="text-sm text-gray-600 dark:text-gray-400">Authorization: Bearer '.$maskedApiKey.'</code>';
                                                $html .= '</div>';
                                                $html .= '<button type="button" onclick="copyWithFeedback(this, \'Authorization: Bearer '.addslashes($apiKey).'\')" class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                $html .= '<svg class="copy-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                $html .= '<svg class="check-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                $html .= '</button>';
                                                $html .= '</div>';

                                                // Example Request
                                                $html .= '<div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">';
                                                $html .= '<div class="flex-1">';
                                                $html .= '<p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">'.__('settings.api.documentation_content.example_request').':</p>';
                                                $html .= '<code class="text-sm text-gray-600 dark:text-gray-400">';
                                                $html .= 'GET '.$baseUrl.'/clients<br>';
                                                $html .= 'Accept: application/json<br>';
                                                $html .= 'Authorization: Bearer '.$maskedApiKey;
                                                $html .= '</code>';
                                                $html .= '</div>';
                                                $html .= '<button type="button" onclick="copyWithFeedback(this, \'GET '.addslashes($baseUrl).'/clients\\nAccept: application/json\\nAuthorization: Bearer '.addslashes($apiKey).'\')" class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                $html .= '<svg class="copy-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                $html .= '<svg class="check-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                $html .= '</button>';
                                                $html .= '</div>';

                                                // Sample Screenshot
                                                $html .= '<div class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">';
                                                $html .= '<p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">'.__('settings.api.documentation_content.sample_screenshot').':</p>';
                                                $html .= '<a href="/images/api-sample-screenshot.png" target="_blank" class="block">';
                                                $html .= '<img src="/images/api-sample-screenshot.png" alt="API Documentation: Sample Screenshot" class="w-full h-auto rounded-lg cursor-pointer hover:opacity-90 transition-opacity">';
                                                $html .= '</a>';
                                                $html .= '</div>';

                                                // List of Supported API
                                                $html .= '<div class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">';
                                                $html .= '<p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">'.__('settings.api.documentation_content.list_of_supported_api').':</p>';

                                                // User Endpoints
                                                $html .= '<div class="space-y-2 mb-4">';
                                                $html .= '<h4 class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">User Endpoints</h4>';

                                                $userEndpoints = [
                                                    'GET '.$baseUrl.'/profile',
                                                    'GET '.$baseUrl.'/api-key-info',
                                                ];

                                                foreach ($userEndpoints as $endpoint) {
                                                    $html .= '<div class="flex items-center justify-between">';
                                                    $html .= '<code class="text-sm text-gray-600 dark:text-gray-400">'.$endpoint.'</code>';
                                                    $html .= '<button type="button" onclick="copyWithFeedback(this, \''.addslashes($endpoint).'\')" class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                    $html .= '<svg class="copy-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                    $html .= '<svg class="check-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                    $html .= '</button>';
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';

                                                // Resource Endpoints
                                                $html .= '<div class="space-y-2 mb-4">';
                                                $html .= '<h4 class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Resource Endpoints</h4>';

                                                $resourceEndpoints = [
                                                    'GET '.$baseUrl.'/clients',
                                                    'GET '.$baseUrl.'/projects',
                                                    'GET '.$baseUrl.'/documents',
                                                    'GET '.$baseUrl.'/important-urls',
                                                    'GET '.$baseUrl.'/phone-numbers',
                                                    'GET '.$baseUrl.'/users',
                                                    'GET '.$baseUrl.'/tasks',
                                                    'GET '.$baseUrl.'/comments',
                                                    'GET '.$baseUrl.'/comments/{comment}',
                                                    'GET '.$baseUrl.'/trello-boards',
                                                    'GET '.$baseUrl.'/openai-logs',
                                                ];

                                                foreach ($resourceEndpoints as $endpoint) {
                                                    $html .= '<div class="flex items-center justify-between">';
                                                    $html .= '<code class="text-sm text-gray-600 dark:text-gray-400">'.$endpoint.'</code>';
                                                    $html .= '<button type="button" onclick="copyWithFeedback(this, \''.addslashes($endpoint).'\')" class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                    $html .= '<svg class="copy-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                    $html .= '<svg class="check-icon w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                    $html .= '</button>';
                                                    $html .= '</div>';
                                                }
                                                $html .= '</div>';

                                                // Copy All Endpoints
                                                $html .= '<div class="pt-3 border-t border-gray-200 dark:border-gray-700">';
                                                $html .= '<div class="flex items-center justify-between">';
                                                $html .= '<span class="text-sm text-gray-500 dark:text-gray-400">Copy all endpoints:</span>';
                                                $allEndpoints = implode('\\n', array_merge($userEndpoints, $resourceEndpoints));
                                                $html .= '<button type="button" onclick="copyWithFeedback(this, \''.addslashes($allEndpoints).'\')" class="inline-flex items-center px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors">';
                                                $html .= '<svg class="copy-icon w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>';
                                                $html .= '<svg class="check-icon w-4 h-4 mr-1 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                                                $html .= '<span class="copy-text">Copy All</span>';
                                                $html .= '<span class="copied-text hidden">Copied!</span>';
                                                $html .= '</button>';
                                                $html .= '</div>';
                                                $html .= '</div>';

                                                $html .= '</div>';

                                                $html .= '</div>';

                                                return new \Illuminate\Support\HtmlString($html);
                                            })
                                            ->columnSpan(12),
                                    ]),
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
                    ->headerActions([
                        \Filament\Forms\Components\Actions\Action::make('create_backup')
                            ->label(__('settings.chatbot.create_backup'))
                            ->icon('heroicon-o-archive-box')
                            ->color('primary')
                            ->action(function () {
                                try {
                                    $user = Auth::user();
                                    $backupService = new ChatbotBackupService;
                                    $backup = $backupService->createBackup($user, 'manual');

                                    Notification::make()
                                        ->title(__('settings.notifications.backup_created'))
                                        ->body(__('settings.notifications.backup_created_body', ['name' => $backup->backup_name]))
                                        ->success()
                                        ->send();

                                    // Refresh the page to show the new backup
                                    $this->redirect(request()->url());
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title(__('settings.notifications.backup_failed'))
                                        ->body($e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }),
                    ])
                    ->schema([
                        // Backups table - Direct implementation without nested Livewire
                        Forms\Components\Grid::make(12)
                            ->schema([
                                Forms\Components\Placeholder::make('backups_table')
                                    ->label('')
                                    ->content(function () {
                                        // Get search and filter parameters from request
                                        $search = request()->get('backup_search', '');
                                        $backupTypeFilter = request()->get('backup_type_filter', '');
                                        $visibleCount = request()->get('backup_visible_count', 5);

                                        $query = \App\Models\ChatbotBackup::where('user_id', Auth::id());

                                        // Apply search filter if search term is provided
                                        if (! empty($search)) {
                                            $query->where(function ($q) use ($search) {
                                                $q->where('backup_name', 'like', '%'.$search.'%')
                                                    ->orWhere('backup_type', 'like', '%'.$search.'%')
                                                    ->orWhere('formatted_date_range', 'like', '%'.$search.'%');
                                            });
                                        }

                                        // Apply backup type filter if selected
                                        if (! empty($backupTypeFilter)) {
                                            $query->where('backup_type', $backupTypeFilter);
                                        }

                                        // When searching or filtering, show all results. Otherwise, limit to visible count
                                        if (! empty($search) || ! empty($backupTypeFilter)) {
                                            $backups = $query->orderBy('backup_date', 'desc')->get();
                                        } else {
                                            $backups = $query->orderBy('backup_date', 'desc')
                                                ->take($visibleCount)
                                                ->get();
                                        }

                                        // Get total count for pagination
                                        $totalQuery = \App\Models\ChatbotBackup::where('user_id', Auth::id());
                                        if (! empty($search)) {
                                            $totalQuery->where(function ($q) use ($search) {
                                                $q->where('backup_name', 'like', '%'.$search.'%')
                                                    ->orWhere('backup_type', 'like', '%'.$search.'%')
                                                    ->orWhere('formatted_date_range', 'like', '%'.$search.'%');
                                            });
                                        }
                                        if (! empty($backupTypeFilter)) {
                                            $totalQuery->where('backup_type', $backupTypeFilter);
                                        }
                                        $totalBackups = $totalQuery->count();
                                        $hasActiveFilters = ! empty($search) || ! empty($backupTypeFilter);

                                        $html = '<div id="chatbot-backups-table" class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">';

                                        // Search Input and Filters - Above Table Header
                                        $html .= '<div class="bg-white dark:bg-gray-900 px-6 py-3 border-b border-gray-200 dark:border-gray-700">';
                                        $html .= '<div class="flex items-center justify-end gap-4">';

                                        // Search Input
                                        $html .= '<div class="relative w-60">';
                                        $html .= '<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">';
                                        $html .= '<svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>';
                                        $html .= '</svg>';
                                        $html .= '</div>';
                                        $html .= '<input type="text" id="backup-search" placeholder="'.__('settings.chatbot.search.placeholder').'" value="'.$search.'" class="fi-input block w-full rounded-lg bg-transparent px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-400 sm:text-sm sm:leading-6 pl-10 pr-10">';
                                        if ($search) {
                                            $html .= '<div class="absolute inset-y-0 right-0 pr-3 flex items-center">';
                                            $html .= '<button type="button" onclick="clearBackupSearch()" class="text-gray-400 hover:text-gray-300 transition-colors duration-200" title="'.__('settings.chatbot.search.clear').'">';
                                            $html .= '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                                            $html .= '</button>';
                                            $html .= '</div>';
                                        }
                                        $html .= '</div>';

                                        // Filter Button / Dropdown
                                        $html .= '<div class="relative inline-block text-left">';
                                        $html .= '<div>';
                                        $html .= '<button type="button" onclick="toggleBackupFilters()" class="fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined flex items-center border-0 text-sm font-medium text-gray-400 hover:text-gray-500 transition duration-75 disabled:bg-gray-50 disabled:text-gray-500 dark:text-gray-500 hover:dark:text-gray-400 dark:disabled:bg-gray-800 dark:disabled:text-gray-500">';
                                        $html .= '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>';
                                        if ($hasActiveFilters) {
                                            $filterCount = ($search ? 1 : 0) + ($backupTypeFilter ? 1 : 0);
                                            $html .= '<span class="fi-badge fi-color-danger fi-size-xs inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30 ml-1">';
                                            $html .= $filterCount;
                                            $html .= '</span>';
                                        }
                                        $html .= '</button>';
                                        $html .= '</div>';

                                        // Filter dropdown
                                        $html .= '<div id="backup-filters-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">';
                                        $html .= '<div class="py-1" role="menu" aria-orientation="vertical">';

                                        // Filter Header
                                        $html .= '<div class="flex items-center justify-between px-2 py-1.5">';
                                        $html .= '<span class="text-sm font-medium text-gray-700 dark:text-gray-200">'.__('settings.chatbot.filter.label').'</span>';
                                        if ($hasActiveFilters) {
                                            $html .= '<button type="button" onclick="clearBackupFilters()" class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">';
                                            $html .= __('settings.chatbot.filter.reset');
                                            $html .= '</button>';
                                        }
                                        $html .= '</div>';

                                        // Backup Type Filter
                                        $html .= '<div class="px-3 py-2">';
                                        $html .= '<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">'.__('settings.chatbot.filter.backup_type').'</label>';
                                        $html .= '<select id="backup-type-filter" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">';
                                        $html .= '<option value="">'.__('settings.chatbot.filter.all_types').'</option>';
                                        $html .= '<option value="weekly"'.($backupTypeFilter === 'weekly' ? ' selected' : '').'>'.__('settings.chatbot.filter.types.weekly').'</option>';
                                        $html .= '<option value="manual"'.($backupTypeFilter === 'manual' ? ' selected' : '').'>'.__('settings.chatbot.filter.types.manual').'</option>';
                                        $html .= '<option value="import"'.($backupTypeFilter === 'import' ? ' selected' : '').'>'.__('settings.chatbot.filter.types.import').'</option>';
                                        $html .= '</select>';
                                        $html .= '</div>';

                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</div>';
                                        $html .= '</div>';

                                        // Table
                                        $html .= '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';

                                        // Table header
                                        $html .= '<thead class="bg-gray-50 dark:bg-gray-800">';
                                        $html .= '<tr>';
                                        $html .= '<th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_id').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_name').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_type').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_messages').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_date_range').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_backed_up').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_size').'</th>';
                                        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400"></th>';
                                        $html .= '</tr>';
                                        $html .= '</thead>';

                                        // Table body
                                        $html .= '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

                                        if ($backups->count() > 0) {
                                            foreach ($backups as $backup) {
                                                $badgeColors = [
                                                    'weekly' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                                    'manual' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                                    'import' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                                ];
                                                $color = $badgeColors[$backup->backup_type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';

                                                $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">'.$backup->id.'</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">'.$backup->backup_name.'</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap">';
                                                $html .= '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full '.$color.'">'.ucfirst($backup->backup_type).'</span>';
                                                $html .= '</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.number_format($backup->message_count).'</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.$backup->formatted_date_range.'</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.$backup->formatted_backup_date.'</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.$backup->file_size.'</td>';
                                                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
                                                $html .= '<div class="flex items-center justify-end">';

                                                // Actions dropdown
                                                $html .= '<div class="relative inline-block text-left">';
                                                $html .= '<div>';
                                                $html .= '<button type="button" onclick="toggleActionsDropdown('.$backup->id.')" class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors" aria-expanded="false" aria-haspopup="true">';
                                                $html .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">';
                                                $html .= '<path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>';
                                                $html .= '</svg>';
                                                $html .= '</button>';
                                                $html .= '</div>';

                                                // Dropdown menu
                                                $html .= '<div id="actions-dropdown-'.$backup->id.'" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">';
                                                $html .= '<div class="py-1" role="menu" aria-orientation="vertical">';

                                                // Download action
                                                $html .= '<button type="button" onclick="downloadBackup('.$backup->id.'); hideActionsDropdown('.$backup->id.');" class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">';
                                                $html .= '<svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                                                $html .= '</svg>';
                                                $html .= __('settings.chatbot.actions.download');
                                                $html .= '</button>';

                                                // Restore action
                                                $html .= '<button type="button" onclick="restoreBackup('.$backup->id.'); hideActionsDropdown('.$backup->id.');" class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">';
                                                $html .= '<svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
                                                $html .= '</svg>';
                                                $html .= __('settings.chatbot.actions.restore');
                                                $html .= '</button>';

                                                // Divider
                                                $html .= '<div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>';

                                                // Delete action
                                                $html .= '<button type="button" onclick="deleteBackup('.$backup->id.'); hideActionsDropdown('.$backup->id.');" class="group flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/20" role="menuitem">';
                                                $html .= '<svg class="w-4 h-4 mr-3 text-red-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>';
                                                $html .= '</svg>';
                                                $html .= __('settings.chatbot.actions.delete');
                                                $html .= '</button>';

                                                $html .= '</div>';
                                                $html .= '</div>';
                                                $html .= '</div>';
                                                $html .= '</div>';
                                                $html .= '</td>';
                                                $html .= '</tr>';
                                            }
                                        } else {
                                            $html .= '<tr>';
                                            $html .= '<td colspan="8" class="px-6 py-12 text-center">';
                                            if ($hasActiveFilters) {
                                                $html .= '<svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>';
                                                $html .= '</svg>';
                                                $html .= '<h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">'.__('settings.chatbot.empty.no_results_title').'</h3>';
                                                $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">';
                                                if ($search && $backupTypeFilter) {
                                                    $html .= __('settings.chatbot.empty.no_results_both', ['search' => $search, 'type' => ucfirst($backupTypeFilter)]);
                                                } elseif ($search) {
                                                    $html .= __('settings.chatbot.empty.no_results_search', ['search' => $search]);
                                                } elseif ($backupTypeFilter) {
                                                    $html .= __('settings.chatbot.empty.no_results_type', ['type' => ucfirst($backupTypeFilter)]);
                                                }
                                                $html .= '</p>';
                                                $html .= '<div class="mt-4">';
                                                $html .= '<button onclick="clearBackupFilters()" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">';
                                                $html .= __('settings.chatbot.actions_menu.clear_filters');
                                                $html .= '</button>';
                                                $html .= '</div>';
                                            } else {
                                                $html .= '<svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                                                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>';
                                                $html .= '</svg>';
                                                $html .= '<h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">'.__('settings.chatbot.no_backups').'</h3>';
                                                $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">'.__('settings.chatbot.no_backups_description').'</p>';
                                            }
                                            $html .= '</td>';
                                            $html .= '</tr>';
                                        }
                                        $html .= '</tbody>';
                                        $html .= '</table>';

                                        // Show total backups - only show when not searching or filtering
                                        if (! $hasActiveFilters && $totalBackups > 0) {
                                            $html .= '<div class="mt-3 text-[10px] text-gray-400 text-center">';
                                            $html .= __('settings.chatbot.showing', ['shown' => $backups->count(), 'total' => $totalBackups]);
                                            $html .= '</div>';
                                        }

                                        // Show more backups button - only show when not searching or filtering
                                        if (! $hasActiveFilters && $totalBackups > $visibleCount) {
                                            $remaining = $totalBackups - $visibleCount;
                                            $html .= '<div class="mt-2">';
                                            $html .= '<button onclick="loadMoreBackups('.$visibleCount.')" type="button" class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-primary-500 dark:bg-primary-600 hover:bg-primary-400 dark:hover:bg-primary-500 text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500/40">';
                                            $html .= __('settings.chatbot.load_more', ['count' => $remaining < 5 ? $remaining : 5]);
                                            $html .= '</button>';
                                            $html .= '</div>';
                                        }

                                        $html .= '</div>';

                                        return new \Illuminate\Support\HtmlString($html);
                                    })
                                    ->columnSpan(12),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    // Save the settings
    public function save(): void
    {
        $data = $this->form->getState();

        // Save the settings to the user model
        $user = Auth::user();
        $user->update([
            'timezone' => $data['timezone'] ?? config('app.timezone', 'UTC'),
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'location_updated_at' => now(),
        ]);

        Notification::make()
            ->title(__('settings.notifications.settings_saved'))
            ->body(__('settings.notifications.settings_saved_body'))
            ->success()
            ->send();
    }

    // Get country from timezone
    private function getCountryFromTimezone(string $timezone): string
    {
        return TimezoneHelper::getCountryFromTimezone($timezone);
    }

    // Get localized date
    private function getLocalizedDate(\DateTime $date): string
    {
        $locale = app()->getLocale();

        if ($locale === 'ms') {
            // Malay date formatting
            $dayNames = [
                'Sunday' => 'Ahad',
                'Monday' => 'Isnin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Khamis',
                'Friday' => 'Jumaat',
                'Saturday' => 'Sabtu',
            ];

            $monthNames = [
                'January' => 'Januari',
                'February' => 'Februari',
                'March' => 'Mac',
                'April' => 'April',
                'May' => 'Mei',
                'June' => 'Jun',
                'July' => 'Julai',
                'August' => 'Ogos',
                'September' => 'September',
                'October' => 'Oktober',
                'November' => 'November',
                'December' => 'Disember',
            ];

            $day = $dayNames[$date->format('l')] ?? $date->format('l');
            $month = $monthNames[$date->format('F')] ?? $date->format('F');
            $dayNum = $date->format('j');
            $year = $date->format('Y');

            return "{$day}, {$month} {$dayNum}, {$year}";
        }

        // Default English format
        return $date->format('l, F j, Y');
    }

    // AJAX endpoint for backup table updates
    public function getBackupTable()
    {
        // Get search and filter parameters from request
        $search = request()->get('backup_search', '');
        $backupTypeFilter = request()->get('backup_type_filter', '');
        $visibleCount = request()->get('backup_visible_count', 5);

        $query = \App\Models\ChatbotBackup::where('user_id', Auth::id());

        // Apply search filter if search term is provided
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('backup_name', 'like', '%'.$search.'%')
                    ->orWhere('backup_type', 'like', '%'.$search.'%')
                    ->orWhere('formatted_date_range', 'like', '%'.$search.'%');
            });
        }

        // Apply backup type filter if selected
        if (! empty($backupTypeFilter)) {
            $query->where('backup_type', $backupTypeFilter);
        }

        // When searching or filtering, show all results. Otherwise, limit to visible count
        if (! empty($search) || ! empty($backupTypeFilter)) {
            $backups = $query->orderBy('backup_date', 'desc')->get();
        } else {
            $backups = $query->orderBy('backup_date', 'desc')
                ->take($visibleCount)
                ->get();
        }

        // Get total count for pagination
        $totalQuery = \App\Models\ChatbotBackup::where('user_id', Auth::id());
        if (! empty($search)) {
            $totalQuery->where(function ($q) use ($search) {
                $q->where('backup_name', 'like', '%'.$search.'%')
                    ->orWhere('backup_type', 'like', '%'.$search.'%')
                    ->orWhere('formatted_date_range', 'like', '%'.$search.'%');
            });
        }
        if (! empty($backupTypeFilter)) {
            $totalQuery->where('backup_type', $backupTypeFilter);
        }
        $totalBackups = $totalQuery->count();
        $hasActiveFilters = ! empty($search) || ! empty($backupTypeFilter);

        $html = '<div id="chatbot-backups-table" class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">';

        // Search Input and Filters - Above Table Header
        $html .= '<div class="bg-white dark:bg-gray-900 px-6 py-3 border-b border-gray-200 dark:border-gray-700">';
        $html .= '<div class="flex items-center justify-end gap-4">';

        // Search Input
        $html .= '<div class="relative w-60">';
        $html .= '<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">';
        $html .= '<svg class="h-5 w-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>';
        $html .= '</svg>';
        $html .= '</div>';
        $html .= '<input type="text" id="backup-search" placeholder="'.__('settings.chatbot.search.placeholder').'" value="'.$search.'" class="fi-input block w-full rounded-lg bg-transparent px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-400 sm:text-sm sm:leading-6 pl-10 pr-10">';
        if ($search) {
            $html .= '<div class="absolute inset-y-0 right-0 pr-3 flex items-center">';
            $html .= '<button type="button" onclick="clearBackupSearch()" class="text-gray-400 hover:text-gray-300 transition-colors duration-200" title="'.__('settings.chatbot.search.clear').'">';
            $html .= '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            $html .= '</button>';
            $html .= '</div>';
        }
        $html .= '</div>';

        // Filter Button / Dropdown
        $html .= '<div class="relative inline-block text-left">';
        $html .= '<div>';
        $html .= '<button type="button" onclick="toggleBackupFilters()" class="fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined flex items-center border-0 text-sm font-medium text-gray-400 hover:text-gray-500 transition duration-75 disabled:bg-gray-50 disabled:text-gray-500 dark:text-gray-500 hover:dark:text-gray-400 dark:disabled:bg-gray-800 dark:disabled:text-gray-500">';
        $html .= '<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>';
        if ($hasActiveFilters) {
            $filterCount = ($search ? 1 : 0) + ($backupTypeFilter ? 1 : 0);
            $html .= '<span class="fi-badge fi-color-danger fi-size-xs inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30 ml-1">';
            $html .= $filterCount;
            $html .= '</span>';
        }
        $html .= '</button>';
        $html .= '</div>';

        // Filter dropdown
        $html .= '<div id="backup-filters-dropdown" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">';
        $html .= '<div class="py-1" role="menu" aria-orientation="vertical">';

        // Filter Header
        $html .= '<div class="flex items-center justify-between px-2 py-1.5">';
        $html .= '<span class="text-sm font-medium text-gray-700 dark:text-gray-200">'.__('settings.chatbot.filter.label').'</span>';
        if ($hasActiveFilters) {
            $html .= '<button type="button" onclick="clearBackupFilters()" class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">';
            $html .= __('settings.chatbot.filter.reset');
            $html .= '</button>';
        }
        $html .= '</div>';

        // Backup Type Filter
        $html .= '<div class="px-3 py-2">';
        $html .= '<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">'.__('settings.chatbot.filter.backup_type').'</label>';
        $html .= '<select id="backup-type-filter" class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">';
        $html .= '<option value="">'.__('settings.chatbot.filter.all_types').'</option>';
        $html .= '<option value="weekly"'.($backupTypeFilter === 'weekly' ? ' selected' : '').'>'.__('settings.chatbot.filter.types.weekly').'</option>';
        $html .= '<option value="manual"'.($backupTypeFilter === 'manual' ? ' selected' : '').'>'.__('settings.chatbot.filter.types.manual').'</option>';
        $html .= '<option value="import"'.($backupTypeFilter === 'import' ? ' selected' : '').'>'.__('settings.chatbot.filter.types.import').'</option>';
        $html .= '</select>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Table
        $html .= '<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">';

        // Table header
        $html .= '<thead class="bg-gray-50 dark:bg-gray-800">';
        $html .= '<tr>';
        $html .= '<th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_id').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_name').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_type').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_messages').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_date_range').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_backed_up').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">'.__('settings.chatbot.backup_size').'</th>';
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400"></th>';
        $html .= '</tr>';
        $html .= '</thead>';

        // Table body
        $html .= '<tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">';

        if ($backups->count() > 0) {
            foreach ($backups as $backup) {
                $badgeColors = [
                    'weekly' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                    'manual' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    'import' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                ];
                $color = $badgeColors[$backup->backup_type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';

                $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-800">';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">'.$backup->id.'</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">'.$backup->backup_name.'</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap">';
                $html .= '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full '.$color.'">'.ucfirst($backup->backup_type).'</span>';
                $html .= '</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.number_format($backup->message_count).'</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.$backup->formatted_date_range.'</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.$backup->formatted_backup_date.'</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">'.$backup->file_size.'</td>';
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
                $html .= '<div class="flex items-center justify-end">';

                // Actions dropdown
                $html .= '<div class="relative inline-block text-left">';
                $html .= '<div>';
                $html .= '<button type="button" onclick="toggleActionsDropdown('.$backup->id.')" class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors" aria-expanded="false" aria-haspopup="true">';
                $html .= '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">';
                $html .= '<path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>';
                $html .= '</svg>';
                $html .= '</button>';
                $html .= '</div>';

                // Dropdown menu
                $html .= '<div id="actions-dropdown-'.$backup->id.'" class="hidden origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">';
                $html .= '<div class="py-1" role="menu" aria-orientation="vertical">';

                // Download action
                $html .= '<button type="button" onclick="downloadBackup('.$backup->id.'); hideActionsDropdown('.$backup->id.');" class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">';
                $html .= '<svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                $html .= '</svg>';
                $html .= __('settings.chatbot.actions.download');
                $html .= '</button>';

                // Restore action
                $html .= '<button type="button" onclick="restoreBackup('.$backup->id.'); hideActionsDropdown('.$backup->id.');" class="group flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" role="menuitem">';
                $html .= '<svg class="w-4 h-4 mr-3 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>';
                $html .= '</svg>';
                $html .= __('settings.chatbot.actions.restore');
                $html .= '</button>';

                // Divider
                $html .= '<div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>';

                // Delete action
                $html .= '<button type="button" onclick="deleteBackup('.$backup->id.'); hideActionsDropdown('.$backup->id.');" class="group flex items-center w-full px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/20" role="menuitem">';
                $html .= '<svg class="w-4 h-4 mr-3 text-red-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>';
                $html .= '</svg>';
                $html .= __('settings.chatbot.actions.delete');
                $html .= '</button>';

                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr>';
            $html .= '<td colspan="8" class="px-6 py-12 text-center">';
            if ($hasActiveFilters) {
                $html .= '<svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>';
                $html .= '</svg>';
                $html .= '<h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">'.__('settings.chatbot.empty.no_results_title').'</h3>';
                $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">';
                if ($search && $backupTypeFilter) {
                    $html .= __('settings.chatbot.empty.no_results_both', ['search' => $search, 'type' => ucfirst($backupTypeFilter)]);
                } elseif ($search) {
                    $html .= __('settings.chatbot.empty.no_results_search', ['search' => $search]);
                } elseif ($backupTypeFilter) {
                    $html .= __('settings.chatbot.empty.no_results_type', ['type' => ucfirst($backupTypeFilter)]);
                }
                $html .= '</p>';
                $html .= '<div class="mt-4">';
                $html .= '<button onclick="clearBackupFilters()" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800">';
                $html .= __('settings.chatbot.actions_menu.clear_filters');
                $html .= '</button>';
                $html .= '</div>';
            } else {
                $html .= '<svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
                $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>';
                $html .= '</svg>';
                $html .= '<h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">'.__('settings.chatbot.no_backups').'</h3>';
                $html .= '<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">'.__('settings.chatbot.no_backups_description').'</p>';
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody>';
        $html .= '</table>';

        // Show total backups - only show when not searching or filtering
        if (! $hasActiveFilters && $totalBackups > 0) {
            $html .= '<div class="mt-3 text-[10px] text-gray-400 text-center">';
            $html .= __('settings.chatbot.showing', ['shown' => $backups->count(), 'total' => $totalBackups]);
            $html .= '</div>';
        }

        // Show more backups button - only show when not searching or filtering
        if (! $hasActiveFilters && $totalBackups > $visibleCount) {
            $remaining = $totalBackups - $visibleCount;
            $html .= '<div class="mt-2">';
            $html .= '<button onclick="loadMoreBackups('.$visibleCount.')" type="button" class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-primary-500 dark:bg-primary-600 hover:bg-primary-400 dark:hover:bg-primary-500 text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500/40">';
            $html .= __('settings.chatbot.load_more', ['count' => $remaining < 5 ? $remaining : 5]);
            $html .= '</button>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return response($html);
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
        // Generate consistent mock data based on city and country
        $seed = crc32($city.$country);
        mt_srand($seed);

        // Mock weather conditions with multilingual support
        $conditions = [
            'clear' => [
                'en' => 'Clear',
                'ms' => 'Jernih',
                'description_en' => 'clear sky',
                'description_ms' => 'langit jernih',
            ],
            'clouds' => [
                'en' => 'Clouds',
                'ms' => 'Berawan',
                'description_en' => 'few clouds',
                'description_ms' => 'sedikit awan',
            ],
            'rain' => [
                'en' => 'Rain',
                'ms' => 'Hujan',
                'description_en' => 'light rain',
                'description_ms' => 'hujan ringan',
            ],
            'sunny' => [
                'en' => 'Sunny',
                'ms' => 'Cerah',
                'description_en' => 'sunny',
                'description_ms' => 'cerah',
            ],
            'partly_cloudy' => [
                'en' => 'Partly Cloudy',
                'ms' => 'Sebahagian Berawan',
                'description_en' => 'partly cloudy',
                'description_ms' => 'sebahagian berawan',
            ],
            'overcast' => [
                'en' => 'Overcast',
                'ms' => 'Mendung',
                'description_en' => 'overcast clouds',
                'description_ms' => 'awan mendung',
            ],
            'thunderstorm' => [
                'en' => 'Thunderstorm',
                'ms' => 'Ribut Petir',
                'description_en' => 'thunderstorm',
                'description_ms' => 'ribut petir',
            ],
            'snow' => [
                'en' => 'Snow',
                'ms' => 'Salji',
                'description_en' => 'light snow',
                'description_ms' => 'salji ringan',
            ],
            'fog' => [
                'en' => 'Fog',
                'ms' => 'Kabus',
                'description_en' => 'fog',
                'description_ms' => 'kabus',
            ],
            'mist' => [
                'en' => 'Mist',
                'ms' => 'Jerebu',
                'description_en' => 'mist',
                'description_ms' => 'jerebu',
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
                'wind_direction' => mt_rand(0, 360),
                'uv_index' => mt_rand(1, 8),
                'sunrise' => '6:30 AM',
                'sunset' => '6:30 PM',
            ],
            'timestamp' => now()->toISOString(),
            'cached' => false,
        ];
    }

    // Get base temperature for country (mock climate data)
    private function getBaseTemperatureForCountry(string $country): int
    {
        $countryTemps = [
            'MY' => 28, // Malaysia - tropical
            'SG' => 28, // Singapore - tropical
            'TH' => 30, // Thailand - tropical
            'ID' => 28, // Indonesia - tropical
            'PH' => 28, // Philippines - tropical
            'US' => 20, // United States - temperate
            'CA' => 15, // Canada - temperate
            'GB' => 12, // United Kingdom - temperate
            'AU' => 22, // Australia - temperate
            'JP' => 18, // Japan - temperate
            'KR' => 16, // South Korea - temperate
            'CN' => 18, // China - temperate
            'IN' => 32, // India - tropical/subtropical
            'BR' => 26, // Brazil - tropical
            'MX' => 24, // Mexico - subtropical
            'RU' => 5,  // Russia - cold
            'DE' => 14, // Germany - temperate
            'FR' => 15, // France - temperate
            'IT' => 18, // Italy - temperate
            'ES' => 20, // Spain - temperate
        ];

        return $countryTemps[strtoupper($country)] ?? 20; // Default temperate
    }

    // Get masked API key
    private function getMaskedApiKey(string $apiKey): string
    {
        if (empty($apiKey) || $apiKey === 'YOUR_API_KEY') {
            return $apiKey;
        }

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
}
