<?php

return [
 /*
 |--------------------------------------------------------------------------
 | Weather API Configuration
 |--------------------------------------------------------------------------
 |
 | Configuration for weather API integration using OpenWeatherMap
 |
 */

 'api' => [
  'key' => env('OPENWEATHERMAP_API_KEY'),
  'base_url' => 'https://api.openweathermap.org/data/2.5',
  'timeout' => 10,
 ],

 'cache' => [
  'ttl_hours' => env('WEATHER_CACHE_TTL_HOURS', 3),
  'key_prefix' => 'weather_',
 ],

 'default_location' => [
  'latitude' => env('WEATHER_DEFAULT_LATITUDE', 3.1390),
  'longitude' => env('WEATHER_DEFAULT_LONGITUDE', 101.6869),
  'city' => env('WEATHER_DEFAULT_CITY', 'Kuala Lumpur'),
  'country' => 'MY',
 ],

 'units' => [
  'temperature' => 'metric', // metric, imperial, kelvin
  'wind_speed' => 'metric', // metric (m/s), imperial (mph)
  'pressure' => 'metric', // metric (hPa), imperial (inHg)
 ],

 'refresh' => [
  'auto_refresh_hours' => 3,
  'only_when_logged_in' => true,
 ],

 'fallback' => [
  'enabled' => true,
  'show_error_message' => true,
  'error_message' => 'Failed to retrieve weather data',
 ],
];
