@php
    $plugin = filament('awcodes/light-switch');
    $alignment = $plugin->getPosition()->value;
@endphp

@if (
    filament()->hasDarkMode() &&
    (! filament()->hasDarkModeForced()) &&
    $plugin->shouldShowSwitcher()
)
    <div @class([
        'auth-theme-switcher fixed w-full flex p-4 z-40',
        'top-0' => str_contains($alignment, 'top'),
        'bottom-0' => str_contains($alignment, 'bottom'),
        'justify-start' => str_contains($alignment, 'left'),
        'justify-end' => str_contains($alignment, 'right'),
        'justify-center' => str_contains($alignment, 'center'),
    ])>
        <div class="rounded-lg bg-gray-50/65 dark:bg-gray-900/65 border border-gray-100 dark:border-gray-800/50">
            <div
                x-data="{
                    theme: null,

                    init: function () {
                        this.theme = localStorage.getItem('theme') || 'system'

                        $dispatch('theme-changed', theme)

                        $watch('theme', (theme) => {
                            $dispatch('theme-changed', theme)
                        })
                    },
                }"
                class="fi-theme-switcher grid grid-flow-col gap-x-1"
            >
                <x-light-switch::button
                    icon="heroicon-m-sun"
                    theme="light"
                />

                <x-light-switch::button
                    icon="heroicon-m-moon"
                    theme="dark"
                />

                <x-light-switch::button
                    icon="heroicon-m-computer-desktop"
                    theme="system"
                />
            </div>
        </div>
    </div>
@endif

