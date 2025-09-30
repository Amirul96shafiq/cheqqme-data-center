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

    <script>
        // Function to clear kanban search (used by no-results component)
        window.clearKanbanSearch = function() {
            // Find the search input and clear it
            const searchInput = document.querySelector('input[wire\\:model="search"]');
            if (searchInput) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            
            // Also trigger the Livewire clearSearch method if available
            if (window.Livewire) {
                Livewire.dispatch('clearSearch');
            }
        };

        // Test function to manually show no-results component
        window.testNoResults = function() {
            // console.log('🧪 Testing no-results component manually');
            const noResultsComponent = document.getElementById('no-results-component');
            const kanbanBoardContainer = document.getElementById('kanban-board-container');
            
            if (noResultsComponent) {
                noResultsComponent.classList.remove('hidden');
                // console.log('✅ No results component shown manually');
            } else {
                // console.log('❌ No results component not found');
            }
            
            if (kanbanBoardContainer) {
                kanbanBoardContainer.classList.add('hidden');
                // console.log('✅ Kanban board hidden manually');
            } else {
                // console.log('❌ Kanban board container not found');
            }
        };

            // Global event listener for unified filter events (search + assigned to filter)
            document.addEventListener('action-board-unified-filter', function(e) {
                console.log('🎯 UNIFIED FILTER EVENT RECEIVED:', e?.detail);
                var search = e?.detail?.search || '';
                var assignedTo = e?.detail?.assignedTo || [];
                console.log('📊 Filter data:', { search, assignedTo });
                
                // Set global states for Alpine.js
                window.searchActive = search.length > 0;
                window.filterActive = assignedTo.length > 0;
                
                // Wait a bit for DOM to update, then filter cards
                setTimeout(function() {
                    const columns = document.querySelectorAll('.ff-column');
                    let totalVisibleCards = 0;
                    
                    columns.forEach(function(col) {
                        const cards = col.querySelectorAll('.ff-card');
                        let visible = 0;
                        
                        cards.forEach(function(card) {
                            let matchesSearch = true;
                            let matchesAssignedFilter = true;
                            
                            // Check search filter
                            if (search.length > 0) {
                                const titleEl = card.querySelector('.ff-card__title');
                                const title = (titleEl?.textContent || '').toLowerCase();
                                matchesSearch = title.includes(search.toLowerCase());
                            }
                            
                            // Check assigned to filter
                            if (assignedTo.length > 0) {
                                matchesAssignedFilter = false;
                                const assignedElements = card.querySelectorAll('[data-assigned-user-ids]');
                                
                                if (assignedElements.length > 0) {
                                    assignedElements.forEach(function(el) {
                                        const userIds = el.getAttribute('data-assigned-user-ids');
                                        
                                        if (userIds && userIds.trim() !== '') {
                                            const cardUserIds = userIds.split(',');
                                            const hasMatch = cardUserIds.some(function(cardUserId) {
                                                const cardUserIdStr = cardUserId.trim();
                                                const cardUserIdNum = parseInt(cardUserIdStr);
                                                return assignedTo.includes(cardUserIdStr) || assignedTo.includes(cardUserIdNum.toString());
                                            });
                                            if (hasMatch) {
                                                matchesAssignedFilter = true;
                                            }
                                        }
                                    });
                                }
                            }
                            
                            // Card must match BOTH search AND assigned filter (if either is active)
                            const matchesAllFilters = matchesSearch && matchesAssignedFilter;
                            
                            card.style.display = matchesAllFilters ? '' : 'none';
                            if (matchesAllFilters) visible++;
                        });
                        
                        // Find create task button in this column
                        const createButton = col.querySelector('.ff-create-button, [data-create-button], .create-task-button, button[title*="create"], button[title*="Create"], .add-task-btn, .create-button');
                        
                        // Hide/show the entire column and create button based on whether it has visible cards
                        const hasActiveFilters = search.length > 0 || assignedTo.length > 0;
                        if (hasActiveFilters && visible === 0) {
                            col.style.display = 'none';
                            // Hide create button when column is hidden
                            if (createButton) {
                                createButton.style.display = 'none';
                            }
                        } else {
                            col.style.display = '';
                            
                            // Hide/show create task button based on filter state
                            if (createButton) {
                                if (hasActiveFilters) {
                                    createButton.style.display = 'none';
                                } else {
                                    createButton.style.display = '';
                                }
                            }
                        }
                        
                        totalVisibleCards += visible;
                    });
                    
                    // Show/hide no-results component
                    const noResultsComponent = document.getElementById('no-results-component');
                    const kanbanBoardContainer = document.getElementById('kanban-board-container');
                    
                    const hasActiveFilters = search.length > 0 || assignedTo.length > 0;
                    if (hasActiveFilters && totalVisibleCards === 0) {
                        if (noResultsComponent) noResultsComponent.classList.remove('hidden');
                        if (kanbanBoardContainer) kanbanBoardContainer.classList.add('hidden');
                    } else {
                        if (noResultsComponent) noResultsComponent.classList.add('hidden');
                        if (kanbanBoardContainer) kanbanBoardContainer.classList.remove('hidden');
                    }
                }, 100);
            });

        // Enhanced search event listener for no-results functionality
        document.addEventListener('DOMContentLoaded', function() {
            // console.log('🚀 DOM loaded, setting up no-results functionality');
            
            // Check if elements exist immediately
            const noResultsComponent = document.getElementById('no-results-component');
            const kanbanBoardContainer = document.getElementById('kanban-board-container');
            // console.log('📋 Initial DOM check:', { 
            //     noResultsComponent: !!noResultsComponent, 
            //     kanbanBoardContainer: !!kanbanBoardContainer,
            //     noResultsVisible: noResultsComponent ? !noResultsComponent.classList.contains('hidden') : 'not found'
            // });
            
            
            // Unified filtering system handles both search and assigned to filter
            // The 'action-board-unified-filter' event listener above handles all filtering logic
        });
    </script>
</x-filament::page>
