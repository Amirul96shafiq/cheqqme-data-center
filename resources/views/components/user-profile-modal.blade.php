@props(['user'])

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
        >
            <!-- Backdrop -->
            <div 
                x-show="showModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-black bg-opacity-50"
                @click.prevent="closeModal()"
            ></div>

            <!-- Modal -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="showModal"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 shadow-xl transition-all w-full max-w-sm"
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
                            
                            <!-- User Badges -->
                            <div class="flex flex-wrap gap-1 justify-center my-3">
                                <!-- Country -->
                                @if($user->country)
                                    <x-tooltip position="top" text="{{ __('user.table.country') }}">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                            {{ $user->country }}
                                        </span>
                                    </x-tooltip>
                                @endif
                                
                                <!-- Timezone -->
                                @if($user->timezone)
                                    <x-tooltip position="top" text="{{ __('user.table.timezone') }}">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-normal bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200">
                                            {{ $user->timezone }}
                                        </span>
                                    </x-tooltip>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Close Button -->
                    <div class="absolute top-4 right-4">
                        <button
                            @click.prevent="closeModal()"
                            type="button"
                            class="rounded-md p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                        >
                            <x-heroicon-o-x-mark class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endif
