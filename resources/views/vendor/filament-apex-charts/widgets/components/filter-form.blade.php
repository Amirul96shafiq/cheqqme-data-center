@props(['indicatorsCount', 'width'])

@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<div class="filament-apex-charts-filter-form relative">
    <div class="filament-dropdown-trigger cursor-pointer flex items-center justify-end" aria-expanded="false">
        <button type="button" @click="dropdownOpen = !dropdownOpen"
            class="fi-icon-btn relative flex items-center justify-center rounded-lg outline-none transition duration-75 focus:ring-2 disabled:pointer-events-none disabled:opacity-70 h-9 w-9 text-gray-400 hover:text-gray-500 focus:ring-primary-600 dark:text-gray-500 dark:hover:text-gray-400 dark:focus:ring-primary-500 fi-ac-icon-btn-action"
            title="Filter">

            <span class="sr-only">
                Filter
            </span>

            <x-filament::icon icon="heroicon-s-funnel" class="h-5 w-5" />

            @if ($indicatorsCount > 0)
                <div class="absolute start-full top-0 z-10 -ms-1 -translate-x-1/2 rounded-md bg-white dark:bg-gray-900">
                    <div style="--c-50:var(--primary-50);--c-300:var(--primary-300);--c-400:var(--primary-400);--c-600:var(--primary-600);"
                        class="fi-badge flex items-center justify-center gap-x-1 whitespace-nowrap rounded-md  text-xs font-medium ring-1 ring-inset px-0.5 min-w-[theme(spacing.4)] tracking-tighter bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">

                        <span>
                            {{ $indicatorsCount }}
                        </span>

                    </div>
                </div>
            @endif

        </button>
    </div>

    <div x-show="dropdownOpen" x-cloak @click="dropdownOpen = false" class="fixed inset-0 h-full w-full z-10"></div>

    <div x-show="dropdownOpen" x-cloak @class([
        'absolute mt-2 z-20 w-screen rounded-lg bg-white shadow-xl border border-gray-200 dark:bg-gray-800 dark:border-gray-700 transition',
    ])
        style="{{ match ($width) {
            MaxWidth::ExtraSmall, 'xs' => 'width: 20rem;',
            MaxWidth::Small, 'sm' => 'width: 24rem;',
            MaxWidth::Medium, 'md' => 'width: 28rem;',
            MaxWidth::Large, 'lg' => 'width: 32rem;',
            MaxWidth::ExtraLarge, 'xl' => 'width: 36rem;',
            MaxWidth::TwoExtraLarge, '2xl' => 'width: 42rem;',
            MaxWidth::ThreeExtraLarge, '3xl' => 'width: 48rem;',
            MaxWidth::FourExtraLarge, '4xl' => 'width: 56rem;',
            MaxWidth::FiveExtraLarge, '5xl' => 'width: 64rem;',
            MaxWidth::SixExtraLarge, '6xl' => 'width: 72rem;',
            MaxWidth::SevenExtraLarge, '7xl' => 'width: 80rem;',
            default => $width,
        } }}; right:0">

        <!-- Filter Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('filament-apex-charts::filters.heading') }}</h3>
            <button
                wire:click="resetFiltersForm"
                class="text-xs text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
            >
                {{ __('filament-apex-charts::filters.reset.label') }}
            </button>
        </div>

        <!-- Filter Content -->
        <div class="p-6">
            {{ $slot }}
        </div>

        <!-- Submit Action -->
        <div class="flex justify-end p-6 border-gray-200 dark:border-gray-700">
            <x-filament::link wire:click="submitFiltersForm" color="primary" tag="button" size="sm">
                {{ __('filament-actions::modal.actions.submit.label') }}
            </x-filament::link>
        </div>

    </div>
</div>
