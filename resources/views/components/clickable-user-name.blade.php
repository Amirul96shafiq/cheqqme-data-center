@props([
    'user' => null,
    'date' => null,
    'dateFormat' => 'j/n/y, h:i A',
    'showDate' => true,
    'fallbackText' => 'Unknown'
])

@if($user)
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
        class="inline-flex items-center"
        x-init="$watch('showModal', value => {
            if (value) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        })"
    >
        @if($showDate && $date)
            <span class="text-sm text-gray-900 dark:text-white">
                {{ $date->format($dateFormat) }}
            </span>
            <span class="ml-1">(</span>
        @endif
        
        <button 
            @click.prevent="openModal()"
            class="cursor-pointer text-sm font-semibold text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-200 underline transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded px-1"
            type="button"
        >
            {{ $user->short_name }}
        </button>
        
        @if($showDate && $date)
            <span>)</span>
        @endif

        <!-- Unified User Profile Modal -->
        <x-user-profile-modal :user="$user" />
    </div>
@else
    @if($showDate && $date)
        <span class="text-sm text-gray-900 dark:text-white">
            {{ $date->format($dateFormat) }}
        </span>
        <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">({{ $fallbackText }})</span>
    @else
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $fallbackText }}</span>
    @endif
@endif
