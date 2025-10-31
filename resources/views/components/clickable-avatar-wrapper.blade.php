@props(['user'])

@php
    // Generate unique modal ID for this specific instance
    $modalId = 'user-modal-' . $user->id . '-' . uniqid();
@endphp

<div 
    x-data="{ 
        showModal: false,
        modalPosition: { x: 0, y: 0 },
        modalId: '{{ $modalId }}',
        openModal(event) {
            // Base position from click
            const clickX = event.clientX;
            const clickY = event.clientY;

            // Tentative position below the cursor
            let targetX = clickX;
            let targetY = clickY + 10; // offset below

            this.showModal = true;
            
            // Dispatch modal show event with user ID and unique modal ID
            this.$dispatch('modal-show', { userId: {{ $user->id }}, modalId: this.modalId });

            // Wait for next tick to measure modal size
            this.$nextTick(() => {
                const panel = this.$refs.modalPanel;
                const container = this.$refs.modalContainer;
                const viewportWidth = window.innerWidth;
                const viewportHeight = window.innerHeight;

                if (panel) {
                    const rect = panel.getBoundingClientRect();
                    const modalWidth = rect.width || 360; // fallback to max-w-sm
                    const modalHeight = rect.height || 300;

                    // If not enough space below, place above
                    const spaceBelow = viewportHeight - clickY;
                    const neededBelow = modalHeight + 24; // include margins
                    if (spaceBelow < neededBelow) {
                        targetY = Math.max(12, clickY - modalHeight - 10);
                    }

                    // Horizontal clamping to keep centered transform within viewport
                    const halfWidth = modalWidth / 2;
                    targetX = Math.min(viewportWidth - halfWidth - 12, Math.max(halfWidth + 12, targetX));

                    // Vertical clamp as well
                    targetY = Math.min(viewportHeight - modalHeight - 12, Math.max(12, targetY));
                }

                this.modalPosition.x = targetX;
                this.modalPosition.y = targetY;
            });
        },
        closeModal() {
            this.showModal = false;
            
            // Dispatch modal hide event with user ID and unique modal ID
            this.$dispatch('modal-hide', { userId: {{ $user->id }}, modalId: this.modalId });
        }
    }"
    @click.stop.prevent="openModal($event)"
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
    <x-user-profile-modal :user="$user" :modal-id="$modalId" />
</div>
