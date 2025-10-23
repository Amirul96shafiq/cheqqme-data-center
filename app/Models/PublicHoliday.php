<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $fillable = [
        'country_code',
        'name',
        'date',
        'type',
        'is_recurring',
        'localized_names',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'localized_names' => 'array',
    ];

    /**
     * Scope to get holidays for a specific country
     */
    public function scopeForCountry($query, string $countryCode)
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Scope to get holidays within a date range
     */
    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Get localized holiday name
     */
    public function getLocalizedName(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if ($this->localized_names && isset($this->localized_names[$locale])) {
            return $this->localized_names[$locale];
        }

        return $this->name;
    }

    /**
     * Check if holiday is recurring
     */
    public function isRecurring(): bool
    {
        return $this->is_recurring;
    }

    /**
     * Get holiday type display name
     */
    public function getTypeDisplayName(): string
    {
        return match ($this->type) {
            'national' => __('calendar.holidays.holiday_type.national'),
            'regional' => __('calendar.holidays.holiday_type.regional'),
            'religious' => __('calendar.holidays.holiday_type.religious'),
            default => $this->type,
        };
    }
}
