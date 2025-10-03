{{-- Simple profile image display modal without interactive features --}}
<div class="fixed inset-0 z-50 overflow-y-auto" x-data="userProfileImages()" x-show="open" @open-modal="open()" x-cloak>
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-50" x-show="open" @click="close"></div>

    <!-- Modal panel -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md" x-show="open">
            
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Profile Images
                </h3>
                <button @click="close" class="text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- Cover Image Display -->
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Cover Image</h4>
                    <div class="relative">
                        <div class="w-full h-32 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700">
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
                        </div>
                    </div>
                </div>

                <!-- Avatar Display -->
                <div class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Avatar</h4>
                    <div class="flex items-center space-x-4">
                        <div class="relative w-16 h-16 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-700">
                            @if($user->getFilamentAvatarUrl())
                                <img src="{{ $user->getFilamentAvatarUrl() }}" alt="Avatar" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 dark:text-gray-300">Current profile images</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end p-4 border-t border-gray-200 dark:border-gray-700">
                <button @click="close" class="px-4 py-2 text-gray-700 dark:text-gray-300">
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