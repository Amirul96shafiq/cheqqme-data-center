<x-filament::page>
    {{-- Custom Kanban Search Bar --}}
    <x-kanban-search-bar
        :search="$search"
        :placeholder="__('action.search_placeholder')"
        :clear-label="__('action.clear_search')"
        wire-model="search"
        wire-clear="clearSearch"
        :show-filter="true"
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

        // Global event listener for filter events
        document.addEventListener('action-board-filter', function(e) {
            console.log('🎯 GLOBAL EVENT LISTENER TRIGGERED!', e.detail);
            
            var assignedTo = e?.detail?.assignedTo || [];
            
            // Set global filter state for Alpine.js
            window.filterActive = assignedTo.length > 0;
            
            // Wait a bit for DOM to update, then check
            setTimeout(function() {
                const columns = document.querySelectorAll('.ff-column');
                let totalVisibleCards = 0;
                
                console.log('📊 Found columns:', columns.length);
                
                columns.forEach(function(col, index) {
                    const cards = col.querySelectorAll('.ff-card');
                    let visible = 0;
                    cards.forEach(function(card) {
                        // Check if card matches assigned_to filter
                        let matchesFilter = true;
                        if (assignedTo.length > 0) {
                            matchesFilter = false;
                            // Check if any of the assigned users match the filter
                            const assignedElements = card.querySelectorAll('[data-assigned-user-ids]');
                            console.log('Card assigned elements:', assignedElements.length, 'Filter:', assignedTo);
                            
                            if (assignedElements.length === 0) {
                                // Card has no assigned users - hide it when filtering
                                console.log('Card has no assigned users - hiding');
                                matchesFilter = false;
                            } else {
                                    assignedElements.forEach(function(el) {
                                        const userIds = el.getAttribute('data-assigned-user-ids');
                                        const debugAssignedTo = el.getAttribute('data-debug-assigned-to');
                                        const debugAllAssigned = el.getAttribute('data-debug-all-assigned');
                                        const debugRecordKeys = el.getAttribute('data-debug-record-keys');
                                        const debugTaskId = el.getAttribute('data-debug-task-id');
                                        const debugRawAssigned = el.getAttribute('data-debug-raw-assigned');
                                        const debugAssignedSelf = el.getAttribute('data-debug-assigned-self');
                                        const debugAssignedUsername = el.getAttribute('data-debug-assigned-username');
                                        const debugAssignedFull = el.getAttribute('data-debug-assigned-full');
                                        console.log('Task ID:', debugTaskId, 'Card userIds:', userIds, 'Debug assigned_to:', debugAssignedTo, 'Debug raw assigned:', debugRawAssigned, 'Debug all_assigned:', debugAllAssigned, 'Debug assigned_self:', debugAssignedSelf, 'Debug assigned_username:', debugAssignedUsername, 'Debug assigned_full:', debugAssignedFull, 'Filter:', assignedTo);
                                    if (userIds && userIds.trim() !== '') {
                                        const cardUserIds = userIds.split(',');
                                        // Check if any of the card's assigned users are in the filter
                                        const hasMatch = cardUserIds.some(function(cardUserId) {
                                            const cardUserIdStr = cardUserId.trim();
                                            const cardUserIdNum = parseInt(cardUserIdStr);
                                            const match = assignedTo.includes(cardUserIdStr) || assignedTo.includes(cardUserIdNum.toString());
                                            console.log('Checking cardUserId:', cardUserIdStr, 'as string and number:', cardUserIdNum, 'in filter:', assignedTo, 'match:', match);
                                            return match;
                                        });
                                        if (hasMatch) {
                                            matchesFilter = true;
                                        }
                                    } else {
                                        // Card has assigned elements but no user IDs - hide it when filtering
                                        console.log('Card has assigned elements but no user IDs - hiding');
                                        matchesFilter = false;
                                    }
                                });
                            }
                        }
                        
                        card.style.display = matchesFilter ? '' : 'none';
                        if (matchesFilter) visible++;
                    });
                    
                    console.log(`Column ${index}: ${visible} visible cards`);
                    totalVisibleCards += visible;
                });
                
                console.log('🎯 Total visible cards:', totalVisibleCards);
                console.log('🔍 Filter assignedTo:', assignedTo);
                
                // Show/hide no-results component
                const noResultsComponent = document.getElementById('no-results-component');
                const kanbanBoardContainer = document.getElementById('kanban-board-container');
                
                if (assignedTo.length > 0 && totalVisibleCards === 0) {
                    // Show no-results component when filtering but no cards match
                    console.log('✅ SHOWING NO-RESULTS COMPONENT');
                    if (noResultsComponent) {
                        noResultsComponent.classList.remove('hidden');
                        console.log('🎉 No results component shown');
                    }
                    if (kanbanBoardContainer) {
                        kanbanBoardContainer.classList.add('hidden');
                        console.log('🙈 Kanban board hidden');
                    }
                } else {
                    // Show kanban board when there are results or no filter
                    console.log('✅ SHOWING KANBAN BOARD');
                    if (noResultsComponent) {
                        noResultsComponent.classList.add('hidden');
                        console.log('🙈 No results component hidden');
                    }
                    if (kanbanBoardContainer) {
                        kanbanBoardContainer.classList.remove('hidden');
                        console.log('🎉 Kanban board shown');
                    }
                }
            }, 100); // Small delay to ensure DOM is updated
        });

        // Enhanced search event listener for no-results functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🚀 DOM loaded, setting up no-results functionality');
            
            // Check if elements exist immediately
            const noResultsComponent = document.getElementById('no-results-component');
            const kanbanBoardContainer = document.getElementById('kanban-board-container');
            // console.log('📋 Initial DOM check:', { 
            //     noResultsComponent: !!noResultsComponent, 
            //     kanbanBoardContainer: !!kanbanBoardContainer,
            //     noResultsVisible: noResultsComponent ? !noResultsComponent.classList.contains('hidden') : 'not found'
            // });
            
            
            // Listen for action-board-search events
            document.addEventListener('action-board-search', function(e) {
                // console.log('🔍 Search event triggered!', { 
                //     detail: e?.detail, 
                //     search: e?.detail?.search,
                //     timestamp: new Date().toISOString()
                // });
                
                var term = (e?.detail?.search || '').toLowerCase();
                
                // Set global search state for Alpine.js
                window.searchActive = term.length > 0;
                
                // Wait a bit for DOM to update, then check
                setTimeout(function() {
                    const columns = document.querySelectorAll('.ff-column');
                    let totalVisibleCards = 0;
                    
                    // console.log('📊 Found columns:', columns.length);
                    
                    columns.forEach(function(col, index) {
                        const cards = col.querySelectorAll('.ff-card');
                        let visible = 0;
                        cards.forEach(function(card) {
                            const titleEl = card.querySelector('.ff-card__title');
                            const title = (titleEl?.textContent || '').toLowerCase();
                            const match = !term || title.includes(term);
                            card.style.display = match ? '' : 'none';
                            if (match) visible++;
                        });
                        
                        // console.log(`Column ${index}: ${visible} visible cards`);
                        totalVisibleCards += visible;
                    });
                    
                    // console.log('🎯 Total visible cards:', totalVisibleCards);
                    // console.log('🔍 Search term:', term);
                    // console.log('📏 Term length:', term.length);
                    
                    // Show/hide no-results component
                    const noResultsComponent = document.getElementById('no-results-component');
                    const kanbanBoardContainer = document.getElementById('kanban-board-container');
                    
                    // console.log('🎭 DOM elements found:', { 
                    //     noResultsComponent: !!noResultsComponent, 
                    //     kanbanBoardContainer: !!kanbanBoardContainer 
                    // });
                    
                    if (term && totalVisibleCards === 0) {
                        // Show no-results component when searching but no cards match
                        // console.log('✅ SHOWING NO-RESULTS COMPONENT');
                        if (noResultsComponent) {
                            noResultsComponent.classList.remove('hidden');
                            // console.log('🎉 No results component shown');
                        }
                        if (kanbanBoardContainer) {
                            kanbanBoardContainer.classList.add('hidden');
                            // console.log('🙈 Kanban board hidden');
                        }
                    } else {
                        // Show kanban board when there are results or no search
                        console.log('✅ SHOWING KANBAN BOARD');
                        if (noResultsComponent) {
                            noResultsComponent.classList.add('hidden');
                            // console.log('🙈 No results component hidden');
                        }
                        if (kanbanBoardContainer) {
                            kanbanBoardContainer.classList.remove('hidden');
                            // console.log('🎉 Kanban board shown');
                        }
                    }
                }, 100); // Small delay to ensure DOM is updated
            });
            
            // Also listen for Livewire events
            if (window.Livewire) {
                // console.log('🔗 Livewire detected, setting up additional listeners');
                
                // Listen for Livewire search updates
                window.Livewire.on('action-board-search', function(data) {
                    // console.log('🔗 Livewire search event:', data);
                });
            }
        });
    </script>
</x-filament::page>
