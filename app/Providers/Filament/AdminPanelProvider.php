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
use Illuminate\Support\HtmlString;


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
            ->brandName(new HtmlString(
                request()->is('admin/login') ?
                <<<'HTML'
                        <div class="text-center">
                            <!-- Logo Light (Login Page) -->
                            <img src="/logos/logo-light.png" alt="CheQQme Logo"
                                class="h-32 dark:hidden mx-auto">

                            <!-- Logo Dark (Login Page) -->
                            <img src="/logos/logo-dark.png" alt="CheQQme Logo"
                                class="h-32 hidden dark:block mx-auto">
                        </div>
                    HTML
                :
                <<<'HTML'
                        <div class="text-center">
                            <!-- Logo Light (Dashboard Pages) -->
                            <img src="/logos/logo-light-vertical.png" alt="CheQQme Logo"
                                class="h-11 dark:hidden mx-auto">

                            <!-- Logo Dark (Dashboard Pages) -->
                            <img src="/logos/logo-dark.png-vertical" alt="CheQQme Logo"
                                class="h-11 hidden dark:block mx-auto">
                        </div>
                    HTML
            ))
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->profile(Profile::class, isSimple: false)
            ->defaultAvatarProvider(GetAvatarProvider::class)
            ->colors([
                'primary' => [
                    50 => '#fbb43e',
                    100 => '#fbb43e',
                    200 => '#fbb43e',
                    300 => '#fbb43e',
                    400 => '#fbb43e',
                    500 => '#fbb43e',
                    600 => '#fbb43e',
                    700 => '#fbb43e',
                    800 => '#fbb43e',
                    900 => '#fbb43e',
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
