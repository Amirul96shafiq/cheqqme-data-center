{{-- Override for Filament navigation item: overlays badge on icon for Action Board --}}

@php
    // Try to match Action Board by route name (adjust as needed)
    $actionBoardRoute = route('filament.admin.pages.action-board', [], false);
    $isActionBoard = ($item->getUrl() === $actionBoardRoute);
    $taskCount = $isActionBoard ? \App\Models\Task::where('status', '!=', 'completed')->count() : null;
@endphp

<li {{ $attributes->class(['fi-sidebar-nav-item']) }}>
    <a
        href="{{ $item->getUrl() }}"
        @if ($item->isActive()) aria-current="page" @endif
        class="fi-sidebar-nav-item-link {{ $item->isActive() ? 'fi-active' : '' }}"
        @if ($item->getTarget()) target="{{ $item->getTarget() }}" @endif
    >
        <span class="fi-sidebar-nav-item-icon-wrapper">
            @if ($isActionBoard)
                <span class="relative flex items-center justify-center">
                    <x-heroicon-o-rocket-launch class="w-6 h-6" />
                    @if($taskCount > 0)
                        <span class="absolute top-0 right-0 z-10 flex items-center justify-center w-4 h-4 text-[10px] font-bold text-white bg-primary-500 rounded-full shadow -translate-y-1/3 translate-x-1/3"
                              style="min-width:1rem;min-height:1rem;">
                            {{ $taskCount }}
                        </span>
                    @endif
                </span>
            @else
                {!! $item->getIcon() !!}
            @endif
        </span>
        <span class="fi-sidebar-nav-item-label">
            {{ $item->getLabel() }}
        </span>
    </a>
</li>
