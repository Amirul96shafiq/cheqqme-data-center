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
        <svg class="fi-sidebar-collapse-button-icon" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M6 5 a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2 V5H6Zm4 0v14h8a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-8ZM3 6a3 3 0 0 1 3-3 h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Z" clip-rule="evenodd"/>
        </svg>
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
        <svg class="fi-sidebar-expand-button-icon" fill="currentColor" viewBox="0 0 24 24">
            <path fill-rule="evenodd" d="M6 5 a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2 V5H6Zm4 0v14h8a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-8ZM3 6a3 3 0 0 1 3-3 h12a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V6Z" clip-rule="evenodd"/>
        </svg>
    </button>
</div>
