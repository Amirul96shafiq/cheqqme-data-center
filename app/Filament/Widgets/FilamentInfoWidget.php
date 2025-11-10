<?php

namespace App\Filament\Widgets;

use Filament\Widgets\FilamentInfoWidget as BaseFilamentInfoWidget;

class FilamentInfoWidget extends BaseFilamentInfoWidget
{
    protected int|string|array $columnSpan = [
        'default' => 'full',
        'sm' => 'full',
        'md' => 'full',
        'lg' => 'full',
        'xl' => 'full',
        '2xl' => 3,
    ];

    protected static ?int $sort = 2;
}
