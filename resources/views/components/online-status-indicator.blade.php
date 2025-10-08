@if($showTooltip)
    <x-tooltip :text="$getStatusDisplayName()" position="top" align="center">
        <div 
            class="{{ $getStatusClasses() }} online-status-indicator
            {!! $getDataAttributesString() !!}
        ></div>
    </x-tooltip>
@else
    <div class="relative inline-block">
        <div 
            class="{{ $getStatusClasses() }} online-status-indicator"
            {!! $getDataAttributesString() !!}
        ></div>
    </div>
@endif