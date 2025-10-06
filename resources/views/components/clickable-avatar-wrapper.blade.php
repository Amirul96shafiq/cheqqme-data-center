@props(['user'])

@php
    $modalId = 'user-modal-' . $user->id;
@endphp

<div 
    x-data="{ 
        showModal: false,
        openModal() {
            this.showModal = true;
        },
        closeModal() {
            this.showModal = false;
        }
    }"
    @click.prevent="openModal()"
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
