<?php

namespace App\Helpers;

class TimezoneHelper
{
    /**
     * Get all timezone options organized by region
     */
    public static function getTimezoneOptions(): array
    {
        return [
            'Malaysia' => [
                'Asia/Kuala_Lumpur' => 'Kuala Lumpur (UTC+08:00)',
            ],
            'Singapore' => [
                'Asia/Singapore' => 'Singapore (UTC+08:00)',
            ],
            'Indonesia' => [
                'Asia/Jakarta' => 'Jakarta (UTC+07:00)',
                'Asia/Makassar' => 'Makassar (UTC+08:00)',
                'Asia/Jayapura' => 'Jayapura (UTC+09:00)',
            ],
            'Philippines' => [
                'Asia/Manila' => 'Manila (UTC+08:00)',
            ],
            'Japan' => [
                'Asia/Tokyo' => 'Tokyo (UTC+09:00)',
            ],
            'South Korea' => [
                'Asia/Seoul' => 'Seoul (UTC+09:00)',
            ],
            'China' => [
                'Asia/Shanghai' => 'Shanghai (UTC+08:00)',
                'Asia/Beijing' => 'Beijing (UTC+08:00)',
                'Asia/Harbin' => 'Harbin (UTC+08:00)',
                'Asia/Urumqi' => 'Urumqi (UTC+08:00)',
            ],
            'Australia' => [
                'Australia/Perth' => 'Perth (UTC+08:00)',
                'Australia/Darwin' => 'Darwin (UTC+09:30)',
                'Australia/Adelaide' => 'Adelaide (UTC+09:30)',
                'Australia/Brisbane' => 'Brisbane (UTC+10:00)',
                'Australia/Sydney' => 'Sydney (UTC+10:00)',
                'Australia/Melbourne' => 'Melbourne (UTC+10:00)',
                'Australia/Hobart' => 'Hobart (UTC+10:00)',
            ],
            'United Kingdom' => [
                'Europe/London' => 'London (UTC+00:00)',
            ],
            'United States' => [
                'America/New_York' => 'New York (UTC-05:00)',
                'America/Chicago' => 'Chicago (UTC-06:00)',
                'America/Denver' => 'Denver (UTC-07:00)',
                'America/Los_Angeles' => 'Los Angeles (UTC-08:00)',
                'America/Anchorage' => 'Anchorage (UTC-09:00)',
                'Pacific/Honolulu' => 'Honolulu (UTC-10:00)',
            ],
        ];
    }

    /**
     * Get flattened timezone options for select filters
     */
    public static function getFlattenedTimezoneOptions(): array
    {
        $options = [];

        foreach (self::getTimezoneOptions() as $region => $timezones) {
            foreach ($timezones as $timezone => $label) {
                $options[$timezone] = $label;
            }
        }

        return $options;
    }

    /**
     * Get timezone options grouped by region for select fields
     */
    public static function getGroupedTimezoneOptions(): array
    {
        return self::getTimezoneOptions();
    }

    /**
     * Get country information from timezone
     */
    public static function getCountryFromTimezone(string $timezone): string
    {
        $timezoneMap = [
            'Asia/Kuala_Lumpur' => 'Malaysia (MY)',
            'Asia/Singapore' => 'Singapore (SG)',
            'Asia/Jakarta' => 'Indonesia (ID)',
            'Asia/Makassar' => 'Indonesia (ID)',
            'Asia/Jayapura' => 'Indonesia (ID)',
            'Asia/Manila' => 'Philippines (PH)',
            'Asia/Tokyo' => 'Japan (JP)',
            'Asia/Seoul' => 'South Korea (KR)',
            'Asia/Shanghai' => 'China (CN)',
            'Asia/Beijing' => 'China (CN)',
            'Asia/Harbin' => 'China (CN)',
            'Asia/Urumqi' => 'China (CN)',
            'Australia/Perth' => 'Australia (AU)',
            'Australia/Darwin' => 'Australia (AU)',
            'Australia/Adelaide' => 'Australia (AU)',
            'Australia/Brisbane' => 'Australia (AU)',
            'Australia/Sydney' => 'Australia (AU)',
            'Australia/Melbourne' => 'Australia (AU)',
            'Australia/Hobart' => 'Australia (AU)',
            'Europe/London' => 'United Kingdom (UK)',
            'America/New_York' => 'United States (US)',
            'America/Chicago' => 'United States (US)',
            'America/Denver' => 'United States (US)',
            'America/Los_Angeles' => 'United States (US)',
            'America/Anchorage' => 'United States (US)',
            'Pacific/Honolulu' => 'United States (US)',
        ];

        return $timezoneMap[$timezone] ?? 'Unknown';
    }

    /**
     * Get timezone from city name
     */
    public static function getTimezoneFromCity(string $city): ?string
    {
        $cityMap = [
            // Malaysia
            'kuala lumpur' => 'Asia/Kuala_Lumpur',
            'kl' => 'Asia/Kuala_Lumpur',
            'petaling jaya' => 'Asia/Kuala_Lumpur',
            'pj' => 'Asia/Kuala_Lumpur',
            'shah alam' => 'Asia/Kuala_Lumpur',
            'subang jaya' => 'Asia/Kuala_Lumpur',
            'klang' => 'Asia/Kuala_Lumpur',
            'johor bahru' => 'Asia/Kuala_Lumpur',
            'jb' => 'Asia/Kuala_Lumpur',
            'george town' => 'Asia/Kuala_Lumpur',
            'penang' => 'Asia/Kuala_Lumpur',
            'ipoh' => 'Asia/Kuala_Lumpur',
            'kuching' => 'Asia/Kuala_Lumpur',
            'kota kinabalu' => 'Asia/Kuala_Lumpur',
            'alor setar' => 'Asia/Kuala_Lumpur',
            'melaka' => 'Asia/Kuala_Lumpur',
            'malacca' => 'Asia/Kuala_Lumpur',
            'seremban' => 'Asia/Kuala_Lumpur',
            'kota bharu' => 'Asia/Kuala_Lumpur',
            'kuala terengganu' => 'Asia/Kuala_Lumpur',

            // Singapore
            'singapore' => 'Asia/Singapore',
            'sg' => 'Asia/Singapore',

            // Indonesia
            'jakarta' => 'Asia/Jakarta',
            'surabaya' => 'Asia/Jakarta',
            'bandung' => 'Asia/Jakarta',
            'medan' => 'Asia/Jakarta',
            'semarang' => 'Asia/Jakarta',
            'palembang' => 'Asia/Jakarta',
            'makassar' => 'Asia/Makassar',
            'jayapura' => 'Asia/Jayapura',

            // Philippines
            'manila' => 'Asia/Manila',
            'quezon city' => 'Asia/Manila',
            'makati' => 'Asia/Manila',
            'cebu' => 'Asia/Manila',
            'davao' => 'Asia/Manila',

            // Japan
            'tokyo' => 'Asia/Tokyo',
            'osaka' => 'Asia/Tokyo',
            'kyoto' => 'Asia/Tokyo',
            'yokohama' => 'Asia/Tokyo',
            'nagoya' => 'Asia/Tokyo',
            'sapporo' => 'Asia/Tokyo',

            // South Korea
            'seoul' => 'Asia/Seoul',
            'busan' => 'Asia/Seoul',
            'incheon' => 'Asia/Seoul',
            'daegu' => 'Asia/Seoul',

            // China
            'shanghai' => 'Asia/Shanghai',
            'beijing' => 'Asia/Beijing',
            'guangzhou' => 'Asia/Shanghai',
            'shenzhen' => 'Asia/Shanghai',
            'harbin' => 'Asia/Harbin',
            'urumqi' => 'Asia/Urumqi',

            // Australia
            'perth' => 'Australia/Perth',
            'darwin' => 'Australia/Darwin',
            'adelaide' => 'Australia/Adelaide',
            'brisbane' => 'Australia/Brisbane',
            'sydney' => 'Australia/Sydney',
            'melbourne' => 'Australia/Melbourne',
            'hobart' => 'Australia/Hobart',

            // United Kingdom
            'london' => 'Europe/London',
            'manchester' => 'Europe/London',
            'birmingham' => 'Europe/London',
            'glasgow' => 'Europe/London',
            'edinburgh' => 'Europe/London',

            // United States
            'new york' => 'America/New_York',
            'chicago' => 'America/Chicago',
            'denver' => 'America/Denver',
            'los angeles' => 'America/Los_Angeles',
            'anchorage' => 'America/Anchorage',
            'honolulu' => 'Pacific/Honolulu',
            'san francisco' => 'America/Los_Angeles',
            'seattle' => 'America/Los_Angeles',
            'miami' => 'America/New_York',
            'houston' => 'America/Chicago',
            'phoenix' => 'America/Denver',
            'las vegas' => 'America/Los_Angeles',

            // Canada
            'toronto' => 'America/New_York',
            'vancouver' => 'America/Los_Angeles',
            'montreal' => 'America/New_York',
            'calgary' => 'America/Denver',

            // Thailand
            'bangkok' => 'Asia/Bangkok',
            'chiang mai' => 'Asia/Bangkok',
            'phuket' => 'Asia/Bangkok',

            // Vietnam
            'ho chi minh city' => 'Asia/Ho_Chi_Minh',
            'hanoi' => 'Asia/Ho_Chi_Minh',
            'da nang' => 'Asia/Ho_Chi_Minh',

            // India
            'mumbai' => 'Asia/Kolkata',
            'delhi' => 'Asia/Kolkata',
            'bangalore' => 'Asia/Kolkata',
            'kolkata' => 'Asia/Kolkata',
            'chennai' => 'Asia/Kolkata',

            // Europe
            'berlin' => 'Europe/Berlin',
            'paris' => 'Europe/Paris',
            'rome' => 'Europe/Rome',
            'madrid' => 'Europe/Madrid',
            'amsterdam' => 'Europe/Amsterdam',
            'zurich' => 'Europe/Zurich',
            'vienna' => 'Europe/Vienna',
            'brussels' => 'Europe/Brussels',
            'copenhagen' => 'Europe/Copenhagen',
            'stockholm' => 'Europe/Stockholm',
            'oslo' => 'Europe/Oslo',
            'helsinki' => 'Europe/Helsinki',
            'warsaw' => 'Europe/Warsaw',
            'prague' => 'Europe/Prague',
            'budapest' => 'Europe/Budapest',
            'bucharest' => 'Europe/Bucharest',
            'sofia' => 'Europe/Sofia',
            'athens' => 'Europe/Athens',
            'lisbon' => 'Europe/Lisbon',
            'dublin' => 'Europe/Dublin',

            // Brazil
            'sao paulo' => 'America/Sao_Paulo',
            'rio de janeiro' => 'America/Sao_Paulo',
            'brasilia' => 'America/Sao_Paulo',

            // Argentina
            'buenos aires' => 'America/Buenos_Aires',

            // Mexico
            'mexico city' => 'America/Mexico_City',

            // Egypt
            'cairo' => 'Africa/Cairo',

            // South Africa
            'johannesburg' => 'Africa/Johannesburg',
            'cape town' => 'Africa/Johannesburg',
        ];

        $normalizedCity = strtolower(trim($city));

        return $cityMap[$normalizedCity] ?? null;
    }

    /**
     * Get country code from city name
     */
    public static function getCountryFromCity(string $city): ?string
    {
        $cityMap = [
            // Malaysia
            'kuala lumpur' => 'MY',
            'kl' => 'MY',
            'petaling jaya' => 'MY',
            'pj' => 'MY',
            'shah alam' => 'MY',
            'subang jaya' => 'MY',
            'klang' => 'MY',
            'johor bahru' => 'MY',
            'jb' => 'MY',
            'george town' => 'MY',
            'penang' => 'MY',
            'ipoh' => 'MY',
            'kuching' => 'MY',
            'kota kinabalu' => 'MY',
            'alor setar' => 'MY',
            'melaka' => 'MY',
            'malacca' => 'MY',
            'seremban' => 'MY',
            'kota bharu' => 'MY',
            'kuala terengganu' => 'MY',

            // Singapore
            'singapore' => 'SG',
            'sg' => 'SG',

            // Indonesia
            'jakarta' => 'ID',
            'surabaya' => 'ID',
            'bandung' => 'ID',
            'medan' => 'ID',
            'semarang' => 'ID',
            'palembang' => 'ID',
            'makassar' => 'ID',
            'jayapura' => 'ID',

            // Philippines
            'manila' => 'PH',
            'quezon city' => 'PH',
            'makati' => 'PH',
            'cebu' => 'PH',
            'davao' => 'PH',

            // Japan
            'tokyo' => 'JP',
            'osaka' => 'JP',
            'kyoto' => 'JP',
            'yokohama' => 'JP',
            'nagoya' => 'JP',
            'sapporo' => 'JP',

            // South Korea
            'seoul' => 'KR',
            'busan' => 'KR',
            'incheon' => 'KR',
            'daegu' => 'KR',

            // China
            'shanghai' => 'CN',
            'beijing' => 'CN',
            'guangzhou' => 'CN',
            'shenzhen' => 'CN',
            'harbin' => 'CN',
            'urumqi' => 'CN',

            // Australia
            'perth' => 'AU',
            'darwin' => 'AU',
            'adelaide' => 'AU',
            'brisbane' => 'AU',
            'sydney' => 'AU',
            'melbourne' => 'AU',
            'hobart' => 'AU',

            // United Kingdom
            'london' => 'GB',
            'manchester' => 'GB',
            'birmingham' => 'GB',
            'glasgow' => 'GB',
            'edinburgh' => 'GB',

            // United States
            'new york' => 'US',
            'chicago' => 'US',
            'denver' => 'US',
            'los angeles' => 'US',
            'anchorage' => 'US',
            'honolulu' => 'US',
            'san francisco' => 'US',
            'seattle' => 'US',
            'miami' => 'US',
            'houston' => 'US',
            'phoenix' => 'US',
            'las vegas' => 'US',

            // Canada
            'toronto' => 'CA',
            'vancouver' => 'CA',
            'montreal' => 'CA',
            'calgary' => 'CA',

            // Thailand
            'bangkok' => 'TH',
            'chiang mai' => 'TH',
            'phuket' => 'TH',

            // Vietnam
            'ho chi minh city' => 'VN',
            'hanoi' => 'VN',
            'da nang' => 'VN',

            // India
            'mumbai' => 'IN',
            'delhi' => 'IN',
            'bangalore' => 'IN',
            'kolkata' => 'IN',
            'chennai' => 'IN',

            // Europe
            'berlin' => 'DE',
            'paris' => 'FR',
            'rome' => 'IT',
            'madrid' => 'ES',
            'amsterdam' => 'NL',
            'zurich' => 'CH',
            'vienna' => 'AT',
            'brussels' => 'BE',
            'copenhagen' => 'DK',
            'stockholm' => 'SE',
            'oslo' => 'NO',
            'helsinki' => 'FI',
            'warsaw' => 'PL',
            'prague' => 'CZ',
            'budapest' => 'HU',
            'bucharest' => 'RO',
            'sofia' => 'BG',
            'athens' => 'GR',
            'lisbon' => 'PT',
            'dublin' => 'IE',

            // Brazil
            'sao paulo' => 'BR',
            'rio de janeiro' => 'BR',
            'brasilia' => 'BR',

            // Argentina
            'buenos aires' => 'AR',

            // Mexico
            'mexico city' => 'MX',

            // Egypt
            'cairo' => 'EG',

            // South Africa
            'johannesburg' => 'ZA',
            'cape town' => 'ZA',
        ];

        $normalizedCity = strtolower(trim($city));

        return $cityMap[$normalizedCity] ?? null;
    }

    /**
     * Get default timezone (Kuala Lumpur UTC+8)
     */
    public static function getDefaultTimezone(): string
    {
        return 'Asia/Kuala_Lumpur';
    }

    /**
     * Get default country (Malaysia)
     */
    public static function getDefaultCountry(): string
    {
        return 'MY';
    }
}
