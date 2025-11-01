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
        style="max-height: calc(100vh - 13rem); min-height: 60px; padding-bottom: 1.5rem;"
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
            {{-- Bottom spacer to ensure last card is fully visible on mobile --}}
            <div class="h-6"></div>
        @else
            <x-flowforge::empty-column
                :columnId="$columnId"
                :pluralCardLabel="$config->getPluralCardLabel()"
            />
        @endif
    </div>
</div>

