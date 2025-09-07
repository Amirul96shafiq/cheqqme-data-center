<script>
function openGreetingModal() {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.id = 'greeting-modal-overlay';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    modal.style.backdropFilter = 'blur(12px)';
    
    // Get current time for personalized greeting
    const hour = new Date().getHours();
    const greeting = hour >= 7 && hour <= 11 ? 'Good Morning' : 
                    hour >= 12 && hour <= 19 ? 'Good Afternoon' : 
                    hour >= 20 && hour <= 23 ? 'Good Evening' : 'Good Night';
    
    const icon = hour >= 7 && hour <= 19 ? 'sun' : 'moon';
    const iconColor = hour >= 7 && hour <= 19 ? 'text-yellow-500' : 'text-blue-400';
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-5xl w-full mx-4 transform transition-all duration-300 scale-95 opacity-0 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex">
                <!-- Weather Information Section (60% width) -->
                <div class="p-6 border-r border-gray-200 dark:border-gray-700" style="width: 60%;">
                    <div class="flex items-center justify-between mb-4">
                        <button onclick="closeGreetingModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            @svg('heroicon-o-arrow-path', 'w-5 h-5')
                        </button>
                    </div>
                    
                    <!-- Weather Content -->
                    <div class="space-y-4">
                        <!-- Current Weather -->
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center">
                                        @svg('heroicon-o-sun', 'w-6 h-6 text-yellow-600 dark:text-yellow-400')
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Sunny</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Kuala Lumpur, Malaysia</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">28°C</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Feels like 32°C</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Weather Details -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-2 mb-1">
                                    @svg('heroicon-o-cloud', 'w-4 h-4 text-gray-500 dark:text-gray-400')
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Humidity</span>
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">75%</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-2 mb-1">
                                    @svg('heroicon-o-arrow-down-circle', 'w-4 h-4 text-gray-500 dark:text-gray-400')
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Wind</span>
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">12 km/h</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-2 mb-1">
                                    @svg('heroicon-o-bolt', 'w-4 h-4 text-gray-500 dark:text-gray-400')
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">UV Index</span>
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">High</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-2 mb-1">
                                    @svg('heroicon-o-clock', 'w-4 h-4 text-gray-500 dark:text-gray-400')
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Sunset</span>
                                </div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">7:15 PM</div>
                            </div>
                        </div>
                        
                            <!-- 7-Day Forecast -->
                            <div class="mt-4">
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">7-Day Forecast</h5>
                                <div class="space-y-2" id="forecast-container">
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Today</span>
                                            @svg('heroicon-o-sun', 'w-5 h-5 text-yellow-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">28°C / 24°C</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Tomorrow</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">26°C / 22°C</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Tuesday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-gray-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">25°C / 21°C</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Wednesday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">26°C / 22°C</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Thursday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">27°C / 23°C</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Friday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">29°C / 25°C</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Saturday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">26°C / 22°C</div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                
                <!-- Greeting Section (30% width) -->
                <div class="p-6" style="width: 40%;">
                <!-- Close Button -->
                <button 
                    onclick="closeGreetingModal()" 
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 z-10"
                    aria-label="Close"
                >
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                </button>
                
                <div class="text-center mb-8">
                    <div class="w-12 h-12 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        <span class="text-primary-600 dark:text-primary-400">{{ \App\Helpers\ClientFormatter::formatClientName(auth()->user()?->name) }}</span>{{ __('greetingmodal.content-title') }}
                    </h4>
                    <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-12">
                        {{ __('greetingmodal.content-message') }}
                    </p>
                </div>
                
                <!-- Quick Actions -->
                <div class="space-y-3 mb-6">
                    <!-- Profile Quick Action -->
                    <button onclick="navigateToProfile()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
                        <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors flex-shrink-0">
                            @svg('heroicon-o-user', 'w-5 h-5 text-primary-600 dark:text-primary-400')
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                {{ __('greetingmodal.action-1-title') }}
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                {{ __('greetingmodal.action-1-description') }}
                            </p>
                        </div>
                    </button>
                    <!-- Settings Quick Action -->
                    <button onclick="navigateToSettings()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
                        <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors flex-shrink-0">
                            @svg('heroicon-o-cog-6-tooth', 'w-5 h-5 text-primary-600 dark:text-primary-400')
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                {{ __('greetingmodal.action-2-title') }}
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                {{ __('greetingmodal.action-2-description') }}
                            </p>
                        </div>
                    </button>
                    <!-- Action Board Quick Action -->
                    <button onclick="navigateToActionBoard()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
                        <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors flex-shrink-0">
                            @svg('heroicon-o-rocket-launch', 'w-5 h-5 text-primary-600 dark:text-primary-400')
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                {{ __('greetingmodal.action-3-title') }}
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                {{ __('greetingmodal.action-3-description') }}
                            </p>
                        </div>
                    </button>
                    <!-- Data Management Quick Action -->
                    <button onclick="toggleDataManagementVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
                        <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors flex-shrink-0">
                            @svg('heroicon-o-table-cells', 'w-5 h-5 text-primary-600 dark:text-primary-400')
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                {{ __('greetingmodal.action-4-title') }}
                            </h5>
                            <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                {{ __('greetingmodal.action-4-description') }}
                            </p>
                        </div>
                    </button>
                </div>
                
                <!-- Video Container -->
                <div id="data-management-video" class="hidden mt-2 mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
                    <div class="bg-gray-50/10 dark:bg-gray-700/10 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center justify-between mb-3">
                            <h6 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ __('greetingmodal.video-title') }}
                            </h6>
                            <button onclick="toggleDataManagementVideo()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600">
                                @svg('heroicon-o-x-mark', 'w-4 h-4')
                            </button>
                        </div>
                        <div class="relative">
                            <video 
                                class="w-full rounded-lg shadow-sm" 
                                controls 
                                preload="metadata"
                            >
                                <source src="/videos/video_sample_01.mp4" type="video/mp4">
                                {{ __('greetingmodal.video-not-supported') }}
                            </video>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            
            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ __('greetingmodal.footer-text') }}
                    </div>
                    <div class="flex space-x-3">
                        <button 
                            onclick="closeGreetingModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                        >
                            {{ __('greetingmodal.action-dismiss') }}
                        </button>
                        <button 
                            onclick="closeGreetingModal()" 
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md"
                        >
                            {{ __('greetingmodal.action-continue') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(modal);
    
    // Animate in
    setTimeout(() => {
        const modalContent = modal.querySelector('.bg-white');
        if (modalContent) {
            modalContent.style.transform = 'scale(1)';
            modalContent.style.opacity = '1';
        }
        
        // Update day labels if needed (only once per day)
        setTimeout(() => {
            updateForecastDayLabels();
        }, 100);
    }, 10);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
    
    // Close on backdrop click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeGreetingModal();
        }
    });
    
    // Close on escape key
    const handleEscape = function(e) {
        if (e.key === 'Escape') {
            closeGreetingModal();
        }
    };
    document.addEventListener('keydown', handleEscape);
    
    // Store the escape handler for cleanup
    modal.escapeHandler = handleEscape;
}

function closeGreetingModal() {
    const modal = document.getElementById('greeting-modal-overlay');
    if (modal) {
        // Animate out
        const modalContent = modal.querySelector('.bg-white');
        modalContent.style.transform = 'scale(0.95)';
        modalContent.style.opacity = '0';
        
        // Remove after animation
        setTimeout(() => {
            // Remove escape key listener
            if (modal.escapeHandler) {
                document.removeEventListener('keydown', modal.escapeHandler);
            }
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Remove modal
            modal.remove();
        }, 300);
    }
}

// Auto-detect clicks on greeting menu item
document.addEventListener('DOMContentLoaded', function() {
    // Wait for Filament to load
    setTimeout(function() {
        console.log('Looking for greeting menu item...');
        
        // Try multiple selectors to find the greeting menu item
        const selectors = [
            '[data-filament-dropdown-list] a[href="javascript:void(0)"]',
            '.fi-dropdown-list a[href="javascript:void(0)"]',
            '[role="menu"] a[href="javascript:void(0)"]',
            '[data-filament-dropdown-list-item] a[href="javascript:void(0)"]',
            '.fi-dropdown-list-item a[href="javascript:void(0)"]',
            'a[href="javascript:void(0)"]'
        ];
        
        let greetingLink = null;
        
        for (const selector of selectors) {
            const links = document.querySelectorAll(selector);
            console.log(`Found ${links.length} links with selector: ${selector}`);
            
            links.forEach(function(link) {
                console.log('Checking link:', link.textContent.trim(), link.href);
                // Check if this is our greeting menu item by looking for time-based greetings
                const text = link.textContent.trim().toLowerCase();
                // More specific check - must be in user menu dropdown
                if ((text.includes('good morning') || text.includes('good afternoon') || 
                    text.includes('good evening') || text.includes('goodnight') ||
                    text.includes('morning') || text.includes('afternoon') || 
                    text.includes('evening') || text.includes('night')) &&
                    (link.closest('[data-filament-dropdown-list]') || 
                     link.closest('.fi-dropdown-list') ||
                     link.closest('[role="menu"]'))) {
                    greetingLink = link;
                    console.log('Found greeting menu item:', text);
                }
            });
            
            if (greetingLink) break;
        }
        
        if (greetingLink) {
            console.log('Attaching click handler to greeting menu item');
            greetingLink.addEventListener('click', function(e) {
                console.log('Greeting menu item clicked!');
                e.preventDefault();
                e.stopPropagation();
                openGreetingModal();
            });
        } else {
            console.log('Greeting menu item not found');
        }
    }, 2000); // Increased timeout to ensure Filament is fully loaded
});

// Fallback: Use event delegation to catch clicks on any element
document.addEventListener('click', function(e) {
    // Check if clicked element or its parent contains greeting text
    let element = e.target;
    let foundGreeting = false;
    
    // Check up to 3 parent levels
    for (let i = 0; i < 3; i++) {
        if (element && element.textContent) {
            const text = element.textContent.trim().toLowerCase();
            // More specific check - must contain greeting AND be in user menu
            if ((text.includes('good morning') || text.includes('good afternoon') || 
                text.includes('good evening') || text.includes('goodnight') ||
                text.includes('morning') || text.includes('afternoon') || 
                text.includes('evening') || text.includes('night')) &&
                (element.closest('[data-filament-dropdown-list]') || 
                 element.closest('.fi-dropdown-list') ||
                 element.closest('[role="menu"]'))) {
                foundGreeting = true;
                break;
            }
        }
        element = element?.parentElement; // Safe navigation with optional chaining
    }
    
    if (foundGreeting) {
        console.log('Greeting text clicked via event delegation');
        e.preventDefault();
        e.stopPropagation();
        openGreetingModal();
    }
});

// Test function - you can call this manually in browser console: testGreetingModal()
window.testGreetingModal = function() {
    console.log('Testing greeting modal...');
    openGreetingModal();
};

// Navigation functions for quick actions
window.navigateToProfile = function() {
    closeGreetingModal();
    window.location.href = '/admin/profile';
};

window.navigateToSettings = function() {
    closeGreetingModal();
    window.location.href = '/admin/settings';
};

window.navigateToActionBoard = function() {
    closeGreetingModal();
    window.location.href = '/admin/action-board';
};

// Toggle data management video container
window.toggleDataManagementVideo = function() {
    const videoContainer = document.getElementById('data-management-video');
    if (videoContainer) {
        const isHidden = videoContainer.classList.contains('hidden');
        
        if (isHidden) {
            // Show video container with animation
            videoContainer.classList.remove('hidden');
            // Force reflow to ensure the element is visible before animation
            videoContainer.offsetHeight;
            // Add animation classes
            videoContainer.classList.remove('opacity-0', 'scale-95');
            videoContainer.classList.add('opacity-100', 'scale-100');
            
            // Reset video to beginning when showing
            setTimeout(() => {
                const video = videoContainer.querySelector('video');
                if (video) {
                    video.currentTime = 0;
                }
            }, 100);
        } else {
            // Hide video container with animation
            videoContainer.classList.remove('opacity-100', 'scale-100');
            videoContainer.classList.add('opacity-0', 'scale-95');
            
            // Pause video when hiding
            const video = videoContainer.querySelector('video');
            if (video) {
                video.pause();
            }
            
            // Hide element after animation completes
            setTimeout(() => {
                videoContainer.classList.add('hidden');
            }, 300);
        }
    }

    // Function to update day labels only when needed (every 24 hours)
    function updateForecastDayLabels() {
        const today = new Date();
        const todayString = today.toDateString();
        
        // Check if we've already updated today
        const lastUpdate = localStorage.getItem('forecast-last-update');
        if (lastUpdate === todayString) {
            return; // Already updated today, no need to update
        }
        
        // Update day labels
        const forecastContainer = document.getElementById('forecast-container');
        if (!forecastContainer) return;
        
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const daySpans = forecastContainer.querySelectorAll('.text-sm.text-gray-600.dark\\:text-gray-400.w-16');
        
        daySpans.forEach((span, index) => {
            if (index === 0) {
                span.textContent = 'Today';
            } else if (index === 1) {
                span.textContent = 'Tomorrow';
            } else {
                // Calculate the correct day for each forecast entry
                const forecastDate = new Date(today);
                forecastDate.setDate(today.getDate() + index);
                span.textContent = dayNames[forecastDate.getDay()];
            }
        });
        
        // Save today's date as last update
        localStorage.setItem('forecast-last-update', todayString);
        console.log('Forecast day labels updated for', todayString);
    }
};

// Make functions globally available
window.openGreetingModal = openGreetingModal;
window.closeGreetingModal = closeGreetingModal;
</script>
