@php
    use App\Services\OnlineStatus\StatusConfig;
    
    $statusOptions = $getStatusOptions();
    $currentStatus = $getCurrentStatus();
    $statusConfig = StatusConfig::getStatusConfig();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div 
        x-data="{ 
            open: false, 
            selectedStatus: '{{ $currentStatus }}',
            statusConfig: @js($statusConfig),
            updateStatus(status) {
                this.selectedStatus = status;
                $wire.set('{{ $getStatePath() }}', status);
                this.open = false;
                
                // Call the global update function if available
                if (typeof window.updateOnlineStatus === 'function') {
                    window.updateOnlineStatus(status);
                }
            }
        }"
        class="relative"
    >
        <!-- Custom Dropdown Trigger -->
        <button
            type="button"
            @click="open = !open"
            @click.outside="open = false"
            class="relative w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg pl-3 pr-10 py-2 text-left cursor-pointer focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
            :class="{ 'ring-2 ring-primary-500 border-primary-500': open }"
        >
            <div class="flex items-center space-x-3">
                <!-- Status Color Circle -->
                <div 
                    class="w-3 h-3 rounded-full border-2 border-white dark:border-gray-900 flex-shrink-0"
                    :class="statusConfig[selectedStatus]?.color || 'bg-gray-400'"
                ></div>
                
                <!-- Status Label -->
                <span class="block truncate text-sm font-medium text-gray-900 dark:text-white">
                    <span x-text="statusConfig[selectedStatus]?.label || 'Unknown'"></span>
                </span>
            </div>
            
            <!-- Dropdown Icon -->
            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </span>
        </button>
        
        <!-- Custom Dropdown Menu -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute top-full mt-1 w-full bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2 z-50"
            style="display: none;"
        >
            @foreach($statusOptions as $status => $option)
                <button 
                    type="button"
                    @click="updateStatus('{{ $status }}')"
                    class="w-full flex items-center space-x-3 px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150"
                    :class="{ 'bg-primary-50 dark:bg-primary-900/10': selectedStatus === '{{ $status }}' }"
                >
                    <!-- Status Color Circle -->
                    <div class="w-3 h-3 rounded-full border-2 border-white dark:border-gray-900 {{ $option['color'] }} flex-shrink-0"></div>
                    
                    <!-- Status Label -->
                    <span class="text-sm font-medium text-gray-900 dark:text-white flex-1">{{ $option['label'] }}</span>
                    
                    <!-- Current Status Indicator -->
                    <div 
                        x-show="selectedStatus === '{{ $status }}'"
                        class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"
                    ></div>
                </button>
            @endforeach
        </div>
    </div>
</x-dynamic-component>
