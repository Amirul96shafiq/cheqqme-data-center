<?php

namespace App\Providers;

use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class SocialiteServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Configure Socialite to handle SSL issues in local development
        if (app()->environment('local')) {
            Socialite::extend('google', function ($app) {
                $config = $app['config']['services.google'];

                return Socialite::buildProvider(
                    \Laravel\Socialite\Two\GoogleProvider::class,
                    $config
                )->setHttpClient(new Client([
                                'verify' => false,
                                'timeout' => 30,
                            ]));
            });
        }
    }
}
