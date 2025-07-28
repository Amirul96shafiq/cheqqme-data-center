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
    // Top row: 3 widgets (left–center–right)
    public function getHeaderWidgets(): array
    {
        return [
            TotalClientsWidget::class,
            TotalDocumentsWidget::class,
            TotalProjectsWidget::class,
        ];
    }

    // Bottom row: 2 widgets (left–right)
    public function getFooterWidgets(): array
    {
        return [
            RecentDocumentsWidget::class,
            RecentProjectsWidget::class,
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
