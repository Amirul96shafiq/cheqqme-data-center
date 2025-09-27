<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Online Status System Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Online Status System Test
                </h1>
                <p class="text-gray-600 dark:text-gray-300">
                    Test the enhanced Laravel Reverb and Echo integration for real-time online status updates.
                </p>
            </div>

            <!-- Status Control Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Status Control Panel
                </h2>
                
                <div class="flex flex-wrap gap-4 mb-4">
                    <button 
                        onclick="updateStatus('online')"
                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                        Set Online
                    </button>
                    <button 
                        onclick="updateStatus('away')"
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                        Set Away
                    </button>
                    <button 
                        onclick="updateStatus('dnd')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                        Set Do Not Disturb
                    </button>
                    <button 
                        onclick="updateStatus('invisible')"
                        class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                        Set Invisible
                    </button>
                </div>

                <div class="text-sm text-gray-600 dark:text-gray-300">
                    <p>Current Status: <span id="current-status" class="font-semibold">Loading...</span></p>
                    <p>Connection Status: <span id="connection-status" class="font-semibold">Connecting...</span></p>
                </div>
            </div>

            <!-- Online Users List -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Online Users (<span id="online-count">0</span>)
                </h2>
                
                <div id="online-users-list" class="space-y-3">
                    <!-- Users will be populated here by JavaScript -->
                </div>
            </div>

            <!-- Status Indicators Demo -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Status Indicators Demo
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="relative inline-block">
                            <img src="/images/default-avatar.png" alt="User" class="w-16 h-16 rounded-full mx-auto">
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-teal-500 rounded-full border-2 border-white dark:border-gray-900 online-status-indicator" data-user-id="{{ auth()->id() }}" data-current-status="online"></div>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Your Status</p>
                    </div>
                </div>
            </div>

            <!-- Connection Log -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">
                    Connection Log
                </h2>
                
                <div id="connection-log" class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 h-64 overflow-y-auto text-sm font-mono">
                    <!-- Log messages will appear here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Include Reverb Configuration -->
    <x-reverb-config />

    <!-- Include Bootstrap and Echo -->
    @vite(['resources/js/bootstrap.js'])
    
    <!-- Include Presence Status Manager -->
    <script src="{{ asset('js/presence-status.js') }}"></script>

    <script>
        // Log function to display messages in the connection log
        function log(message) {
            const logElement = document.getElementById('connection-log');
            const timestamp = new Date().toLocaleTimeString();
            const logEntry = document.createElement('div');
            logEntry.textContent = `[${timestamp}] ${message}`;
            logElement.appendChild(logEntry);
            logElement.scrollTop = logElement.scrollHeight;
        }

        // Override console.log to also display in our log
        const originalLog = console.log;
        console.log = function(...args) {
            originalLog.apply(console, args);
            log(args.join(' '));
        };

        // Override console.error to also display in our log
        const originalError = console.error;
        console.error = function(...args) {
            originalError.apply(console, args);
            log('ERROR: ' + args.join(' '));
        };

        // Override console.warn to also display in our log
        const originalWarn = console.warn;
        console.warn = function(...args) {
            originalWarn.apply(console, args);
            log('WARN: ' + args.join(' '));
        };

        // Function to update user status
        async function updateStatus(status) {
            if (window.presenceStatusManager) {
                try {
                    await window.presenceStatusManager.updateUserStatus(status);
                    log(`Status updated to: ${status}`);
                } catch (error) {
                    log(`Failed to update status: ${error.message}`);
                }
            } else {
                log('Presence Status Manager not initialized');
            }
        }

        // Update connection status
        function updateConnectionStatus() {
            const statusElement = document.getElementById('connection-status');
            if (window.presenceStatusManager && window.presenceStatusManager.isInitialized) {
                statusElement.textContent = 'Connected';
                statusElement.className = 'font-semibold text-green-600';
            } else {
                statusElement.textContent = 'Disconnected';
                statusElement.className = 'font-semibold text-red-600';
            }
        }

        // Update current status display
        function updateCurrentStatus() {
            const statusElement = document.getElementById('current-status');
            if (window.currentUser) {
                statusElement.textContent = window.currentUser.status;
                statusElement.className = 'font-semibold capitalize';
            }
        }

        // Initialize when page loads
        document.addEventListener('DOMContentLoaded', function() {
            log('Page loaded, initializing presence status system...');
            
            // Wait for presence status manager to initialize
            setTimeout(() => {
                updateConnectionStatus();
                updateCurrentStatus();
                
                // Set up periodic status updates
                setInterval(() => {
                    updateConnectionStatus();
                    if (window.presenceStatusManager && window.presenceStatusManager.currentUser) {
                        updateCurrentStatus();
                    }
                }, 1000);
            }, 2000);
        });

        // Listen for status changes
        document.addEventListener('userStatusChanged', function(event) {
            log(`User status changed: ${event.detail.status}`);
            updateCurrentStatus();
        });
    </script>
</body>
</html>
