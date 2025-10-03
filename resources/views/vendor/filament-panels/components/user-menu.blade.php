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
                    <div class="w-full h-full bg-gradient-to-r from-blue-500 to-purple-600"></div>
                @endif
            </div>
            
            <!-- Avatar Below Cover Image -->
            <div class="flex justify-center -mt-8">
                <x-filament::avatar
                    :src="filament()->getUserAvatarUrl($user)"
                    :alt="filament()->getUserName($user)"
                    size="w-16 h-16"
                    class="border-4 border-white dark:border-gray-900 z-10"
                    draggable="false"
                />
            </div>

            <!-- User Info -->
            <div class="pt-2 pb-3 px-4 text-center">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                    {{ $user->name }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $user->email }}
                </p>
            </div>
        </div>

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

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
        <x-filament::dropdown.list>
            <x-filament-panels::theme-switcher />
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
    width: 280px;
}

.fi-user-menu .user-profile-header img {
    max-width: 100%;
    height: auto;
}

.fi-user-menu .user-profile-header .fi-avatar {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
</style>
