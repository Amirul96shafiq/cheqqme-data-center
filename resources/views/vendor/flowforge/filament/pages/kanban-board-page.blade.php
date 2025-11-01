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
        :due-date-preset="$dueDatePreset"
        :due-date-from="$dueDateFrom"
        :due-date-to="$dueDateTo"
        :priority-filter="$priorityFilter"
    />
    
    <div class="h-[calc(100vh-16rem)] min-h-[400px] pb-8 md:pb-0">
        {{-- No Results Component (initially hidden) --}}
        <div id="no-results-component" class="hidden">
            <x-no-results-found 
                :searchTerm="$this->search"
                icon="heroicon-o-magnifying-glass"
                :heading="__('action.no_results.title')"
                :description="__('action.no_results.description')"
            />
        </div>
        
        {{-- Kanban Board --}}
        <div id="kanban-board-container" class="h-full">
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
            // Set global empty column text for JavaScript
            window.emptyColumnText = '{{ $this->getEmptyColumnText() }}';
            
            // Set global translations for JavaScript
            window.kanbanTranslations = {
                noResults: {
                    search: {
                        title: '{{ __("action.no_results.search.title") }}',
                        description: '{{ __("action.no_results.search.description") }}'
                    },
                    assignedTo: {
                        title: '{{ __("action.no_results.assigned_to.title") }}',
                        description: '{{ __("action.no_results.assigned_to.description") }}'
                    },
                    dueDate: {
                        title: '{{ __("action.no_results.due_date.title") }}',
                        description: '{{ __("action.no_results.due_date.description") }}'
                    }
                }
            };
            
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
            
            // Auto-open create task modal if URL parameter is present
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('create_task') === '1') {
                // Wait for page to fully load and then trigger the create task modal
                setTimeout(() => {
                    // Find the create task button and click it
                    const createTaskButton = document.querySelector('[wire\\:click="mountAction(\'createTask\')"]');
                    if (createTaskButton) {
                        createTaskButton.click();
                    } else {
                        // Fallback: try to find by text content
                        const buttons = document.querySelectorAll('button');
                        for (const button of buttons) {
                            if (button.textContent.includes('{{ __("action.modal.create_title") }}')) {
                                button.click();
                                break;
                            }
                        }
                    }
                    
                    // Clean up URL parameter without reloading
                    const newUrl = new URL(window.location);
                    newUrl.searchParams.delete('create_task');
                    window.history.replaceState({}, '', newUrl);
                }, 1000); // 1 second delay to ensure page is fully loaded
            }
        });
    </script>
</x-filament::page>
