<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;
use Jenssegers\Agent\Agent;

class SessionHelper
{
    /**
     * Parse user agent string to extract browser and device information
     */
    public static function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return [
                'browser' => 'Unknown',
                'browser_version' => '',
                'device' => 'Unknown',
                'platform' => 'Unknown',
                'platform_version' => '',
                'is_desktop' => true,
                'is_mobile' => false,
                'is_tablet' => false,
            ];
        }

        $agent = new Agent;
        $agent->setUserAgent($userAgent);

        $browser = $agent->browser();
        $browserVersion = $agent->version($browser);
        $platform = $agent->platform();
        $platformVersion = $agent->version($platform);

        // Determine device type
        $deviceType = 'Desktop';
        $isMobile = false;
        $isTablet = false;
        $isDesktop = true;

        if ($agent->isTablet()) {
            $deviceType = $agent->device() ?: 'Tablet';
            $isTablet = true;
            $isDesktop = false;
        } elseif ($agent->isMobile()) {
            $deviceType = $agent->device() ?: 'Mobile';
            $isMobile = true;
            $isDesktop = false;
        } else {
            $deviceType = 'Desktop';
        }

        return [
            'browser' => $browser ?: 'Unknown',
            'browser_version' => $browserVersion ?: '',
            'device' => $deviceType,
            'platform' => $platform ?: 'Unknown',
            'platform_version' => $platformVersion ?: '',
            'is_desktop' => $isDesktop,
            'is_mobile' => $isMobile,
            'is_tablet' => $isTablet,
        ];
    }

    /**
     * Get country information from IP address using ipapi.co
     */
    public static function getCountryFromIp(?string $ipAddress): array
    {
        if (empty($ipAddress) || $ipAddress === '127.0.0.1' || $ipAddress === '::1') {
            return [
                'country' => 'Local',
                'country_code' => 'LC',
                'city' => 'Localhost',
            ];
        }

        try {
            $response = Http::timeout(5)->get("https://ipapi.co/{$ipAddress}/json/");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'country' => $data['country_name'] ?? 'Unknown',
                    'country_code' => $data['country_code'] ?? 'XX',
                    'city' => $data['city'] ?? 'Unknown',
                ];
            }
        } catch (\Exception $e) {
            \Log::debug('IP geolocation lookup failed', [
                'ip' => $ipAddress,
                'error' => $e->getMessage(),
            ]);
        }

        return [
            'country' => 'Unknown',
            'country_code' => 'XX',
            'city' => 'Unknown',
        ];
    }

    /**
     * Format session data for display
     */
    public static function formatSessionData(object $session, bool $isCurrentSession = false): array
    {
        $agentInfo = self::parseUserAgent($session->user_agent);
        $locationInfo = self::getCountryFromIp($session->ip_address);

        return [
            'id' => $session->id,
            'browser' => $agentInfo['browser'],
            'browser_version' => $agentInfo['browser_version'],
            'device' => $agentInfo['device'],
            'platform' => $agentInfo['platform'],
            'platform_version' => $agentInfo['platform_version'],
            'is_mobile' => $agentInfo['is_mobile'],
            'is_tablet' => $agentInfo['is_tablet'],
            'is_desktop' => $agentInfo['is_desktop'],
            'ip_address' => $session->ip_address,
            'country' => $locationInfo['country'],
            'country_code' => $locationInfo['country_code'],
            'city' => $locationInfo['city'],
            'last_activity' => \Carbon\Carbon::createFromTimestamp($session->last_activity),
            'is_current' => $isCurrentSession,
        ];
    }

    /**
     * Get device icon based on device type
     */
    public static function getDeviceIcon(array $sessionData): string
    {
        if ($sessionData['is_mobile']) {
            return 'heroicon-o-device-phone-mobile';
        }

        if ($sessionData['is_tablet']) {
            return 'heroicon-o-device-tablet';
        }

        return 'heroicon-o-computer-desktop';
    }
}
