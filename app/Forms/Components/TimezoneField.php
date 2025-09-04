<?php

namespace App\Forms\Components;

use App\Helpers\TimezoneHelper;
use Filament\Forms\Components\Select;

class TimezoneField extends Select
{
    // Set up the timezone field
    protected function setUp(): void
    {
        parent::setUp();

        $this->options(TimezoneHelper::getGroupedTimezoneOptions())
            ->searchable();
    }
}
