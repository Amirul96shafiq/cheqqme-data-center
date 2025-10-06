@php
    $user = $getRecord();
    $avatarUrl = filament()->getUserAvatarUrl($user);
    $coverImageUrl = $user->getFilamentCoverImageUrl();
    $modalId = 'user-modal-' . $user->id;
@endphp

<div 
    x-data="{ 
        showModal: false,
        modalPosition: { x: 0, y: 0 },
        openModal(event) {
            // Capture click position
            this.modalPosition.x = event.clientX;
            this.modalPosition.y = event.clientY + 10; // 10px below cursor
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
        }
    }"
    class="relative inline-block"
    x-init="$watch('showModal', value => {
        if (value) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    })"
>
    <!-- Clickable Avatar -->
    <button 
        @click.prevent="openModal($event)"
        class="cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-full"
        type="button"
    >
        <img
            src="{{ $avatarUrl }}"
            alt="{{ filament()->getUserName($user) }}"
            class="w-12 h-12 rounded-full object-cover {{ $coverImageUrl ? 'border-2 border-white dark:border-gray-900' : 'border-0' }}"
        />
        
        <!-- Online Status Indicator -->
        <div class="absolute bottom-1 -right-0.5">
            <x-online-status-indicator :user="$user" size="md" />
        </div>
    </button>

    <!-- Unified User Profile Modal -->
    <x-user-profile-modal :user="$user" />
</div>
