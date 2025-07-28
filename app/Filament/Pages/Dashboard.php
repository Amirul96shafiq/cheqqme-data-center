<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Filament\Widgets\{
    TotalClientsWidget,
    TotalProjectsWidget,
    TotalDocumentsWidget,
    RecentProjectsWidget,
    RecentDocumentsWidget
};

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.dashboard';
    public function getWidgets(): array
    {
        return [
            TotalClientsWidget::class,
            TotalProjectsWidget::class,
            TotalDocumentsWidget::class,
            RecentProjectsWidget::class,
            RecentDocumentsWidget::class,
        ];
    }
}
