@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Facades\FilamentView;

    // The closures are available directly when Component renders the view
    $chartColor = $getChartColor() ?? 'gray';
    $descriptionColor = $getDescriptionColor() ?? 'gray';
    $descriptionIcon = $getDescriptionIcon();
    $descriptionIconPosition = $getDescriptionIconPosition();
    $url = $getUrl();
    $tag = $url ? 'a' : 'div';
    $dataChecksum = $generateDataChecksum();
    $chart = $getChart();

    $descriptionIconClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-wi-stats-overview-stat-description-icon h-5 w-5',
        match ($descriptionColor) {
            'gray' => 'text-gray-400 dark:text-gray-500',
            default => 'text-custom-500',
        },
    ]);

    $descriptionIconStyles = \Illuminate\Support\Arr::toCssStyles([
        \Filament\Support\get_color_css_variables(
            $descriptionColor,
            shades: [500],
            alias: 'widgets::stats-overview-widget.stat.description.icon',
        ) => $descriptionColor !== 'gray',
    ]);
@endphp

<{!! $tag !!}
    @if ($url)
        {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab()) }}
    @endif
    {{
        $getExtraAttributeBag()
            ->class([
                'fi-wi-stats-overview-stat relative rounded-xl bg-white/65 p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900/65 dark:ring-white/10 backdrop-blur-sm transition-all duration-200 hover:bg-white/80 hover:shadow-md hover:ring-gray-950/10 dark:hover:bg-gray-900/80 dark:hover:ring-white/20',
            ])
    }}
>
    {{-- Cycle Action Button --}}
    <button
        type="button"
        wire:click.prevent.stop="toggleView"
        @click.prevent.stop
        class="absolute top-2 right-2 z-10 p-1.5 rounded-md text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        title="{{ __('dashboard.actions.toggle_view') }}"
    >
        <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            wire:loading.class="animate-spin"
            wire:target="toggleView"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
    </button>

    <div class="grid gap-y-2">
        <div class="flex items-center gap-x-2">
            @if ($icon = $getIcon())
                <x-filament::icon
                    :icon="$icon"
                    class="fi-wi-stats-overview-stat-icon h-5 w-5 text-gray-400 dark:text-gray-500"
                />
            @endif

            <span
                class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-500 dark:text-gray-400"
            >
                {{ $getLabel() }}
            </span>
        </div>

        <div
            class="fi-wi-stats-overview-stat-value text-3xl font-semibold tracking-tight text-gray-950 dark:text-white"
        >
            {{ $getValue() }}
        </div>

        @if ($description = $getDescription())
            <div class="flex items-center gap-x-1">
                @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::Before, 'before']))
                    <x-filament::icon
                        :icon="$descriptionIcon"
                        :class="$descriptionIconClasses"
                        :style="$descriptionIconStyles"
                    />
                @endif

                <span
                    @class([
                        'fi-wi-stats-overview-stat-description text-sm',
                        match ($descriptionColor) {
                            'gray' => 'text-gray-500 dark:text-gray-400',
                            default => 'fi-color-custom text-custom-600 dark:text-custom-400',
                        },
                        is_string($descriptionColor) ? "fi-color-{$descriptionColor}" : null,
                    ])
                    @style([
                        \Filament\Support\get_color_css_variables(
                            $descriptionColor,
                            shades: [400, 600],
                            alias: 'widgets::stats-overview-widget.stat.description',
                        ) => $descriptionColor !== 'gray',
                    ])
                >
                    {{ $description }}
                </span>

                @if ($descriptionIcon && in_array($descriptionIconPosition, [IconPosition::After, 'after']))
                    <x-filament::icon
                        :icon="$descriptionIcon"
                        :class="$descriptionIconClasses"
                        :style="$descriptionIconStyles"
                    />
                @endif
            </div>
        @endif
    </div>

    @if ($chart)
        {{-- An empty function to initialize the Alpine component with until it's loaded with `x-load`. This removes the need for `x-ignore`, allowing the chart to be updated via Livewire polling. --}}
        <div x-data="{ statsOverviewStatChart: function () {} }">
            <div
                @if (FilamentView::hasSpaMode())
                    x-load="visible"
                @else
                    x-load
                @endif
                x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('stats-overview/stat/chart', 'filament/widgets') }}"
                x-data="statsOverviewStatChart({
                            dataChecksum: @js($dataChecksum),
                            labels: @js(array_keys($chart)),
                            values: @js(array_values($chart)),
                        })"
                @class([
                    'fi-wi-stats-overview-stat-chart absolute inset-x-0 bottom-0 overflow-hidden rounded-b-xl',
                    match ($chartColor) {
                        'gray' => null,
                        default => 'fi-color-custom',
                    },
                    is_string($chartColor) ? "fi-color-{$chartColor}" : null,
                ])
                @style([
                    \Filament\Support\get_color_css_variables(
                        $chartColor,
                        shades: [50, 400, 500],
                        alias: 'widgets::stats-overview-widget.stat.chart',
                    ) => $chartColor !== 'gray',
                ])
            >
                <canvas x-ref="canvas" class="h-6"></canvas>

                <span
                    x-ref="backgroundColorElement"
                    @class([
                        match ($chartColor) {
                            'gray' => 'text-gray-100 dark:text-gray-800',
                            default => 'text-custom-50 dark:text-custom-400/10',
                        },
                    ])
                ></span>

                <span
                    x-ref="borderColorElement"
                    @class([
                        match ($chartColor) {
                            'gray' => 'text-gray-400',
                            default => 'text-custom-500 dark:text-custom-400',
                        },
                    ])
                ></span>
            </div>
        </div>
    @endif
</{!! $tag !!}>

