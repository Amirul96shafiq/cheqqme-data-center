{{-- Theme Toggle Component --}}
<div class="flex justify-center">                    
    <fieldset class="flex flex-row gap-2 p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 border border-gray-200/50 dark:border-gray-700/50">
        <legend class="sr-only">Theme selection</legend>
        {{-- Light Theme (Sun) --}}
        <x-tooltip position="bottom" :text="__('login.tooltips.lightTheme')">
            <button type="button" 
                    class="theme-toggle-btn p-2 rounded-lg transition-colors" 
                    data-theme="light" 
                    aria-label="Enable light theme"
                    title="Enable light theme">
                <x-heroicon-m-sun class="w-5 h-5" aria-hidden="true" />
            </button>
        </x-tooltip>

        {{-- Dark Theme (Moon) --}}
        <x-tooltip position="bottom" :text="__('login.tooltips.darkTheme')">
            <button type="button" 
                    class="theme-toggle-btn p-2 rounded-lg transition-colors" 
                    data-theme="dark" 
                    aria-label="Enable dark theme"
                    title="Enable dark theme">
                <x-heroicon-m-moon class="w-5 h-5" aria-hidden="true" />
            </button>
        </x-tooltip>

        {{-- System Theme (Desktop) --}}
        <x-tooltip position="bottom" :text="__('login.tooltips.systemTheme')">
            <button type="button" 
                    class="theme-toggle-btn p-2 rounded-lg transition-colors" 
                    data-theme="system" 
                    aria-label="Enable system theme"
                    title="Enable system theme">
                <x-heroicon-m-computer-desktop class="w-5 h-5" aria-hidden="true" />
            </button>
        </x-tooltip>
    </fieldset>
</div>
