@props(['user', 'modalId'])

@if($user)
    <!-- User Profile Modal - Shared across the entire application -->
    <template x-teleport="body">
        <div 
            x-show="showModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[9999] overflow-y-auto"
            x-cloak
            x-ref="modalContainer"
            @click.self="closeModal()"
        >

        <!-- Modal -->
        <div 
            class="fixed p-4 overflow-visible"
            :style="{
                left: modalPosition.x + 'px',
                top: modalPosition.y + 'px',
                transform: 'translateX(-50%)'
            }"
        >
            <div 
                x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-visible rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-xl transition-all w-80"
                x-ref="modalPanel"
                @click.stop
            >
                    <!-- User Profile Header with Cover Image -->
                    <div class="relative user-profile-header">
                        <!-- Cover Image Background -->
                        <div class="relative h-[78px] bg-gray-100 dark:bg-gray-800 rounded-t-2xl overflow-hidden">
                            @if($user->getFilamentCoverImageUrl())
                                <img 
                                    src="{{ $user->getFilamentCoverImageUrl() }}" 
                                    alt="Cover Image"
                                    class="w-full h-full object-cover z-5"
                                    draggable="false"
                                />
                            @else
                                <img 
                                    src="{{ asset('images/default-cover-img.png') }}" 
                                    alt="Default Cover Image"
                                    class="w-full h-full object-cover z-5"
                                    draggable="false"
                                />
                            @endif
                            <!-- User ID badge -->
                            <div class="absolute top-1 left-1/2 -translate-x-1/2 z-20">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-black/30 text-white dark:bg-black/30">
                                    ID: {{ $user->id }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Avatar Container -->
                        <div class="flex justify-center -mt-8 relative">
                            <div class="relative inline-block">
                                <x-filament::avatar
                                    :src="filament()->getUserAvatarUrl($user)"
                                    :alt="filament()->getUserName($user)"
                                    size="w-16 h-16"
                                    class="border-4 border-white dark:border-gray-900 z-10"
                                    draggable="false"
                                />
                                
                                <!-- Online Status Indicator - positioned within avatar -->
                                <div class="absolute bottom-0.5 right-0.5 z-20">
                                    <x-online-status-indicator 
                                        :user="$user" 
                                        size="md" 
                                        :showTooltip="true"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="px-4 py-3 text-center">
                            <!-- Username -->
                            <h3 class="text-md font-bold text-gray-900 dark:text-white truncate">
                                {{ $user->username }}
                            </h3>

                            <!-- Name -->
                            <h4 class="text-[10px] font-regular text-gray-700 dark:text-gray-200 truncate -mt-1">
                                {{ $user->name }}
                            </h4>

                            <!-- Email -->
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-2">
                                {{ $user->email }}
                            </p>

                            <!-- Phone Number and Date of Birth -->
                            @if($user->phone || $user->date_of_birth)
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-1 flex items-center justify-center gap-1">
                                    @if($user->phone)
                                        {{ $user->getPhoneWithoutCountryCode() }}
                                    @endif
                                    @if($user->phone && $user->date_of_birth)
                                        <span>|</span>
                                    @endif
                                    @if($user->date_of_birth)
                                        {{ $user->date_of_birth->format('d/m/Y') }}
                                    @endif
                                </p>
                            @endif
                            
                            <!-- User Badges -->
                            <div class="my-3">
                                <x-user-badges :user="$user" size="sm" gap="1" :showIcons="true" />
                            </div>

                            <!-- Spotify Now Playing -->
                            <!-- Pure Alpine.js component - No Livewire to avoid snapshot conflicts -->
                            @if($user->hasSpotifyAuth())
                                <div class="my-3">
                                    <x-spotify-now-playing-alpine :user="$user" context="modal" :modal-id="$modalId" />
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Close Button -->
                    <div class="absolute top-4 right-4">
                        <x-close-button 
                            @click.prevent="closeModal()"
                            aria-label="Close profile modal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </template>
@endif
