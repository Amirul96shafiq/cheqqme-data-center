<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget\Stat as BaseStat;
use Illuminate\Contracts\View\View;

class StatWithMeetingCycleButton extends BaseStat
{
    public function render(): View
    {
        return view('filament.widgets.stat-with-meeting-cycle-button', $this->data());
    }
}
