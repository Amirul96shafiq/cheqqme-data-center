{{-- Language Switcher Component --}}
<div class="flex justify-center">
    <div x-data="{ open: false }" class="relative">
        
        <!-- Dropdown -->
        <div x-show="open" 
            @click.away="open = false"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            role="menu"
            aria-orientation="vertical"
            class="fi-dropdown-panel absolute bottom-full mb-2 w-max overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10"
            style="left: 50%; transform: translateX(-50%); z-index: 50;"
            x-cloak>
            <div class="fi-dropdown-list space-y-1 p-2">
                @if(app()->getLocale() !== 'en')
                <form method="POST" action="{{ route('locale.set') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <button type="submit"
                            role="menuitem"
                            class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm font-medium transition-colors duration-75 outline-none hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">EN</span>
                        <span class="flex-1 text-left text-gray-700 dark:text-gray-200">{{ __('auth.english') }}</span>
                    </button>
                </form>
                @endif
                @if(app()->getLocale() !== 'ms')
                <form method="POST" action="{{ route('locale.set') }}" class="w-full">
                    @csrf
                    <input type="hidden" name="locale" value="ms">
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <button type="submit"
                            role="menuitem"
                            class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm font-medium transition-colors duration-75 outline-none hover:bg-gray-50 focus-visible:bg-gray-50 dark:hover:bg-white/5 dark:focus-visible:bg-white/5">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-md bg-primary-50 text-xs font-bold text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">MS</span>
                        <span class="flex-1 text-left text-gray-700 dark:text-gray-200">{{ __('auth.malay') }}</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        <!-- Toggle -->
        <x-tooltip position="left" :text="__('login.tooltips.languageSwitcher')">
            <button @click="open = !open" 
                    type="button"
                    aria-label="Change language"
                    aria-expanded="false"
                    aria-haspopup="true"
                    class="flex items-center justify-center w-10 h-10 language-switch-trigger text-primary-600 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 hover:border-gray-300/50 dark:hover:border-gray-600/50 rounded-lg transition font-semibold">
                {{ strtoupper(app()->getLocale()) }}
            </button>
        </x-tooltip>
    </div>
</div>
