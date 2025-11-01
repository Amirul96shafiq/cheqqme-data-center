@props([
    'user',
    'size' => 'md',
    'showStatus' => true,
    'statusSize' => null,
    'highlightCurrentUser' => false,
    'coverImageBorder' => false,
    'clickable' => true,
    'lazyLoad' => true,
])

@php
    // Size configurations
    $sizeClasses = [
        'xs' => 'w-6 h-6',
        'sm' => 'w-8 h-8',
        'md' => 'w-10 h-10',
        'lg' => 'w-12 h-12',
        'xl' => 'w-16 h-16',
    ];
    
    $textSizes = [
        'xs' => 'text-[10px]',
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-base',
        'xl' => 'text-lg',
    ];
    
    // Status indicator positioning based on avatar size
    $statusPositions = [
        'xs' => 'absolute bottom-0 -right-0.5',
        'sm' => 'absolute -bottom-1 -right-0.5',
        'md' => 'absolute -bottom-1 -right-0.5',
        'lg' => 'absolute -bottom-1 -right-0.5',
        'xl' => 'absolute -bottom-1 -right-1',
    ];
    
    // Auto-determine status size based on avatar size if not specified
    if (!$statusSize) {
        $statusSize = match($size) {
            'xs' => 'xs',
            'sm' => 'sm',
            'md' => 'md',
            'lg' => 'md',
            'xl' => 'lg',
            default => 'md',
        };
    }
    
    $avatarSizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $textSizeClass = $textSizes[$size] ?? $textSizes['md'];
    $statusPosition = $statusPositions[$size] ?? $statusPositions['md'];
    
    // Get avatar URL - prefer Filament's avatar provider
    $avatarUrl = filament()->getUserAvatarUrl($user);
    
    // Fallback to UiAvatarsProvider if Filament returns null or default
    if (!$avatarUrl || str_contains($avatarUrl, 'ui-avatars.com')) {
        $customAvatarPath = $user->avatar ?? null;
        if ($customAvatarPath) {
            $avatarUrl = \Storage::url($customAvatarPath);
        } else {
            // Use UiAvatarsProvider as fallback
            $avatarUrl = (new \Filament\AvatarProviders\UiAvatarsProvider())->get($user);
        }
    }
    
    // Check if user has cover image (for special border styling)
    $hasCoverImage = $coverImageBorder && method_exists($user, 'getFilamentCoverImageUrl') && $user->getFilamentCoverImageUrl();
    
    // Determine if this is the current user's avatar
    $isCurrentUser = $highlightCurrentUser && auth()->id() === $user->id;
    
    // Username for fallback display
    $username = $user->username ?? $user->name ?? $user->email ?? __('User');
    $userInitial = mb_substr($username, 0, 1);
    
    // Determine border/ring classes based on conditions
    if ($hasCoverImage) {
        $imageBorderClass = 'border-2 border-white dark:border-gray-900';
        $letterBorderClass = 'ring-1 ring-white/20 dark:ring-gray-800'; // Fallback won't have cover image
    } elseif ($isCurrentUser) {
        $imageBorderClass = 'border-2 border-primary-500/80';
        $letterBorderClass = 'border-2 border-white/80';
    } else {
        $imageBorderClass = 'ring-1 ring-white/20 dark:ring-gray-800';
        $letterBorderClass = 'ring-1 ring-white/20 dark:ring-gray-800';
    }
@endphp

@if($clickable)
    <x-clickable-avatar-wrapper :user="$user">
        <div class="relative inline-block">
            @if($avatarUrl)
                <img 
                    src="{{ $avatarUrl }}" 
                    alt="{{ $username }}"
                    class="{{ $avatarSizeClass }} rounded-full object-cover {{ $imageBorderClass }} shadow-sm relative z-10"
                    @if($lazyLoad) loading="lazy" @endif
                    draggable="false"
                />
            @else
                <!-- Fallback: Letter avatar -->
                <div class="{{ $avatarSizeClass }} rounded-full bg-primary-500 {{ $letterBorderClass }} shadow-sm flex items-center justify-center relative z-10">
                    <span class="{{ $textSizeClass }} font-medium text-white">
                        {{ strtoupper($userInitial) }}
                    </span>
                </div>
            @endif

            @if($showStatus)
                <div class="{{ $statusPosition }} z-20">
                    <x-online-status-indicator :user="$user" :size="$statusSize" />
                </div>
            @endif
        </div>
    </x-clickable-avatar-wrapper>
@else
    <div class="relative inline-block">
        @if($avatarUrl)
            <img 
                src="{{ $avatarUrl }}" 
                alt="{{ $username }}"
                class="{{ $avatarSizeClass }} rounded-full object-cover {{ $imageBorderClass }} shadow-sm relative z-10"
                @if($lazyLoad) loading="lazy" @endif
                draggable="false"
            />
        @else
            <!-- Fallback: Letter avatar -->
            <div class="{{ $avatarSizeClass }} rounded-full bg-primary-500 {{ $letterBorderClass }} shadow-sm flex items-center justify-center relative z-10">
                <span class="{{ $textSizeClass }} font-medium text-white">
                    {{ strtoupper($userInitial) }}
                </span>
            </div>
        @endif

        @if($showStatus)
            <div class="{{ $statusPosition }} z-20">
                <x-online-status-indicator :user="$user" :size="$statusSize" />
            </div>
        @endif
    </div>
@endif

