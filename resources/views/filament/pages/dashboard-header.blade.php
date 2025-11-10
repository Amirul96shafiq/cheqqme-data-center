<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-950 dark:text-white">
            {{ __('filament-panels::pages/dashboard.title') }}
        </h1>
    </div>

    <div class="flex items-center gap-2">

        <!-- Overview Tab -->
        <button
            wire:click="switchToOverview"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors duration-200 flex items-center gap-2 border {{ $activeTab === 'overview' ? 'bg-primary-500 hover:bg-primary-400 text-primary-900 border-primary-500' : 'border-gray-300 bg-white text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900' }}"
        >
            <x-heroicon-m-bars-3 wire:loading.remove wire:target="switchToOverview" class="w-4 h-4" />
            <x-heroicon-m-arrow-path wire:loading wire:target="switchToOverview" class="w-4 h-4 animate-spin" />
            <span wire:loading.remove wire:target="switchToOverview">{{ __('dashboard.tabs.overview') }}</span>
            <span wire:loading wire:target="switchToOverview">{{ __('calendar.calendar.loading') }}</span>
        </button>

        <!-- Analytics Tab -->
        <button
            wire:click="switchToAnalytics"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors duration-200 flex items-center gap-2 border {{ $activeTab === 'analytics' ? 'bg-primary-500 hover:bg-primary-400 text-primary-900 border-primary-500' : 'border-gray-300 bg-white text-gray-800 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-white dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:focus:ring-primary-500/40 dark:focus:ring-offset-gray-900' }}"
        >
            <x-heroicon-m-chart-bar wire:loading.remove wire:target="switchToAnalytics" class="w-4 h-4" />
            <x-heroicon-m-arrow-path wire:loading wire:target="switchToAnalytics" class="w-4 h-4 animate-spin" />
            <span wire:loading.remove wire:target="switchToAnalytics">{{ __('dashboard.tabs.analytics') }}</span>
            <span wire:loading wire:target="switchToAnalytics">{{ __('calendar.calendar.loading') }}</span>
        </button>

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
