<?php

namespace App\Filament\Widgets;

use Filament\Widgets\FilamentInfoWidget as BaseFilamentInfoWidget;

class OverviewFilamentInfoWidget extends BaseFilamentInfoWidget
{
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
        '2xl' => 1,
    ];

    protected static ?int $sort = 2;
}
