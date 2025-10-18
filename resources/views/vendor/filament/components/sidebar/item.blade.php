@props([
    'active' => false,
    'activeChildItems' => false,
    'activeIcon' => null,
    'badge' => null,
    'badgeColor' => null,
    'badgeTooltip' => null,
    'childItems' => [],
    'first' => false,
    'grouped' => false,
    'icon' => null,
    'last' => false,
    'shouldOpenUrlInNewTab' => false,
    'sidebarCollapsible' => true,
    'subGrouped' => false,
    'url',
    'dropdownItems' => [],
    'dropdownTitle' => null,
])

@php
    $sidebarCollapsible = $sidebarCollapsible && filament()->isSidebarCollapsibleOnDesktop();
    
    // Check if this item has a dropdown
    $hasDropdown = !empty($dropdownItems);
@endphp

<li
    {{
        $attributes->class([
            'fi-sidebar-item',
            'fi-active fi-sidebar-item-active' => $active,
            'flex flex-col gap-y-1' => $active || $activeChildItems,
        ])
    }}
>
    <div 
        @if ($hasDropdown && $sidebarCollapsible)
            x-data="{ 
                showDropdown: false,
                timeout: null,
                enter() {
                    clearTimeout(this.timeout);
                    this.showDropdown = true;
                },
                leave() {
                    this.timeout = setTimeout(() => {
                        this.showDropdown = false;
                    }, 150);
                }
            }"
            @mouseenter="enter()"
            @mouseleave="leave()"
            class="relative"
        @endif
    >
        <a
        {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab) }}
        x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
        @if ($sidebarCollapsible)
            x-data="{ tooltip: false }"
            x-effect="
                tooltip = $store.sidebar.isOpen
                    ? false
                    : {
                          content: @js($slot->toHtml()),
                          placement: document.dir === 'rtl' ? 'left' : 'right',
                          theme: $store.theme,
                      }
            "
            x-tooltip.html="tooltip"
        @endif
        @class([
            'fi-sidebar-item-button relative flex items-center justify-center gap-x-3 rounded-lg px-2 py-2 outline-none transition duration-75',
            'hover:bg-gray-100 focus-visible:bg-gray-100 dark:hover:bg-white/10 dark:focus-visible:bg-white/5' => filled($url),
            'bg-gray-100 dark:bg-white/5' => $active,
        ])
    >
        @if (filled($icon) && ((! $subGrouped) || $sidebarCollapsible))
            <span class="relative inline-block">
                <x-filament::icon
                    :icon="($active && $activeIcon) ? $activeIcon : $icon"
                    :x-show="($subGrouped && $sidebarCollapsible) ? '! $store.sidebar.isOpen' : false"
                    @class([
                        'fi-sidebar-item-icon h-6 w-6',
                        'text-gray-400 dark:text-gray-500' => ! $active,
                        'text-primary-600 dark:text-primary-400' => $active,
                    ])
                />
            </span>
        @endif

        @if ((blank($icon) && $grouped) || $subGrouped)
            <div
                @if (filled($icon) && $subGrouped && $sidebarCollapsible)
                    x-show="$store.sidebar.isOpen"
                @endif
                class="fi-sidebar-item-grouped-border relative flex h-6 w-6 items-center justify-center"
            >
                @if (! $first)
                    <div
                        class="absolute -top-1/2 bottom-1/2 w-px bg-gray-300 dark:bg-gray-600"
                    ></div>
                @endif

                @if (! $last)
                    <div
                        class="absolute -bottom-1/2 top-1/2 w-px bg-gray-300 dark:bg-gray-600"
                    ></div>
                @endif

                <div
                    @class([
                        'relative h-1.5 w-1.5 rounded-full',
                        'bg-gray-400 dark:bg-gray-500' => ! $active,
                        'bg-primary-600 dark:bg-primary-400' => $active,
                    ])
                ></div>
            </div>
        @endif

        <span
            @if ($sidebarCollapsible)
                x-show="$store.sidebar.isOpen"
                x-transition:enter="lg:transition lg:delay-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
            @endif
            @class([
                'fi-sidebar-item-label flex-1 truncate text-sm font-medium',
                'text-gray-700 dark:text-gray-200' => ! $active,
                'text-primary-600 dark:text-primary-400' => $active,
            ])
        >
            {{ $slot }}
        </span>

        @if (filled($badge))
            <span
                @if ($sidebarCollapsible)
                    x-show="$store.sidebar.isOpen"
                    x-transition:enter="lg:transition lg:delay-100"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                @endif
            >
                <x-filament::badge
                    :color="$badgeColor"
                    :tooltip="$badgeTooltip"
                >
                    {{ $badge }}
                </x-filament::badge>
            </span>
        @endif

        @if ($hasDropdown && $sidebarCollapsible)
            <span
                x-show="$store.sidebar.isOpen"
                x-transition:enter="lg:transition lg:delay-100"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fi-sidebar-item-dropdown-arrow"
            >
                <x-filament::icon
                    icon="heroicon-m-chevron-right"
                    class="h-4 w-4 text-gray-400 dark:text-gray-500"
                />
            </span>
        @endif
    </a>

    @if ($hasDropdown && $sidebarCollapsible)
        <!-- Dropdown Menu -->
        <div
            x-show="showDropdown"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            x-show="$store.sidebar.isOpen && showDropdown"
            class="absolute left-full top-0 z-50 ml-2 w-64 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            style="display: none;"
        >
            <div class="py-2">
                @if ($dropdownTitle)
                    <div class="px-4 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        {{ $dropdownTitle }}
                    </div>
                @endif
                
                @foreach ($dropdownItems as $dropdownItem)
                    <a
                        href="{{ $dropdownItem['url'] ?? '#' }}"
                        @if ($dropdownItem['new_tab'] ?? false)
                            target="_blank"
                        @endif
                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors duration-150"
                    >
                        @if (isset($dropdownItem['icon']))
                            <x-filament::icon
                                :icon="$dropdownItem['icon']"
                                class="h-4 w-4 text-gray-400 dark:text-gray-500"
                            />
                        @endif
                        <span>{{ $dropdownItem['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
    </div>

    @if (($active || $activeChildItems) && $childItems)
        <ul class="fi-sidebar-sub-group-items flex flex-col gap-y-1">
            @foreach ($childItems as $childItem)
                <x-filament-panels::sidebar.item
                    :active="$childItem->isActive()"
                    :active-child-items="$childItem->isChildItemsActive()"
                    :active-icon="$childItem->getActiveIcon()"
                    :badge="$childItem->getBadge()"
                    :badge-color="$childItem->getBadgeColor()"
                    :badge-tooltip="$childItem->getBadgeTooltip()"
                    :first="$loop->first"
                    grouped
                    :icon="$childItem->getIcon()"
                    :last="$loop->last"
                    :should-open-url-in-new-tab="$childItem->shouldOpenUrlInNewTab()"
                    sub-grouped
                    :url="$childItem->getUrl()"
                >
                    {{ $childItem->getLabel() }}
                </x-filament-panels::sidebar.item>
            @endforeach
        </ul>
    @endif
</li>
