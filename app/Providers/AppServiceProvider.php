<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use BezhanSalleh\FilamentLanguageSwitch\Enums\Placement;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
            $switch
                ->locales(['en', 'ms'])
                ->visible(
                    insidePanels: true,
                    outsidePanels: fn() => request()->is('admin/login') || request()->is('admin/forgot-password') || request()->is('admin/reset-password')
                )
                ->outsidePanelPlacement(Placement::TopRight);
        });
    }
}
