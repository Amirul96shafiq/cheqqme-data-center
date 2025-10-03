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
    
    // Always add user data attributes, using optimized images if available
    $originalCoverImageUrl = $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png';
    
    // Use ImageOptimizationService for cover image only if user has uploaded a custom cover image
    if ($user->cover_image && !empty($user->cover_image)) {
        try {
            $imageOptimization = app(\App\Services\ImageOptimizationService::class);
            $optimizedCoverUrl = $imageOptimization->getOptimizedImageUrl($user->cover_image, 'medium');
            $coverImageUrl = $optimizedCoverUrl ?? $originalCoverImageUrl;
        } catch (\Exception $e) {
            // Fallback to original if optimization fails
            $coverImageUrl = $originalCoverImageUrl;
        }
    } else {
        // Use default cover image for users without custom uploaded cover image
        $coverImageUrl = '/images/default-cover-img.png';
    }
    
    $avatarUrl = filament()->getUserAvatarUrl($user); // This automatically handles fallback to Filament's default avatar
    
    // Create optimized URL for JavaScript usage
    $coverImageOptimizedUrl = $coverImageUrl;
    
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

        <!-- Interactive Profile Header -->
        <div x-data="profileHeader()" class="relative">
            <!-- Custom User Profile Header with Cover Image Background -->
            <div class="relative h-[56px] bg-gray-100 dark:bg-gray-800 rounded-t-lg">
                <!-- Cover Image Background -->
                <div class="absolute inset-0" 
                     :style="coverImageStyle">
                </div>

                <!-- Avatar Overlay -->
                <div class="absolute top-6 left-1/2 transform -translate-x-1/2">
                    <div class="relative">
                        <div class="relative w-14 h-14 rounded-full border-4 border-white dark:border-gray-900 bg-white dark:bg-gray-800"
                             data-open-image-modal>
                            <img :src="avatarImageUrl" 
                                 :alt="$user.name" 
                                 class="w-full h-full rounded-full object-cover">
                        </div>
                        
                        <!-- Online Status Indicator -->
                        <div class="absolute -bottom-0.5 -right-0.5">
                            <x-interactive-online-status-indicator :user="$user" size="md" :showTooltip="false" position="bottom" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Info Section -->
            <div class="px-4 pt-10 pb-4 text-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                    {{ $user->name }}
                </h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                    {{ $user->email }}
                </p>
                
                {{-- <!-- Quick Actions -->
                <div class="flex gap-1 justify-center">
                    <button data-open-image-modal
                            class="flex items-center gap-1 px-2 py-1 text-xs bg-blue-50 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Manage
                    </button>
                    
                    @if ($hasProfileItem)
                        <a href="{{ $profileItemUrl ?? filament()->getProfileUrl() }}"
                           class="flex items-center gap-1 px-2 py-1 text-xs bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Profile
                        </a>
                    @endif
                </div> --}}
            </div>
        </div>

        <!-- Profile Item List -->
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

<!-- Include the Livewire User Profile Images Component -->
<livewire:user-profile-images />

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}

<!-- Preload images for better performance -->
<link rel="preload" as="image" href="{{ $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png' }}?v={{ time() }}" data-user-id="{{ $user->id }}">
<link rel="preload" as="image" href="{{ $avatarUrl }}?v={{ time() }}" data-user-id="{{ $user->id }}">

<!-- Custom styling for compact user menu -->
<style data-user-id="{{ $user->id }}" data-cache-buster="{{ time() }}">
/* Compact user menu dropdown */
.fi-user-menu .fi-dropdown-panel {
    position: relative;
    overflow: hidden;
    margin-top: -45px !important; /* Reduce gap from topbar */
    z-index: 1000 !important;
    width: 260px; /* Default width */
}

/* Image responsiveness in dropdown */
.fi-user-menu .interactive-profile-header img {
    max-width: 100%;
    height: auto;
}

::-webkit-scrollbar {
    width: 4px;
}

/* Dark theme avatar border fix */
.dark .fi-user-menu .fi-dropdown-panel .avatar-border {
    border: 3px solid rgb(17 24 39); /* dark:bg-gray-900 */
}

/* Override avatar overlay old styles */
.fi-user-menu .fi-dropdown-panel .avatar-overlay {
    display: none !important;
}
</style>

<!-- JavaScript for interactive user menu -->
<script data-user-id="{{ $user->id }}">
// Alpine.js data function for profile header
function profileHeader() {
    return {
        coverImageUrl: '{{ $coverImageOptimizedUrl }}?v={{ time() }}',
        avatarImageUrl: '{{ $avatarUrl }}?v={{ time() }}',
        
        get coverImageStyle() {
            return `background-image: url('${this.coverImageUrl}'); background-size: cover; background-position: center center; background-repeat: no-repeat;`;
        },
        
        refreshImages() {
            this.coverImageUrl = '{{ $coverImageOptimizedUrl }}?v={{ time() }}';
            this.avatarImageUrl = '{{ $avatarUrl }}?v={{ time() }}';
        }
    }
}

// Global event listener for image updates
document.addEventListener('livewire:init', function() {
    Livewire.on('avatar-updated', () => {
        // Trigger page refresh to update images
        window.location.reload();
    });
    
    Livewire.on('cover-image-updated', () => {
        // Trigger page refresh to update images
        window.location.reload();
    });
});

// Image preloading for better performance
document.addEventListener('DOMContentLoaded', function() {
    const coverImageUrl = '{{ $user->getFilamentCoverImageUrl() ?? '/images/default-cover-img.png' }}?v={{ time() }}';
    const avatarImageUrl = '{{ $avatarUrl }}?v={{ time() }}';
    
    // Preload cover image
    const preloadCoverImage = new Image();
    preloadCoverImage.src = coverImageUrl;
    
    // Preload avatar image
    const preloadAvatarImage = new Image();
    preloadAvatarImage.src = avatarImageUrl;
});
</script>
