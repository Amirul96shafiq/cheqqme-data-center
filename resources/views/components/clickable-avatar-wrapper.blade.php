@props(['user'])

@php
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
    @click.prevent="openModal($event)"
    class="cursor-pointer"
    x-init="$watch('showModal', value => {
        if (value) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    })"
>
    {{ $slot }}

    <!-- Unified User Profile Modal -->
    <x-user-profile-modal :user="$user" />
</div>
