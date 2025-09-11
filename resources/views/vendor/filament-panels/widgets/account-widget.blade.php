@php
    $user = filament()->auth()->user();
@endphp

<x-filament-widgets::widget class="fi-account-widget">
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2 class="flex-1 text-base font-medium leading-6 text-gray-950 dark:text-white">{!! __('dashboard.widgets.welcome_back', ['name' => '<span class="font-extrabold text-primary-600 dark:text-primary-400">' . filament()->getUserName($user) . '</span>']) !!}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('dashboard.widgets.dashboard_subtitle') }}</p>
            </div>

            {{-- <form
                action="{{ filament()->getLogoutUrl() }}"
                method="post"
                class="my-auto"
            >
                @csrf

                <x-filament::button
                    color="gray"
                    icon="heroicon-m-arrow-left-on-rectangle"
                    icon-alias="panels::widgets.account.logout-button"
                    labeled-from="sm"
                    tag="button"
                    type="submit"
                >
                    {{ __('filament-panels::widgets/account-widget.actions.logout.label') }}
                </x-filament::button>
            </form> --}}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
