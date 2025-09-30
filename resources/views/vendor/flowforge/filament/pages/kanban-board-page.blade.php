<x-filament::page>
    
    {{-- Custom Kanban Search Bar --}}
    <x-kanban-search-filter
        :search="$search"
        :placeholder="__('action.search_placeholder')"
        :clear-label="__('action.clear_search')"
        wire-model="search"
        wire-clear="clearSearch"
        :show-filter="true"
        :assigned-to-filter="$assignedToFilter"
    />
    
    <div class="h-[calc(100vh-16rem)]">
        {{-- No Results Component (initially hidden) --}}
        <div id="no-results-component" class="hidden">
            <x-no-results-found 
                :searchTerm="$this->search"
                icon="heroicon-o-magnifying-glass"
            />
        </div>
        
        {{-- Kanban Board --}}
        <div id="kanban-board-container">
            @livewire('relaticle.flowforge.kanban-board', [
                'adapter' => $this->getAdapter(),
                'pageClass' => $this::class
            ])
        </div>
    </div>

    @vite(['resources/js/kanban-alpine.js', 'resources/css/kanban-drag-drop.css'])
    
    <script>
        // Initialize Kanban functionality when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            // Function to clear kanban search (used by no-results component)
            window.clearKanbanSearch = function() {
                // Find the search input and clear it
                const searchInput = document.querySelector('input[x-model="globalSearch"]');
                if (searchInput) {
                    searchInput.value = '';
                    
                    // Dispatch input event to trigger Alpine.js
                    searchInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            };
        });
    </script>
</x-filament::page>
