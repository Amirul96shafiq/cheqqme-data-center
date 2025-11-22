@props([
    'user',
    'size' => 'md',
    'gap' => '2',
    'showIcons' => true,
])

@php
    $sizeClasses = match($size) {
        'sm' => 'text-[10px] px-2 py-0.5',
        'md' => 'text-[10px] md:text-xs px-2.5 py-1',
        'lg' => 'text-xs md:text-sm px-3 py-1.5',
        default => 'text-[10px] md:text-xs px-2.5 py-1',
    };

    $iconSize = match($size) {
        'sm' => 'w-2.5 h-2.5',
        'md' => 'w-3 h-3',
        'lg' => 'w-3.5 h-3.5',
        default => 'w-3 h-3',
    };

    $gapClass = "gap-{$gap}";
    $itemGap = $showIcons ? 'gap-1' : '';
@endphp

<div class="flex flex-wrap {{ $gapClass }} justify-center">

    <!-- Country Badge -->
    @if ($user && $user->country)
        <x-tooltip position="top" text="{{ __('user.badge.country') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-teal-100/90 text-teal-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="map-pin" class="{{ $iconSize }}" color="" />
                @endif
                <span>{{ $user->country }}</span>
            </span>
        </x-tooltip>
    @endif

    <!-- Timezone Badge -->
    @if ($user && $user->timezone)
        <x-tooltip position="top" text="{{ __('user.badge.timezone') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-teal-100/90 text-teal-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="clock" class="{{ $iconSize }}" color="" />
                @endif
                <span>{{ $user->timezone }}</span>
            </span>
        </x-tooltip>
    @endif

    <!-- Google OAuth Badge -->
    @if ($user->google_id && $user->google_connected_at)
        <x-tooltip position="top" text="{{ __('user.badge.google_oauth') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-gray-100/90 text-gray-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="google" class="{{ $iconSize }}" color="" />
                @endif
                <span>Google</span>
            </span>
        </x-tooltip>
    @endif

    <!-- Google Calendar Badge -->
    @if ($user->google_calendar_token && $user->google_calendar_connected_at)
        <x-tooltip position="top" text="{{ __('user.badge.google_calendar') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-gray-100/90 text-gray-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="google-calendar" class="{{ $iconSize }}" color="" />
                @endif
                <span>Calendar</span>
            </span>
        </x-tooltip>
    @endif

    <!-- Microsoft OAuth Badge -->
    @if ($user->microsoft_id && $user->microsoft_connected_at)
        <x-tooltip position="top" text="{{ __('user.badge.microsoft_oauth') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-blue-100/90 text-blue-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="microsoft" class="{{ $iconSize }}" color="" />
                @endif
                <span>Microsoft</span>
            </span>
        </x-tooltip>
    @endif

    <!-- Zoom API Badge -->
    @if ($user->zoom_token && $user->zoom_connected_at)
        <x-tooltip position="top" text="{{ __('user.badge.zoom_api') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-indigo-100/90 text-indigo-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="video-camera" class="{{ $iconSize }}" color="" />
                @endif
                <span>Zoom</span>
            </span>
        </x-tooltip>
    @endif

    <!-- Spotify Badge -->
    @if ($user->spotify_id && $user->spotify_connected_at)
        <x-tooltip position="top" text="{{ __('user.badge.spotify') }}">
            <span class="inline-flex items-center {{ $itemGap }} {{ $sizeClasses }} rounded-full font-medium bg-green-100/90 text-green-900 shadow-sm">
                @if($showIcons)
                    <x-icons.custom-icon name="spotify" class="{{ $iconSize }}" color="" />
                @endif
                <span>Spotify</span>
            </span>
        </x-tooltip>
    @endif

</div>

