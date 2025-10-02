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
    
    // Always add user data attributes, using default images if needed
    $coverImageUrl = $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png';
    $avatarUrl = filament()->getUserAvatarUrl($user); // This automatically handles fallback to Filament's default avatar
    
    $dropdownAttributes = $dropdownAttributes->merge([
        'data-user-cover' => $user->id,
        'data-cover-image' => $coverImageUrl,
        'data-avatar-image' => $avatarUrl
    ]);
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

        @if ($hasProfileItem)
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
        @else
            <x-filament::dropdown.header
                :color="$profileItem?->getColor()"
                :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
            >
                {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
            </x-filament::dropdown.header>
        @endif

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
    
    <!-- Custom avatar overlay using img element for better reliability -->
    <img 
        src="{{ $avatarUrl }}?v={{ time() }}" 
        alt="{{ $user->name }}"
        class="avatar-overlay"
        data-user-id="{{ $user->id }}"
    />
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}

<!-- Preload images for better performance -->
<link rel="preload" as="image" href="{{ $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png' }}?v={{ time() }}" data-user-id="{{ $user->id }}">
<link rel="preload" as="image" href="{{ $avatarUrl }}?v={{ time() }}" data-user-id="{{ $user->id }}">

<!-- Custom styling for increased minimum height and cover image background -->
<style data-user-id="{{ $user->id }}" data-cache-buster="{{ time() }}">
/* User menu dropdown with cover image background */
.fi-user-menu .fi-dropdown-panel {
    min-height: 35vh !important;
    position: relative;
    overflow: hidden;
    margin-top: -45px !important; /* Reduce gap from topbar */
    z-index: 1000 !important;
}

/* Cover image background at top of dropdown */
.fi-user-menu .fi-dropdown-panel::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 100px;
    background-image: url('{{ $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png' }}?v={{ time() }}');
    background-size: cover;
    background-position: center top;
    background-repeat: no-repeat;
    background-attachment: local;
    opacity: 1;
    z-index: 0;
    pointer-events: none;
    display: block;
    border-radius: 0.65rem 0.65rem 0 0;
}

/* Ensure content is above the cover image */
.fi-user-menu .fi-dropdown-panel > * {
    position: relative;
    z-index: 2;
}

/* Custom avatar overlay styles for img element */
.fi-user-menu .fi-dropdown-panel .avatar-overlay {
    position: absolute;
    top: 62px;
    left: 50%;
    transform: translateX(-50%);
    width: 82px;
    height: 82px;
    border-radius: 50%;
    border: 5px solid rgba(255, 255, 255);
    z-index: 3;
    pointer-events: none;
    object-fit: cover;
}

/* Dark theme avatar border */
.dark .fi-user-menu .fi-dropdown-panel .avatar-overlay {
    border: 5px solid rgb(17 24 39); /* dark:bg-gray-900 */
}

/* Add space and gap for cover image at top */
.fi-user-menu .fi-dropdown-panel .fi-dropdown-list:first-child,
.fi-user-menu .fi-dropdown-panel .fi-dropdown-header:first-child {
    padding-top: 145px !important;
}
</style>

<!-- JavaScript for optimized image preloading -->
<script data-user-id="{{ $user->id }}">
document.addEventListener('DOMContentLoaded', function() {
    const coverImageUrl = '{{ $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png' }}?v={{ time() }}';
    const avatarImageUrl = '{{ $avatarUrl }}?v={{ time() }}';
    const userId = '{{ $user->id }}';
    
    console.log('Preloading images for user:', userId);
    console.log('Avatar URL:', avatarImageUrl);
    
    // Preload cover image
    const preloadCoverImage = new Image();
    preloadCoverImage.onload = function() {
        console.log('Cover image preloaded successfully');
    };
    preloadCoverImage.src = coverImageUrl;
    
    // Preload avatar image
    const preloadAvatarImage = new Image();
    preloadAvatarImage.onload = function() {
        console.log('Avatar image preloaded successfully:', avatarImageUrl);
    };
    preloadAvatarImage.onerror = function() {
        console.warn('Failed to preload avatar image:', avatarImageUrl);
    };
    preloadAvatarImage.src = avatarImageUrl;
});
</script>
