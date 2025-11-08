<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat as BaseStat;
use Illuminate\Contracts\View\View;

class StatWithCycleButton extends BaseStat
{
    public function render(): View
    {
        return view('filament.widgets.stat-with-cycle-button', $this->data());
    }
}
