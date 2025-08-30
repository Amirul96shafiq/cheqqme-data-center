<!-- Filament Settings Page -->
<x-filament-panels::page>
    <!-- Settings Form -->
    <form wire:submit.prevent="save" id="settings-form">
        {{ $this->form }}

        <!-- Save Settings Button -->
        <div class="flex justify-end mt-6">
            <x-filament::button
                type="submit"
                wire:loading.attr="disabled"
                class="filament-button filament-button-primary"
            >
                <x-filament::loading-indicator
                    wire:loading
                    wire:target="save"
                    class="w-4 h-4 mr-2"
                />
                {{ __('settings.form.save') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
