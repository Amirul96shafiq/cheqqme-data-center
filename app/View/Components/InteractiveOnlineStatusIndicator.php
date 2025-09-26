<?php

namespace App\View\Components;

use App\Models\User;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class InteractiveOnlineStatusIndicator extends Component
{
    public User $user;

    public string $size;

    public bool $showTooltip;

    /**
     * Create a new component instance.
     */
    public function __construct(User $user, string $size = 'sm', bool $showTooltip = true)
    {
        $this->user = $user;
        $this->size = $size;
        $this->showTooltip = $showTooltip;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.interactive-online-status-indicator');
    }

    /**
     * Get the CSS classes for the status indicator based on size
     */
    public function getSizeClasses(): string
    {
        return match ($this->size) {
            'xs' => 'w-3 h-3',
            'sm' => 'w-4 h-4',
            'md' => 'w-5 h-5',
            'lg' => 'w-6 h-6',
            'xl' => 'w-8 h-8',
            default => 'w-4 h-4',
        };
    }

    /**
     * Get the CSS classes for the status indicator based on status
     */
    public function getStatusClasses(): string
    {
        $baseClasses = 'rounded-full border-2 border-white dark:border-gray-900';

        return match ($this->user->online_status ?? 'online') {
            'online' => $baseClasses.' bg-teal-500', // Teal/Green
            'away' => $baseClasses.' bg-primary-500', // Primary color
            'dnd' => $baseClasses.' bg-red-500', // Danger/Red
            'invisible' => $baseClasses.' bg-gray-400', // Gray
            default => $baseClasses.' bg-gray-400',
        };
    }

    /**
     * Get the display name for the status
     */
    public function getStatusDisplayName(): string
    {
        return match ($this->user->online_status ?? 'online') {
            'online' => 'Online',
            'away' => 'Away',
            'dnd' => 'Do Not Disturb',
            'invisible' => 'Invisible',
            default => 'Unknown',
        };
    }

    /**
     * Get available online status options with icons
     */
    public function getStatusOptions(): array
    {
        return [
            'online' => [
                'label' => __('user.indicator.online_status_online'),
                'color' => 'bg-teal-500',
                'icon' => 'heroicon-o-check-circle',
            ],
            'away' => [
                'label' => __('user.indicator.online_status_away'),
                'color' => 'bg-primary-500',
                'icon' => 'heroicon-o-clock',
            ],
            'dnd' => [
                'label' => __('user.indicator.online_status_dnd'),
                'color' => 'bg-red-500',
                'icon' => 'heroicon-o-x-circle',
            ],
            'invisible' => [
                'label' => __('user.indicator.online_status_invisible'),
                'color' => 'bg-gray-400',
                'icon' => 'heroicon-o-eye-slash',
            ],
        ];
    }
}
