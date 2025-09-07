<script>
function openGreetingModal() {
    console.log('openGreetingModal function called!');
    
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
                <div class="p-6 border-r border-gray-200 dark:border-gray-700 weather-section" style="width: 60%;">
                    <div class="flex items-center justify-between mb-4">
                        <button onclick="refreshWeatherData()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="Refresh Weather Data">
                            @svg('heroicon-o-arrow-path', 'w-5 h-5')
                        </button>
                    </div>
                    
                    <!-- Weather Content -->
                    <div class="space-y-4">
                        <!-- Current Weather -->
                        <div class="bg-gradient-to-r from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/20 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-full flex items-center justify-center weather-icon-container">
                                        @svg('heroicon-o-sun', 'w-6 h-6 text-yellow-600 dark:text-yellow-400')
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white weather-condition">Loading...</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 weather-location">Loading...</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white current-temp">Loading...</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 feels-like">Feels like Loading...</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Weather Details -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-cloud', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Humidity</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white humidity-value">Loading...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-arrow-down-circle', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Wind</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white wind-value">Loading...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-bolt', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">UV Index</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white uv-value">Loading...</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-clock', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sunset</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white sunset-value">Loading...</div>
                                    </div>
                                </div>
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
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Tomorrow</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Tuesday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-gray-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Wednesday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Thursday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Friday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
                                    </div>
                                    <div class="flex items-center justify-between py-2">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Saturday</span>
                                            @svg('heroicon-o-cloud', 'w-5 h-5 text-blue-500')
                                        </div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">Loading... / Loading...</div>
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
        
        // Day labels are now handled directly in updateForecastData() function
        
        // Debug weather elements right after modal creation
        setTimeout(() => {
            console.log('=== DEBUGGING WEATHER ELEMENTS RIGHT AFTER MODAL CREATION ===');
            debugWeatherElements();
            
            // Additional focused debug
            console.log('=== FOCUSED WEATHER DEBUG ===');
            const weatherSection = document.querySelector('.weather-section');
            console.log('Weather section found:', !!weatherSection);
            if (weatherSection) {
                console.log('Weather section innerHTML length:', weatherSection.innerHTML.length);
                console.log('Contains current-temp:', weatherSection.innerHTML.includes('current-temp'));
                console.log('Contains weather-condition:', weatherSection.innerHTML.includes('weather-condition'));
                console.log('Contains weather-location:', weatherSection.innerHTML.includes('weather-location'));
                console.log('Contains feels-like:', weatherSection.innerHTML.includes('feels-like'));
            }
        }, 150);
        
        // Check user location and fetch weather data AFTER modal is fully rendered
        setTimeout(() => {
            checkUserLocationAndFetchWeather();
        }, 200);
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
        console.log('About to call openGreetingModal...');
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



};

// Weather API Integration Functions (Global Scope)

function showWeatherLoading() {
    // Loading spinner disabled to prevent replacing weather elements
    console.log('Loading spinner called but disabled to prevent element replacement');
}

function updateWeatherData(weatherData) {
    console.log('updateWeatherData called with:', weatherData);
    const weatherSection = document.querySelector('.weather-section');
    if (!weatherSection || !weatherData) {
        console.error('Weather section not found or no data:', weatherSection, weatherData);
        return;
    }

    const { current, forecast, error } = weatherData;
    
    if (error) {
        console.error('Weather data has error:', error);
        showWeatherError();
        return;
    }

    console.log('Updating current weather:', current);
    console.log('Updating forecast:', forecast);

    // Update current weather
    updateCurrentWeather(current);
    
    // Update forecast
    updateForecastData(forecast);
}

function updateCurrentWeather(weatherData, retryCount = 0) {
    console.log('updateCurrentWeather called with:', weatherData, 'retryCount:', retryCount);
    
    // Extract current weather data from nested structure
    // weatherData is the 'current' object from the API response
    const actualCurrentDetails = weatherData.current || {};
    const locationDetails = weatherData.location || {};
    
    console.log('Extracted weather data:', {
        temperature: actualCurrentDetails.temperature,
        feels_like: actualCurrentDetails.feels_like,
        condition: actualCurrentDetails.condition,
        icon: actualCurrentDetails.icon,
        location: locationDetails
    });
    
    console.log('Location details breakdown:', {
        city: locationDetails.city,
        country: locationDetails.country,
        fullLocation: locationDetails
    });
    
    // Check if all required elements exist
    const tempElement = document.querySelector('.current-temp');
    const feelsLikeElement = document.querySelector('.feels-like');
    const conditionElement = document.querySelector('.weather-condition');
    const locationElement = document.querySelector('.weather-location');
    
    console.log('Element check results:');
    console.log('- tempElement:', tempElement ? 'found' : 'NOT FOUND');
    console.log('- feelsLikeElement:', feelsLikeElement ? 'found' : 'NOT FOUND');
    console.log('- conditionElement:', conditionElement ? 'found' : 'NOT FOUND');
    console.log('- locationElement:', locationElement ? 'found' : 'NOT FOUND');
    
    if (!tempElement || !feelsLikeElement || !conditionElement || !locationElement) {
        if (retryCount < 10) { // Limit retries to prevent infinite loop
            console.error('Some weather elements not found, retrying in 100ms... (attempt', retryCount + 1, 'of 10)');
            if (retryCount === 0) {
                debugWeatherElements(); // Debug on first retry
            }
            setTimeout(() => updateCurrentWeather(current, retryCount + 1), 100);
            return;
        } else {
            console.error('Max retries reached, giving up on weather update');
            debugWeatherElements(); // Debug when giving up
            return;
        }
    }
    
    // Update temperature
    tempElement.textContent = actualCurrentDetails.temperature + '°C';
    console.log('Updated temperature to:', actualCurrentDetails.temperature + '°C');

    // Update feels like
    feelsLikeElement.textContent = 'Feels like ' + actualCurrentDetails.feels_like + '°C';
    console.log('Updated feels like to:', actualCurrentDetails.feels_like + '°C');

    // Update condition
    conditionElement.textContent = actualCurrentDetails.condition;
    console.log('Updated condition to:', actualCurrentDetails.condition);

    // Update location
    locationElement.textContent = locationDetails.city + ', ' + locationDetails.country;
    console.log('Updated location to:', locationDetails.city + ', ' + locationDetails.country);

    // Update weather icon with animation
    updateWeatherIcon(actualCurrentDetails.icon, actualCurrentDetails.condition);

    // Update weather details
    updateWeatherDetails(actualCurrentDetails);
}

function updateWeatherIcon(iconCode, condition) {
    const iconContainer = document.querySelector('.weather-icon-container');
    if (!iconContainer) return;

    // Add weather-specific animations
    iconContainer.className = 'w-12 h-12 rounded-full flex items-center justify-center weather-icon-container';
    
    // Determine background color and animation based on weather condition
    let bgClass = 'bg-yellow-100 dark:bg-yellow-900/30';
    let animationClass = '';
    
    // Handle undefined condition
    if (!condition) {
        console.log('Weather condition is undefined, using default styling');
        condition = 'sunny'; // Default fallback
    }
    
    switch (condition.toLowerCase()) {
        case 'clear':
        case 'sunny':
            bgClass = 'bg-yellow-100 dark:bg-yellow-900/30';
            animationClass = 'animate-pulse';
            break;
        case 'clouds':
        case 'cloudy':
            bgClass = 'bg-gray-100 dark:bg-gray-700/30';
            animationClass = 'animate-pulse';
            break;
        case 'rain':
        case 'drizzle':
            bgClass = 'bg-blue-100 dark:bg-blue-900/30';
            animationClass = 'animate-pulse';
            break;
        case 'thunderstorm':
            bgClass = 'bg-purple-100 dark:bg-purple-900/30';
            animationClass = 'animate-ping';
            break;
        case 'snow':
            bgClass = 'bg-blue-50 dark:bg-blue-800/30';
            animationClass = 'animate-pulse';
            break;
        default:
            bgClass = 'bg-gray-100 dark:bg-gray-700/30';
            animationClass = 'animate-pulse';
    }
    
    iconContainer.className += ` ${bgClass} ${animationClass}`;
    
    // Update icon with OpenWeatherMap icon
    iconContainer.innerHTML = `
        <img src="https://openweathermap.org/img/wn/${iconCode}@2x.png" 
             alt="${condition}" 
             class="w-6 h-6">
    `;
}

function updateWeatherDetails(current) {
    // Update humidity
    const humidityElement = document.querySelector('.humidity-value');
    if (humidityElement) {
        humidityElement.textContent = current.humidity + '%';
    }

    // Update wind
    const windElement = document.querySelector('.wind-value');
    if (windElement) {
        windElement.textContent = current.wind_speed + ' km/h';
    }

    // Update UV index
    const uvElement = document.querySelector('.uv-value');
    if (uvElement) {
        uvElement.textContent = current.uv_index;
    }

    // Update sunset
    const sunsetElement = document.querySelector('.sunset-value');
    if (sunsetElement) {
        sunsetElement.textContent = current.sunset;
    }
}

function updateForecastData(weatherData) {
    console.log('updateForecastData called with:', weatherData);
    
    // Extract forecast data from nested structure
    // weatherData is the 'forecast' object from the API response
    const forecast = weatherData.forecast || [];
    console.log('Extracted forecast data:', forecast);
    
    const forecastContainer = document.getElementById('forecast-container');
    if (!forecastContainer || !Array.isArray(forecast)) {
        console.log('Forecast container not found or forecast is not an array');
        return;
    }

    let forecastHTML = '';
    
    // Get day names for proper labeling
    const today = new Date();
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    forecast.forEach((day, index) => {
        const isLastItem = index === forecast.length - 1;
        const borderClass = isLastItem ? '' : 'border-b border-gray-200 dark:border-gray-600';
        
        // Use our day labeling logic instead of API day names
        let dayLabel;
        if (index === 0) {
            dayLabel = 'Today';
        } else if (index === 1) {
            dayLabel = 'Tomorrow';
        } else {
            // Calculate the correct day for each forecast entry
            const forecastDate = new Date(today);
            forecastDate.setDate(today.getDate() + index);
            dayLabel = dayNames[forecastDate.getDay()];
        }
        
        forecastHTML += `
            <div class="flex items-center justify-between py-2 ${borderClass}">
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">${dayLabel}</span>
                    <div class="w-5 h-5 flex items-center justify-center">
                        <img src="https://openweathermap.org/img/wn/${day.icon}@2x.png" 
                             alt="${day.condition}" 
                             class="w-5 h-5">
                    </div>
                </div>
                <div class="text-sm font-medium text-gray-900 dark:text-white">${day.max_temp}°C / ${day.min_temp}°C</div>
            </div>
        `;
    });

    forecastContainer.innerHTML = forecastHTML;
}

function showWeatherError() {
    const weatherSection = document.querySelector('.weather-section');
    if (weatherSection) {
        weatherSection.innerHTML = `
            <div class="p-6">
                <div class="text-center text-gray-500 dark:text-gray-400 mb-4">
                    <p>Failed to retrieve weather data</p>
                    <p class="text-sm mt-2">Weather information temporarily unavailable</p>
                </div>
            </div>
        `;
    }
}

async function fetchWeatherData(retryCount = 0) {
    try {
        console.log('Starting weather data fetch... retryCount:', retryCount);
        
        // Check if weather section exists before showing loading
        const weatherSection = document.querySelector('.weather-section');
        if (!weatherSection) {
            if (retryCount < 10) { // Limit retries to prevent infinite loop
                console.error('Weather section not found, retrying in 100ms... (attempt', retryCount + 1, 'of 10)');
                setTimeout(() => fetchWeatherData(retryCount + 1), 100);
                return;
            } else {
                console.error('Max retries reached, giving up on weather fetch');
                return;
            }
        }
        
        // Loading spinner removed to prevent element replacement
        console.log('Weather data fetch started - no loading spinner to prevent element replacement');
        
        const response = await fetch('/weather/data', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        console.log('Weather API response status:', response.status);
        console.log('Weather API response ok:', response.ok);

        if (!response.ok) {
            const errorText = await response.text();
            console.error('Weather API error response:', errorText);
            throw new Error(`Weather API request failed: ${response.status}`);
        }

        const result = await response.json();
        console.log('Weather API result:', result);
        
        if (result.success) {
            console.log('Weather API result.data structure:', result.data);
            console.log('result.data.current:', result.data.current);
            console.log('result.data.forecast:', result.data.forecast);
            updateWeatherData(result.data);
        } else {
            console.error('Weather API returned success: false');
            showWeatherError();
        }
    } catch (error) {
        console.error('Weather fetch error:', error);
        showWeatherError();
    }
}

// Debug function to check DOM elements
function debugWeatherElements() {
    console.log('=== DEBUGGING WEATHER ELEMENTS ===');
    console.log('All elements with weather-related classes:');
    
    const allElements = document.querySelectorAll('[class*="weather"], [class*="current"], [class*="feels"]');
    allElements.forEach((el, index) => {
        console.log(`${index + 1}. ${el.tagName} with classes: ${el.className}`);
    });
    
    console.log('Specific weather elements:');
    console.log('- .weather-section:', document.querySelector('.weather-section'));
    console.log('- .current-temp:', document.querySelector('.current-temp'));
    console.log('- .feels-like:', document.querySelector('.feels-like'));
    console.log('- .weather-condition:', document.querySelector('.weather-condition'));
    console.log('- .weather-location:', document.querySelector('.weather-location'));
    
    // Check what's actually inside the weather section
    const weatherSection = document.querySelector('.weather-section');
    if (weatherSection) {
        console.log('Weather section innerHTML:', weatherSection.innerHTML);
        console.log('Weather section children count:', weatherSection.children.length);
        console.log('Weather section children:', Array.from(weatherSection.children).map(child => child.tagName + ' with classes: ' + child.className));
    }
    
    console.log('=== END DEBUG ===');
}

async function refreshWeatherData() {
    try {
        console.log('Refreshing weather data...');
        
        // Add visual feedback to the refresh button
        const refreshButton = document.querySelector('button[onclick="refreshWeatherData()"]');
        if (refreshButton) {
            refreshButton.disabled = true;
            refreshButton.innerHTML = `
                <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            `;
        }
        
        // Clear cache first
        await fetch('/weather/clear-cache', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        // Fetch fresh data
        await fetchWeatherData();
        
        console.log('Weather data refreshed successfully');
    } catch (error) {
        console.error('Weather refresh error:', error);
        showWeatherError();
    } finally {
        // Restore refresh button
        const refreshButton = document.querySelector('button[onclick="refreshWeatherData()"]');
        if (refreshButton) {
            refreshButton.disabled = false;
            refreshButton.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            `;
        }
    }
}

async function checkUserLocationAndFetchWeather() {
    try {
        // Check if user has saved location settings
        const response = await fetch('/weather/user-location', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        console.log('User location check result:', data);
        
        if (data.hasLocation && data.latitude && data.longitude) {
            console.log('User has saved location, using saved coordinates:', data);
            // User has saved location, use it directly
            setTimeout(() => {
                console.log('=== DEBUGGING WEATHER ELEMENTS RIGHT BEFORE API CALL ===');
                debugWeatherElements();
                
                // Additional focused debug before API call
                console.log('=== FOCUSED WEATHER DEBUG BEFORE API CALL ===');
                const weatherSection = document.querySelector('.weather-section');
                console.log('Weather section found:', !!weatherSection);
                if (weatherSection) {
                    console.log('Weather section innerHTML length:', weatherSection.innerHTML.length);
                    console.log('Contains current-temp:', weatherSection.innerHTML.includes('current-temp'));
                    console.log('Contains weather-condition:', weatherSection.innerHTML.includes('weather-condition'));
                    console.log('Contains weather-location:', weatherSection.innerHTML.includes('weather-location'));
                    console.log('Contains feels-like:', weatherSection.innerHTML.includes('feels-like'));
                }
                
                fetchWeatherData();
            }, 500);
        } else {
            console.log('No saved location found, detecting current location');
            // No saved location, detect current location
            detectUserLocation();
            // Wait for location detection before fetching weather
            setTimeout(() => {
                console.log('=== DEBUGGING WEATHER ELEMENTS RIGHT BEFORE API CALL ===');
                debugWeatherElements();
                
                // Additional focused debug before API call
                console.log('=== FOCUSED WEATHER DEBUG BEFORE API CALL ===');
                const weatherSection = document.querySelector('.weather-section');
                console.log('Weather section found:', !!weatherSection);
                if (weatherSection) {
                    console.log('Weather section innerHTML length:', weatherSection.innerHTML.length);
                    console.log('Contains current-temp:', weatherSection.innerHTML.includes('current-temp'));
                    console.log('Contains weather-condition:', weatherSection.innerHTML.includes('weather-condition'));
                    console.log('Contains weather-location:', weatherSection.innerHTML.includes('weather-location'));
                    console.log('Contains feels-like:', weatherSection.innerHTML.includes('feels-like'));
                }
                
                fetchWeatherData();
            }, 1000);
        }
    } catch (error) {
        console.error('Error checking user location:', error);
        // Fallback: detect current location
        detectUserLocation();
        setTimeout(() => {
            console.log('=== DEBUGGING WEATHER ELEMENTS RIGHT BEFORE API CALL ===');
            debugWeatherElements();
            
            // Additional focused debug before API call
            console.log('=== FOCUSED WEATHER DEBUG BEFORE API CALL ===');
            const weatherSection = document.querySelector('.weather-section');
            console.log('Weather section found:', !!weatherSection);
            if (weatherSection) {
                console.log('Weather section innerHTML length:', weatherSection.innerHTML.length);
                console.log('Contains current-temp:', weatherSection.innerHTML.includes('current-temp'));
                console.log('Contains weather-condition:', weatherSection.innerHTML.includes('weather-condition'));
                console.log('Contains weather-location:', weatherSection.innerHTML.includes('weather-location'));
                console.log('Contains feels-like:', weatherSection.innerHTML.includes('feels-like'));
            }
            
            fetchWeatherData();
        }, 1000);
    }
}

function detectUserLocation() {
    if (!navigator.geolocation) {
        console.log('Geolocation not supported');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            
            try {
                // Update user location in database
                await fetch('/weather/location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude
                    })
                });
                
                console.log('Location updated successfully');
            } catch (error) {
                console.error('Location update error:', error);
            }
        },
        (error) => {
            console.log('Geolocation error:', error.message);
            // Use default location (Kuala Lumpur)
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

// Make functions globally available
window.openGreetingModal = openGreetingModal;
window.closeGreetingModal = closeGreetingModal;
window.detectUserLocation = detectUserLocation;
window.checkUserLocationAndFetchWeather = checkUserLocationAndFetchWeather;
window.refreshWeatherData = refreshWeatherData;
window.fetchWeatherData = fetchWeatherData;
window.debugWeatherElements = debugWeatherElements;
</script>
