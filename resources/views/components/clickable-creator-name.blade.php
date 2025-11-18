@props(['user', 'fallbackText' => 'the creator'])

@php
    // Generate unique modal ID for this specific instance
    $modalId = 'creator-modal-' . ($user?->id ?? 'unknown') . '-' . uniqid();
@endphp

@if($user)
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
        class="cursor-pointer inline"
        x-init="$watch('showModal', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        })"
    >
        <button
            class="cursor-pointer text-sm font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200 underline transition-colors duration-200"
            type="button"
        >
            {{ $user->short_name }}
        </button>

        <!-- Unified User Profile Modal -->
        <x-user-profile-modal :user="$user" :modal-id="$modalId" />
    </div>
@else
    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $fallbackText }}</span>
@endif
