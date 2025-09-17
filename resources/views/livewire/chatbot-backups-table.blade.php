<div id="chatbot-backups-table">
    <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
        <!-- Search Input and Filters - Above Table Header -->
        <div class="bg-white dark:bg-gray-900 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <!-- Create Backup Button -->
                <div>
                    <button
                        type="button"
                        wire:click="showCreateBackupConfirmation"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-primary-900 bg-primary-600 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                        wire:loading.attr="disabled"
                        wire:target="showCreateBackupConfirmation"
                    >
                        <x-heroicon-o-archive-box wire:loading.remove wire:target="showCreateBackupConfirmation" class="h-4 w-4 mr-2" />
                        <x-heroicon-o-arrow-path wire:loading wire:target="showCreateBackupConfirmation" class="h-4 w-4 mr-2 animate-spin" />
                        <span wire:loading.remove wire:target="showCreateBackupConfirmation">{{ __('settings.chatbot.create_backup') }}</span>
                        <span wire:loading wire:target="showCreateBackupConfirmation">{{ __('settings.chatbot.creating_backup') }}</span>
                    </button>
                </div>

                <!-- Search and Filters -->
                <div class="flex items-center gap-4">
                <!-- Search Input -->
                <div class="relative w-60">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    </div>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('settings.chatbot.search.placeholder') }}"
                        class="fi-input block w-full rounded-lg bg-transparent px-3 py-1.5 bg-white dark:bg-white/5 border border-gray-200 dark:border-gray-700 text-base text-gray-950 outline-none transition duration-75 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-500 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] disabled:placeholder:[-webkit-text-fill-color:theme(colors.gray.400)] dark:text-white dark:placeholder:text-gray-500 dark:focus:ring-primary-400 sm:text-sm sm:leading-6 pl-10 pr-10"
                    >
                    @if($search)
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                            <button 
                                type="button"
                                wire:click="clearSearch"
                                class="text-gray-400 hover:text-gray-300 transition-colors duration-200"
                                title="{{ __('settings.chatbot.search.clear') }}"
                            >
                                <x-heroicon-o-x-mark class="h-4 w-4" />
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Filter Button / Dropdown (Filament native) -->
                <x-filament::dropdown width="xs">
                    <x-slot name="trigger">
                        <button 
                            type="button"
                            class="fi-btn fi-btn-color-gray fi-btn-size-sm fi-btn-outlined flex items-center border-0 text-sm font-medium text-gray-400 hover:text-gray-500 transition duration-75 disabled:bg-gray-50 disabled:text-gray-500 dark:text-gray-500 hover:dark:text-gray-400 dark:disabled:bg-gray-800 dark:disabled:text-gray-500"
                        >
                            <x-heroicon-m-funnel class="h-5 w-5" />
                            @if($this->hasActiveFilters)
                                <span class="fi-badge fi-color-danger fi-size-xs inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-xs font-medium ring-1 ring-inset bg-primary-50 text-primary-700 ring-primary-600/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/30">
                                    {{ ($search ? 1 : 0) + ($backupTypeFilter ? 1 : 0) }}
                                </span>
                            @endif
                        </button>
                    </x-slot>

                    <div class="fi-dropdown-list p-1">
                        <!-- Filter Header -->
                        <div class="flex items-center justify-between px-2 py-1.5">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ __('settings.chatbot.filter.label') }}</span>
                            @if($this->hasActiveFilters)
                                <button 
                                    type="button"
                                    wire:click="clearFilters"
                                    class="text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                >
                                    {{ __('settings.chatbot.filter.reset') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="fi-dropdown-list p-1">
                        <!-- Backup Type Filter -->
                        <div class="px-3 py-2">
                            <label class="sr-only">{{ __('settings.chatbot.filter.backup_type') }}</label>
                            <x-filament::input.wrapper :prefix="__('settings.chatbot.filter.backup_type')">
                                <x-filament::input.select wire:model.live="backupTypeFilter">
                                    <option value="">{{ __('settings.chatbot.filter.all_types') }}</option>
                                    <option value="weekly">{{ __('settings.chatbot.filter.types.weekly') }}</option>
                                    <option value="manual">{{ __('settings.chatbot.filter.types.manual') }}</option>
                                    <option value="import">{{ __('settings.chatbot.filter.types.import') }}</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                </x-filament::dropdown>
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
                @if($backups->count() > 0)
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
                                    <!-- Use Filament's native dropdown to avoid conflicts -->
                                    <x-filament::dropdown width="xs">
                                        <x-slot name="trigger">
                                            <button 
                                                type="button"
                                                class="inline-flex items-center p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-full transition-colors duration-200"
                                                title="{{ __('settings.chatbot.actions_menu.title') }}"
                                            >
                                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                                                </svg>
                                            </button>
                                        </x-slot>

                                        <div class="fi-dropdown-list p-1">
                                            <!-- Download action -->
                                            <button 
                                                type="button"
                                                wire:click="downloadBackup({{ $backup->id }})"
                                                class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/10 dark:focus-visible:bg-white/5"
                                            >
                                                <x-heroicon-o-arrow-down-tray class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                                <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                                                    {{ __('settings.chatbot.actions_menu.download') }}
                                                </span>
                                            </button>

                                            <!-- Restore action -->
                                            <button
                                                type="button"
                                                wire:click="showRestoreBackupConfirmation({{ $backup->id }})"
                                                class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/10 dark:focus-visible:bg-white/5"
                                                wire:loading.attr="disabled"
                                                wire:target="showRestoreBackupConfirmation"
                                            >
                                                <x-heroicon-o-arrow-path wire:loading.remove wire:target="showRestoreBackupConfirmation" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                                <x-heroicon-o-arrow-path wire:loading wire:target="showRestoreBackupConfirmation" class="h-4 w-4 animate-spin text-gray-400 dark:text-gray-500" />
                                                <span wire:loading.remove wire:target="showRestoreBackupConfirmation" class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                                                    {{ __('settings.chatbot.actions_menu.restore') }}
                                                </span>
                                                <span wire:loading wire:target="showRestoreBackupConfirmation" class="fi-dropdown-list-item-label flex-1 truncate text-start text-gray-700 dark:text-gray-200">
                                                    {{ __('settings.chatbot.loading') }}
                                                </span>
                                            </button>

                                            <!-- Delete action -->
                                            <button
                                                type="button"
                                                wire:click="showDeleteBackupConfirmation({{ $backup->id }})"
                                                class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 hover:bg-red-50 focus-visible:bg-red-50 dark:hover:bg-red-500/20 dark:focus-visible:bg-red-500/20"
                                                wire:loading.attr="disabled"
                                                wire:target="showDeleteBackupConfirmation"
                                            >
                                                <x-heroicon-o-trash wire:loading.remove wire:target="showDeleteBackupConfirmation" class="h-4 w-4 text-red-500 dark:text-red-400" />
                                                <x-heroicon-o-arrow-path wire:loading wire:target="showDeleteBackupConfirmation" class="h-4 w-4 animate-spin text-red-500 dark:text-red-400" />
                                                <span wire:loading.remove wire:target="showDeleteBackupConfirmation" class="fi-dropdown-list-item-label flex-1 truncate text-start text-red-600 dark:text-red-400">
                                                    {{ __('settings.chatbot.actions_menu.delete') }}
                                                </span>
                                                <span wire:loading wire:target="showDeleteBackupConfirmation" class="fi-dropdown-list-item-label flex-1 truncate text-start text-red-600 dark:text-red-400">
                                                    {{ __('settings.chatbot.loading') }}
                                                </span>
                                            </button>
                                        </div>
                                    </x-filament::dropdown>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            @if($this->hasActiveFilters)
                                <x-heroicon-o-magnifying-glass class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('settings.chatbot.empty.no_results_title') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    @if($search && $backupTypeFilter)
                                        {{ __('settings.chatbot.empty.no_results_both', ['search' => $search, 'type' => ucfirst($backupTypeFilter)]) }}
                                    @elseif($search)
                                        {{ __('settings.chatbot.empty.no_results_search', ['search' => $search]) }}
                                    @elseif($backupTypeFilter)
                                        {{ __('settings.chatbot.empty.no_results_type', ['type' => ucfirst($backupTypeFilter)]) }}
                                    @endif
                                </p>
                                <div class="mt-4">
                                    <button 
                                        wire:click="clearFilters"
                                        type="button"
                                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:focus:ring-offset-gray-800"
                                    >
                                        {{ __('settings.chatbot.actions_menu.clear_filters') }}
                                    </button>
                                </div>
                            @else
                                <x-heroicon-o-circle-stack class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('settings.chatbot.no_backups') }}</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('settings.chatbot.no_backups_description') }}</p>
                            @endif
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
        
        <!-- Show total backups - only show when not searching or filtering -->
        @if(!$this->hasActiveFilters && $this->totalBackups > 0)
            <div class="mt-3 text-[10px] text-gray-400 text-center">
                {{ __('settings.chatbot.showing', ['shown' => $backups->count(), 'total' => $this->totalBackups]) }}
            </div>
        @endif

        <!-- Show more backups button - only show when not searching or filtering -->
        @if(!$this->hasActiveFilters && $this->totalBackups > $visibleCount)
            @php $remaining = $this->totalBackups - $visibleCount; @endphp
            <div class="mt-2">
                <button wire:click="showMore" 
                        type="button" 
                        class="w-full text-xs font-medium px-3 py-2 rounded-lg bg-primary-500 dark:bg-primary-600 hover:bg-primary-400 dark:hover:bg-primary-500 text-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500/40"
                        wire:loading.attr="disabled"
                        wire:target="showMore">
                    <span wire:loading.remove wire:target="showMore">
                        {{ __('settings.chatbot.load_more', ['count' => $remaining < 5 ? $remaining : 5]) }}
                    </span>
                    <span wire:loading wire:target="showMore">
                        {{ __('settings.chatbot.loading') }}
                    </span>
                </button>
            </div>
        @endif
    </div>

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
