<?php

namespace App\Services\OnlineStatus;

use Illuminate\Support\Facades\Lang;

/**
 * Centralized online status management
 * 
 * This class handles all status definitions, configurations, and business rules
 * for the online status system.
 */
class StatusManager
{
    // Core status constants
    public const ONLINE = 'online';
    public const AWAY = 'away';
    public const DO_NOT_DISTURB = 'dnd';
    public const INVISIBLE = 'invisible';

    // Status metadata
    public const AUTO_MANAGED = 'auto_managed';
    public const MANUAL_ONLY = 'manual_only';
    public const REFRESH_RESET = 'refresh_reset';

    /**
     * Get all available statuses
     */
    public static function getAllStatuses(): array
    {
        return [
            self::ONLINE,
            self::AWAY,
            self::DO_NOT_DISTURB,
            self::INVISIBLE,
        ];
    }

    /**
     * Get user-selectable statuses (excludes system-managed statuses)
     */
    public static function getUserSelectableStatuses(): array
    {
        return [
            self::ONLINE,
            self::AWAY,
            self::DO_NOT_DISTURB,
            self::INVISIBLE,
        ];
    }

    /**
     * Get status configuration with all properties
     */
    public static function getStatusConfig(): array
    {
        return [
            self::ONLINE => [
                'label' => Lang::get('user.indicator.online_status_online'),
                'color' => 'bg-teal-500',
                'filament_color' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'User is actively online and available',
                'properties' => [
                    self::AUTO_MANAGED => true,
                    self::REFRESH_RESET => false,
                ],
            ],
            self::AWAY => [
                'label' => Lang::get('user.indicator.online_status_away'),
                'color' => 'bg-primary-500',
                'filament_color' => 'primary',
                'icon' => 'heroicon-o-clock',
                'description' => 'User is away but may respond',
                'properties' => [
                    self::AUTO_MANAGED => true,
                    self::REFRESH_RESET => true, // Auto-away users return to online on refresh
                ],
            ],
            self::DO_NOT_DISTURB => [
                'label' => Lang::get('user.indicator.online_status_dnd'),
                'color' => 'bg-red-500',
                'filament_color' => 'danger',
                'icon' => 'heroicon-o-x-circle',
                'description' => 'User does not want to be disturbed',
                'properties' => [
                    self::AUTO_MANAGED => false,
                    self::REFRESH_RESET => false,
                ],
            ],
            self::INVISIBLE => [
                'label' => Lang::get('user.indicator.online_status_invisible'),
                'color' => 'bg-gray-400',
                'filament_color' => 'gray',
                'icon' => 'heroicon-o-eye-slash',
                'description' => 'User appears offline to others',
                'properties' => [
                    self::AUTO_MANAGED => false,
                    self::REFRESH_RESET => false,
                ],
            ],
        ];
    }

    /**
     * Get status property
     */
    public static function getStatusProperty(string $status, string $property): mixed
    {
        $config = self::getStatusConfig();
        return $config[$status]['properties'][$property] ?? null;
    }

    /**
     * Check if status is auto-managed
     */
    public static function isAutoManaged(string $status): bool
    {
        return self::getStatusProperty($status, self::AUTO_MANAGED) === true;
    }

    /**
     * Check if status resets on page refresh
     */
    public static function resetsOnRefresh(string $status): bool
    {
        return self::getStatusProperty($status, self::REFRESH_RESET) === true;
    }

    /**
     * Get status label
     */
    public static function getStatusLabel(string $status): string
    {
        $config = self::getStatusConfig();
        return $config[$status]['label'] ?? 'Unknown';
    }

    /**
     * Get status color
     */
    public static function getStatusColor(string $status): string
    {
        $config = self::getStatusConfig();
        return $config[$status]['color'] ?? 'bg-gray-400';
    }

    /**
     * Get Filament color
     */
    public static function getFilamentColor(string $status): string
    {
        $config = self::getStatusConfig();
        return $config[$status]['filament_color'] ?? 'gray';
    }

    /**
     * Get status icon
     */
    public static function getStatusIcon(string $status): string
    {
        $config = self::getStatusConfig();
        return $config[$status]['icon'] ?? 'heroicon-o-question-mark-circle';
    }

    /**
     * Get status description
     */
    public static function getStatusDescription(string $status): string
    {
        $config = self::getStatusConfig();
        return $config[$status]['description'] ?? 'Unknown status';
    }

    /**
     * Get default status
     */
    public static function getDefaultStatus(): string
    {
        return self::ONLINE;
    }

    /**
     * Check if status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::getAllStatuses());
    }

    /**
     * Get validation rules
     */
    public static function getValidationRules(): string
    {
        return 'required|in:' . implode(',', self::getAllStatuses());
    }

}
