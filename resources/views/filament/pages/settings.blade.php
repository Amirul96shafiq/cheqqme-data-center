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

    <!-- Copy API Key Immediate Function -->
    <script>
        // Copy API key immediately on click for mobile compatibility
        // This function executes synchronously within the user gesture context
        window.copyApiKeyImmediate = function(event, apiKey) {
            if (!apiKey) {
                console.error('No API key provided');
                return; // Let Livewire action proceed normally
            }

            // Copy immediately using clipboard API with mobile fallback
            const copyToClipboard = async (text) => {
                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    try {
                        await navigator.clipboard.writeText(text);
                        return true;
                    } catch (err) {
                        console.warn('Clipboard API failed, trying fallback:', err);
                    }
                }

                // Fallback for mobile browsers
                try {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    textArea.style.top = '-9999px';
                    textArea.style.opacity = '0';
                    textArea.setAttribute('readonly', '');
                    document.body.appendChild(textArea);

                    // For iOS Safari
                    if (navigator.userAgent.match(/ipad|iphone/i)) {
                        const range = document.createRange();
                        range.selectNodeContents(textArea);
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        textArea.setSelectionRange(0, 999999);
                    } else {
                        textArea.select();
                        textArea.setSelectionRange(0, 99999); // For mobile devices
                    }

                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);

                    if (successful) {
                        return true;
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }

                return false;
            };

            // Execute copy immediately (don't await to keep it synchronous)
            copyToClipboard(apiKey).catch(err => {
                console.error('Copy failed:', err);
            });

            // Don't prevent default - let Livewire action proceed for notification
            // The copy happens immediately, preserving the user gesture context
        };
    </script>

    <!-- Location Detection Script -->
    <script>
        document.addEventListener('livewire:init', function () {
            // Listen for location detection request
            Livewire.on('detect-user-location', function () {
                // console.log('[Location Detection] Location detection requested');
                
                // Track start time for timeout debugging
                const startTime = Date.now();
                
                // IP-based geolocation fallback function (doesn't require browser geolocation API)
                function attemptIpBasedGeolocation() {
                    // console.log('[Location Detection] Attempting IP-based geolocation...');
                    
                    // Try ipapi.co first (free, no API key required)
                    fetch('https://ipapi.co/json/')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            // console.log('[Location Detection] IP-based geolocation result:', data);
                            
                            if (data.error) {
                                throw new Error(data.reason || 'IP geolocation error');
                            }
                            
                            const latitude = parseFloat(data.latitude);
                            const longitude = parseFloat(data.longitude);
                            const city = data.city || 'Unknown';
                            const country = data.country_code || 'Unknown';
                            
                            if (isNaN(latitude) || isNaN(longitude)) {
                                throw new Error('Invalid coordinates from IP geolocation');
                            }
                            
                            // console.log('[Location Detection] IP-based location detected:', {
                            //     latitude,
                            //     longitude,
                            //     city,
                            //     country
                            // });
                            
                            // Dispatch success event with IP-based location data
                            Livewire.dispatch('location-detected', {
                                latitude: latitude,
                                longitude: longitude,
                                city: city,
                                country: country
                            });
                        })
                        .catch(error => {
                            console.error('[Location Detection] IP-based geolocation (ipapi.co) failed:', error);
                            
                            // Try alternative service: ip-api.com
                            // console.log('[Location Detection] Trying alternative IP geolocation service...');
                            fetch('http://ip-api.com/json/?fields=status,message,lat,lon,city,countryCode')
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    // console.log('[Location Detection] Alternative IP geolocation result:', data);
                                    
                                    if (data.status === 'fail') {
                                        throw new Error(data.message || 'IP geolocation failed');
                                    }
                                    
                                    const latitude = parseFloat(data.lat);
                                    const longitude = parseFloat(data.lon);
                                    const city = data.city || 'Unknown';
                                    const country = data.countryCode || 'Unknown';
                                    
                                    if (isNaN(latitude) || isNaN(longitude)) {
                                        throw new Error('Invalid coordinates from IP geolocation');
                                    }
                                    
                                    // console.log('[Location Detection] Alternative IP-based location detected:', {
                                    //     latitude,
                                    //     longitude,
                                    //     city,
                                    //     country
                                    // });
                                    
                                    // Dispatch success event with IP-based location data
                                    Livewire.dispatch('location-detected', {
                                        latitude: latitude,
                                        longitude: longitude,
                                        city: city,
                                        country: country
                                    });
                                })
                                .catch(error => {
                                    console.error('[Location Detection] Alternative IP geolocation also failed:', error);
                                    console.error('[Location Detection] All location detection methods failed');
                                    Livewire.dispatch('location-detection-failed');
                                });
                        });
                }
                
                // Use IP-based geolocation directly (faster and more reliable)
                // console.log('[Location Detection] Using IP-based geolocation (skipping browser geolocation)...');
                attemptIpBasedGeolocation();
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

    {{-- Unsaved Changes Alert --}}
    <x-filament-panels::page.unsaved-data-changes-alert />
    
</x-filament-panels::page>
