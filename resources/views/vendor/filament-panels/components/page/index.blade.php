@props([
    'fullHeight' => false,
])

@php
    use Filament\Pages\SubNavigationPosition;

    $subNavigation = $this->getCachedSubNavigation();
    $subNavigationPosition = $this->getSubNavigationPosition();
    $widgetData = $this->getWidgetData();
@endphp

<div
    {{
        $attributes->class([
            'fi-page',
            'min-h-[65vh] max-h-[65vh] h-auto',
        ])
    }}
>
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_START, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

    <section
        @class([
            'flex flex-col gap-y-8 py-8',
            'min-h-[65vh] max-h-[65vh] h-auto',
        ])
    >
        @if ($header = $this->getHeader())
            {{ $header }}
        @elseif ($heading = $this->getHeading())
            @php
                $subheading = $this->getSubheading();
            @endphp

            <x-filament-panels::header
                :actions="$this->getCachedHeaderActions()"
                :breadcrumbs="filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : []"
                :heading="$heading"
                :subheading="$subheading"
            >
                @if ($heading instanceof \Illuminate\Contracts\Support\Htmlable)
                    <x-slot name="heading">
                        {{ $heading }}
                    </x-slot>
                @endif

                @if ($subheading instanceof \Illuminate\Contracts\Support\Htmlable)
                    <x-slot name="subheading">
                        {{ $subheading }}
                    </x-slot>
                @endif
            </x-filament-panels::header>
        @endif

        <div
            @class([
                'flex flex-col gap-8' => $subNavigation,
                match ($subNavigationPosition) {
                    SubNavigationPosition::Start, SubNavigationPosition::End => 'md:flex-row md:items-start',
                    default => null,
                } => $subNavigation,
                'min-h-[65vh] max-h-[65vh] h-auto',
            ])
        >
            @if ($subNavigation)
                <div class="contents md:hidden">
                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_SELECT_BEFORE, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}
                </div>

                <x-filament-panels::page.sub-navigation.select
                    :navigation="$subNavigation"
                />

                <div class="contents md:hidden">
                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_SELECT_AFTER, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}
                </div>

                @if ($subNavigationPosition === SubNavigationPosition::Start)
                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_START_BEFORE, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

                    <x-filament-panels::page.sub-navigation.sidebar
                        :navigation="$subNavigation"
                    />

                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_START_AFTER, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}
                @endif

                @if ($subNavigationPosition === SubNavigationPosition::Top)
                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_BEFORE, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

                    <x-filament-panels::page.sub-navigation.tabs
                        :navigation="$subNavigation"
                    />

                    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_TOP_AFTER, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}
                @endif
            @endif

            <div
                @class([
                    'grid flex-1 auto-cols-fr gap-y-8',
                    'min-h-[65vh] max-h-[65vh] h-auto',
                ])
            >
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_WIDGETS_BEFORE, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

                @if ($headerWidgets = $this->getVisibleHeaderWidgets())
                    <x-filament-widgets::widgets
                        :columns="$this->getHeaderWidgetsColumns()"
                        :data="$widgetData"
                        :widgets="$headerWidgets"
                        class="fi-page-header-widgets"
                    />
                @endif

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_WIDGETS_AFTER, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

                {{ $slot }}

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_FOOTER_WIDGETS_BEFORE, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

                @if ($footerWidgets = $this->getVisibleFooterWidgets())
                    <x-filament-widgets::widgets
                        :columns="$this->getFooterWidgetsColumns()"
                        :data="$widgetData"
                        :widgets="$footerWidgets"
                        class="fi-page-footer-widgets"
                    />
                @endif

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_FOOTER_WIDGETS_AFTER, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}
            </div>

            @if ($subNavigation && $subNavigationPosition === SubNavigationPosition::End)
                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_END_BEFORE, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

                <x-filament-panels::page.sub-navigation.sidebar
                    :navigation="$subNavigation"
                />

                {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_SUB_NAVIGATION_END_AFTER, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}
            @endif
        </div>

        @if ($footer = $this->getFooter())
            {{ $footer }}
        @endif
    </section>

        @if (! ($this instanceof \Filament\Tables\Contracts\HasTable))
        <x-filament-actions::modals />
    @elseif (
        method_exists($this, 'isTableLoaded')
        && $this->isTableLoaded()
    )
        @php
            $componentProperties = get_object_vars($this);
            $defaultTableAction = $componentProperties['defaultTableAction'] ?? null;
            $defaultTableActionRecord = $componentProperties['defaultTableActionRecord'] ?? null;
            $defaultTableActionArguments = $componentProperties['defaultTableActionArguments'] ?? null;
            $hasDefaultTableAction = filled($defaultTableAction);
        @endphp

        @if ($hasDefaultTableAction)
            <div
                wire:init="mountTableAction(@js($defaultTableAction), @if ($defaultTableActionRecord) @js($defaultTableActionRecord) @else {{ 'null' }} @endif @if ($defaultTableActionArguments) , @js($defaultTableActionArguments) @endif)"
            ></div>
        @endif
    @endif

    @php
        $componentProperties = isset($componentProperties) ? $componentProperties : get_object_vars($this);
        $defaultAction = $componentProperties['defaultAction'] ?? null;
        $defaultActionArguments = $componentProperties['defaultActionArguments'] ?? null;
        $hasDefaultAction = filled($defaultAction);
    @endphp

    @if ($hasDefaultAction)
        <div
            wire:init="mountAction(@js($defaultAction) @if ($defaultActionArguments) , @js($defaultActionArguments) @endif)"
        ></div>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_END, scopes: method_exists($this, 'getRenderHookScopes') ? $this->getRenderHookScopes() : []) }}

    <x-filament-panels::unsaved-action-changes-alert />
</div>
