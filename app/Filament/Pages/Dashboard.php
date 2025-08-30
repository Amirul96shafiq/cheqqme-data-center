<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard';

    protected static ?string $slug = 'dashboard';

    protected static ?int $navigationSort = -2;

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide the Dashboard from the sidebar navigation
    }

    public static function getSlug(): string
    {
        return 'dashboard';
    }
}
