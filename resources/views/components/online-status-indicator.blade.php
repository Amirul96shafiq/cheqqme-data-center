@if($showTooltip)
    <x-tooltip :text="$getStatusDisplayName()" position="top" align="center">
        <div 
            class="{{ $getSizeClasses() }} {{ $getStatusClasses() }} cursor-help"
        ></div>
    </x-tooltip>
@else
    <div class="relative inline-block">
        <div 
            class="{{ $getSizeClasses() }} {{ $getStatusClasses() }}"
        ></div>
    </div>
@endif