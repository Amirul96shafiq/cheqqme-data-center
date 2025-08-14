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

// Global Search Modal by CharrafiMed
use CharrafiMed\GlobalSearchModal\GlobalSearchModalPlugin;
use Filament\Support\Enums\MaxWidth;

// ActivityLog by Rômulo Ramos
use Rmsramos\Activitylog\ActivitylogPlugin;

// Kanban by Mo Khosh
use Mokhosh\FilamentKanban\FilamentKanbanPlugin;

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
                // Add native danger palette so Filament can style danger buttons (fi-color-danger)
                'danger' => Color::Red,
            ])
            //->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('20rem')
            // Load both the Filament admin theme and the main app Tailwind bundle so that
            // all generated utilities (including danger reds) are guaranteed to be present
            // even if purge / safelist changes or fallback overrides are removed later.
            ->viteTheme([
                'resources/css/filament/admin/theme.css',
                'resources/css/app.css',
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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Data Management'),

                NavigationGroup::make()
                    ->label('User Management'),

                NavigationGroup::make()
                    ->label('Tools'),
            ])
            ->renderHook(
                'panels::head.end',
                fn() => <<<'HTML'
                            <script>
                                // Global Search Keyboard Shortcut + Custom Placeholder
                                document.addEventListener('DOMContentLoaded', () => {
                                    const searchInput = document.querySelector('.fi-global-search input');

                                    // Set placeholder
                                    if (searchInput) {
                                        searchInput.placeholder = "Type / to search";
                                    }

                                    // Keyboard shortcut: /
                                    document.addEventListener('keydown', function (e) {
                                        if (e.key.toLowerCase() === '/') {
                                            e.preventDefault();
                                            const input = document.querySelector('.fi-global-search input');
                                            if (input) {
                                                input.focus();
                                            }
                                        }
                                    });
                                });
                            </script>
                        HTML,
            )
            ->renderHook(
                'panels::body.end',
                fn() => <<<'HTML'
                            <script>
                                // Enable horizontal drag-scroll on Flowforge board by dragging empty areas inside columns
                                (function () {
                                    let isBound = false;
                                    function bind() {
                                        if (isBound) return; isBound = true;
                                        document.addEventListener('mousedown', function (e) {
                                            const content = e.target.closest('.ff-column__content');
                                            if (!content) return;
                                            if (e.target.closest('.ff-card')) return; // don't interfere with card drag
                                            const scroller = content.closest('.ff-board__columns');
                                            if (!scroller) return;
                                            e.preventDefault(); // prevent text selection
                                            let isDown = true;
                                            const startX = e.pageX;
                                            const startScrollLeft = scroller.scrollLeft;
                                            scroller.classList.add('ff-drag-scrolling');
                                            const onMove = (ev) => {
                                                if (!isDown) return;
                                                scroller.scrollLeft = startScrollLeft - (ev.pageX - startX);
                                                ev.preventDefault();
                                            };
                                            const end = () => {
                                                isDown = false;
                                                scroller.classList.remove('ff-drag-scrolling');
                                                window.removeEventListener('mousemove', onMove);
                                                window.removeEventListener('mouseup', end);
                                                window.removeEventListener('mouseleave', end);
                                            };
                                            window.addEventListener('mousemove', onMove);
                                            window.addEventListener('mouseup', end);
                                            window.addEventListener('mouseleave', end);
                                        });
                                    }
                                    if (document.readyState === 'loading') {
                                        document.addEventListener('DOMContentLoaded', bind);
                                    } else {
                                        bind();
                                    }
                                    document.addEventListener('livewire:navigated', function(){ isBound = false; bind(); });
                                })();
                            </script>
                        HTML,
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
