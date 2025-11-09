<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class AnalyticsWidget extends Widget
{
    protected static string $view = 'filament.widgets.analytics-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 1;
}
