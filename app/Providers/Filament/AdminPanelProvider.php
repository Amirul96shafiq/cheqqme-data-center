<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pages\Login;
use App\Filament\Resources\PhoneNumberResource;
use Filament\Navigation\NavigationGroup;
use App\Filament\Pages\Profile;
use App\Filament\AvatarProviders\GetAvatarProvider;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use App\Filament\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Filament\Pages\Settings;
use Filament\Navigation\MenuItem;
use Filament\Navigation\UserMenuItem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Actions\Logout;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Request;


// Plugins
// Light Switch by Adam Weston
use Awcodes\LightSwitch\LightSwitchPlugin;
use Awcodes\LightSwitch\Enums\Alignment;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->homeUrl(fn() => route('filament.admin.pages.dashboard'))
            ->id('admin')
            ->path('admin')
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
            ->defaultAvatarProvider(GetAvatarProvider::class)
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
            ])
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('20rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Data Management'),

                NavigationGroup::make()
                    ->label('User Management'),
            ])
            ->plugins([
                LightSwitchPlugin::make()
                    ->position(Alignment::TopCenter)
                    ->enabledOn([
                        'auth.login',
                    ]),
            ]);
    }
}
