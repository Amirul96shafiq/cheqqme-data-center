<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherService
{
 protected string $apiKey;
 protected string $baseUrl;
 protected int $timeout;
 protected int $cacheTtlHours;

 public function __construct()
 {
  // Use environment variable directly since config isn't loading properly
  $this->apiKey = env('OPENWEATHERMAP_API_KEY', '561e5fef9f7edc71ec464a21eb7e0b54');
  $this->baseUrl = 'https://api.openweathermap.org/data/2.5';
  $this->timeout = 10;
  $this->cacheTtlHours = 3;
 }

 /**
  * Get current weather for a user
  */
 public function getCurrentWeather(User $user): array
 {
  $location = $this->getUserLocation($user);
  $cacheKey = $this->getCacheKey('current', $location);

  return Cache::remember($cacheKey, $this->cacheTtlHours * 3600, function () use ($location) {
   return $this->fetchCurrentWeather($location);
  });
 }

 /**
  * Get 7-day forecast for a user
  */
 public function getForecast(User $user): array
 {
  $location = $this->getUserLocation($user);
  $cacheKey = $this->getCacheKey('forecast', $location);

  return Cache::remember($cacheKey, $this->cacheTtlHours * 3600, function () use ($location) {
   return $this->fetchForecast($location);
  });
 }

 /**
  * Get user's location (from database or default)
  */
 protected function getUserLocation(User $user): array
 {
  if ($user->latitude && $user->longitude) {
   return [
    'lat' => $user->latitude,
    'lon' => $user->longitude,
    'city' => $user->city ?? config('weather.default_location.city'),
    'country' => $user->country ?? config('weather.default_location.country'),
   ];
  }

  return [
   'lat' => config('weather.default_location.latitude'),
   'lon' => config('weather.default_location.longitude'),
   'city' => config('weather.default_location.city'),
   'country' => config('weather.default_location.country'),
  ];
 }

 /**
  * Fetch current weather from API
  */
 protected function fetchCurrentWeather(array $location): array
 {
  try {
   $response = Http::timeout($this->timeout)
    ->get($this->baseUrl . '/weather', [
     'lat' => $location['lat'],
     'lon' => $location['lon'],
     'appid' => $this->apiKey,
     'units' => config('weather.units.temperature'),
    ]);

   if ($response->successful()) {
    $data = $response->json();
    return $this->formatCurrentWeatherData($data, $location);
   }

   throw new \Exception('API request failed: ' . $response->status());
  } catch (\Exception $e) {
   Log::error('Weather API Error (Current): ' . $e->getMessage());
   return $this->getFallbackWeatherData($location);
  }
 }

 /**
  * Fetch forecast from API
  */
 protected function fetchForecast(array $location): array
 {
  try {
   $response = Http::timeout($this->timeout)
    ->get($this->baseUrl . '/forecast', [
     'lat' => $location['lat'],
     'lon' => $location['lon'],
     'appid' => $this->apiKey,
     'units' => config('weather.units.temperature'),
    ]);

   if ($response->successful()) {
    $data = $response->json();
    return $this->formatForecastData($data, $location);
   }

   throw new \Exception('API request failed: ' . $response->status());
  } catch (\Exception $e) {
   Log::error('Weather API Error (Forecast): ' . $e->getMessage());
   return $this->getFallbackForecastData($location);
  }
 }

 /**
  * Format current weather data
  */
 protected function formatCurrentWeatherData(array $data, array $location): array
 {
  $weather = $data['weather'][0] ?? [];
  $main = $data['main'] ?? [];
  $wind = $data['wind'] ?? [];
  $sys = $data['sys'] ?? [];

  return [
   'location' => [
    'city' => $location['city'],
    'country' => $location['country'],
   ],
   'current' => [
    'temperature' => round($main['temp'] ?? 0),
    'feels_like' => round($main['feels_like'] ?? 0),
    'condition' => $weather['main'] ?? 'Unknown',
    'description' => $weather['description'] ?? 'Unknown',
    'icon' => $weather['icon'] ?? '01d',
    'humidity' => $main['humidity'] ?? 0,
    'pressure' => $main['pressure'] ?? 0,
    'wind_speed' => round(($wind['speed'] ?? 0) * 3.6), // Convert m/s to km/h
    'wind_direction' => $wind['deg'] ?? 0,
    'uv_index' => $this->getUvIndex($data),
    'sunrise' => $sys['sunrise'] ? Carbon::createFromTimestamp($sys['sunrise'])->format('g:i A') : '-',
    'sunset' => $sys['sunset'] ? Carbon::createFromTimestamp($sys['sunset'])->format('g:i A') : '-',
   ],
   'timestamp' => now()->toISOString(),
   'cached' => false,
  ];
 }

 /**
  * Format forecast data
  */
 protected function formatForecastData(array $data, array $location): array
 {
  $forecast = [];
  $list = $data['list'] ?? [];

  // Group by date and get daily forecasts
  $dailyForecasts = [];
  foreach ($list as $item) {
   $date = Carbon::createFromTimestamp($item['dt'])->format('Y-m-d');
   if (!isset($dailyForecasts[$date])) {
    $dailyForecasts[$date] = [];
   }
   $dailyForecasts[$date][] = $item;
  }

  // Get 7 days starting from today
  $today = now()->startOfDay();
  for ($i = 0; $i < 7; $i++) {
   $date = $today->copy()->addDays($i)->format('Y-m-d');
   $dayData = $dailyForecasts[$date] ?? [];

   if (!empty($dayData)) {
    $temps = array_column($dayData, 'main');
    $weathers = array_column($dayData, 'weather');

    $minTemp = min(array_column($temps, 'temp_min'));
    $maxTemp = max(array_column($temps, 'temp_max'));
    $primaryWeather = $weathers[0][0] ?? [];

    $forecast[] = [
     'date' => $date,
     'day_name' => $today->copy()->addDays($i)->format('l'),
     'min_temp' => round($minTemp),
     'max_temp' => round($maxTemp),
     'condition' => $primaryWeather['main'] ?? 'Unknown',
     'description' => $primaryWeather['description'] ?? 'Unknown',
     'icon' => $primaryWeather['icon'] ?? '01d',
    ];
   }
  }

  return [
   'location' => [
    'city' => $location['city'],
    'country' => $location['country'],
   ],
   'forecast' => $forecast,
   'timestamp' => now()->toISOString(),
   'cached' => false,
  ];
 }

 /**
  * Get UV index (simplified calculation)
  */
 protected function getUvIndex(array $data): string
 {
  // This is a simplified UV index calculation
  // In a real implementation, you'd use a separate UV API or more complex calculation
  $hour = now()->hour;
  if ($hour >= 10 && $hour <= 16) {
   return 'High';
  } elseif ($hour >= 8 && $hour <= 18) {
   return 'Moderate';
  }
  return 'Low';
 }

 /**
  * Get fallback weather data when API fails
  */
 protected function getFallbackWeatherData(array $location): array
 {
  return [
   'location' => [
    'city' => $location['city'],
    'country' => $location['country'],
   ],
   'current' => [
    'temperature' => '-',
    'feels_like' => '-',
    'condition' => 'Unknown',
    'description' => 'Weather data unavailable',
    'icon' => '01d',
    'humidity' => '-',
    'pressure' => '-',
    'wind_speed' => '-',
    'wind_direction' => '-',
    'uv_index' => '-',
    'sunrise' => '-',
    'sunset' => '-',
   ],
   'timestamp' => now()->toISOString(),
   'cached' => false,
   'error' => true,
  ];
 }

 /**
  * Get fallback forecast data when API fails
  */
 protected function getFallbackForecastData(array $location): array
 {
  $forecast = [];
  $today = now()->startOfDay();

  for ($i = 0; $i < 7; $i++) {
   $forecast[] = [
    'date' => $today->copy()->addDays($i)->format('Y-m-d'),
    'day_name' => $today->copy()->addDays($i)->format('l'),
    'min_temp' => '-',
    'max_temp' => '-',
    'condition' => 'Unknown',
    'description' => 'Forecast unavailable',
    'icon' => '01d',
   ];
  }

  return [
   'location' => [
    'city' => $location['city'],
    'country' => $location['country'],
   ],
   'forecast' => $forecast,
   'timestamp' => now()->toISOString(),
   'cached' => false,
   'error' => true,
  ];
 }

 /**
  * Generate cache key
  */
 protected function getCacheKey(string $type, array $location): string
 {
  return config('weather.cache.key_prefix') . $type . '_' .
   round($location['lat'], 2) . '_' . round($location['lon'], 2);
 }

 /**
  * Clear weather cache for a user
  */
 public function clearCache(User $user): void
 {
  $location = $this->getUserLocation($user);
  $currentKey = $this->getCacheKey('current', $location);
  $forecastKey = $this->getCacheKey('forecast', $location);

  Cache::forget($currentKey);
  Cache::forget($forecastKey);
 }

 /**
  * Update user location
  */
 public function updateUserLocation(User $user, float $latitude, float $longitude, string $city = null, string $country = null): void
 {
  $user->update([
   'latitude' => $latitude,
   'longitude' => $longitude,
   'city' => $city,
   'country' => $country,
   'location_updated_at' => now(),
  ]);

  // Clear cache when location changes
  $this->clearCache($user);
 }
}
