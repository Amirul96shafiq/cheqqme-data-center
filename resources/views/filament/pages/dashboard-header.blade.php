<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-950 dark:text-white">
            {{ $activeTab === 'analytics' ? __('dashboard.tabs.analytics') : __('filament-panels::pages/dashboard.title') }}
        </h1>
    </div>

    <div class="flex items-center gap-2">

        <!-- Overview Tab -->
        <a
            href="{{ \App\Filament\Pages\Dashboard::getOverviewUrl() }}"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors duration-200 flex items-center gap-2 border {{ $activeTab === 'overview' ? 'bg-primary-500 hover:bg-primary-400 text-primary-900 border-primary-500' : 'border-gray-300 bg-white text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900' }}"
        >
            <x-heroicon-m-bars-3 class="w-4 h-4" />
            <span class="hidden sm:inline">
                <span>{{ __('dashboard.tabs.overview') }}</span>
            </span>
        </a>

        <!-- Analytics Tab -->
        <a
            href="{{ \App\Filament\Pages\Dashboard::getAnalyticsUrl() }}"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors duration-200 flex items-center gap-2 border {{ $activeTab === 'analytics' ? 'bg-primary-500 hover:bg-primary-400 text-primary-900 border-primary-500' : 'border-gray-300 bg-white text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900' }}"
        >
            <x-heroicon-m-chart-bar class="w-4 h-4" />
            <span class="hidden sm:inline">
                <span>{{ __('dashboard.tabs.analytics') }}</span>
            </span>
        </a>

    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('update-url', (data) => {
        if (data.url && window.history.replaceState) {
            window.history.replaceState(null, '', data.url);
        }
    });
});
</script>
