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
        $this->activeTab = request()->query('tab', 'overview');
    }

    public function switchToOverview(): void
    {
        $this->activeTab = 'overview';
        $this->updateBrowserUrl();
    }

    public function switchToAnalytics(): void
    {
        $this->activeTab = 'analytics';
        $this->updateBrowserUrl();
    }

    protected function updateBrowserUrl(): void
    {
        $queryParams = request()->query();
        $queryParams['tab'] = $this->activeTab;

        $url = route('filament.admin.pages.dashboard', $queryParams);

        $this->dispatch('update-url', url: $url);
    }

    public static function getTabUrl(string $tab): string
    {
        return route('filament.admin.pages.dashboard', ['tab' => $tab]);
    }

    public static function getOverviewUrl(): string
    {
        return self::getTabUrl('overview');
    }

    public static function getAnalyticsUrl(): string
    {
        return self::getTabUrl('analytics');
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
        // Always show these widgets regardless of active tab
        $persistentWidgets = [
            \Filament\Widgets\AccountWidget::class,
            \Filament\Widgets\FilamentInfoWidget::class,
        ];

        // Add tab-specific widgets
        $tabWidgets = $this->activeTab === 'analytics'
            ? [\App\Filament\Widgets\AnalyticsWidget::class]
            : [
                \App\Filament\Widgets\TotalWidget::class,
                \App\Filament\Widgets\RecentProjectsWidget::class,
                \App\Filament\Widgets\RecentDocumentsWidget::class,
            ];

        return array_merge($persistentWidgets, $tabWidgets);
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
