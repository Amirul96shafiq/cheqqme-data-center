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
    <!-- Copy API Key Script -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Listen for the copy-api-key event
            Livewire.on('copy-api-key', function (data) {
                const apiKey = data.apiKey;

                if (apiKey) {
                    // Copy to clipboard
                    navigator.clipboard.writeText(apiKey).then(function() {
                        // Show Filament success notification
                        $wire.dispatch('notify', {
                            type: 'success',
                            message: 'API key copied to clipboard!'
                        });
                    }).catch(function(err) {
                        console.error('Failed to copy API key: ', err);
                        // Fallback for older browsers
                        const textArea = document.createElement('textarea');
                        textArea.value = apiKey;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                        // Show Filament success notification
                        $wire.dispatch('notify', {
                            type: 'success',
                            message: 'API key copied to clipboard!'
                        });
                    });
                }
            });
            // Listen for the notify event and show Filament notification
            Livewire.on('notify', function (data) {
                // Use Filament's notification system
                if (window.FilamentNotification) {
                    if (data.type === 'success') {
                        window.FilamentNotification.success(data.message);
                    } else if (data.type === 'error') {
                        window.FilamentNotification.error(data.message);
                    } else if (data.type === 'warning') {
                        window.FilamentNotification.warning(data.message);
                    } else if (data.type === 'info') {
                        window.FilamentNotification.info(data.message);
                    }
                }
            });
        });
    </script>
</x-filament-panels::page>
