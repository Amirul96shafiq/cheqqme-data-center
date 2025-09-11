@php
    $languageSwitch = \BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch::make();
    $locales = $languageSwitch->getLocales();
    $isCircular = $languageSwitch->isCircular();
    $isFlagsOnly = $languageSwitch->isFlagsOnly();
    $hasFlags = filled($languageSwitch->getFlags());
    $isVisibleOutsidePanels = $languageSwitch->isVisibleOutsidePanels();
    $outsidePanelsPlacement = $languageSwitch->getOutsidePanelPlacement()->value;
    $placement = match(true){
        $outsidePanelsPlacement === 'top-center' && $isFlagsOnly => 'bottom',
        $outsidePanelsPlacement === 'bottom-center' && $isFlagsOnly => 'top',
        ! $isVisibleOutsidePanels && $isFlagsOnly=> 'bottom',
        default => 'bottom-end',
    };
    $maxHeight = $languageSwitch->getMaxHeight();
@endphp
<div>
    <style>
        .flags-only {
            max-width: 3rem !important;
        }

        .fls-dropdown-width {
            max-width: fit-content !important;
        }

        .fls-display-on {
            position: fixed !important;
            left: 50% !important;
            bottom: 1rem !important;
            transform: translateX(-50%) !important;
            z-index: 9999 !important;
        }
    </style>

    @if (request()->is('admin/login'))
        <style>
            /* Fix dropdown alignment */
            .fi-dropdown-panel {
                left: 50% !important;
                transform: translateX(-50%) !important;
            }
        </style>
    @endif

    @if ($isVisibleOutsidePanels)
        <div class="fls-display-on">
            <div class="rounded-lg bg-gray-50/50 dark:bg-gray-950/50 backdrop-blur-md">
                @include('filament-language-switch::switch')
            </div>
        </div>
    @else
        @include('filament-language-switch::switch')
    @endif
</div>