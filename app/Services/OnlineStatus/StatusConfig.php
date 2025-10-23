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
                'label' => Lang::get('user.indicator.online_status_online'),
                'color' => 'bg-teal-500',
                'filament_color' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'User is actively online and available',
            ],
            self::AWAY => [
                'label' => Lang::get('user.indicator.online_status_away'),
                'color' => 'bg-primary-500',
                'filament_color' => 'primary',
                'icon' => 'heroicon-o-clock',
                'description' => 'User is away but may respond',
            ],
            self::DO_NOT_DISTURB => [
                'label' => Lang::get('user.indicator.online_status_dnd'),
                'color' => 'bg-red-500',
                'filament_color' => 'danger',
                'icon' => 'heroicon-o-x-circle',
                'description' => 'User does not want to be disturbed',
            ],
            self::INVISIBLE => [
                'label' => Lang::get('user.indicator.online_status_invisible'),
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
                'classes' => 'w-2 h-2 md:w-3 md:h-3',
                'border_classes' => 'w-1 h-1 md:w-2 md:h-2',
            ],
            self::SIZE_SM => [
                'classes' => 'w-3 h-3 md:w-4 md:h-4',
                'border_classes' => 'w-2 h-2 md:w-3 md:h-3',
            ],
            self::SIZE_MD => [
                'classes' => 'w-4 h-4 md:w-5 md:h-5',
                'border_classes' => 'w-3 h-3 md:w-4 md:h-4',
            ],
            self::SIZE_LG => [
                'classes' => 'w-5 h-5 md:w-6 md:h-6',
                'border_classes' => 'w-4 h-4 md:w-5 md:h-5',
            ],
            self::SIZE_XL => [
                'classes' => 'w-6 h-6 md:w-8 md:h-8',
                'border_classes' => 'w-4 h-4 md:w-6 md:h-6',
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
     * Get box shadow class for a given size to create outline effect
     */
    public static function getBoxShadowForSize(string $size): string
    {
        return match ($size) {
            self::SIZE_XS => 'shadow-[0_0_0_1px_white] dark:shadow-[0_0_0_1px_rgb(17_24_39)]',
            self::SIZE_SM => 'shadow-[0_0_0_2px_white] dark:shadow-[0_0_0_2px_rgb(17_24_39)]',
            self::SIZE_MD => 'shadow-[0_0_0_2px_white] dark:shadow-[0_0_0_2px_rgb(17_24_39)]',
            self::SIZE_LG => 'shadow-[0_0_0_2px_white] dark:shadow-[0_0_0_2px_rgb(17_24_39)]',
            self::SIZE_XL => 'shadow-[0_0_0_5px_white] dark:shadow-[0_0_0_5px_rgb(17_24_39)]',
            default => 'shadow-[0_0_0_2px_white] dark:shadow-[0_0_0_2px_rgb(17_24_39)]',
        };
    }

    /**
     * Get complete status classes for display
     */
    public static function getStatusClasses(string $status, ?string $size = null): string
    {
        $size = $size ?? self::getDefaultSize();
        $boxShadow = self::getBoxShadowForSize($size);
        $baseClasses = "rounded-full {$boxShadow}";
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
