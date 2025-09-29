<x-filament::page>
    {{-- Search Bar --}}
    <div class="-mb-8 px-4">
        <div class="flex items-center gap-2">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('action.search_placeholder') }}"
                    class="w-full"
                />
            </x-filament::input.wrapper>
            @if($search)
                <x-filament::button
                    wire:click="clearSearch"
                    color="gray"
                    size="sm"
                    icon="heroicon-o-x-mark"
                >
                    {{ __('action.clear_search') }}
                </x-filament::button>
            @endif
        </div>
    </div>
    
    <div class="h-[calc(100vh-16rem)]">
    @livewire('relaticle.flowforge.kanban-board', [
            'adapter' => $this->getAdapter(),
            'pageClass' => $this::class
        ])
    </div>
</x-filament::page>
