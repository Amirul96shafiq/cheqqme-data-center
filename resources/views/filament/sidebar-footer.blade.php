<div class="fi-sidebar-footer" x-data="{}" x-bind:class="$store.sidebar.isOpen ? 'justify-start' : 'justify-center'">
    <!-- Collapse button (shown when sidebar is open) -->
    <x-tooltip position="right" :text="__('filament-panels::layout.actions.sidebar.collapse.label')">
        <button
            type="button"
            x-data="{}"
            x-on:click="$dispatch('trigger-sidebar-fade')"
            class="fi-sidebar-collapse-button fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-style-ghost transition-opacity duration-300"
            x-bind:aria-label="'{{ __('filament-panels::layout.actions.sidebar.collapse.label') }}'"
            x-bind:class="{ 'opacity-0 pointer-events-none': isFading, 'opacity-100': !isFading && $store.sidebar.isOpen }"
            x-show="$store.sidebar.isOpen"
        >
            <x-icons.custom-icon name="sidebar-panel" class="fi-sidebar-collapse-button-icon" />
        </button>
    </x-tooltip>

    <!-- Expand button (shown when sidebar is closed) -->
    <x-tooltip position="right" :text="__('filament-panels::layout.actions.sidebar.expand.label')">
        <button
            type="button"
            x-data="{}"
            x-on:click="$dispatch('trigger-sidebar-fade-in')"
            class="fi-sidebar-expand-button fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-style-ghost transition-opacity duration-300"
            x-bind:aria-label="'{{ __('filament-panels::layout.actions.sidebar.expand.label') }}'"
            x-bind:class="{ 'opacity-0 pointer-events-none': isFading, 'opacity-100': !isFading && !$store.sidebar.isOpen }"
            x-show="!$store.sidebar.isOpen"
        >
            <x-icons.custom-icon name="sidebar-panel" class="fi-sidebar-expand-button-icon" />
        </button>
    </x-tooltip>
</div>
