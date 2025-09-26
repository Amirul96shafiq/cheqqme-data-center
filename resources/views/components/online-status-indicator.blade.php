@if($showTooltip)
    <x-tooltip :text="$getStatusDisplayName()" position="top" align="center">
        <div 
            class="{{ $getStatusClasses() }} online-status-indicator cursor-help"
            data-user-id="{{ $user->id }}"
            data-current-status="{{ $user->online_status }}"
            data-is-current-user="{{ auth()->check() && auth()->id() === $user->id ? 'true' : 'false' }}"
        ></div>
    </x-tooltip>
@else
    <div class="relative inline-block">
        <div 
            class="{{ $getStatusClasses() }} online-status-indicator"
            data-user-id="{{ $user->id }}"
            data-current-status="{{ $user->online_status }}"
            data-is-current-user="{{ auth()->check() && auth()->id() === $user->id ? 'true' : 'false' }}"
        ></div>
    </div>
@endif