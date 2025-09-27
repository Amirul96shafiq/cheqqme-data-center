<?php

namespace App\View\Components\Traits;

use App\Services\OnlineStatus\StatusConfig;

/**
 * Trait for components that display online status
 *
 * Provides common functionality for online status indicators
 */
trait HasOnlineStatus
{
    /**
     * Get the CSS classes for the status indicator based on size
     */
    public function getSizeClasses(): string
    {
        return StatusConfig::getSizeClasses($this->size);
    }

    /**
     * Get the CSS classes for the status indicator based on status
     */
    public function getStatusClasses(): string
    {
        $status = $this->user->online_status ?? StatusConfig::getDefaultStatus();

        return StatusConfig::getStatusClasses($status, $this->size);
    }

    /**
     * Get the display name for the status
     */
    public function getStatusDisplayName(): string
    {
        $status = $this->user->online_status ?? StatusConfig::getDefaultStatus();

        return StatusConfig::getStatusLabel($status);
    }

    /**
     * Get available online status options
     */
    public function getStatusOptions(): array
    {
        return StatusConfig::getStatusConfig();
    }

    /**
     * Get the current user's status
     */
    public function getCurrentStatus(): string
    {
        return $this->user->online_status ?? StatusConfig::getDefaultStatus();
    }

    /**
     * Check if this is the current user
     */
    public function isCurrentUser(): bool
    {
        return auth()->check() && auth()->id() === $this->user->id;
    }

    /**
     * Get data attributes for the status indicator
     */
    public function getDataAttributes(): array
    {
        return [
            'data-user-id' => $this->user->id,
            'data-current-status' => $this->getCurrentStatus(),
            'data-is-current-user' => $this->isCurrentUser() ? 'true' : 'false',
            'data-tooltip-text' => $this->getStatusDisplayName(),
        ];
    }

    /**
     * Get data attributes as HTML string
     */
    public function getDataAttributesString(): string
    {
        $attributes = $this->getDataAttributes();
        $html = '';

        foreach ($attributes as $key => $value) {
            $html .= " {$key}=\"{$value}\"";
        }

        return $html;
    }
}
