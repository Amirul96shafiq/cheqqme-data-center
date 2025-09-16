<div id="chatbot-backups-table">
    @if($backups->count() > 0)
        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <!-- Search Input - Above Table Header -->
            <div class="bg-white dark:bg-gray-900 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-end">
                    <div class="relative w-60">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        </div>
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search"
                            class="fi-input block w-full rounded-lg bg-transparent px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-400 sm:text-sm sm:leading-6 pl-10 pr-10"
                        >
                        @if($search)
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                <button 
                                    type="button"
                                    wire:click="clearSearch"
                                    class="text-gray-400 hover:text-gray-300 transition-colors duration-200"
                                    title="Clear search"
                                >
                                    <x-heroicon-o-x-mark class="h-4 w-4" />
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_id') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_name') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_type') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_messages') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_date_range') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_backed_up') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('settings.chatbot.backup_size') }}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{-- {{ __('settings.chatbot.backup_actions') }} --}}
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($backups as $backup)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                {{ $backup->id }}
                            </td>
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
                                    <div class="relative" x-data="{ open: false }" x-init="
                                        $watch('open', value => {
                                            if (value) {
                                                // Position dropdown outside table overflow
                                                const dropdown = $refs.dropdown;
                                                const button = $refs.button;
                                                const rect = button.getBoundingClientRect();
                                                dropdown.style.position = 'fixed';
                                                dropdown.style.top = (rect.top - dropdown.offsetHeight - 4) + 'px';
                                                dropdown.style.left = (rect.right - dropdown.offsetWidth) + 'px';
                                                dropdown.style.zIndex = '9999';
                                            }
                                        })
                                    ">
                                        <!-- Dropdown trigger button with horizontal dots -->
                                        <button 
                                            x-ref="button"
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
                                            x-ref="dropdown"
                                            x-show="open"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="fi-dropdown-panel w-screen divide-y divide-gray-100 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 transition dark:divide-white/5 dark:bg-gray-900 dark:ring-white/10 !max-w-[14rem] overflow-y-auto"
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

        <!-- Show total backups -->
        @if($this->totalBackups > 0)
            <div class="mt-3 text-[10px] text-gray-400 text-center">
                {{ __('settings.chatbot.showing', ['shown' => $backups->count(), 'total' => $this->totalBackups]) }}
            </div>
        @endif

        <!-- Show more backups button -->
        @if($this->totalBackups > $visibleCount)
            @php $remaining = $this->totalBackups - $visibleCount; @endphp
            <div class="mt-2">
                <button wire:click="showMore" 
                        type="button" 
                        class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-primary-500 dark:bg-primary-600 hover:bg-primary-400 dark:hover:bg-primary-500 text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                        wire:loading.attr="disabled"
                        wire:target="showMore">
                    <span wire:loading.remove wire:target="showMore">
                        @if($search)
                            Load {{ $remaining < 5 ? $remaining : 5 }} more result{{ $remaining === 1 ? '' : 's' }}
                        @else
                            {{ __('settings.chatbot.load_more', ['count' => $remaining < 5 ? $remaining : 5]) }}
                        @endif
                    </span>
                    <span wire:loading wire:target="showMore">
                        {{ __('settings.chatbot.loading') }}
                    </span>
                </button>
            </div>
        @endif
    @else
        <div class="text-center py-12">
            @if($search)
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No backups found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No backups match your search for "{{ $search }}"</p>
                <div class="mt-4">
                    <button 
                        wire:click="clearSearch"
                        type="button"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                    >
                        Clear search
                    </button>
                </div>
            @else
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('settings.chatbot.no_backups') }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.chatbot.no_backups_description') }}</p>
            @endif
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
