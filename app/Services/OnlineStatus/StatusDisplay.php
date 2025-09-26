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
        $config = StatusManager::getStatusConfig();
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
        $config = StatusManager::getStatusConfig();
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
     * Get status classes for display
     */
    public static function getStatusClasses(string $status, string $size = 'sm'): string
    {
        $baseClasses = 'rounded-full border-2 border-white dark:border-gray-900';
        $color = StatusManager::getStatusColor($status);
        
        $sizeClasses = match ($size) {
            'xs' => 'w-2 h-2',
            'sm' => 'w-3 h-3',
            'md' => 'w-4 h-4',
            'lg' => 'w-5 h-5',
            'xl' => 'w-6 h-6',
            default => 'w-4 h-4',
        };
        
        return $baseClasses . ' ' . $color . ' ' . $sizeClasses;
    }

    /**
     * Get status display name
     */
    public static function getDisplayName(string $status): string
    {
        return StatusManager::getStatusLabel($status);
    }

    /**
     * Get status tooltip text
     */
    public static function getTooltipText(string $status): string
    {
        return StatusManager::getStatusDescription($status);
    }
}
