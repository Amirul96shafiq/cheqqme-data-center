<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleMapsService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
    }

    /**
     * Resolve a Google Maps share URL and extract location data
     */
    public function resolveShareUrl(string $shareUrl): ?array
    {
        try {
            // Step 1: Resolve the shortened URL to get the full Maps URL
            $resolvedUrl = $this->resolveShortenedUrl($shareUrl);

            if (! $resolvedUrl) {
                return null;
            }

            // Step 2: Extract place ID or coordinates from the resolved URL
            $placeData = $this->extractPlaceData($resolvedUrl);

            if (! $placeData) {
                return null;
            }

            // Step 3: Get place details using Google Places API or reverse geocoding
            if (isset($placeData['place_id'])) {
                $details = $this->getPlaceDetails($placeData['place_id']);
                if ($details) {
                    return array_merge($placeData, $details, [
                        'share_url' => $shareUrl,
                        'resolved_url' => $resolvedUrl,
                    ]);
                }
            }

            // If Places API fails but we have coordinates, try reverse geocoding
            if (isset($placeData['coordinates'])) {
                $reverseGeocodeData = $this->reverseGeocode($placeData['coordinates']);
                if ($reverseGeocodeData) {
                    return array_merge($placeData, $reverseGeocodeData, [
                        'share_url' => $shareUrl,
                        'resolved_url' => $resolvedUrl,
                    ]);
                }

                // If reverse geocoding also fails, return basic data with coordinates
                return array_merge($placeData, [
                    'share_url' => $shareUrl,
                    'resolved_url' => $resolvedUrl,
                    'title' => null,
                    'address' => null,
                ]);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Failed to resolve Google Maps share URL: '.$e->getMessage(), [
                'url' => $shareUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resolve shortened goo.gl/maps.app.goo.gl URLs
     */
    protected function resolveShortenedUrl(string $url): ?string
    {
        try {
            // Use Guzzle to follow redirects and get the final URL
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($url);

            return $response->effectiveUri()->__toString();
        } catch (\Exception $e) {
            Log::warning('Failed to resolve shortened URL: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Extract place ID or coordinates from resolved Google Maps URL
     */
    protected function extractPlaceData(string $url): ?array
    {
        // Parse the URL to extract parameters
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Check for place ID in various formats
        if (isset($queryParams['place_id'])) {
            return ['place_id' => $queryParams['place_id']];
        }

        // Check for place ID in Google Maps data parameter format: !1s<place_id>
        // Format: data=!3m1!4b1!4m6!3m5!1s<place_id>!8m2!3d<lat>!4d<lng>
        if (preg_match('/!1s([0-9a-fx:]+)/', $url, $matches)) {
            $placeId = $matches[1];

            // Also extract coordinates if available in the same URL
            $coordinates = null;
            if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $coordMatches)) {
                $coordinates = [
                    'lat' => (float) $coordMatches[1],
                    'lng' => (float) $coordMatches[2],
                ];
            }

            return [
                'place_id' => $placeId,
                'coordinates' => $coordinates,
            ];
        }

        // Check for coordinates in various formats
        if (isset($queryParams['ll'])) {
            // Format: ll=lat,lng
            [$lat, $lng] = explode(',', $queryParams['ll']);

            return ['coordinates' => ['lat' => (float) $lat, 'lng' => (float) $lng]];
        }

        if (isset($queryParams['q'])) {
            // Sometimes coordinates are in 'q' parameter
            $coords = explode(',', $queryParams['q']);
            if (count($coords) === 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
                return ['coordinates' => ['lat' => (float) $coords[0], 'lng' => (float) $coords[1]]];
            }
        }

        // Check URL path for coordinates (format: /place/PlaceName/@lat,lng,zoom)
        if (preg_match('/\/place\/[^\/]+\/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            return ['coordinates' => ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]]];
        }

        return null;
    }

    /**
     * Reverse geocode coordinates to get address and title
     */
    protected function reverseGeocode(array $coordinates): ?array
    {
        if (! $this->apiKey) {
            Log::error('Google Maps API key not configured');

            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'latlng' => $coordinates['lat'].','.$coordinates['lng'],
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($data['status'] === 'OK' && isset($data['results'][0])) {
                $result = $data['results'][0];
                $address = $result['formatted_address'];

                // Extract title from address components (similar to the JS logic)
                $title = $this->extractTitleFromAddressComponents($result['address_components']);

                return [
                    'title' => $title,
                    'address' => $address,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Geocoding API request failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Extract title from address components
     */
    protected function extractTitleFromAddressComponents(array $components): ?string
    {
        $priorityTypes = [
            'establishment',
            'point_of_interest',
            'park',
            'neighborhood',
            'sublocality',
            'sublocality_level_1',
            'locality',
            'route',
            'administrative_area_level_2',
            'administrative_area_level_1',
            'country',
        ];

        foreach ($priorityTypes as $type) {
            foreach ($components as $component) {
                if (in_array($type, $component['types'])) {
                    return $component['long_name'];
                }
            }
        }

        // Fallback to first meaningful part of the address
        return null;
    }

    /**
     * Get place details using Google Places API
     */
    protected function getPlaceDetails(string $placeId): ?array
    {
        if (! $this->apiKey) {
            Log::error('Google Maps API key not configured');

            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'name,formatted_address,geometry',
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($data['status'] === 'OK' && isset($data['result'])) {
                $result = $data['result'];

                return [
                    'title' => $result['name'] ?? null,
                    'address' => $result['formatted_address'] ?? null,
                    'coordinates' => [
                        'lat' => $result['geometry']['location']['lat'] ?? null,
                        'lng' => $result['geometry']['location']['lng'] ?? null,
                    ],
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Places API request failed: '.$e->getMessage());

            return null;
        }
    }
}
