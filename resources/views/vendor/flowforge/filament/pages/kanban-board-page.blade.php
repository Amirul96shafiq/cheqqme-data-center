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
        :card-type-filter="$cardTypeFilter"
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
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ __('action.move.title') }}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1" x-text="getTaskTitle()"></p>
                </div>
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
                <div class="space-y-4">

                    {{-- Column Selector --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{ __('action.move.to_column') }}
                        </label>
                        <div class="flex gap-1 overflow-x-auto pb-2 scrollbar-hide"
                             style="scrollbar-width: none; -ms-overflow-style: none;">
                            <style>
                                .scrollbar-hide::-webkit-scrollbar {
                                    display: none;
                                }
                            </style>
                            <template x-for="column in availableColumns" :key="column.id">
                                <button
                                    type="button"
                                    :class="selectedColumn === column.id
                                        ? 'bg-primary-500 text-primary-900 border-primary-500'
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    @click="selectColumn(column.id)"
                                    class="flex-shrink-0 px-2.5 py-1.5 text-xs font-medium rounded-full border transition-colors whitespace-nowrap">
                                    <span x-text="column.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Move Options --}}
                    <div class="space-y-2">

                        {{-- Move to Top --}}
                    <button type="button"
                            @click="move('top')"
                            :disabled="isAtTop()"
                            :class="isAtTop()
                                ? 'opacity-25 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600'
                                : 'bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.to_top') }}
                        </span>
                    </button>

                    {{-- Move Up One --}}
                    <button type="button"
                            @click="move('up')"
                            :disabled="isAtTop()"
                            :class="isAtTop()
                                ? 'opacity-25 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600'
                                : 'bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
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
                            :disabled="isAtBottom()"
                            :class="isAtBottom()
                                ? 'opacity-25 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600'
                                : 'bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.down_one') }}
                        </span>
                    </button>

                    {{-- Move to Bottom --}}
                    <button type="button"
                            @click="move('bottom')"
                            :disabled="isAtBottom()"
                            :class="isAtBottom()
                                ? 'opacity-25 cursor-not-allowed bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-600'
                                : 'bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700'"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left rounded-lg border border-gray-200 dark:border-gray-700 transition-colors">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        <span class="text-base font-medium text-gray-900 dark:text-gray-100">
                            {{ __('action.move.to_bottom') }}
                        </span>
                    </button>

                    </div>
                </div>
            </div>

        </div>
        
    </div>
    
    <script>
        function mobileMoveModal() {
            return {
                isOpen: false,
                taskId: null,
                selectedColumn: null,
                originalColumn: null,
                availableColumns: [],

                init() {
                    // Listen for the event on window
                    window.addEventListener('kanban-show-move-modal', (e) => {
                        if (e.detail && e.detail.taskId) {
                            this.open(e.detail.taskId);
                        }
                    });
                },

                open(taskId) {
                    this.taskId = taskId;

                    // Get available columns from the board
                    this.availableColumns = this.getAvailableColumns();

                    // Set original and selected column (current column)
                    this.originalColumn = this.getCurrentColumn(taskId);
                    this.selectedColumn = this.originalColumn;

                    this.isOpen = true;
                    // Prevent body scroll when modal is open
                    document.body.style.overflow = 'hidden';
                },

                close() {
                    this.isOpen = false;
                    this.taskId = null;
                    this.selectedColumn = null;
                    this.originalColumn = null;
                    this.availableColumns = [];
                    // Restore body scroll
                    document.body.style.overflow = '';
                },

                getAvailableColumns() {
                    // Get all columns from the board
                    const columns = [];
                    const columnElements = document.querySelectorAll('.ff-column__content[data-column-id]');

                    columnElements.forEach(columnElement => {
                        const columnId = columnElement.getAttribute('data-column-id');
                        const columnTitle = columnElement.closest('.ff-column').querySelector('.ff-column__title')?.textContent?.trim();

                        if (columnId && columnTitle) {
                            columns.push({
                                id: columnId,
                                label: columnTitle
                            });
                        }
                    });

                    return columns;
                },

                getCurrentColumn(taskId) {
                    // Find which column the card is currently in
                    const cardElement = document.querySelector(`[data-task-id="${taskId}"], [x-sortable-item="${taskId}"]`);
                    if (cardElement) {
                        const columnElement = cardElement.closest('.ff-column__content[data-column-id]');
                        if (columnElement) {
                            return columnElement.getAttribute('data-column-id');
                        }
                    }
                    return null;
                },

                getTaskTitle() {
                    if (!this.taskId) {
                        return '';
                    }

                    // Find the card element and get its title
                    const cardElement = document.querySelector(`[data-task-id="${this.taskId}"], [x-sortable-item="${this.taskId}"]`);
                    if (cardElement) {
                        // Look for the title in the card content (h4 with ff-card__title class)
                        const titleElement = cardElement.querySelector('h4.ff-card__title');
                        if (titleElement) {
                            const title = titleElement.textContent?.trim() || '';
                            // Limit to 30 characters and add ellipsis if needed
                            return title.length > 30 ? title.substring(0, 30) + '...' : title;
                        }

                        // Fallback: look for any h4 element
                        const fallbackTitle = cardElement.querySelector('h4');
                        if (fallbackTitle) {
                            const title = fallbackTitle.textContent?.trim() || '';
                            return title.length > 30 ? title.substring(0, 30) + '...' : title;
                        }

                        // Last fallback: get text content from the card
                        const cardText = cardElement.textContent?.trim() || '';
                        return cardText.length > 30 ? cardText.substring(0, 30) + '...' : cardText;
                    }

                    return '';
                },

                selectColumn(columnId) {
                    // Set the selected column
                    this.selectedColumn = columnId;

                    // If it's a different column, move immediately
                    if (columnId !== this.originalColumn) {
                        this.moveToColumn(columnId);
                    }
                },

                moveToColumn(targetColumnId) {
                    if (!this.taskId) {
                        return;
                    }

                    const taskIdToMove = parseInt(this.taskId, 10);
                    if (isNaN(taskIdToMove)) {
                        return;
                    }

                    // Close modal immediately
                    this.close();

                    // Find the card element
                    const cardElement = document.querySelector(`[data-task-id="${taskIdToMove}"], [x-sortable-item="${taskIdToMove}"]`);
                    if (!cardElement) {
                        return;
                    }

                    // Get target column element
                    const targetColumnElement = document.querySelector(`.ff-column__content[data-column-id="${targetColumnId}"]`);
                    if (!targetColumnElement) {
                        return;
                    }

                    // Get all cards in target column and add the moved card at the bottom
                    const targetCards = Array.from(targetColumnElement.querySelectorAll('[data-task-id], [x-sortable-item]'));
                    const seenIds = new Map();
                    const targetOrder = [];

                    targetCards.forEach((card) => {
                        const id = card.getAttribute('data-task-id') || card.getAttribute('x-sortable-item');
                        const taskId = parseInt(id, 10);

                        if (!isNaN(taskId) && !seenIds.has(taskId)) {
                            seenIds.set(taskId, true);
                            targetOrder.push(taskId);
                        }
                    });

                    // Add the moved card at the end of the target column
                    const newOrder = [...targetOrder, taskIdToMove];

                    // Call the API to move the card
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
                            columnId: targetColumnId,
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
                            // Refresh the entire page immediately
                            setTimeout(() => {
                                window.location.reload();
                            }, 100);
                        } else {
                            throw new Error(data.message || 'Server update failed');
                        }
                    })
                    .catch(error => {
                        console.error('Error moving card to column:', error);
                    });
                },

                isAtTop() {
                    if (!this.taskId || !this.selectedColumn) {
                        return false;
                    }

                    const taskIdToCheck = parseInt(this.taskId, 10);
                    if (isNaN(taskIdToCheck)) {
                        return false;
                    }

                    // Get the target column element
                    const targetColumnElement = document.querySelector(`.ff-column__content[data-column-id="${this.selectedColumn}"]`);
                    if (!targetColumnElement) {
                        return false;
                    }

                    // Get all cards in the target column
                    const allCardsInColumn = Array.from(targetColumnElement.querySelectorAll('[data-task-id], [x-sortable-item]'));
                    if (allCardsInColumn.length === 0) {
                        return false;
                    }

                    // Find the first card (top position)
                    const firstCard = allCardsInColumn[0];
                    const firstCardId = firstCard.getAttribute('data-task-id') || firstCard.getAttribute('x-sortable-item');

                    return parseInt(firstCardId, 10) === taskIdToCheck;
                },

                isAtBottom() {
                    if (!this.taskId || !this.selectedColumn) {
                        return false;
                    }

                    const taskIdToCheck = parseInt(this.taskId, 10);
                    if (isNaN(taskIdToCheck)) {
                        return false;
                    }

                    // Get the target column element
                    const targetColumnElement = document.querySelector(`.ff-column__content[data-column-id="${this.selectedColumn}"]`);
                    if (!targetColumnElement) {
                        return false;
                    }

                    // Get all cards in the target column
                    const allCardsInColumn = Array.from(targetColumnElement.querySelectorAll('[data-task-id], [x-sortable-item]'));
                    if (allCardsInColumn.length === 0) {
                        return false;
                    }

                    // Find the last card (bottom position)
                    const lastCard = allCardsInColumn[allCardsInColumn.length - 1];
                    const lastCardId = lastCard.getAttribute('data-task-id') || lastCard.getAttribute('x-sortable-item');

                    return parseInt(lastCardId, 10) === taskIdToCheck;
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

                    // Use the selected column (defaults to current column if not changed)
                    let targetColumnId = this.selectedColumn;

                    // If no column is selected, get the current column
                    if (!targetColumnId) {
                        const cardElement = document.querySelector(`[data-task-id="${taskIdToMove}"], [x-sortable-item="${taskIdToMove}"]`);
                        if (cardElement) {
                            const columnElement = cardElement.closest('.ff-column__content[data-column-id]');
                            if (columnElement) {
                                targetColumnId = columnElement.getAttribute('data-column-id');
                            }
                        }
                    }

                    if (!targetColumnId) {
                        return;
                    }

                    // Get the target column element
                    const targetColumnElement = document.querySelector(`.ff-column__content[data-column-id="${targetColumnId}"]`);
                    if (!targetColumnElement) {
                        return;
                    }

                    // Get all cards in the target column
                    const allCardsInColumn = Array.from(targetColumnElement.querySelectorAll('[data-task-id], [x-sortable-item]'));
                    const seenIds = new Map();
                    const currentOrder = [];

                    allCardsInColumn.forEach((card) => {
                        const id = card.getAttribute('data-task-id') || card.getAttribute('x-sortable-item');
                        const taskId = parseInt(id, 10);

                        if (!isNaN(taskId) && !seenIds.has(taskId)) {
                            seenIds.set(taskId, true);
                            currentOrder.push(taskId);
                        }
                    });

                    // Find the index of the card to move
                    const currentIndex = currentOrder.indexOf(taskIdToMove);
                    if (currentIndex === -1) {
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
                            columnId: targetColumnId,
                            cardIds: newOrder
                        })
                    })
                    .then(response => {
                        //console.log('Move API response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        //console.log('Move API response data:', data);
                        if (data.success) {
                            console.log('Move successful, refreshing page');
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
                    },
                    priority: {
                        title: '{{ __("action.no_results.priority.title") }}',
                        description: '{{ __("action.no_results.priority.description") }}'
                    },
                    cardType: {
                        title: '{{ __("action.no_results.card_type.title") }}',
                        description: '{{ __("action.no_results.card_type.description") }}'
                    }
                },
                cardTypeLabels: {
                    all: '{{ __("action.filter.card_type_all") }}',
                    tasks: '{{ __("action.filter.card_type_tasks") }}',
                    issue_trackers: '{{ __("action.filter.card_type_issue_trackers") }}'
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
