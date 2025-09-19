<div class="fi-sidebar-footer">
    <!-- Collapse button (shown when sidebar is open) -->
    <button
        type="button"
        x-data="{}"
        x-on:click="$store.sidebar.close()"
        class="fi-sidebar-collapse-button fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-style-ghost"
        x-bind:aria-label="'{{ __('filament-panels::layout.actions.sidebar.collapse.label') }}'"
        x-bind:title="'{{ __('filament-panels::layout.actions.sidebar.collapse.label') }}'"
        x-show="$store.sidebar.isOpen"
    >
        <x-filament::icon
            icon="heroicon-m-chevron-left"
            class="fi-sidebar-collapse-button-icon"
        />
    </button>

    <!-- Expand button (shown when sidebar is closed) -->
    <button
        type="button"
        x-data="{}"
        x-on:click="$store.sidebar.open()"
        class="fi-sidebar-expand-button fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-style-ghost"
        x-bind:aria-label="'{{ __('filament-panels::layout.actions.sidebar.expand.label') }}'"
        x-bind:title="'{{ __('filament-panels::layout.actions.sidebar.expand.label') }}'"
        x-show="!$store.sidebar.isOpen"
    >
        <x-filament::icon
            icon="heroicon-m-chevron-right"
            class="fi-sidebar-expand-button-icon"
        />
    </button>
</div>
