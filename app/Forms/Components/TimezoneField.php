<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Select;

class TimezoneField extends Select
{
    // Set up the timezone field
    protected function setUp(): void
    {
        parent::setUp();

        $this->options($this->getTimezoneOptions())
            ->searchable();
    }

    // Get the timezone options
    protected function getTimezoneOptions(): array
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
}
