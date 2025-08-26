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
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->homeUrl(fn() => route('filament.admin.pages.dashboard'))
            ->id('admin')
            ->path('admin')
            ->favicon(asset('images/favicon.png'))
            ->brandLogo(Request::is('admin/login')
                ? asset('logos/logo-light.png')
                : asset('logos/logo-light-vertical.png'))

            ->darkModeBrandLogo(Request::is('admin/login')
                ? asset('logos/logo-dark.png')
                : asset('logos/logo-dark-vertical.png'))

            ->brandLogoHeight(Request::is('admin/login') ? '8rem' : '2.75rem')
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
                \Filament\Pages\Dashboard::class,
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
                    ->icon('heroicon-o-face-smile')
                    ->label(fn() => 'Hello ' . collect(explode(' ', auth()->user()?->name ?? ''))->map(fn($part, $index) => $index === 0 ? $part : substr($part, 0, 1) . '.')->implode(' ') ?: 'Profile')
                    ->url(fn() => '#'),
                MenuItem::make()
                    ->label('Profile')
                    ->icon('heroicon-o-user')
                    ->url(fn() => filament()->getProfileUrl())
                    ->sort(-1),
                MenuItem::make()
                    ->label('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn() => '#')
                    ->sort(0),
                'logout' => MenuItem::make()
                    ->label('Logout')
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
