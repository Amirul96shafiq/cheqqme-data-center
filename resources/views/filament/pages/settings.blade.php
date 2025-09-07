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

    <!-- Location Detection Script -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Listen for location detection request
            Livewire.on('detect-user-location', function () {
                console.log('Location detection requested');
                
                if (!navigator.geolocation) {
                    console.error('Geolocation is not supported by this browser');
                    Livewire.dispatch('location-detection-failed');
                    return;
                }

                // Show loading state
                console.log('Requesting location permission...');
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        console.log('Location detected:', position.coords);
                        
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        
                        // Reverse geocoding to get city and country
                        fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`)
                            .then(response => response.json())
                            .then(data => {
                                console.log('Reverse geocoding result:', data);
                                
                                const city = data.city || data.locality || 'Unknown';
                                const country = data.countryCode || 'Unknown';
                                
                                // Dispatch success event with location data
                                Livewire.dispatch('location-detected', {
                                    latitude: latitude,
                                    longitude: longitude,
                                    city: city,
                                    country: country
                                });
                            })
                            .catch(error => {
                                console.error('Reverse geocoding failed:', error);
                                
                                // Still dispatch with coordinates even if reverse geocoding fails
                                Livewire.dispatch('location-detected', {
                                    latitude: latitude,
                                    longitude: longitude,
                                    city: 'Unknown',
                                    country: 'Unknown'
                                });
                            });
                    },
                    function(error) {
                        console.error('Location detection failed:', error);
                        
                        let errorMessage = 'Unknown error';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                errorMessage = 'Location access denied by user';
                                break;
                            case error.POSITION_UNAVAILABLE:
                                errorMessage = 'Location information unavailable';
                                break;
                            case error.TIMEOUT:
                                errorMessage = 'Location request timed out';
                                break;
                        }
                        
                        console.error('Error details:', errorMessage);
                        Livewire.dispatch('location-detection-failed');
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000 // 5 minutes
                    }
                );
            });
        });
    </script>
</x-filament-panels::page>
