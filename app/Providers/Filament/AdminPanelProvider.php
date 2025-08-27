<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Profile;
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
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
// -----------------------------
// Plugins
// -----------------------------
// Light Switch by Adam Weston
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
// Global Search Modal by CharrafiMed
use Illuminate\Support\Facades\Request;
use Illuminate\View\Middleware\ShareErrorsFromSession;
// ActivityLog by Rômulo Ramos
use Rmsramos\Activitylog\ActivitylogPlugin;

class AdminPanelProvider extends PanelProvider
{
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
            ->homeUrl(fn() => route('filament.admin.pages.dashboard'))
            ->id('admin')
            ->path('admin')
            ->favicon(asset('images/favicon.png'))
            ->brandLogo($currentLogo)
            ->darkModeBrandLogo($currentDarkLogo)
            ->brandLogoHeight($currentLogoHeight)
            ->font('Roboto')
            ->login(\App\Filament\Pages\Auth\Login::class)
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
            ->sidebarWidth('17rem')
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/css/app.css',
                'resources/js/chatbot.js',
                'resources/js/app-custom.js',
            ])
            ->pages([
                \App\Filament\Pages\Dashboard::class,
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
            
                        return 'heroicon-o-moon-stars'; // Goodnight (12AM-6AM)
                    })
                    ->label(function () {
                        $userName = auth()->user()?->name ?? '';
                        if (!$userName) {
                            return 'Profile';
                        }

                        // Format name: First name + initials for remaining parts
                        $formattedName = collect(explode(' ', $userName))
                            ->map(fn($part, $index) => $index === 0 ? $part : substr($part, 0, 1) . '.')
                            ->implode(' ');

                        $hour = now()->hour;
                        $greeting = match (true) {
                            $hour >= 7 && $hour <= 11 => 'Morning',
                            $hour >= 12 && $hour <= 19 => 'Afternoon',
                            $hour >= 20 && $hour <= 23 => 'Evening',
                            default => 'Goodnight' // 12AM-6AM
                        };

                        return "{$greeting}, {$formattedName}";
                    })
                    ->url(fn() => 'https://motivation.app/')
                    ->openUrlInNewTab(),
                MenuItem::make()
                    ->label(fn() => __('dashboard.user-menu.profile-label'))
                    ->icon('heroicon-o-user')
                    ->url(fn() => filament()->getProfileUrl())
                    ->sort(-1),
                MenuItem::make()
                    ->label(fn() => __('dashboard.user-menu.settings-label'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn() => '#')
                    ->sort(0),
                'logout' => MenuItem::make()
                    ->label(fn() => __('dashboard.user-menu.logout-label'))
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('danger')
                    ->url(fn() => filament()->getLogoutUrl())
                    ->sort(1),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Data Management'),

                NavigationGroup::make()
                    ->label('User Management'),

                NavigationGroup::make()
                    ->label('Tools'),
            ])
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
                    ->navigationGroup(fn() => __('activitylog.navigation_group'))
                    ->navigationSort(11),
            ]);
    }
}
