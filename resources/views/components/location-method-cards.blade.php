@props([
    'selectedMethod' => 'picker',
    'urlLabel' => 'URL',
    'pickerLabel' => 'Map Picker',
    'urlDescription' => 'Paste a Google Maps share URL to automatically fill location details',
    'pickerDescription' => 'Use an interactive map to search and select your location'
])

<div
    x-data="{
        selected: @entangle('data.location_method').live || '{{ $selectedMethod }}',
        selectMethod(method) {
            this.selected = method;
            // Update Livewire form data directly
            $wire.set('data.location_method', method);
        }
    }"
    class="grid grid-cols-1 md:grid-cols-2 gap-4"
>
    {{-- URL Method Card --}}
    <div
        @click="selectMethod('url')"
        :class="selected === 'url'
            ? 'bg-primary-50 dark:bg-primary-950/50 border-primary-600 dark:border-primary-600'
            : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50/30 dark:hover:bg-primary-950/20'"
        class="relative cursor-pointer rounded-xl border bg-white dark:bg-gray-800 p-6 transition-all duration-200"
    >
        <!-- Selection indicator -->
        <div
            :class="selected === 'url'
                ? 'bg-primary-600 border-primary-600'
                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'"
            class="absolute top-4 left-4 flex h-6 w-6 items-center justify-center rounded-full border transition-colors duration-200"
        >
            <svg
                x-show="selected === 'url'"
                class="h-4 w-4 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
        </div>

        <div class="flex flex-col items-center text-center gap-4">
            <!-- Icon -->
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30">
                <x-filament::icon
                    icon="heroicon-m-link"
                    class="h-6 w-6 text-primary-600 dark:text-primary-400"
                />
            </div>

            <!-- Content -->
            <div class="space-y-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $urlLabel }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    {{ $urlDescription }}
                </p>
            </div>
        </div>
    </div>

    {{-- Map Picker Method Card --}}
    <div
        @click="selectMethod('picker')"
        :class="selected === 'picker'
            ? 'bg-primary-50 dark:bg-primary-950/50 border-primary-600 dark:border-primary-600'
            : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-600 hover:bg-primary-50/30 dark:hover:bg-primary-950/20'"
        class="relative cursor-pointer rounded-xl border bg-white dark:bg-gray-800 p-6 transition-all duration-200"
    >
        <!-- Selection indicator -->
        <div
            :class="selected === 'picker'
                ? 'bg-primary-600 border-primary-600'
                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'"
            class="absolute top-4 left-4 flex h-6 w-6 items-center justify-center rounded-full border transition-colors duration-200"
        >
            <svg
                x-show="selected === 'picker'"
                class="h-4 w-4 text-white"
                fill="currentColor"
                viewBox="0 0 20 20"
            >
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
            </svg>
        </div>

        <div class="flex flex-col items-center text-center gap-4">
            <!-- Icon -->
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-primary-100 dark:bg-primary-900/30">
                <x-filament::icon
                    icon="heroicon-m-map-pin"
                    class="h-6 w-6 text-primary-600 dark:text-primary-400"
                />
            </div>

            <!-- Content -->
            <div class="space-y-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $pickerLabel }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    {{ $pickerDescription }}
                </p>
            </div>
        </div>
    </div>
</div>
