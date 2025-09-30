<x-filament::page>
    {{-- Custom Kanban Search Bar --}}
    <x-kanban-search-bar
        :search="$search"
        :placeholder="__('action.search_placeholder')"
        :clear-label="__('action.clear_search')"
        wire-model="search"
        wire-clear="clearSearch"
    />
    
    <div class="h-[calc(100vh-16rem)]">
    @livewire('relaticle.flowforge.kanban-board', [
            'adapter' => $this->getAdapter(),
            'pageClass' => $this::class
        ])
    </div>
</x-filament::page>
