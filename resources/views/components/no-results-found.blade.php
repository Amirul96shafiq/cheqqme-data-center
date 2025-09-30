@props([
    'searchTerm' => '',
    'icon' => null,
    'heading' => null,
    'description' => null
])

<div class="flex items-top justify-center py-4">
    <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm rounded-2xl border border-gray-200 dark:border-gray-700 p-8 sm:p-12 w-full mx-2 sm:mx-4">
        <div class="text-center space-y-6">
            
            {{-- Icon --}}
            <div class="mb-6">
                    <x-heroicon-o-x-mark class="w-14 h-14 mx-auto text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-500/20 rounded-full p-4" />
            </div>

            {{-- Heading --}}
            <h3 class="text-base font-semibold leading-6 text-gray-950 dark:text-white">
                {{ $heading ?? __('action.no_results.title') }}
            </h3>

            {{-- Description --}}
            <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed max-w-md mx-auto">
                {{ $description ?? __('action.no_results.description') }}
            </p>
        </div>
    </div>
</div>

