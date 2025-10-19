@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <h2 class="flex-1 text-base font-medium leading-6 text-gray-950 dark:text-white">{!! __('dashboard.widgets.welcome_back', ['name' => '<span class="font-extrabold text-primary-600 dark:text-primary-400">' . filament()->getUserName($user) . '</span>']) !!}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.widgets.dashboard_subtitle') }}</p>
            </div>

            <div class="my-auto">
                <x-filament::button
                    color="primary"
                    icon="heroicon-o-calendar-days"
                    icon-alias="panels::widgets.account.calendar-button"
                    labeled-from="sm"
                    tag="button"
                    onclick="if (window.showGlobalModal) { window.showGlobalModal('calendar'); }"
                >
                    {{ __('dashboard.widgets.view_calendar') }}
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
