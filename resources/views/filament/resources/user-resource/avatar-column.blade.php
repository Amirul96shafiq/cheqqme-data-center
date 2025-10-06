@php
    $user = $getRecord();
    $avatarUrl = filament()->getUserAvatarUrl($user);
    $coverImageUrl = $user->getFilamentCoverImageUrl();
    $modalId = 'user-modal-' . $user->id;
@endphp

<x-clickable-avatar-wrapper :user="$user">
    <div class="relative inline-block">
        <img
            src="{{ $avatarUrl }}"
            alt="{{ filament()->getUserName($user) }}"
            class="w-12 h-12 rounded-full object-cover {{ $coverImageUrl ? 'border-2 border-white dark:border-gray-900' : 'border-0' }}"
            draggable="false"
        />

        <div class="absolute bottom-1 -right-0.5">
            <x-online-status-indicator :user="$user" size="md" />
        </div>
    </div>
</x-clickable-avatar-wrapper>
