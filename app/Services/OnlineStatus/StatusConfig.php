<?php

namespace App\Services\OnlineStatus;

use Illuminate\Support\Facades\Lang;

/**
 * Centralized status configuration for online status system
 *
 * This class provides a single source of truth for all status-related
 * configuration, including colors, labels, and JavaScript mappings.
 */
class StatusConfig
{
    // Status constants
    public const ONLINE = 'online';

    public const AWAY = 'away';

    public const DO_NOT_DISTURB = 'dnd';

    public const INVISIBLE = 'invisible';

    // Size constants
    public const SIZE_XS = 'xs';

    public const SIZE_SM = 'sm';

    public const SIZE_MD = 'md';

    public const SIZE_LG = 'lg';

    public const SIZE_XL = 'xl';

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
     * Get all available sizes
     */
    public static function getAllSizes(): array
    {
        return [
            self::SIZE_XS,
            self::SIZE_SM,
            self::SIZE_MD,
            self::SIZE_LG,
            self::SIZE_XL,
        ];
    }

    /**
     * Get default status
     */
    public static function getDefaultStatus(): string
    {
        return self::ONLINE;
    }

    /**
     * Get default size
     */
    public static function getDefaultSize(): string
    {
        return self::SIZE_SM;
    }

    /**
     * Get status configuration with all properties
     */
    public static function getStatusConfig(): array
    {
        return [
            self::ONLINE => [
                'label' => Lang::get('user.indicator.online_status_online', [], 'en'),
                'color' => 'bg-teal-500',
                'filament_color' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'User is actively online and available',
            ],
            self::AWAY => [
                'label' => Lang::get('user.indicator.online_status_away', [], 'en'),
                'color' => 'bg-primary-500',
                'filament_color' => 'primary',
                'icon' => 'heroicon-o-clock',
                'description' => 'User is away but may respond',
            ],
            self::DO_NOT_DISTURB => [
                'label' => Lang::get('user.indicator.online_status_dnd', [], 'en'),
                'color' => 'bg-red-500',
                'filament_color' => 'danger',
                'icon' => 'heroicon-o-x-circle',
                'description' => 'User does not want to be disturbed',
            ],
            self::INVISIBLE => [
                'label' => Lang::get('user.indicator.online_status_invisible', [], 'en'),
                'color' => 'bg-gray-400',
                'filament_color' => 'gray',
                'icon' => 'heroicon-o-eye-slash',
                'description' => 'User appears offline to others',
            ],
        ];
    }

    /**
     * Get size configuration
     */
    public static function getSizeConfig(): array
    {
        return [
            self::SIZE_XS => [
                'classes' => 'w-3 h-3',
                'border_classes' => 'w-2 h-2',
            ],
            self::SIZE_SM => [
                'classes' => 'w-4 h-4',
                'border_classes' => 'w-3 h-3',
            ],
            self::SIZE_MD => [
                'classes' => 'w-5 h-5',
                'border_classes' => 'w-4 h-4',
            ],
            self::SIZE_LG => [
                'classes' => 'w-6 h-6',
                'border_classes' => 'w-5 h-5',
            ],
            self::SIZE_XL => [
                'classes' => 'w-8 h-8',
                'border_classes' => 'w-6 h-6',
            ],
        ];
    }

    /**
     * Get JavaScript configuration for frontend
     */
    public static function getJavaScriptConfig(): array
    {
        $config = self::getStatusConfig();
        $jsConfig = [];

        foreach ($config as $status => $data) {
            $jsConfig[$status] = [
                'label' => $data['label'],
                'color' => $data['color'],
                'icon' => $data['icon'],
            ];
        }

        return $jsConfig;
    }

    /**
     * Get all status colors as an array
     */
    public static function getStatusColors(): array
    {
        $config = self::getStatusConfig();

        return array_column($config, 'color');
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
     * Get size classes
     */
    public static function getSizeClasses(string $size): string
    {
        $config = self::getSizeConfig();

        return $config[$size]['classes'] ?? $config[self::getDefaultSize()]['classes'];
    }

    /**
     * Get border size classes
     */
    public static function getBorderSizeClasses(string $size): string
    {
        $config = self::getSizeConfig();

        return $config[$size]['border_classes'] ?? $config[self::getDefaultSize()]['border_classes'];
    }

    /**
     * Get complete status classes for display
     */
    public static function getStatusClasses(string $status, ?string $size = null): string
    {
        $size = $size ?? self::getDefaultSize();
        $baseClasses = 'rounded-full border-2 border-white dark:border-gray-900';
        $color = self::getStatusColor($status);
        $sizeClasses = self::getSizeClasses($size);

        return $baseClasses.' '.$color.' '.$sizeClasses;
    }

    /**
     * Check if status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::getAllStatuses());
    }

    /**
     * Check if size is valid
     */
    public static function isValidSize(string $size): bool
    {
        return in_array($size, self::getAllSizes());
    }

    /**
     * Get validation rules for status
     */
    public static function getStatusValidationRules(): string
    {
        return 'required|in:'.implode(',', self::getAllStatuses());
    }

    /**
     * Get validation rules for size
     */
    public static function getSizeValidationRules(): string
    {
        return 'required|in:'.implode(',', self::getAllSizes());
    }
}
