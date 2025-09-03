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
        return __('settings.navigation_label');
    }

    // Get title
    public function getTitle(): string
    {
        return __('settings.title');
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

        // Set initial timezone preview
        if (!empty($data['timezone'])) {
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
        ];
    }

    // Handle clipboard copy success
    public function handleClipboardCopySuccess(): void
    {
        Notification::make()
            ->title(__('settings.form.api_key_copied'))
            ->body(__('settings.form.api_key_copied_body'))
            ->success()
            ->send();
    }

    // Handle clipboard copy failure
    public function handleClipboardCopyFailure(): void
    {
        Notification::make()
            ->title(__('settings.form.api_key_copy_failed'))
            ->body(__('settings.form.api_key_copy_failed_body'))
            ->danger()
            ->send();
    }

    // Get form
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // API section
                Forms\Components\Section::make(__('settings.section.api'))
                    ->description(__('settings.section.api_description'))
                    ->collapsible()
                    ->schema([
                        // Current API key row
                        Forms\Components\Grid::make(12)
                            ->schema([
                                Forms\Components\Placeholder::make('current_api_key_label')
                                    ->label(__('settings.form.current_api_key'))
                                    ->content('')
                                    ->columnSpan(4),

                                Forms\Components\Grid::make(8)
                                    ->schema([
                                        Forms\Components\TextInput::make('current_api_key')
                                            ->label('')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->placeholder(__('settings.form.no_api_key'))
                                            ->helperText(__('settings.form.api_key_helper'))
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

                                // Actions: Generate, Copy, Regenerate, Delete
                                Forms\Components\Actions::make([
                                    // Copy API key
                                    \Filament\Forms\Components\Actions\Action::make('copy_api_key')
                                        ->label(__('settings.form.copy_api_key'))
                                        ->icon('heroicon-o-clipboard')
                                        ->color('gray')
                                        ->visible(fn() => auth()->user()->hasApiKey())
                                        ->action(function () {
                                            $user = auth()->user();

                                            // Dispatch browser event to copy API key to clipboard
                                            $this->dispatch('copy-api-key', apiKey: $user->api_key);

                                            // Show notification that copy operation was initiated
                                            Notification::make()
                                                ->title(__('settings.form.api_key_copying'))
                                                ->body(__('settings.form.api_key_copying_body'))
                                                ->info()
                                                ->send();
                                        }),

                                    // Generate API key
                                    \Filament\Forms\Components\Actions\Action::make('generate_api_key')
                                        ->label(__('settings.form.generate_api_key'))
                                        ->icon('heroicon-o-key')
                                        ->color('gray')
                                        ->visible(fn() => !auth()->user()->hasApiKey())
                                        ->action(function ($set) {
                                            $user = auth()->user();
                                            $apiKey = $user->generateApiKey();
                                            $set('current_api_key', $user->getMaskedApiKey());

                                            // Notification
                                            Notification::make()
                                                ->title(__('settings.form.api_key_generated'))
                                                ->body(__('settings.form.api_key_generated_body'))
                                                ->success()
                                                ->send();
                                        }),

                                    // Regenerate API key
                                    \Filament\Forms\Components\Actions\Action::make('regenerate_api_key')
                                        ->label(__('settings.form.regenerate_api_key'))
                                        ->icon('heroicon-o-arrow-path')
                                        ->color('gray')
                                        ->visible(fn() => auth()->user()->hasApiKey())
                                        ->requiresConfirmation()
                                        ->modalHeading(__('settings.form.confirm_regenerate'))
                                        ->modalDescription(__('settings.form.confirm_regenerate_description'))
                                        ->modalSubmitActionLabel(__('settings.form.regenerate'))
                                        ->action(function ($set) {
                                            $user = auth()->user();
                                            $apiKey = $user->generateApiKey();
                                            $set('current_api_key', $user->getMaskedApiKey());

                                            Notification::make()
                                                ->title(__('settings.form.api_key_regenerated'))
                                                ->body(__('settings.form.api_key_regenerated_body'))
                                                ->warning()
                                                ->send();
                                        }),

                                    // Delete API key
                                    \Filament\Forms\Components\Actions\Action::make('delete_api_key')
                                        ->label(__('settings.form.delete_api_key'))
                                        ->icon('heroicon-o-trash')
                                        ->color('danger')
                                        ->outlined()
                                        ->visible(fn() => auth()->user()->hasApiKey())
                                        ->requiresConfirmation()
                                        ->modalHeading(__('settings.form.confirm_delete'))
                                        ->modalDescription(__('settings.form.confirm_delete_description'))
                                        ->modalSubmitActionLabel(__('settings.form.delete'))
                                        ->action(function ($set) {
                                            $user = auth()->user();
                                            $user->update([
                                                'api_key' => null,
                                                'api_key_generated_at' => null,
                                            ]);
                                            $set('current_api_key', '');

                                            Notification::make()
                                                ->title(__('settings.form.api_key_deleted'))
                                                ->body(__('settings.form.api_key_deleted_body'))
                                                ->success()
                                                ->send();
                                        }),
                                ])
                                    ->columns(3)
                                    ->columnSpan(8),
                            ]),

                        // API documentation section
                        Forms\Components\Section::make(__('settings.form.api_documentation'))
                            ->collapsible()
                            ->collapsed()
                            ->description(__('settings.form.api_documentation_description'))
                            ->schema([
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        Forms\Components\Livewire::make('api-documentation')
                                            ->label('')
                                            ->columnSpan(12),
                                    ]),
                            ]),
                    ]),

                // Timezone section
                Forms\Components\Section::make(__('settings.section.timezone'))
                    ->description(__('settings.section.timezone_description'))
                    ->collapsible()
                    ->schema([
                        // Timezone row with label and field
                        Forms\Components\Grid::make(12)
                            ->schema([
                                Forms\Components\Placeholder::make('timezone_label')
                                    ->label(__('settings.form.timezone'))
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
                        Forms\Components\Section::make(__('settings.form.current_time_in_timezone'))
                            ->collapsible()
                            ->collapsed()
                            ->description(__('settings.form.timezone_preview_description'))
                            ->schema([
                                Forms\Components\Grid::make(12)
                                    ->schema([
                                        Forms\Components\Placeholder::make('timezone_preview')
                                            ->label('')
                                            ->content(function ($get) {
                                                $timezone = $get('timezone');
                                                if (!$timezone) {
                                                    return __('settings.form.select_timezone_to_preview');
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
                                                    $html .= '<div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">';
                                                    $html .= '<h4 class="font-medium text-blue-900 dark:text-blue-100 mb-3">' . __('settings.form.current_time') . '</h4>';
                                                    $html .= '<div class="text-center">';
                                                    $html .= '<div class="text-2xl font-bold text-blue-700 dark:text-blue-300 mb-2">' . $now->format('g:i A') . '</div>';
                                                    $html .= '<div class="text-sm text-blue-600 dark:text-blue-400">' . $this->getLocalizedDate($now) . '</div>';
                                                    $html .= '<div class="text-xs text-blue-500 dark:text-blue-500 mt-1">' . $now->format('P') . '</div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';

                                                    // Timezone information - Right side
                                                    $html .= '<div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">';
                                                    $html .= '<h4 class="font-medium text-green-900 dark:text-green-100 mb-3">' . __('settings.form.timezone_information') . '</h4>';
                                                    $html .= '<div class="space-y-2 text-sm">';
                                                    $html .= '<div class="flex justify-between"><span class="font-medium text-green-700 dark:text-green-300">' . __('settings.form.identifier_name') . ':</span> <span class="text-green-600 dark:text-green-400">' . $timezone . '</span></div>';
                                                    $html .= '<div class="flex justify-between"><span class="font-medium text-green-700 dark:text-green-300">' . __('settings.form.country_code') . ':</span> <span class="text-green-600 dark:text-green-400">' . $this->getCountryFromTimezone($timezone) . '</span></div>';
                                                    $html .= '<div class="flex justify-between"><span class="font-medium text-green-700 dark:text-green-300">' . __('settings.form.utc_offset') . ':</span> <span class="text-green-600 dark:text-green-400">' . $now->format('P') . '</span></div>';
                                                    $html .= '<div class="flex justify-between"><span class="font-medium text-green-700 dark:text-green-300">' . __('settings.form.abbreviation') . ':</span> <span class="text-green-600 dark:text-green-400">' . $now->format('T') . '</span></div>';
                                                    $html .= '</div>';
                                                    $html .= '</div>';

                                                    $html .= '</div>';

                                                    // Sample data table - Full width below
                                                    $html .= '<div class="mt-6 bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg">';
                                                    $html .= '<h4 class="font-medium text-gray-900 dark:text-gray-100 mb-3">' . __('settings.form.sample_data_preview') . '</h4>';
                                                    $html .= '<div class="overflow-x-auto">';
                                                    $html .= '<table class="min-w-full text-sm">';
                                                    $html .= '<thead class="border-b border-gray-200 dark:border-gray-700">';
                                                    $html .= '<tr>';
                                                    $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">' . __('settings.form.id') . '</th>';
                                                    $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">' . __('settings.form.title') . '</th>';
                                                    $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">' . __('settings.form.created_at') . '</th>';
                                                    $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">' . __('settings.form.updated_at') . '</th>';
                                                    $html .= '<th class="text-left py-2 px-3 font-medium text-gray-700 dark:text-gray-300">' . __('settings.form.by') . '</th>';
                                                    $html .= '</tr>';
                                                    $html .= '</thead>';
                                                    $html .= '<tbody class="divide-y divide-gray-200 dark:divide-gray-700">';

                                                    // Sample rows with current time-based timestamps
                                                    $sampleData = [
                                                        ['id' => 1, 'title' => __('settings.form.sample_project_alpha'), 'created' => clone $createdAt, 'updated' => clone $updatedAt, 'by' => $userName],
                                                        ['id' => 2, 'title' => __('settings.form.sample_task_review'), 'created' => (clone $createdAt)->modify('-1 day'), 'updated' => (clone $updatedAt)->modify('-1 day'), 'by' => $userName],
                                                        ['id' => 3, 'title' => 'Meeting Notes', 'created' => (clone $createdAt)->modify('-3 days'), 'updated' => (clone $updatedAt)->modify('-2 days'), 'by' => $userName],
                                                    ];

                                                    foreach ($sampleData as $row) {
                                                        $html .= '<tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">';
                                                        $html .= '<td class="py-2 px-3 text-gray-900 dark:text-gray-100">' . $row['id'] . '</td>';
                                                        $html .= '<td class="py-2 px-3 text-gray-900 dark:text-gray-100">' . $row['title'] . '</td>';
                                                        $html .= '<td class="py-2 px-3 text-gray-600 dark:text-gray-400">' . $row['created']->format('j/n/y, h:i A') . '</td>';
                                                        $html .= '<td class="py-2 px-3 text-gray-600 dark:text-gray-400">' . $row['updated']->format('j/n/y, h:i A') . '</td>';
                                                        $html .= '<td class="py-2 px-3 text-gray-600 dark:text-gray-400">' . $row['by'] . '</td>';
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
                                            ->visible(fn($get) => !empty($get('timezone')))
                                            ->extraAttributes(['class' => 'text-sm text-gray-600 dark:text-gray-400'])
                                            ->columnSpan(12),
                                    ]),
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
        ]);

        Notification::make()
            ->title(__('settings.form.saved'))
            ->body(__('settings.form.saved_body'))
            ->success()
            ->send();
    }

    // Get country from timezone
    private function getCountryFromTimezone(string $timezone): string
    {
        $timezoneMap = [
            'Asia/Kuala_Lumpur' => 'Malaysia (MY)',
            'Asia/Singapore' => 'Singapore (SG)',
            'Asia/Jakarta' => 'Indonesia (ID)',
            'Asia/Makassar' => 'Indonesia (ID)',
            'Asia/Jayapura' => 'Indonesia (ID)',
            'Asia/Manila' => 'Philippines (PH)',
            'Asia/Tokyo' => 'Japan (JP)',
            'Asia/Seoul' => 'South Korea (KR)',
            'Asia/Shanghai' => 'China (CN)',
            'Asia/Beijing' => 'China (CN)',
            'Asia/Harbin' => 'China (CN)',
            'Asia/Urumqi' => 'China (CN)',
            'Australia/Perth' => 'Australia (AU)',
            'Australia/Darwin' => 'Australia (AU)',
            'Australia/Adelaide' => 'Australia (AU)',
            'Australia/Brisbane' => 'Australia (AU)',
            'Australia/Sydney' => 'Australia (AU)',
            'Australia/Melbourne' => 'Australia (AU)',
            'Australia/Hobart' => 'Australia (AU)',
            'Europe/London' => 'United Kingdom (UK)',
            'America/New_York' => 'United States (US)',
            'America/Chicago' => 'United States (US)',
            'America/Denver' => 'United States (US)',
            'America/Los_Angeles' => 'United States (US)',
            'America/Anchorage' => 'United States (US)',
            'Pacific/Honolulu' => 'United States (US)',
        ];

        return $timezoneMap[$timezone] ?? 'Unknown';
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
                'Saturday' => 'Sabtu'
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
                'December' => 'Disember'
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
}
