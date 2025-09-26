<?php

namespace App\Http;

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * Define the application's global HTTP middleware stack.
     */
    protected function middleware(Application $app): array
    {
        return [
            HandlePrecognitiveRequests::class,
            \App\Http\Middleware\TrustProxies::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ];
    }

    /**
     * Define the application's middleware groups.
     */
    protected function middlewareGroups(Application $app): array
    {
        return [
            'web' => [
                \App\Http\Middleware\SetLocaleFromSession::class,
                StartSession::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                ShareErrorsFromSession::class,
                \App\Http\Middleware\SetLocale::class,
                ValidateCsrfToken::class,
                SubstituteBindings::class,
                \App\Http\Middleware\TrackUserActivity::class,
            ],

            // API routes middleware stack
            'api' => [
                SubstituteBindings::class, // Binds the request parameters to the route parameters
                ThrottleRequests::class.':api', // Throttle requests to the API
            ],
        ];
    }

    /**
     * Define the application's route middleware aliases.
     */
    protected function routeMiddleware(): array
    {
        return [
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'api.key.auth' => \App\Http\Middleware\ApiKeyAuth::class, // Middleware to authenticate API requests using API key
        ];
    }

    /**
     * Define the application's exception handling callbacks.
     */
    protected function bootstrappers(): array
    {
        return [
            Exceptions::class,
            \App\Http\Middleware\SetLocaleFromSession::class,
            StartSession::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            ShareErrorsFromSession::class,
            \App\Http\Middleware\SetLocale::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
        ];
    }
}
