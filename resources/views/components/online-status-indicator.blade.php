@if($showTooltip)
    <x-tooltip :text="$getStatusDisplayName()" position="top" align="center">
        <div 
            class="{{ $getSizeClasses() }} {{ $getStatusClasses() }} cursor-help online-status-indicator"
            data-user-id="{{ $user->id }}"
            data-current-status="{{ $user->online_status }}"
        ></div>
    </x-tooltip>
@else
    <div class="relative inline-block">
        <div 
            class="{{ $getSizeClasses() }} {{ $getStatusClasses() }} online-status-indicator"
            data-user-id="{{ $user->id }}"
            data-current-status="{{ $user->online_status }}"
        ></div>
    </div>
@endif