<div id="chatbot-backups-table">
    @if($backups->count() > 0)
        <div class="overflow-visible rounded-lg border border-gray-200 dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Backup Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Messages
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Date Range
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Backed Up
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Size
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($backups as $backup)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ $backup->backup_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badgeColors = [
                                        'weekly' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                        'manual' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'import' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                    ];
                                    $color = $badgeColors[$backup->backup_type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
                                @endphp
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $color }}">
                                    {{ ucfirst($backup->backup_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ number_format($backup->message_count) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $backup->formatted_date_range }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $backup->formatted_backup_date }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                {{ $backup->file_size }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center justify-end">
                                    <div class="relative" x-data="{ open: false }">
                                        <!-- Dropdown trigger button with horizontal dots -->
                                        <button 
                                            type="button"
                                            @click="open = !open"
                                            @click.away="open = false"
                                            class="inline-flex items-center p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors duration-200"
                                            title="Actions"
                                        >
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                            </svg>
                                        </button>

                                        <!-- Filament native dropdown menu -->
                                        <div 
                                            x-show="open"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:leave-end="opacity-0"
                                            class="fi-dropdown-panel absolute right-0 bottom-full mb-1 z-50 w-screen divide-y divide-gray-100 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 transition dark:divide-white/5 dark:bg-gray-900 dark:ring-white/10 !max-w-[14rem] overflow-y-auto"
                                            style="display: none;"
                                        >
                                            <div class="fi-dropdown-list p-1">
                                                <!-- Download action -->
                                                <button 
                                                    type="button"
                                                    wire:click="downloadBackup({{ $backup->id }})"
                                                    class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/10 dark:focus-visible:bg-white/5"
                                                >
                                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                                    <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                                                        Download Backup
                                                    </span>
                                                </button>

                                                <!-- Restore action -->
                                                <button 
                                                    type="button"
                                                    wire:click="restoreBackup({{ $backup->id }})"
                                                    class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/10 dark:focus-visible:bg-white/5"
                                                >
                                                    <x-heroicon-o-arrow-path class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                                    <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                                                        Restore Backup
                                                    </span>
                                                </button>

                                                <!-- Delete action -->
                                                <button 
                                                    type="button"
                                                    wire:click="deleteBackup({{ $backup->id }})"
                                                    class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-red-50 focus-visible:bg-red-50 dark:hover:bg-red-500/20 dark:focus-visible:bg-red-500/20"
                                                >
                                                    <x-heroicon-o-trash class="h-4 w-4 text-red-500 dark:text-red-400" />
                                                    <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-red-600 dark:text-red-400">
                                                        Delete Backup
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No backups yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create your first backup to save your chatbot conversations.</p>
        </div>
    @endif

    <!-- Download Script -->
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('download-backup', function (data) {
                const link = document.createElement('a');
                link.href = data.url;
                link.download = data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });

            // Listen for refresh-backups event as backup method
            Livewire.on('refresh-backups', function () {
                $wire.call('refreshBackups');
            });
        });

        // Listen for backup created event to refresh the backup list
        window.addEventListener('backup-created', () => {
            // Add a small delay to ensure component is fully initialized
            setTimeout(() => {
                const container = document.getElementById('chatbot-backups-table');
                if (container) {
                    const wireId = container.getAttribute('wire:id');
                    if (wireId) {
                        const component = Livewire.find(wireId);
                        if (component && component.call) {
                            component.call('refreshBackups');
                        } else {
                            // Fallback: trigger Livewire event
                            Livewire.dispatch('refresh-backups');
                        }
                    }
                }
            }, 500);
        });
    </script>
</div>
