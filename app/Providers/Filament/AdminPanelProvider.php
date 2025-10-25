<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Profile;
use App\Models\TrelloBoard;
use Awcodes\LightSwitch\Enums\Alignment;
use Awcodes\LightSwitch\LightSwitchPlugin;
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
// -----------------------------
// Plugins
// -----------------------------
// Light Switch by Adam Weston
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
// Global Search Modal by CharrafiMed
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Request;
use Illuminate\View\Middleware\ShareErrorsFromSession;
// ActivityLog by Rômulo Ramos
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
    /**
     * Get enabled Trello boards for navigation
     */
    protected function getEnabledTrelloBoards(): array
    {
        try {
            return TrelloBoard::where('show_on_boards', true)
                ->where('url', '!=', '')
                ->orderBy('name')
                ->get(['id', 'name', 'url'])
                ->map(function ($board) {
                    return NavigationItem::make("trello-board-{$board->id}")
                        ->icon('heroicon-o-hashtag')
                        ->label(fn () => (strlen($board->name) > 10 ? substr($board->name, 0, 10).'...' : $board->name).' '.__('navigation.trello_board_suffix'))
                        ->url($board->url)
                        ->openUrlInNewTab()
                        ->group(fn () => __('navigation.groups.boards'))
                        ->sort(2);
                })
                ->toArray();
        } catch (\Exception $e) {
            // Return empty array if trello_boards table doesn't exist or other errors
            return [];
        }
    }

    /**
     * Determine if the current request is for a login page
     */
    protected function isLoginPage(): bool
    {
        if (auth()->check()) {
            return false;
        }

        $currentUrl = request()->fullUrl();

        // Direct URL and path matching
        if (request()->is('admin/login') || ($currentUrl && str_contains($currentUrl, '/admin/login'))) {
            return true;
        }

        // Livewire login component detection
        if (request()->hasHeader('X-Livewire')) {
            $livewireData = json_decode(request()->header('X-Livewire'), true);
            if ($livewireData && isset($livewireData['name'])) {
                $componentName = $livewireData['name'];
                if (
                    str_contains($componentName, 'Auth\\Login') ||
                    str_contains($componentName, 'Login') ||
                    $componentName === 'App\\Filament\\Pages\\Auth\\Login'
                ) {
                    return true;
                }
            }
        }

        // Login form submissions
        if (request()->isMethod('post') && request()->has(['email', 'password'])) {
            return true;
        }

        // Livewire form submission detection
        $requestData = request()->all();
        if (isset($requestData['components']) && is_array($requestData['components'])) {
            foreach ($requestData['components'] as $component) {
                if (
                    (isset($component['snapshot']) && str_contains($component['snapshot'], 'Auth\\Login')) ||
                    (isset($component['name']) && str_contains($component['name'], 'Login'))
                ) {
                    return true;
                }
            }
        }

        // Session validation errors (crucial for failed logins)
        if (session()->has('errors')) {
            $errors = session()->get('errors');
            if ($errors) {
                // Check specific field errors
                if ($errors->has('data.email') || $errors->has('email') || $errors->has('password')) {
                    return true;
                }
                // Check error messages for login-related terms
                foreach ($errors->all() as $error) {
                    if (
                        str_contains($error, 'email') || str_contains($error, 'password') ||
                        str_contains($error, 'credentials') || str_contains($error, 'login')
                    ) {
                        return true;
                    }
                }
            }
        }

        // Livewire update requests
        if (request()->is('livewire/update') && request()->isMethod('post')) {
            $requestData = request()->all();
            if (isset($requestData['components']) && is_array($requestData['components'])) {
                foreach ($requestData['components'] as $component) {
                    if (
                        isset($component['snapshot']) &&
                        (str_contains($component['snapshot'], 'login') || str_contains($component['snapshot'], 'Auth'))
                    ) {
                        return true;
                    }
                }
            }
        }

        // Fallback route-based check
        if (request()->routeIs('filament.*.login')) {
            return true;
        }

        return false;
    }

    public function panel(Panel $panel): Panel
    {
        // More reliable login page detection that works throughout the request lifecycle
        $isLoginPage = $this->isLoginPage();

        // Logo configurations
        $loginLogo = asset('logos/logo-light.png');
        $loginDarkLogo = asset('logos/logo-dark.png');
        $headerLogo = asset('logos/logo-light-vertical.png');
        $headerDarkLogo = asset('logos/logo-dark-vertical.png');

        // Determine which logo to use based on current page
        $currentLogo = $isLoginPage ? $loginLogo : $headerLogo;
        $currentDarkLogo = $isLoginPage ? $loginDarkLogo : $headerDarkLogo;
        $currentLogoHeight = $isLoginPage ? '8rem' : '2.75rem';

        return $panel
            ->default()
            ->homeUrl(fn () => route('filament.admin.pages.dashboard'))
            ->id('admin')
            ->path('admin')
            // ->spa() // SPA mode disabled - rendering issues with complex components
            // ->spaUrlExceptions([
            //     // Exclude OAuth callback routes from SPA navigation
            //     '*/auth/google/*',
            //     '*/auth/spotify/*',
            //     '*/auth/zoom/*',
            //     // Exclude login/logout routes
            //     '*/admin/login',
            //     '*/logout',
            // ])
            ->favicon(asset('images/favicon.png'))
            ->brandLogo($currentLogo)
            ->darkModeBrandLogo($currentDarkLogo)
            ->brandLogoHeight($currentLogoHeight)
            ->font('Roboto')
            ->login(null)
            ->profile(Profile::class, isSimple: false)
            ->databaseNotifications(true, false)
            ->databaseNotificationsPolling('5s')
            ->colors([
                'primary' => [
                    '50' => '#fff8eb',
                    '100' => '#fde7c3',
                    '200' => '#fcd39b',
                    '300' => '#fbbe72',
                    '400' => '#fab54f',
                    '500' => '#fbb43e',
                    '600' => '#e6a135',
                    '700' => '#c5862c',
                    '800' => '#a56b23',
                    '900' => '#844f1a',
                ],
                // Add native danger palette so Filament can style danger buttons (fi-color-danger)
                'danger' => Color::Red,
            ])
            ->sidebarWidth('15rem')
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(MaxWidth::Full)
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/css/app.css',
                'resources/js/chatbot.js',
                'resources/js/app-custom.js',
                'resources/js/drag-drop-upload.js',
                'resources/js/service-worker-register.js',
                'resources/js/spa-loading-indicator.js',
            ])
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ChatbotHistory::class,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->icon(function () {
                        $hour = now()->hour;
                        if ($hour >= 7 && $hour <= 11) {
                            return 'heroicon-o-sun';
                        } // Morning
                        if ($hour >= 12 && $hour <= 19) {
                            return 'heroicon-o-sun';
                        } // Afternoon
                        if ($hour >= 20 && $hour <= 23) {
                            return 'heroicon-o-moon';
                        } // Evening

                        return 'heroicon-o-moon'; // Goodnight (12AM-6AM)
                    })
                    ->label(function () {
                        $userName = auth()->user()?->name ?? '';
                        if (! $userName) {
                            return __('greetings.profile');
                        }

                        // Format name: First name + initials for remaining parts
                        // $formattedName = \App\Helpers\ClientFormatter::formatClientName($userName);

                        $hour = now()->hour;
                        $greeting = match (true) {
                            $hour >= 7 && $hour <= 11 => __('greetings.morning'),
                            $hour >= 12 && $hour <= 19 => __('greetings.afternoon'),
                            $hour >= 20 && $hour <= 23 => __('greetings.evening'),
                            default => __('greetings.goodnight') // 12AM-6AM
                        };

                        return "{$greeting}";
                        // return "{$greeting}, {$formattedName}";
                    })
                    ->color('primary')
                    ->url(fn () => 'javascript:void(0)'),
                MenuItem::make()
                    ->label(fn () => __('dashboard.user-menu.profile-label'))
                    ->icon('heroicon-o-user')
                    ->url(fn () => filament()->getProfileUrl())
                    ->sort(-2),
                MenuItem::make()
                    ->label(fn () => __('dashboard.user-menu.settings-label'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn () => route('filament.admin.pages.settings'))
                    ->sort(-1),
                MenuItem::make()
                    ->label(fn () => __('chatbot.history.navigation_label'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn () => '/admin/chatbot-history')
                    ->sort(0),
                MenuItem::make()
                    ->label(fn () => __('dashboard.user-menu.whats-news-label'))
                    ->icon('heroicon-o-code-bracket')
                    ->url('javascript:void(0)')
                    ->sort(1),
                MenuItem::make()
                    ->label(fn () => __('dashboard.user-menu.calendar-label'))
                    ->icon('heroicon-o-calendar-days')
                    ->url('javascript:void(0)')
                    ->sort(1.5),
                'logout' => MenuItem::make()
                    ->label(fn () => __('dashboard.user-menu.logout-label'))
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->url(fn () => filament()->getLogoutUrl())
                    ->sort(2),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('navigation.groups.boards')),

                NavigationGroup::make()
                    ->label(__('navigation.groups.data_management')),

                NavigationGroup::make()
                    ->label(__('navigation.groups.user_management')),

                NavigationGroup::make()
                    ->label(__('navigation.groups.tools')),
            ])
            ->navigationItems($this->getEnabledTrelloBoards())
            ->renderHook(
                'panels::body.end',
                function () {
                    // Exclude chatbot from login, forgot password, and reset password pages
                    $currentPath = request()->path();
                    $authPages = ['/login', '/forgot-password', '/reset-password'];

                    foreach ($authPages as $authPage) {
                        if (str_contains($currentPath, $authPage)) {
                            return null;
                        }
                    }

                    return view('partials.chatbot', [
                        'userName' => auth()->user()?->name ?? 'You',
                    ]);
                },
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                function () {
                    // Performance optimizations: Resource hints for external domains
                    $resourceHints = [
                        // DNS prefetch for external domains
                        '<link rel="dns-prefetch" href="https://sdk.scdn.co">',
                        '<link rel="dns-prefetch" href="https://fonts.googleapis.com">',
                        '<link rel="dns-prefetch" href="https://cdn.jsdelivr.net">',

                        // Preconnect for critical external resources
                        '<link rel="preconnect" href="https://sdk.scdn.co" crossorigin>',

                        // Preload critical Spotify SDK
                        '<link rel="preload" href="https://sdk.scdn.co/spotify-player.js" as="script" crossorigin="anonymous">',
                        '<script src="https://sdk.scdn.co/spotify-player.js" async crossorigin="anonymous"></script>',

                        // Prefetch fonts for faster subsequent page loads
                        '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>',
                        '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
                    ];

                    return implode("\n", $resourceHints);
                },
            )
            ->renderHook(
                'panels::scripts.after',
                function () {
                    // Expose Reverb configuration to frontend
                    $reverbConfig = [
                        'key' => config('broadcasting.connections.reverb.key'),
                        'host' => config('broadcasting.connections.reverb.options.host'),
                        'port' => config('broadcasting.connections.reverb.options.port'),
                        'scheme' => config('broadcasting.connections.reverb.options.scheme'),
                    ];

                    return view('filament.scripts.greeting-modal').
                        view('components.drag-drop-lang').
                        '<script>window.reverbConfig = '.json_encode($reverbConfig).';</script>';
                },
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_FOOTER,
                function () {
                    return view('filament.sidebar-footer');
                },
            )
            ->plugins([
                LightSwitchPlugin::make()
                    ->position(Alignment::TopCenter)
                    ->enabledOn([
                        'auth.login',
                    ]),

                GlobalSearchModalPlugin::make()
                    ->maxWidth(MaxWidth::ThreeExtraLarge)
                    ->expandedUrlTarget(enabled: false),

                ActivitylogPlugin::make()
                    ->navigationGroup(fn () => __('activitylog.navigation_group'))
                    ->navigationSort(11),
            ]);
    }
}
