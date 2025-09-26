<?php

namespace App\Services;

class OnlineStatusManager
{
    /**
     * Available online status options
     */
    public const STATUS_ONLINE = 'online';

    public const STATUS_AWAY = 'away';

    public const STATUS_DND = 'dnd';

    public const STATUS_INVISIBLE = 'invisible';

    /**
     * Get all available status options
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ONLINE,
            self::STATUS_AWAY,
            self::STATUS_DND,
            self::STATUS_INVISIBLE,
        ];
    }

    /**
     * Get status configuration with all properties
     */
    public static function getStatusConfig(): array
    {
        return [
            self::STATUS_ONLINE => [
                'label' => __('user.indicator.online_status_online'),
                'color' => 'bg-teal-500',
                'filament_color' => 'success',
                'icon' => 'heroicon-o-check-circle',
                'description' => 'User is actively online',
            ],
            self::STATUS_AWAY => [
                'label' => __('user.indicator.online_status_away'),
                'color' => 'bg-primary-500',
                'filament_color' => 'primary',
                'icon' => 'heroicon-o-clock',
                'description' => 'User is away but may respond',
            ],
            self::STATUS_DND => [
                'label' => __('user.indicator.online_status_dnd'),
                'color' => 'bg-red-500',
                'filament_color' => 'danger',
                'icon' => 'heroicon-o-x-circle',
                'description' => 'User does not want to be disturbed',
            ],
            self::STATUS_INVISIBLE => [
                'label' => __('user.indicator.online_status_invisible'),
                'color' => 'bg-gray-400',
                'filament_color' => 'gray',
                'icon' => 'heroicon-o-eye-slash',
                'description' => 'User appears offline to others',
            ],
        ];
    }

    /**
     * Get status labels for form options
     */
    public static function getStatusLabels(): array
    {
        $config = self::getStatusConfig();

        return array_column($config, 'label');
    }

    /**
     * Get status options with icons for Filament forms
     */
    public static function getStatusOptionsWithIcons(): array
    {
        $config = self::getStatusConfig();
        $options = [];

        foreach ($config as $status => $data) {
            $options[$status] = sprintf(
                '<div class="flex items-center gap-2"><div class="w-4 h-4 rounded-full %s border-2 border-white dark:border-gray-900"></div><span>%s</span></div>',
                $data['color'],
                $data['label']
            );
        }

        return $options;
    }

    /**
     * Get status color for a given status
     */
    public static function getStatusColor(string $status): string
    {
        $config = self::getStatusConfig();

        return $config[$status]['color'] ?? 'bg-gray-400';
    }

    /**
     * Get Filament color for a given status
     */
    public static function getFilamentColor(string $status): string
    {
        $config = self::getStatusConfig();

        return $config[$status]['filament_color'] ?? 'gray';
    }

    /**
     * Get status label for a given status
     */
    public static function getStatusLabel(string $status): string
    {
        $config = self::getStatusConfig();

        return $config[$status]['label'] ?? 'Unknown';
    }

    /**
     * Get status icon for a given status
     */
    public static function getStatusIcon(string $status): string
    {
        $config = self::getStatusConfig();

        return $config[$status]['icon'] ?? 'heroicon-o-question-mark-circle';
    }

    /**
     * Get status description for a given status
     */
    public static function getStatusDescription(string $status): string
    {
        $config = self::getStatusConfig();

        return $config[$status]['description'] ?? 'Unknown status';
    }

    /**
     * Check if a status is valid
     */
    public static function isValidStatus(string $status): bool
    {
        return in_array($status, self::getAvailableStatuses());
    }

    /**
     * Get default status
     */
    public static function getDefaultStatus(): string
    {
        return self::STATUS_ONLINE;
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
            ];
        }

        return $jsConfig;
    }

    /**
     * Get validation rules for online status
     */
    public static function getValidationRules(): string
    {
        return 'required|in:'.implode(',', self::getAvailableStatuses());
    }
}
