{{-- Language Switcher Component --}}
<div class="flex justify-center">
    <div x-data="{ open: false }" class="relative">
        
        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false"
            role="menu"
            aria-orientation="vertical"
            class="absolute bottom-full mb-2 w-max rounded-md shadow-lg bg-white dark:bg-neutral-900 ring-1 ring-gray-950/5 dark:ring-white/10 z-10"
            x-cloak
            style="left: 50%; transform: translateX(-50%);">
            <div class="py-2 text-sm text-gray-700 dark:text-gray-100">
                @if(app()->getLocale() !== 'en')
                <form method="POST" action="{{ route('locale.set') }}" class="inline-block w-full">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <button type="submit"
                            role="menuitem"
                            class="block w-full text-center font-semibold px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800">
                    <span class="text-center p-1.5 mr-2 text-primary-500 bg-primary-100/25 dark:bg-primary-100/5 rounded-lg">EN</span>{{ __('auth.english') }}
                    </button>
                </form>
                @endif
                @if(app()->getLocale() !== 'ms')
                <form method="POST" action="{{ route('locale.set') }}" class="inline-block w-full">
                    @csrf
                    <input type="hidden" name="locale" value="ms">
                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                    <button type="submit"
                            role="menuitem"
                            class="block w-full text-center font-semibold px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800">
                        <span class="text-center p-1.5 mr-2 text-primary-500 bg-primary-100/25 dark:bg-primary-100/5 rounded-lg">MS</span>{{ __('auth.malay') }}
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
