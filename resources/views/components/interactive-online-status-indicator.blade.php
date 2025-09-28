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
          <x-online-status-dropdown 
              :user="$user" 
              :status-options="$getStatusOptions()"
              position="bottom" 
              :show-tooltip="$showTooltip"
          />
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
        <x-online-status-dropdown 
            :user="$user" 
            :status-options="$getStatusOptions()"
            position="top" 
            :show-tooltip="$showTooltip"
        />
    </div>
@endif

