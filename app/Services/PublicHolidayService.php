<?php

namespace App\Services;

use App\Models\PublicHoliday;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PublicHolidayService
{
    protected ?string $googleCalendarKey;

    protected int $cacheTtlHours = 24;

    public function __construct()
    {
        $this->googleCalendarKey = config('services.google_calendar.key');
    }

    /**
     * Get holidays for a specific country and date range
     */
    public function getHolidaysForCountry(string $countryCode, Carbon $startDate, Carbon $endDate): Collection
    {
        $cacheKey = "holidays_{$countryCode}_{$startDate->format('Y-m')}_{$endDate->format('Y-m')}";

        return Cache::remember($cacheKey, $this->cacheTtlHours * 3600, function () use ($countryCode, $startDate, $endDate) {
            return $this->fetchHolidaysFromAPI($countryCode, $startDate, $endDate);
        });
    }

    /**
     * Fetch holidays from external API
     */
    protected function fetchHolidaysFromAPI(string $countryCode, Carbon $startDate, Carbon $endDate): Collection
    {
        $allHolidays = collect();

        // Use Google Calendar API for all years in the range
        if ($this->googleCalendarKey) {
            $startYear = $startDate->year;
            $endYear = $endDate->year;

            // Fetch holidays for each year in the range
            for ($year = $startYear; $year <= $endYear; $year++) {
                $yearHolidays = $this->fetchFromGoogleCalendar($countryCode, $year);
                $allHolidays = $allHolidays->merge($yearHolidays);
            }

            if ($allHolidays->isNotEmpty()) {
                return $allHolidays->filter(function ($holiday) use ($startDate, $endDate) {
                    return $holiday->date->between($startDate, $endDate);
                });
            }
        }

        // Fallback to local database
        return $this->getHolidaysFromDatabase($countryCode, $startDate, $endDate);
    }

    /**
     * Get holidays from local database as fallback
     */
    protected function getHolidaysFromDatabase(string $countryCode, Carbon $startDate, Carbon $endDate): Collection
    {
        return PublicHoliday::forCountry($countryCode)
            ->inDateRange($startDate, $endDate)
            ->get()
            ->map(function ($holiday) {
                return (object) [
                    'name' => $holiday->getLocalizedName(),
                    'date' => $holiday->date,
                    'type' => $holiday->type,
                    'country_code' => $holiday->country_code,
                ];
            });
    }

    /**
     * Fetch holidays from Google Calendar API
     */
    protected function fetchFromGoogleCalendar(string $countryCode, int $year): Collection
    {
        try {
            // Map country codes to Google Calendar holiday calendar IDs
            $calendarIds = [
                'MY' => 'en.malaysia#holiday@group.v.calendar.google.com',
                'SG' => 'en.singapore#holiday@group.v.calendar.google.com',
                'US' => 'en.usa#holiday@group.v.calendar.google.com',
                'GB' => 'en.uk#holiday@group.v.calendar.google.com',
                'AU' => 'en.australian#holiday@group.v.calendar.google.com',
            ];

            $calendarId = $calendarIds[$countryCode] ?? null;
            if (! $calendarId) {
                return collect();
            }

            $startDate = Carbon::create($year, 1, 1)->toRfc3339String();
            $endDate = Carbon::create($year, 12, 31)->toRfc3339String();

            $response = Http::timeout(10)->get('https://www.googleapis.com/calendar/v3/calendars/'.urlencode($calendarId).'/events', [
                'key' => $this->googleCalendarKey,
                'timeMin' => $startDate,
                'timeMax' => $endDate,
                'singleEvents' => 'true',
                'orderBy' => 'startTime',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $events = $data['items'] ?? [];

                return collect($events)->map(function ($event) use ($countryCode) {
                    $startDate = $event['start']['date'] ?? $event['start']['dateTime'];

                    return (object) [
                        'name' => $event['summary'],
                        'date' => Carbon::parse($startDate),
                        'type' => 'national',
                        'country_code' => $countryCode,
                    ];
                });
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch holidays from Google Calendar', [
                'country' => $countryCode,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);
        }

        return collect();
    }

    /**
     * Check if a specific date is a holiday
     */
    public function isHoliday(string $countryCode, Carbon $date): bool
    {
        $holidays = $this->getHolidaysForCountry($countryCode, $date, $date);

        return $holidays->isNotEmpty();
    }

    /**
     * Get country name from country code
     */
    public function getCountryName(string $countryCode): string
    {
        $countries = [
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'ID' => 'Indonesia',
            'PH' => 'Philippines',
            'US' => 'United States',
            'GB' => 'United Kingdom',
            'AU' => 'Australia',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'CN' => 'China',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
            'IN' => 'India',
            'CA' => 'Canada',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'CH' => 'Switzerland',
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'DK' => 'Denmark',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'FI' => 'Finland',
            'PL' => 'Poland',
            'CZ' => 'Czech Republic',
            'HU' => 'Hungary',
            'RO' => 'Romania',
            'BG' => 'Bulgaria',
            'GR' => 'Greece',
            'PT' => 'Portugal',
            'IE' => 'Ireland',
            'BR' => 'Brazil',
            'AR' => 'Argentina',
            'MX' => 'Mexico',
            'EG' => 'Egypt',
            'ZA' => 'South Africa',
        ];

        return $countries[$countryCode] ?? $countryCode;
    }

    /**
     * Sync holidays from API to database
     */
    public function syncHolidaysFromAPI(string $countryCode, int $year): bool
    {
        try {
            $allHolidays = collect();

            if ($this->googleCalendarKey) {
                // Use Google Calendar API for all years
                $allHolidays = $this->fetchFromGoogleCalendar($countryCode, $year);
            }

            foreach ($allHolidays as $holiday) {
                PublicHoliday::updateOrCreate(
                    [
                        'country_code' => $holiday->country_code,
                        'name' => $holiday->name,
                        'date' => $holiday->date->format('Y-m-d'),
                    ],
                    [
                        'type' => $holiday->type,
                        'is_recurring' => true, // Assume API holidays are recurring
                    ]
                );
            }

            Log::info('Successfully synced holidays from API', [
                'country' => $countryCode,
                'year' => $year,
                'count' => $allHolidays->count(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to sync holidays from API', [
                'country' => $countryCode,
                'year' => $year,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Clear holiday cache for a specific country
     */
    public function clearCache(string $countryCode): void
    {
        $pattern = "holidays_{$countryCode}_*";
        // Note: This is a simplified cache clearing. In production, you might want to use Redis tags
        Cache::flush(); // Clear all cache for simplicity
    }
}
