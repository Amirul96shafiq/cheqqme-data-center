@php
    $user = filament()->auth()->user();
    $items = filament()->getUserMenuItems();

    $profileItem = $items['profile'] ?? $items['account'] ?? null;
    $profileItemUrl = $profileItem?->getUrl();
    $profilePage = filament()->getProfilePage();
    $hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

    $logoutItem = $items['logout'] ?? null;

    $items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

@php
    $dropdownAttributes = \Filament\Support\prepare_inherited_attributes($attributes)
        ->class(['fi-user-menu', 'fi-user-profile-menu']);
@endphp

<x-filament::dropdown
    placement="bottom-end"
    teleport
    :attributes="$dropdownAttributes"
>
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="shrink-0"
        >
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

        <!-- User Profile Header with Cover Image -->
        <div class="relative user-profile-header">
            <!-- Cover Image Background -->
            <div class="relative h-full bg-gray-100 dark:bg-gray-800 rounded-t-lg overflow-hidden">
                @if($user->getFilamentCoverImageUrl())
                    <img 
                        src="{{ $user->getFilamentCoverImageUrl() }}" 
                        alt="Cover Image"
                        class="w-full h-full object-cover z-5"
                        draggable="false"
                    />
                @else
                    <img 
                        src="{{ asset('images/default-cover-img.png') }}" 
                        alt="Default Cover Image"
                        class="w-full h-full object-cover z-5"
                        draggable="false"
                    />
                @endif
                <!-- User ID badge -->
                <div class="absolute top-1 right-1 z-20">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/25 text-white dark:bg-black/25">
                        ID: {{ $user->id }}
                    </span>
                </div>
            </div>
            
            <!-- Avatar Container -->
            <div class="flex justify-center -mt-8 relative">
                <div class="relative inline-block">
                    <x-filament::avatar
                        :src="filament()->getUserAvatarUrl($user)"
                        :alt="filament()->getUserName($user)"
                        size="w-16 h-16"
                        class="border-4 border-white dark:border-gray-900 z-10"
                        draggable="false"
                    />
                    
                    <!-- Online Status Indicator - positioned within avatar -->
                    <div class="absolute bottom-0.5 right-0.5 z-20">
                        <x-tooltip position="top" text="{{ $user->online_status }}">
                            <x-interactive-online-status-indicator 
                                :user="$user" 
                                size="md" 
                                :showTooltip="false" 
                                position="bottom"
                            />
                        </x-tooltip>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="px-4 py-3 text-center">

                <!-- Username -->
                <h3 class="text-md font-bold text-gray-900 dark:text-white truncate">
                    {{ $user->username }}
                </h3>

                <!-- Name -->
                <h4 class="text-[10px] font-regular text-gray-700 dark:text-gray-200 truncate -mt-1">
                    {{ $user->name }}
                </h4>

                <!-- Email -->
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-2">
                    {{ $user->email }}
                </p>
                
                <!-- Phone Number and Date of Birth -->
                @if($user->phone || $user->date_of_birth)
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1 flex items-center justify-center gap-1">
                        @if($user->phone)
                            {{ $user->getPhoneWithoutCountryCode() }}
                        @endif
                        @if($user->phone && $user->date_of_birth)
                            <span>|</span>
                        @endif
                        @if($user->date_of_birth)
                            {{ $user->date_of_birth->format('d/m/Y') }}
                        @endif
                    </p>
                @endif
                            
                
                <!-- User Badges -->
                <div class="my-3">
                    <x-user-badges :user="$user" size="sm" gap="1" :showIcons="true" />
                </div>

                <!-- Spotify Now Playing -->
                @if($user->hasSpotifyAuth())
                    <div class="my-3">
                        <x-spotify-now-playing-alpine :user="$user" context="dropdown" />
                    </div>
                @endif
            </div>
        </div>

        @if(!$profileItem || $profileItem->getUrl() !== 'javascript:void(0)')
            <x-filament::dropdown.list>
                <x-filament::dropdown.list.item
                    :color="$profileItem?->getColor()"
                    :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
                    :href="$profileItemUrl ?? filament()->getProfileUrl()"
                    :target="($profileItem?->shouldOpenUrlInNewTab() ?? false) ? '_blank' : null"
                    tag="a"
                >
                    {{ $profileItem?->getLabel() ?? ($profilePage ? $profilePage::getLabel() : null) ?? filament()->getUserName($user) }}
                </x-filament::dropdown.list.item>
            </x-filament::dropdown.list>
        @endif

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
        </x-filament::dropdown.list>
    @endif

    @php
        $greetingItem = null;
        $originalItems = filament()->getUserMenuItems();
        if (isset($originalItems['profile']) && $originalItems['profile']->getUrl() === 'javascript:void(0)') {
            $greetingItem = $originalItems['profile'];
        }
    @endphp

    @if ($greetingItem && $greetingItem->getUrl() === 'javascript:void(0)')
        <x-filament::dropdown.list>
            <x-tooltip position="top" text="{{ __('dashboard.user-menu.tooltip.greeting') }}">
                <div class="px-3 py-2 text-center hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-md transition-colors cursor-pointer">
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                        @if($greetingItem->getIcon())
                            @svg($greetingItem->getIcon(), 'h-5 w-5 text-primary-500')
                        @endif
                        <span class="text-primary-500 dark:text-primary-400 font-regular">{{ $greetingItem->getLabel() }}</span>
                    </div>
                </div>
            </x-tooltip>
        </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
            @php
                $itemPostAction = $item->getPostAction();
            @endphp

            <x-filament::dropdown.list.item
                :action="$itemPostAction"
                :color="$item->getColor()"
                :href="$item->getUrl()"
                :icon="$item->getIcon()"
                :method="filled($itemPostAction) ? 'post' : null"
                :tag="filled($itemPostAction) ? 'form' : 'a'"
                :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null"
            >
                {{ $item->getLabel() }}
            </x-filament::dropdown.list.item>
        @endforeach

        <button
            type="button"
            class="fi-dropdown-list-item flex w-full items-center gap-2 whitespace-nowrap rounded-md p-2 text-sm transition-colors duration-75 outline-none disabled:pointer-events-none disabled:opacity-70 fi-color-danger hover:bg-danger-50 focus-visible:bg-danger-50 dark:hover:bg-danger-400/10 dark:focus-visible:bg-danger-400/10"
            x-data="{ loggingOut: false }"
            x-bind:disabled="loggingOut"
            x-bind:class="{ 'opacity-70 cursor-wait': loggingOut }"
            x-on:click="
                if (loggingOut) return;
                loggingOut = true;

                // Clear all intervals and timeouts to prevent polling conflicts during logout
                for (let i = 1; i < 10000; i++) {
                    clearInterval(i);
                    clearTimeout(i);
                }

                // Create and submit logout form directly for reliable session termination
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ $logoutItem?->getUrl() ?? filament()->getLogoutUrl() }}';
                form.style.display = 'none';

                const tokenInput = document.createElement('input');
                tokenInput.type = 'hidden';
                tokenInput.name = '_token';
                tokenInput.value = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
                form.appendChild(tokenInput);

                document.body.appendChild(form);
                form.submit();
            "
        >

            <!-- Original icon - hidden when logging out -->
            <x-filament::icon
                icon="heroicon-o-arrow-left-on-rectangle"
                x-show="!loggingOut"
                class="fi-dropdown-list-item-icon h-5 w-5 text-danger-500 dark:text-danger-400"
            />

            <!-- Loading spinner - shown when logging out -->
            <x-filament::loading-indicator
                x-cloak
                x-show="loggingOut"
                class="fi-dropdown-list-item-icon h-5 w-5 text-danger-500 dark:text-danger-400"
            />

            <!-- Logout label -->
            <span class="fi-dropdown-list-item-label flex-1 truncate text-start text-danger-600 dark:text-danger-400">
                {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
            </span>

        </button>

    </x-filament::dropdown.list>
    
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}

<!-- Minimal styling for user profile header -->
<style>
/* Only target the user profile menu dropdown, not language switcher */
.fi-user-profile-menu .fi-dropdown-panel {
    margin-top: -45px;
    z-index: 1000 !important;
    max-width: 312px !important;
}

.fi-user-profile-menu .user-profile-header img {
    max-width: 100%;
    height: auto;
}

</style>
