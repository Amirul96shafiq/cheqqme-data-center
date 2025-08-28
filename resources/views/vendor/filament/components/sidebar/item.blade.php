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
])

@php
    $sidebarCollapsible = $sidebarCollapsible && filament()->isSidebarCollapsibleOnDesktop();
    // Detect Action Board by URL (adjust as needed)
    $actionBoardRoute = route('filament.admin.pages.action-board', [], false);
    $isActionBoard = ($url === $actionBoardRoute);
    $taskCount = $isActionBoard ? \App\Models\Task::where('status', '!=', 'completed')->count() : null;
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
        {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab) }}
        x-on:click="window.matchMedia(`(max-width: 1024px)`).matches && $store.sidebar.close()"
        @if ($sidebarCollapsible)
            x-data="{ tooltip: false }"
            x-effect="
                tooltip = $store.sidebar.isOpen
                    ? false
                    : true
            "
            x-tooltip="tooltip ? '{{ $active ? '' : ($badgeTooltip ?? $badge) }}' : false"
        @endif
        class="fi-sidebar-item-link {{ $active ? 'fi-active' : '' }}"
        @if ($active) aria-current="page" @endif
    >
        <span class="fi-sidebar-item-icon-wrapper">
            @if ($isActionBoard)
                <span class="relative flex items-center justify-center">
                    {!! $icon !!}
                    @if($taskCount > 0)
                        <span class="absolute top-0 right-0 z-10 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-primary-500 rounded-full shadow -translate-y-1/3 translate-x-1/3"
                              style="min-width:1rem;min-height:1rem;">
                            {{ $taskCount }}
                        </span>
                    @endif
                </span>
            @else
                {!! $icon !!}
            @endif
        </span>
        <span class="fi-sidebar-item-label">
            {{ $slot }}
        </span>
    </a>
</li>
