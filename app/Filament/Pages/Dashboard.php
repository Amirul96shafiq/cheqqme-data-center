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

    public ?string $activeTab = 'overview';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide the Dashboard from the sidebar navigation
    }

    public static function getSlug(): string
    {
        return 'dashboard';
    }

    public function mount(): void
    {
        $this->activeTab = 'overview';
    }

    public function switchToOverview(): void
    {
        $this->activeTab = 'overview';
    }

    public function switchToAnalytics(): void
    {
        $this->activeTab = 'analytics';
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.pages.dashboard-header', [
            'activeTab' => $this->activeTab,
        ]);
    }

    public function getWidgets(): array
    {
        if ($this->activeTab === 'analytics') {
            return [
                \App\Filament\Widgets\AnalyticsWidget::class,
            ];
        }

        return [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
            \App\Filament\Widgets\TotalWidget::class,
            \App\Filament\Widgets\RecentProjectsWidget::class,
            \App\Filament\Widgets\RecentDocumentsWidget::class,
        ];
    }

    public function getColumns(): int|array|string
    {
        return [
            'default' => 1,
            'sm' => 1,
            'md' => 1,
            'lg' => 1,
            'xl' => 1,
            '2xl' => 2,
        ];
    }
}
