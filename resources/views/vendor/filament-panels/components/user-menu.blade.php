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
        ->class(['fi-user-menu']);
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
            <div class="relative h-[56px] bg-gray-100 dark:bg-gray-800 rounded-t-lg overflow-hidden">
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
                
                <!-- User Badges -->
                <div class="flex flex-wrap gap-1 justify-center my-3">

                    <!-- Country -->
                    @if($user->country)
                        <x-tooltip position="top" text="{{ __('user.table.country') }}">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                {{ $user->country }}
                            </span>
                        </x-tooltip>
                    @endif
                    
                    <!-- Timezone -->
                    @if($user->timezone)
                        <x-tooltip position="top" text="{{ __('user.table.timezone') }}">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                {{ $user->timezone }}
                            </span>
                        </x-tooltip>
                    @endif

                </div>
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

        <x-filament::dropdown.list.item
            :action="$logoutItem?->getUrl() ?? filament()->getLogoutUrl()"
            :color="$logoutItem?->getColor()"
            :icon="$logoutItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.logout-button') ?? 'heroicon-m-arrow-left-on-rectangle'"
            method="post"
            tag="form"
        >
            {{ $logoutItem?->getLabel() ?? __('filament-panels::layout.actions.logout.label') }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
    
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}

<!-- Minimal styling for user profile header -->
<style>
.fi-user-menu .fi-dropdown-panel {
    margin-top: -45px;
    z-index: 1000 !important;
}

.fi-user-menu .user-profile-header img {
    max-width: 100%;
    height: auto;
}

</style>
