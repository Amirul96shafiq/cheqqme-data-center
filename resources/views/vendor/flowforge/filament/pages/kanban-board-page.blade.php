<x-filament::page>
    <div class="h-[calc(100vh-12.5rem)]">
    @livewire('relaticle.flowforge.kanban-board', [
            'adapter' => $this->getAdapter(),
            'pageClass' => $this::class
        ])
    </div>
</x-filament::page>
