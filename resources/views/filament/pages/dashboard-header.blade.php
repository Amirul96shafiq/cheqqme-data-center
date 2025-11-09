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
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors duration-200 {{ $activeTab === 'overview' ? 'bg-primary-500 text-primary-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
        >
            Overview
        </button>

        <!-- Analytics Tab -->
        <button
            wire:click="switchToAnalytics"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-colors duration-200 {{ $activeTab === 'analytics' ? 'bg-primary-500 text-primary-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}"
        >
            Analytics
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
