<?php

namespace App\Services\OnlineStatus;

/**
 * Handles display and UI-related functionality for online status
 */
class StatusDisplay
{
    /**
     * Get status options with icons for Filament forms
     */
    public static function getFormOptions(): array
    {
        $config = StatusConfig::getStatusConfig();
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
     * Get JavaScript configuration for frontend
     */
    public static function getJavaScriptConfig(): array
    {
        return StatusConfig::getJavaScriptConfig();
    }

    /**
     * Get status classes for display
     */
    public static function getStatusClasses(string $status, ?string $size = null): string
    {
        return StatusConfig::getStatusClasses($status, $size);
    }

    /**
     * Get status display name
     */
    public static function getDisplayName(string $status): string
    {
        return StatusConfig::getStatusLabel($status);
    }

    /**
     * Get status tooltip text
     */
    public static function getTooltipText(string $status): string
    {
        return StatusConfig::getStatusDescription($status);
    }
}
