<div class="fixed inset-0 z-50 overflow-y-auto" x-data="userProfileImages()" x-show="open" @open-modal="open()" x-cloak>
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" x-show="open" @click="close"></div>

    <!-- Modal panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all w-full max-w-2xl" 
             x-show="open" 
             x-transition:enter="duration-300 ease-out"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="duration-200 ease-in"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Manage Profile Images
                </h3>
                <button @click="close" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 in-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-8">
                <!-- Cover Image Section -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Cover Image</h4>
                    
                    <div class="relative">
                        <div class="w-full h-32 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors"
                             x-data="coverImageUpload()">
                            
                            <!-- Current cover image -->
                            @if($user->getFilamentCoverImageUrl())
                                <img src="{{ $user->getFilamentCoverImageUrl() }}" 
                                     alt="Cover Image" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="ml-2 text-sm text-gray-500">No cover image</span>
                                </div>
                            @endif
                            
                            <!-- Upload overlay -->
                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-50 transition-opacity flex items-center justify-center opacity-0 hover:opacity-100">
                                <label class="cursor-pointer bg-white dark:bg-gray-700 px-4 py-2 rounded-lg shadow-lg">
                                    <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Upload
                                    <input type="file" 
                                           wire:model="coverImage" 
                                           accept="image/*"
                                           class="hidden"
                                           @change="previewImage($event)">
                                </label>
                            </div>
                        </div>
                        
                        <!-- Preview -->
                        <div x-show="preview" x-transition class="mt-4">
                            <img :src="preview" class="w-full h-24 object-cover rounded-lg">
                            <div class="flex gap-2 mt-2">
                                <button wire:click="saveCoverImage" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                        :disabled="!preview">
                                    Save Cover
                                </button>
                                <button @click="clearPreview" 
                                        class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        
                        @if($user->cover_image)
                            <button wire:click="removeCoverImage" 
                                    wire:confirm="Are you sure you want to remove your cover image?"
                                    class="mt-2 px-3 py-1 text-sm bg-red-100 text-red-700 hover:bg-red-200 rounded-lg transition-colors">
                                Remove Cover Image
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Avatar Section -->
                <div class="space-y-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Avatar</h4>
                    
                    <div class="flex items-center space-x-6">
                        <!-- Avatar Preview -->
                        <div x-data="avatarUpload()">
                            <div class="relative w-24 h-24 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700 border-2 border-dashed border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                                <label class="cursor-pointer w-full h-full flex items-center justify-center">
                                    @if($avatarPreview)
                                        <img src="{{ $avatarPreview }}" alt="Preview" class="w-full h-full object-cover">
                                    @elseif($user->getFilamentAvatarUrl())
                                        <img src="{{ $user->getFilamentAvatarUrl() }}" alt="Avatar" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    @endif
                                    <input type="file" 
                                           wire:model="avatar" 
                                           accept="image/*"
                                           class="hidden">
                                </label>
                            </div>
                        </div>
                        
                        
                        <!-- Avatar Actions -->
                        <div class="flex-1 space-y-2">
                            @if($avatarPreview)
                                <div class="flex gap-2">
                                    <button wire:click="saveAvatar" 
                                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                        Save Avatar
                                    </button>
                                    <button wire:click="$set('avatar', null); $set('avatarPreview', null)" 
                                            class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500 transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <label class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 cursor-pointer transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Change Avatar
                                    <input type="file" 
                                           wire:model="avatar" 
                                           accept="image/*"
                                           class="hidden">
                                </label>
                            @endif
                            
                            @if($user->avatar)
                                <button wire:click="removeAvatar" 
                                        wire:confirm="Are you sure you want to remove your custom avatar?"
                                        class="text-sm px-3 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded-lg transition-colors">
                                    Remove Avatar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 p-6 bg-gray-50 dark:bg-gray-900 rounded-b-xl">
                <button @click="close" 
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function userProfileImages() {
    return {
        open: false,
        open() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        }
    }
}

function coverImageUpload() {
    return {
        preview: null,
        previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },
        clearPreview() {
            this.preview = null;
        }
    }
}

function avatarUpload() {
    return {
        preview(event) {
            // Preview logic is handled by Livewire
        }
    }
}

// Global event listener for opening the image modal
document.addEventListener('DOMContentLoaded', function() {
    // Listen for clicks on the interactive profile elements
    document.addEventListener('click', function(event) {
        // Check if clicked element has the data attribute to open modal
        if (event.target.closest('[data-open-image-modal]')) {
            event.preventDefault();
            // Dispatch event to open modal
            window.dispatchEvent(new CustomEvent('open-modal'));
        }
    });
    
    // Listen for the global open modal event
    window.addEventListener('open-modal', function() {
        // Find the user profile images component and open it
        const modal = document.querySelector('[x-data*="userProfileImages"]');
        if (modal) {
            modal._x_dataStack[0].open();
        }
    });
});
</script>
