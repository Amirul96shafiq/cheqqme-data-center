{{-- Custom Filament navigation item for Action Board with badge overlay --}}
<div class="relative flex items-center justify-center">
    <x-heroicon-o-rocket-launch class="w-6 h-6" />
    @if(isset($taskCount) && $taskCount > 0)
        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white bg-primary-500 rounded-full shadow transform translate-x-1/2 -translate-y-1/2">
            {{ $taskCount }}
        </span>
    @endif
</div>
