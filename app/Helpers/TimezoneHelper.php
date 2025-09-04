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
}
