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

    @vite(['resources/js/kanban-alpine.js', 'resources/js/kanban-mobile-move.js', 'resources/css/kanban-drag-drop.css'])
    
    {{-- Mobile Movement Modal --}}
    <div x-data="mobileMoveModal()" 
         x-init="init()"
         x-cloak
         class="fixed inset-0 z-[99999] pointer-events-none">
        
        {{-- Backdrop --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="close()"
             class="absolute inset-0 bg-gray-950/50 dark:bg-gray-950/75 backdrop-blur-sm pointer-events-auto"></div>
        
        {{-- Modal --}}
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed bottom-0 left-0 right-0 pointer-events-auto bg-white dark:bg-gray-900 rounded-t-xl shadow-2xl border-t border-gray-200 dark:border-gray-700 max-h-[50vh] flex flex-col">
            
            {{-- Modal Header --}}
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('action.move.title') }}
                </h3>
                <button type="button"
                        @click="close()"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500/30 rounded-md p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            {{-- Modal Content --}}
            <div class="flex-1 overflow-y-auto p-4">
                <div class="space-y-2">
                    {{-- Move to Top --}}
                    <button type="button"
                            @click="move('top')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.to_top') }}
                        </span>
                    </button>
                    
                    {{-- Move to Bottom --}}
                    <button type="button"
                            @click="move('bottom')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.to_bottom') }}
                        </span>
                    </button>
                    
                    {{-- Move Up One --}}
                    <button type="button"
                            @click="move('up')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.up_one') }}
                        </span>
                    </button>
                    
                    {{-- Move Down One --}}
                    <button type="button"
                            @click="move('down')"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.down_one') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function mobileMoveModal() {
            return {
                isOpen: false,
                taskId: null,
                
                init() {
                    // Listen for the event on window
                    window.addEventListener('kanban-show-move-modal', (e) => {
                        console.log('Received kanban-show-move-modal event:', e.detail);
                        if (e.detail && e.detail.taskId) {
                            this.open(e.detail.taskId);
                        }
                    });
                },
                
                open(taskId) {
                    console.log('Opening modal with taskId:', taskId);
                    this.taskId = taskId;
                    this.isOpen = true;
                    // Prevent body scroll when modal is open
                    document.body.style.overflow = 'hidden';
                },
                
                close() {
                    this.isOpen = false;
                    this.taskId = null;
                    // Restore body scroll
                    document.body.style.overflow = '';
                },
                
                move(direction) {
                    if (!this.taskId) {
                        console.warn('No task ID available for move operation');
                        return;
                    }
                    
                    const taskIdToMove = parseInt(this.taskId, 10);
                    if (isNaN(taskIdToMove)) {
                        console.error('Invalid task ID:', this.taskId);
                        return;
                    }
                    
                    // Close modal immediately
                    this.close();
                    
                    // Find the card element to get its column
                    const cardElement = document.querySelector(`[data-task-id="${taskIdToMove}"], [x-sortable-item="${taskIdToMove}"]`);
                    if (!cardElement) {
                        console.error('Card element not found for task ID:', taskIdToMove);
                        return;
                    }
                    
                    // Get the column ID from the card's parent column
                    const columnElement = cardElement.closest('.ff-column__content');
                    if (!columnElement) {
                        console.error('Column element not found for card');
                        return;
                    }
                    
                    const columnId = columnElement.getAttribute('data-column-id');
                    if (!columnId) {
                        console.error('Column ID not found');
                        return;
                    }
                    
                    // Get all card IDs in the current column (in their current order)
                    // Remove duplicates by using a Map to track first occurrence
                    const allCardsInColumn = Array.from(columnElement.querySelectorAll('[data-task-id], [x-sortable-item]'));
                    const seenIds = new Map();
                    const currentOrder = [];
                    
                    allCardsInColumn.forEach((card, index) => {
                        const id = card.getAttribute('data-task-id') || card.getAttribute('x-sortable-item');
                        const taskId = parseInt(id, 10);
                        
                        if (!isNaN(taskId)) {
                            // Only add if we haven't seen this ID before, or keep the first occurrence
                            if (!seenIds.has(taskId)) {
                                seenIds.set(taskId, index);
                                currentOrder.push(taskId);
                            }
                        }
                    });
                    
                    // Find the index of the card to move
                    const currentIndex = currentOrder.indexOf(taskIdToMove);
                    if (currentIndex === -1) {
                        console.error('Task not found in column order');
                        return;
                    }
                    
                    // Rearrange the array based on direction
                    let newOrder = [...currentOrder];
                    
                    if (direction === 'top') {
                        // Move to most top: remove from current position and add at start
                        newOrder.splice(currentIndex, 1);
                        newOrder.unshift(taskIdToMove);
                    } else if (direction === 'bottom') {
                        // Move to most bottom: remove from current position and add at end
                        newOrder.splice(currentIndex, 1);
                        newOrder.push(taskIdToMove);
                    } else if (direction === 'up') {
                        // Move up 1 position: swap with previous
                        if (currentIndex > 0) {
                            [newOrder[currentIndex - 1], newOrder[currentIndex]] = [newOrder[currentIndex], newOrder[currentIndex - 1]];
                        }
                    } else if (direction === 'down') {
                        // Move down 1 position: swap with next
                        if (currentIndex < newOrder.length - 1) {
                            [newOrder[currentIndex], newOrder[currentIndex + 1]] = [newOrder[currentIndex + 1], newOrder[currentIndex]];
                        }
                    }
                    
                    // Call the same API endpoint that drag and drop uses
                    fetch('/api/kanban/update-order', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            columnId: columnId,
                            cardIds: newOrder
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Simple and reliable: refresh the entire page
                            // This guarantees no duplicates and works on all devices
                            setTimeout(() => {
                                window.location.reload();
                            }, 100);
                        } else {
                            throw new Error(data.message || 'Server update failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error moving card:', error);
                        // Could show error notification here if needed
                    });
                }
            }
        }
    </script>
    
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
