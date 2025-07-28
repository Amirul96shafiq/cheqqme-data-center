<?php

namespace App\Filament\Widgets;

use App\Models\Document;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TotalDocumentsWidget extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Documents', Document::count())
                ->description('All registered documents')
                ->color('success')
                ->icon('heroicon-o-user-group'),
        ];
    }
}