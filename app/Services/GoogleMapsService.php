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

            // Step 2: Extract place data including title from URL path
            $placeData = $this->extractPlaceData($resolvedUrl);

            if (! $placeData) {
                return null;
            }

            // Step 3: Try to get better data from Google Places API
            // First try with valid ChIJ place ID, then try text search if we have URL title
            if (isset($placeData['place_id']) && str_starts_with($placeData['place_id'], 'ChIJ')) {
                $details = $this->getPlaceDetails($placeData['place_id']);
                if ($details) {
                    return array_merge($placeData, $details, [
                        'share_url' => $shareUrl,
                        'resolved_url' => $resolvedUrl,
                    ]);
                }
            }

            // If we have a URL title, try to find the place by text search
            if (isset($placeData['url_title']) && isset($placeData['coordinates'])) {
                $searchDetails = $this->searchPlaceByText($placeData['url_title'], $placeData['coordinates']);
                if ($searchDetails) {
                    return array_merge($placeData, $searchDetails, [
                        'share_url' => $shareUrl,
                        'resolved_url' => $resolvedUrl,
                    ]);
                }
            }

            // Step 4: If Places API fails or we don't have a valid place ID,
            // try reverse geocoding with coordinates
            if (isset($placeData['coordinates'])) {
                $reverseGeocodeData = $this->reverseGeocode($placeData['coordinates']);
                if ($reverseGeocodeData) {
                    // Use URL-extracted title if available, otherwise use geocoding result
                    $title = $placeData['url_title'] ?? $reverseGeocodeData['title'];
                    $address = $reverseGeocodeData['address'];

                    return array_merge($placeData, [
                        'title' => $title,
                        'address' => $address,
                        'share_url' => $shareUrl,
                        'resolved_url' => $resolvedUrl,
                    ]);
                }

                // If reverse geocoding also fails, return basic data with coordinates
                return array_merge($placeData, [
                    'share_url' => $shareUrl,
                    'resolved_url' => $resolvedUrl,
                    'title' => $placeData['url_title'] ?? null,
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

        $result = [];

        // Extract place name from URL path if available
        // Format: /place/PlaceName/... or /place/Place+Name+With+Spaces/...
        if (preg_match('/\/place\/([^\/@]+)/', $url, $matches)) {
            $encodedPlaceName = $matches[1];
            // Decode URL encoding and replace + with spaces
            $placeName = str_replace('+', ' ', urldecode($encodedPlaceName));
            $result['url_title'] = $placeName;
        }

        // Check for place ID in various formats
        if (isset($queryParams['place_id'])) {
            $result['place_id'] = $queryParams['place_id'];
        }

        // Check for place ID in Google Maps data parameter format: !1s<place_id>
        // Format: data=!3m1!4b1!4m6!3m5!1s<place_id>!8m2!3d<lat>!4d<lng>
        if (preg_match('/!1s([0-9a-fx:]+)/', $url, $matches)) {
            $result['place_id'] = $matches[1];
        }

        // Extract coordinates from various formats
        $coordinates = null;

        // Check for coordinates in Google Maps data format
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $coordMatches)) {
            $coordinates = [
                'lat' => (float) $coordMatches[1],
                'lng' => (float) $coordMatches[2],
            ];
        }

        // Check for coordinates in URL path (format: /place/PlaceName/@lat,lng,zoom)
        if (! $coordinates && preg_match('/\/place\/[^\/]+\/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $matches)) {
            $coordinates = ['lat' => (float) $matches[1], 'lng' => (float) $matches[2]];
        }

        // Check for coordinates in query parameters
        if (! $coordinates && isset($queryParams['ll'])) {
            // Format: ll=lat,lng
            [$lat, $lng] = explode(',', $queryParams['ll']);
            $coordinates = ['lat' => (float) $lat, 'lng' => (float) $lng];
        }

        if (! $coordinates && isset($queryParams['q'])) {
            // Sometimes coordinates are in 'q' parameter
            $coords = explode(',', $queryParams['q']);
            if (count($coords) === 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
                $coordinates = ['lat' => (float) $coords[0], 'lng' => (float) $coords[1]];
            }
        }

        if ($coordinates) {
            $result['coordinates'] = $coordinates;
        }

        // Return result only if we have at least coordinates or a place ID
        return (! empty($result['coordinates']) || ! empty($result['place_id'])) ? $result : null;
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
     * Search for a place by text query and coordinates using Google Places Text Search API
     */
    protected function searchPlaceByText(string $query, array $coordinates): ?array
    {
        if (! $this->apiKey) {
            Log::error('Google Maps API key not configured');

            return null;
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/textsearch/json', [
                'query' => $query,
                'location' => $coordinates['lat'].','.$coordinates['lng'],
                'radius' => 100, // Search within 100 meters
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            if ($data['status'] === 'OK' && isset($data['results'][0])) {
                $result = $data['results'][0];

                return [
                    'title' => $result['name'] ?? null,
                    'address' => $result['formatted_address'] ?? null,
                    'coordinates' => [
                        'lat' => $result['geometry']['location']['lat'] ?? null,
                        'lng' => $result['geometry']['location']['lng'] ?? null,
                    ],
                    'place_id' => $result['place_id'] ?? null,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Places Text Search API request failed: '.$e->getMessage());

            return null;
        }
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
