<?php

return [

  /*
  |--------------------------------------------------------------------------
  | HTTP Client Configuration
  |--------------------------------------------------------------------------
  |
  | Here you may configure the HTTP client settings used by your application
  | for making HTTP requests to external services.
  |
  */

  'defaults' => [
    'timeout' => env('HTTP_TIMEOUT', 30),
    'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
    'verify' => env('HTTP_VERIFY_SSL', false), // Set to false for Windows environments
    'http_errors' => false,
  ],

  /*
  |--------------------------------------------------------------------------
  | HTTP Client Options
  |--------------------------------------------------------------------------
  |
  | Here you may configure additional options for the HTTP client.
  |
  */

  'options' => [
    'curl' => [
      CURLOPT_SSL_VERIFYPEER => env('HTTP_VERIFY_SSL', false),
      CURLOPT_SSL_VERIFYHOST => env('HTTP_VERIFY_SSL', false) ? 2 : 0,
      CURLOPT_CAINFO => env('HTTP_CA_BUNDLE'),
      CURLOPT_CAPATH => env('HTTP_CA_PATH'),
      // Additional Windows-specific SSL options
      CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
      CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    ],
  ],

  /*
  |--------------------------------------------------------------------------
  | Proxy Configuration
  |--------------------------------------------------------------------------
  |
  | Configure proxy settings if needed for your network environment.
  |
  */

  'proxy' => [
    'http' => env('HTTP_PROXY'),
    'https' => env('HTTPS_PROXY'),
    'no' => env('NO_PROXY'),
  ],

  /*
  |--------------------------------------------------------------------------
  | Retry Configuration
  |--------------------------------------------------------------------------
  |
  | Configure retry behavior for failed requests.
  |
  */

  'retry' => [
    'times' => env('HTTP_RETRY_TIMES', 3),
    'sleep' => env('HTTP_RETRY_SLEEP', 100),
    'status_codes' => [408, 429, 500, 502, 503, 504],
  ],

  /*
  |--------------------------------------------------------------------------
  | Windows SSL Fix
  |--------------------------------------------------------------------------
  |
  | Special configuration for Windows environments with SSL issues.
  |
  */

  'windows_ssl_fix' => [
    'enabled' => env('WINDOWS_SSL_FIX', true),
    'curl_options' => [
      CURLOPT_SSL_VERIFYPEER => false,
      CURLOPT_SSL_VERIFYHOST => 0,
      CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
    ],
  ],

];
