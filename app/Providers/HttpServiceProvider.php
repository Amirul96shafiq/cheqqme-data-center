<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

class HttpServiceProvider extends ServiceProvider
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
        // Override the default HTTP client with Windows SSL fix
        Http::macro('withWindowsSSLFix', function () {
            return $this->withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ]);
        });

        // Apply Windows SSL fix to all HTTP requests by default
        Http::beforeSending(function (PendingRequest $request) {
            // Force SSL verification to false for Windows environments
            $request->withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ]);

            // Apply timeout configurations
            $request->timeout(config('http.defaults.timeout', 30))
                ->connectTimeout(config('http.defaults.connect_timeout', 10));

            // Apply retry configuration
            if (config('http.retry.times', 0) > 0) {
                $request->retry(
                    config('http.retry.times', 3),
                    config('http.retry.sleep', 100)
                );
            }
        });

        // Override the default HTTP client configuration globally
        config([
            'http.defaults.verify' => false,
            'http.options.curl.' . CURLOPT_SSL_VERIFYPEER => false,
            'http.options.curl.' . CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        // Force all HTTP requests to use SSL verification disabled
        Http::macro('default', function () {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ]);
        });

        // Override the base HTTP client to always include SSL fix
        Http::macro('base', function () {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ]);
        });

        // Override the default HTTP facade methods to always include SSL fix
        Http::macro('get', function ($url, $query = null) {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->get($url, $query);
        });

        Http::macro('post', function ($url, $data = []) {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->post($url, $data);
        });

        Http::macro('put', function ($url, $data = []) {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->put($url, $data);
        });

        Http::macro('patch', function ($url, $data = []) {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->patch($url, $data);
        });

        Http::macro('delete', function ($url, $data = []) {
            return Http::withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->delete($url, $data);
        });

        // Override the PendingRequest class methods to ensure SSL fix is always applied
        Http::macro('timeout', function ($seconds) {
            return $this->withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->timeout($seconds);
        });

        Http::macro('connectTimeout', function ($seconds) {
            return $this->withOptions([
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
                ],
            ])->connectTimeout($seconds);
        });
    }
}
