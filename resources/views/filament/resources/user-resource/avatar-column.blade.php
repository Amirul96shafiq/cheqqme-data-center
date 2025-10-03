@php
    $user = $getRecord();
    $avatarUrl = filament()->getUserAvatarUrl($user);
    $coverImageUrl = $user->getFilamentCoverImageUrl();
@endphp

<div class="relative inline-block">
    <img
        src="{{ $avatarUrl }}"
        alt="{{ filament()->getUserName($user) }}"
        class="w-12 h-12 rounded-full object-cover {{ $coverImageUrl ? 'border-2 border-white dark:border-gray-900' : 'border-0' }}"
    />
    
    <!-- Online Status Indicator -->
    <div class="absolute -bottom-0.5 -right-0.5">
        <x-online-status-indicator :user="$user" size="md" />
    </div>
</div>
