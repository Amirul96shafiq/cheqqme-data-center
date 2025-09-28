<?php

namespace App\Forms\Components;

use App\Services\OnlineStatus\StatusConfig;
use Filament\Forms\Components\Field;

class OnlineStatusSelect extends Field
{
    protected string $view = 'forms.components.online-status-select';

    public function getStatusOptions(): array
    {
        return StatusConfig::getStatusConfig();
    }

    public function getCurrentStatus(): string
    {
        return $this->getState() ?? StatusConfig::getDefaultStatus();
    }
}
