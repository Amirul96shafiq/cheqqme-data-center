@props(['columnId', 'column', 'config'])

<div
    class="ff-column kanban-column">
    <!-- Column Header -->
    <div class="ff-column__header">
        <div class="ff-column__title-container">
            <h3 class="ff-column__title">
                {{ $column['label'] }}
            </h3>
            <div class="ff-column__count kanban-color-{{ $column['color'] ?? 'default' }}">
                {{ $column['total'] ?? (isset($column['items']) ? count($column['items']) : 0) }}
            </div>
        </div>

        @if ($this->createAction() && ($this->createAction)(['column' => $columnId])->isVisible())
            <div class="create-button-container" x-data="{ searchActive: false }" x-init="
                window.addEventListener('action-board-search', (e) => {
                    searchActive = (e.detail.search || '').length > 0;
                });
            " x-show="!searchActive">
                {{ ($this->createAction)(['column' => $columnId]) }}
            </div>
        @endif
    </div>

    <!-- Column Content -->
    <div
        x-data="columnDragDrop('{{ $columnId }}')"
        x-init="init()"
        x-sortable
        x-sortable-group="cards"
        x-sortable-ghost-class="sortable-ghost"
        x-sortable-chosen-class="sortable-chosen"
        x-sortable-drag-class="sortable-drag"
        data-column-id="{{ $columnId }}"
        @end.stop="handleDragEnd($event)"
        x-bind:class="filterActive ? 'drag-disabled' : ''"
        x-on:dragstart="filterActive && $event.preventDefault()"
        x-on:drag="filterActive && $event.preventDefault()"
        x-on:dragenter="filterActive && $event.preventDefault()"
        x-on:dragover="filterActive && $event.preventDefault()"
        x-on:dragleave="filterActive && $event.preventDefault()"
        x-on:dragend="filterActive && $event.preventDefault()"
        x-on:drop="filterActive && $event.preventDefault()"
        class="ff-column__content overflow-y-auto"
        style="max-height: calc(100vh - 13rem); min-height: 60px;"
    >
        @if (isset($column['items']) && count($column['items']) > 0)
            @foreach ($column['items'] as $record)
                <x-flowforge::card
                    :record="$record"
                    :config="$config"
                    :columnId="$columnId"
                    wire:key="card-{{ $record['id'] }}"
                />
            @endforeach

            @if(isset($column['total']) && $column['total'] > count($column['items']))
                <div
                    x-intersect.full="
                        if (!isLoadingColumn('{{ $columnId }}')) {
                            beginLoading('{{ $columnId }}');
                            $wire.loadMoreItems('{{ $columnId }}', {{ $config->cardsIncrement ?? 'null' }});
                        }
                    "
                    class="ff-column__loader"
                >
                    <div wire:loading wire:target="loadMoreItems('{{ $columnId }}')"
                         class="ff-column__loading-text">
                        {{ __('Loading more cards...') }}
                        <div class="mt-1 flex justify-center">
                            <svg class="animate-spin h-4 w-4 text-primary-600 dark:text-primary-400"
                                 xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>
                    <div wire:loading.remove wire:target="loadMoreItems('{{ $columnId }}')"
                         class="ff-column__count-text">
                        {{ count($column['items']) }}
                        / {{ $column['total'] }} {{ $config->getPluralCardLabel() }}
                    </div>
                </div>
            @endif
        @else
            <x-flowforge::empty-column
                :columnId="$columnId"
                :pluralCardLabel="$config->getPluralCardLabel()"
            />
        @endif
    </div>
</div>

<script>
function columnDragDrop(columnId) {
    return {
        filterActive: false,
        dragOperations: new Map(), // Track ongoing drag operations
        
        init() {
            // Listen for filter events to disable drag and drop
            window.addEventListener('action-board-unified-filter', (e) => {
                const search = e?.detail?.search || '';
                const assignedTo = e?.detail?.assignedTo || [];
                this.filterActive = search.length > 0 || assignedTo.length > 0;
            });
        },
        
        handleDragEnd(event) {
            if (this.filterActive) {
                return; // Don't handle drag if filtering is active
            }
            
            const targetColumn = event.to.getAttribute('data-column-id');
            const cardIds = event.to.sortable.toArray();
            
            // Store original state for potential rollback
            const originalState = {
                columnId: columnId,
                cardIds: event.from.sortable.toArray(),
                timestamp: Date.now()
            };
            
            // Optimistic UI update - cards are already moved visually by Alpine.js
            this.dragOperations.set(targetColumn, {
                originalState,
                newState: { columnId: targetColumn, cardIds },
                status: 'pending'
            });
            
            // Show visual feedback
            this.showDragFeedback(targetColumn, 'success');
            
            // Background server sync (non-blocking)
            this.syncWithServer(targetColumn, cardIds, originalState);
        },
        
        syncWithServer(columnId, cardIds, originalState) {
            // Use fetch API for non-blocking server sync with session auth
            fetch('/api/kanban/update-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin', // Include session cookies
                body: JSON.stringify({
                    columnId: columnId,
                    cardIds: cardIds
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
                    this.dragOperations.set(columnId, {
                        ...this.dragOperations.get(columnId),
                        status: 'completed'
                    });
                    this.showDragFeedback(columnId, 'completed');
                } else {
                    throw new Error(data.message || 'Server update failed');
                }
            })
            .catch(error => {
                console.error('Drag and drop sync failed:', error);
                this.showDragFeedback(columnId, 'error');
                // Optional: Rollback UI changes
                // this.rollbackDragOperation(originalState);
            })
            .finally(() => {
                // Clean up after 3 seconds
                setTimeout(() => {
                    this.dragOperations.delete(columnId);
                }, 3000);
            });
        },
        
        showDragFeedback(columnId, type) {
            const column = document.querySelector(`[data-column-id="${columnId}"]`);
            if (!column) return;
            
            // Remove existing feedback classes
            column.classList.remove('drag-success', 'drag-error', 'drag-pending');
            
            // Add appropriate feedback class
            switch (type) {
                case 'success':
                case 'completed':
                    column.classList.add('drag-success');
                    break;
                case 'error':
                    column.classList.add('drag-error');
                    break;
                case 'pending':
                    column.classList.add('drag-pending');
                    break;
            }
            
            // Remove feedback after animation
            setTimeout(() => {
                column.classList.remove('drag-success', 'drag-error', 'drag-pending');
            }, 1500);
        },
        
        rollbackDragOperation(originalState) {
            // Implement rollback logic if needed
            console.log('Rolling back drag operation:', originalState);
        }
    }
}
</script>

<style>
/* Drag and drop feedback styles */
.drag-success {
    background-color: rgba(34, 197, 94, 0.1);
    border: 2px solid rgba(34, 197, 94, 0.3);
    transition: all 0.3s ease;
}

.drag-error {
    background-color: rgba(239, 68, 68, 0.1);
    border: 2px solid rgba(239, 68, 68, 0.3);
    transition: all 0.3s ease;
}

.drag-pending {
    background-color: rgba(59, 130, 246, 0.1);
    border: 2px solid rgba(59, 130, 246, 0.3);
    transition: all 0.3s ease;
}
</style>
