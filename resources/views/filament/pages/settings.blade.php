<!-- Filament Settings Page -->
<x-filament-panels::page>
    <!-- Settings Form -->
    <form wire:submit.prevent="save" id="settings-form">
        {{ $this->form }}

        <!-- Save Settings Button -->
        <div class="flex justify-left mt-6">
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
                {{ __('settings.actions.save') }}
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

        // Backup action functions
        function downloadBackup(backupId) {
            const link = document.createElement('a');
            link.href = `/chatbot/backup/${backupId}/download`;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function restoreBackup(backupId) {
            if (confirm('Are you sure you want to restore this backup? This will add the conversations to your current chatbot.')) {
                // Get current conversation ID
                const conversationId = localStorage.getItem("chatbot_conversation_id_" + window.chatbotUserId);
                
                // Make AJAX request to restore backup
                fetch(`/chatbot/backup/${backupId}/restore`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        conversation_id: conversationId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Backup restored successfully!');
                        // Refresh the page to show updated data
                        window.location.reload();
                    } else {
                        alert('Failed to restore backup: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to restore backup: ' + error.message);
                });
            }
        }

        function deleteBackup(backupId) {
            if (confirm('Are you sure you want to delete this backup? This action cannot be undone.')) {
                // Make AJAX request to delete backup
                fetch(`/chatbot/backup/${backupId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Backup deleted successfully!');
                        // Refresh the page to show updated data
                        window.location.reload();
                    } else {
                        alert('Failed to delete backup: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to delete backup: ' + error.message);
                });
            }
        }

        // Copy with visual feedback function
        function copyWithFeedback(button, text) {
            // Copy to clipboard
            window.copyToClipboard(text).then(() => {
                // Show visual feedback
                const copyIcon = button.querySelector('.copy-icon');
                const checkIcon = button.querySelector('.check-icon');
                const copyText = button.querySelector('.copy-text');
                const copiedText = button.querySelector('.copied-text');
                
                if (copyIcon && checkIcon) {
                    // Hide copy icon, show check icon
                    copyIcon.classList.add('hidden');
                    checkIcon.classList.remove('hidden');
                    
                    // Reset after 2 seconds
                    setTimeout(() => {
                        copyIcon.classList.remove('hidden');
                        checkIcon.classList.add('hidden');
                    }, 2000);
                }
                
                if (copyText && copiedText) {
                    // Hide copy text, show copied text
                    copyText.classList.add('hidden');
                    copiedText.classList.remove('hidden');
                    
                    // Reset after 2 seconds
                    setTimeout(() => {
                        copyText.classList.remove('hidden');
                        copiedText.classList.add('hidden');
                    }, 2000);
                }
            }).catch(error => {
                console.error('Failed to copy:', error);
            });
        }

        // Actions dropdown functions
        function toggleActionsDropdown(backupId) {
            // Close all other dropdowns first
            const allDropdowns = document.querySelectorAll('[id^="actions-dropdown-"]');
            allDropdowns.forEach(dropdown => {
                if (dropdown.id !== `actions-dropdown-${backupId}`) {
                    dropdown.classList.add('hidden');
                }
            });
            
            // Toggle the current dropdown
            const dropdown = document.getElementById(`actions-dropdown-${backupId}`);
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        function hideActionsDropdown(backupId) {
            const dropdown = document.getElementById(`actions-dropdown-${backupId}`);
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[onclick*="toggleActionsDropdown"]') && 
                !event.target.closest('[id^="actions-dropdown-"]')) {
                const allDropdowns = document.querySelectorAll('[id^="actions-dropdown-"]');
                allDropdowns.forEach(dropdown => {
                    dropdown.classList.add('hidden');
                });
            }
        });

        // Backup search and filter functions
        let searchTimeout;
        
        function initBackupSearch() {
            const searchInput = document.getElementById('backup-search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        refreshBackupTable();
                    }, 300);
                });
            }
        }

        function initBackupFilters() {
            const filterSelect = document.getElementById('backup-type-filter');
            if (filterSelect) {
                filterSelect.addEventListener('change', function() {
                    refreshBackupTable();
                });
            }
        }

        function toggleBackupFilters() {
            const dropdown = document.getElementById('backup-filters-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        }

        function clearBackupSearch() {
            const searchInput = document.getElementById('backup-search');
            if (searchInput) {
                searchInput.value = '';
                refreshBackupTable();
            }
        }

        function clearBackupFilters() {
            const searchInput = document.getElementById('backup-search');
            const filterSelect = document.getElementById('backup-type-filter');
            
            if (searchInput) searchInput.value = '';
            if (filterSelect) filterSelect.value = '';
            
            refreshBackupTable();
        }

        function loadMoreBackups(currentCount = null) {
            const count = currentCount || getUrlParameter('backup_visible_count') || 5;
            const newCount = parseInt(count) + 5;
            refreshBackupTable(newCount);
        }

        function refreshBackupTable(visibleCount = null) {
            const searchInput = document.getElementById('backup-search');
            const filterSelect = document.getElementById('backup-type-filter');
            
            const search = searchInput ? searchInput.value : '';
            const filter = filterSelect ? filterSelect.value : '';
            const count = visibleCount || getUrlParameter('backup_visible_count') || 5;
            
            // Show loading state
            const tableContainer = document.getElementById('chatbot-backups-table');
            if (tableContainer) {
                tableContainer.style.opacity = '0.5';
                tableContainer.style.pointerEvents = 'none';
            }
            
            // Make AJAX request to get updated table
            const params = new URLSearchParams();
            if (search) params.set('backup_search', search);
            if (filter) params.set('backup_type_filter', filter);
            if (count !== 5) params.set('backup_visible_count', count);
            
            fetch(`/settings/backup-table?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                }
            })
            .then(response => response.text())
            .then(html => {
                // Replace the table content
                if (tableContainer) {
                    tableContainer.outerHTML = html;
                    
                    // Re-initialize event listeners
                    initBackupSearch();
                    initBackupFilters();
                }
            })
            .catch(error => {
                console.error('Error refreshing backup table:', error);
                // Fallback to page refresh
                window.location.reload();
            })
            .finally(() => {
                // Remove loading state
                if (tableContainer) {
                    tableContainer.style.opacity = '1';
                    tableContainer.style.pointerEvents = 'auto';
                }
            });
        }

        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name);
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            initBackupSearch();
            initBackupFilters();
        });
    </script>
</x-filament-panels::page>
