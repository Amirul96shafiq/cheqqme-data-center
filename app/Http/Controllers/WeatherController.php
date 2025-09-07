<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
 protected WeatherService $weatherService;

 public function __construct(WeatherService $weatherService)
 {
  $this->weatherService = $weatherService;
 }

 /**
  * Get current weather for authenticated user
  */
 public function getCurrentWeather(): JsonResponse
 {
  $user = Auth::user();
  if (!$user) {
   return response()->json(['error' => 'Unauthorized'], 401);
  }

  $weather = $this->weatherService->getCurrentWeather($user);

  return response()->json([
   'success' => true,
   'data' => $weather,
  ]);
 }

 /**
  * Get forecast for authenticated user
  */
 public function getForecast(): JsonResponse
 {
  $user = Auth::user();
  if (!$user) {
   return response()->json(['error' => 'Unauthorized'], 401);
  }

  $forecast = $this->weatherService->getForecast($user);

  return response()->json([
   'success' => true,
   'data' => $forecast,
  ]);
 }

 /**
  * Check if user has saved location settings
  */
 public function checkUserLocation(): JsonResponse
 {
  $user = Auth::user();

  if (!$user) {
   return response()->json([
    'success' => false,
    'message' => 'User not authenticated'
   ], 401);
  }

  $hasLocation = !empty($user->latitude) && !empty($user->longitude);

  return response()->json([
   'success' => true,
   'hasLocation' => $hasLocation,
   'latitude' => $user->latitude,
   'longitude' => $user->longitude,
   'city' => $user->city,
   'country' => $user->country,
   'locationUpdatedAt' => $user->location_updated_at?->toISOString()
  ]);
 }

 /**
  * Update user location
  */
 public function updateLocation(Request $request): JsonResponse
 {
  $user = Auth::user();
  if (!$user) {
   return response()->json(['error' => 'Unauthorized'], 401);
  }

  $validator = Validator::make($request->all(), [
   'latitude' => 'required|numeric|between:-90,90',
   'longitude' => 'required|numeric|between:-180,180',
   'city' => 'nullable|string|max:255',
   'country' => 'nullable|string|max:255',
  ]);

  if ($validator->fails()) {
   return response()->json([
    'success' => false,
    'errors' => $validator->errors(),
   ], 422);
  }

  $this->weatherService->updateUserLocation(
   $user,
   $request->latitude,
   $request->longitude,
   $request->city,
   $request->country
  );

  return response()->json([
   'success' => true,
   'message' => 'Location updated successfully',
  ]);
 }

 /**
  * Clear weather cache for authenticated user
  */
 public function clearCache(): JsonResponse
 {
  $user = Auth::user();
  if (!$user) {
   return response()->json(['error' => 'Unauthorized'], 401);
  }

  $this->weatherService->clearCache($user);

  return response()->json([
   'success' => true,
   'message' => 'Weather cache cleared successfully',
  ]);
 }

 /**
  * Get weather data (current + forecast) for authenticated user
  */
 public function getWeatherData(): JsonResponse
 {
  $user = Auth::user();
  if (!$user) {
   return response()->json(['error' => 'Unauthorized'], 401);
  }

  $currentWeather = $this->weatherService->getCurrentWeather($user);
  $forecast = $this->weatherService->getForecast($user);

  return response()->json([
   'success' => true,
   'data' => [
    'current' => $currentWeather,
    'forecast' => $forecast,
   ],
  ]);
 }
}
