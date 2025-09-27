@if($showTooltip)
    <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
        <!-- Status Indicator (Clickable) with Tooltip -->
        <x-tooltip :text="$getStatusDisplayName()" position="top" align="center">
            <button 
                @click="open = !open"
                class="{{ $getSizeClasses() }} {{ $getStatusClasses() }} cursor-pointer hover:scale-110 transition-transform duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                :class="{ 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-800': open }"
                title="Click to change status"
                data-status-indicator="true"
                {!! $getDataAttributesString() !!}
            ></button>
        </x-tooltip>
            
          <!-- Dropdown Menu -->
          <div 
              x-show="open"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 scale-95"
              x-transition:enter-end="opacity-100 scale-100"
              x-transition:leave="transition ease-in duration-150"
              x-transition:leave-start="opacity-100 scale-100"
              x-transition:leave-end="opacity-0 scale-95"
              class="absolute top-full mt-2 left-1/2 transform -translate-x-1/2 z-50 min-w-[180px]"
              style="display: none;"
          >
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                  @foreach($getStatusOptions() as $status => $option)
                      <button 
                          @click="
                              console.log('Dropdown clicked for status:', '{{ $status }}');
                              if (typeof window.updateOnlineStatus === 'function') {
                                  window.updateOnlineStatus('{{ $status }}');
                              } else {
                                  console.error('updateOnlineStatus function not found');
                              }
                              open = false;
                          "
                          class="w-full flex items-center space-x-3 px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 {{ $user->online_status === $status ? 'bg-primary-50 dark:bg-primary-900/10' : '' }}"
                      >
                          <!-- Status Color Circle -->
                          <div class="w-3 h-3 rounded-full border-2 border-white dark:border-gray-900 {{ $option['color'] }} flex-shrink-0"></div>
                          
                          <!-- Status Label -->
                          <span class="text-sm font-medium text-gray-900 dark:text-white flex-1">{{ $option['label'] }}</span>
                          
                          <!-- Current Status Indicator -->
                          @if($user->online_status === $status)
                              <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></div>
                          @endif
                      </button>
                  @endforeach
              </div>
              
              <!-- Dropdown Arrow -->
              <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-b-4 border-transparent border-b-white dark:border-b-gray-800"></div>
          </div>
    </div>
@else
    <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
        <!-- Status Indicator (Clickable) -->
        <button 
            @click="open = !open"
            class="{{ $getSizeClasses() }} {{ $getStatusClasses() }} cursor-pointer hover:scale-110 transition-transform duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
            :class="{ 'ring-2 ring-primary-500 ring-offset-2 dark:ring-offset-gray-800': open }"
            title="Click to change status"
            data-status-indicator="true"
            {!! $getDataAttributesString() !!}
        ></button>
        
        <!-- Dropdown Menu -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute bottom-full mb-2 left-1/2 transform -translate-x-1/2 z-50 min-w-[180px]"
            style="display: none;"
        >
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-2">
                @foreach($getStatusOptions() as $status => $option)
                    <button 
                        @click="
                            console.log('Dropdown clicked for status:', '{{ $status }}');
                            if (typeof window.updateOnlineStatus === 'function') {
                                window.updateOnlineStatus('{{ $status }}');
                            } else {
                                console.error('updateOnlineStatus function not found');
                            }
                            open = false;
                        "
                        class="w-full flex items-center space-x-3 px-4 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 {{ $user->online_status === $status ? 'bg-primary-50 dark:bg-primary-900/10' : '' }}"
                    >
                        <!-- Status Color Circle -->
                        <div class="w-3 h-3 rounded-full border-2 border-white dark:border-gray-900 {{ $option['color'] }} flex-shrink-0"></div>
                        
                        <!-- Status Label -->
                        <span class="text-sm font-medium text-gray-900 dark:text-white flex-1">{{ $option['label'] }}</span>
                        
                        <!-- Current Status Indicator -->
                        @if($user->online_status === $status)
                            <div class="w-2 h-2 rounded-full bg-primary-500 flex-shrink-0"></div>
                        @endif
                    </button>
                @endforeach
            </div>
            
            <!-- Dropdown Arrow -->
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-white dark:border-t-gray-800"></div>
        </div>
    </div>
@endif

