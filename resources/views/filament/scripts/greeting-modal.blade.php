<script>
function openGreetingModal() {
    // Create modal overlay
    const modal = document.createElement('div');
    modal.id = 'greeting-modal-overlay';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center p-4';
    modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
    modal.style.backdropFilter = 'blur(12px)';
    
    // Get current time for personalized greeting
    const hour = new Date().getHours();
    const greeting = hour >= 7 && hour <= 11 ? '{{ __("greetingmodal.good_morning") }}' : 
                    hour >= 12 && hour <= 19 ? '{{ __("greetingmodal.good_afternoon") }}' : 
                    hour >= 20 && hour <= 23 ? '{{ __("greetingmodal.good_evening") }}' : '{{ __("greetingmodal.good_night") }}';
    
    const icon = hour >= 7 && hour <= 19 ? 'sun' : 'moon';
    const iconColor = hour >= 7 && hour <= 19 ? 'text-yellow-500' : 'text-blue-400';
    
    modal.innerHTML = `
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] transform transition-all duration-300 scale-95 opacity-0 border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <div class="flex flex-col lg:flex-row flex-1 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-transparent">
                
                <!-- Weather Information Section (40% width on desktop, 100% on mobile) -->
                <div class="p-6 border-r-0 lg:border-r border-gray-200 dark:border-gray-700 border-b lg:border-b-0 weather-section w-full lg:w-2/5">
                    <div class="flex items-center justify-between mb-4">
                        <button onclick="refreshWeatherData()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('weather.refresh_weather') }}">
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
                                        @svg('heroicon-o-arrow-path', 'w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin')
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-semibold text-teal-700 dark:text-teal-100 weather-condition">{{ __('weather.loading') }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 weather-location">{{ __('weather.loading') }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-teal-700 dark:text-teal-100 current-temp">{{ __('weather.loading') }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 feels-like">{{ __('weather.feels_like') }} {{ __('weather.loading') }}</div>
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
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.humidity') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white humidity-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-arrow-down-circle', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.wind') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white wind-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-bolt', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.uv_index') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white uv-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @svg('heroicon-o-clock', 'w-6 h-6 text-gray-500 dark:text-gray-400')
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.sunset') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white sunset-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 5-Day Forecast -->
                    <div class="mt-12">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('weather.forecast') }}</h5>
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('weather.forecast_high_low') }}</span>
                        </div>
                        <div class="space-y-2" id="forecast-container">
                            <div class="flex items-center justify-between py-4 px-3 rounded-lg bg-gray-100 dark:bg-gray-700">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">{{ __('weather.today') }}</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Tomorrow</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Day 3</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Day 4</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ __('weather.loading') }} / {{ __('weather.loading') }}</div>
                            </div>
                            <div class="flex items-center justify-between p-3">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Day 5</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-yellow-600 dark:text-yellow-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Greeting Section (60% width on desktop, 100% on mobile) -->
                <div class="p-6 flex flex-col justify-end bg-cover bg-center bg-no-repeat w-full lg:w-3/5" 
                     style="background-image: url('/images/greeting-light.png'); background-position: top center; background-size: contain;" 
                     data-bg-light="/images/greeting-light.png" 
                     data-bg-dark="/images/greeting-dark.png">            
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-center space-x-2 mt-32 lg:mt-0">
                            <svg class="w-4 h-4 ${iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                ${icon === 'sun' ? 
                                    `@svg('heroicon-o-sun', 'w-4 h-4')` :
                                    `@svg('heroicon-o-moon', 'w-4 h-4')`
                                }
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400 greeting-text">
                                ${greeting}
                            </p>
                        </div>
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ \App\Helpers\ClientFormatter::formatClientName(auth()->user()?->name) }}</span>{{ __('greetingmodal.content-title') }}
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed mb-3">
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
                            <div class="flex-shrink-0">
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors')
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
                            <div class="flex-shrink-0">
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors')
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
                            <div class="flex-shrink-0">
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors')
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
                            <div class="flex-shrink-0">
                                @svg('heroicon-o-chevron-right', 'w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors')
                            </div>
                        </button>
                    </div>
                
                    <!-- Video Container -->
                    <div id="data-management-video" class="hidden mt-[-20px] mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
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
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600 flex-shrink-0">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-0">
                    <div class="text-xs text-gray-500 dark:text-gray-400 text-center sm:text-left" id="weather-footer-text" data-last-updated-text="{{ __('weather.last_weather_updated') }}">
                        {{ __('greetingmodal.footer-text') }}
                    </div>
                    <div class="flex space-x-3">
                        <button 
                            onclick="closeGreetingModal()" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                        >
                            {{ __('greetingmodal.action-dismiss') }}
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
        
        // Set up dark mode background image switching after modal is rendered
        setTimeout(() => {
            setupGreetingBackgroundImage();
        }, 50);
        
        // Check user location and fetch weather data
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
    setTimeout(function() {
        const selectors = [
            '[data-filament-dropdown-list] a[href="javascript:void(0)"]',
            '.fi-dropdown-list a[href="javascript:void(0)"]',
            '[role="menu"] a[href="javascript:void(0)"]'
        ];
        
        let greetingLink = null;
        
        for (const selector of selectors) {
            const links = document.querySelectorAll(selector);
            
            links.forEach(function(link) {
                const text = link.textContent.trim().toLowerCase();
                if ((text.includes('good morning') || text.includes('good afternoon') || 
                    text.includes('good evening') || text.includes('goodnight') ||
                    text.includes('morning') || text.includes('afternoon') || 
                    text.includes('evening') || text.includes('night') ||
                    text.includes('pagi') || text.includes('petang') || 
                    text.includes('malam') || text.includes('selamat malam')) &&
                    (link.closest('[data-filament-dropdown-list]') || 
                     link.closest('.fi-dropdown-list') ||
                     link.closest('[role="menu"]'))) {
                    greetingLink = link;
                }
            });
            
            if (greetingLink) break;
        }
        
        if (greetingLink) {
            greetingLink.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                openGreetingModal();
            });
        }
    }, 2000);
});

// Fallback: Use event delegation to catch clicks on any element
document.addEventListener('click', function(e) {
    let element = e.target;
    
    // Check up to 3 parent levels
    for (let i = 0; i < 3; i++) {
        if (element && element.textContent) {
            const text = element.textContent.trim().toLowerCase();
            if ((text.includes('good morning') || text.includes('good afternoon') || 
                text.includes('good evening') || text.includes('goodnight') ||
                text.includes('morning') || text.includes('afternoon') || 
                text.includes('evening') || text.includes('night') ||
                text.includes('pagi') || text.includes('petang') || 
                text.includes('malam') || text.includes('selamat malam')) &&
                (element.closest('[data-filament-dropdown-list]') || 
                 element.closest('.fi-dropdown-list') ||
                 element.closest('[role="menu"]'))) {
                e.preventDefault();
                e.stopPropagation();
                openGreetingModal();
                break;
            }
        }
        element = element?.parentElement;
    }
});

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
    const quickActionsContainer = videoContainer?.parentElement?.querySelector('.space-y-3');
    
    if (videoContainer && quickActionsContainer) {
        const isHidden = videoContainer.classList.contains('hidden');
        
        if (isHidden) {
            // Show video container with animation
            videoContainer.classList.remove('hidden');
            // Force reflow to ensure the element is visible before animation
            videoContainer.offsetHeight;
            // Add animation classes
            videoContainer.classList.remove('opacity-0', 'scale-95');
            videoContainer.classList.add('opacity-100', 'scale-100');
            
            // Hide other quick actions
            const otherActions = quickActionsContainer.querySelectorAll('button:not([onclick="toggleDataManagementVideo()"])');
            otherActions.forEach(action => {
                action.style.display = 'none';
            });
            
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
                
                // Show other quick actions after video container is completely hidden
                setTimeout(() => {
                    const otherActions = quickActionsContainer.querySelectorAll('button:not([onclick="toggleDataManagementVideo()"])');
                    otherActions.forEach(action => {
                        action.style.display = 'flex';
                    });
                }, 100); // Additional delay after video container is hidden
            }, 300);
        }
    }
};

// Weather API Integration Functions
const weatherIconMap = {
    'sunny': { icon: 'heroicon-o-sun', color: 'text-yellow-500' },
    'clear': { icon: 'heroicon-o-sun', color: 'text-yellow-500' },
    'cloud': { icon: 'heroicon-o-cloud', color: 'text-gray-500' },
    'overcast': { icon: 'heroicon-o-cloud', color: 'text-gray-500' },
    'rain': { icon: 'heroicon-o-cloud-rain', color: 'text-blue-500' },
    'drizzle': { icon: 'heroicon-o-cloud-rain', color: 'text-blue-500' },
    'shower': { icon: 'heroicon-o-cloud-rain', color: 'text-blue-500' },
    'storm': { icon: 'heroicon-o-bolt', color: 'text-purple-500' },
    'thunder': { icon: 'heroicon-o-bolt', color: 'text-purple-500' },
    'lightning': { icon: 'heroicon-o-bolt', color: 'text-purple-500' },
    'snow': { icon: 'heroicon-o-snowflake', color: 'text-blue-300' },
    'blizzard': { icon: 'heroicon-o-snowflake', color: 'text-blue-300' },
    'sleet': { icon: 'heroicon-o-snowflake', color: 'text-blue-300' },
    'fog': { icon: 'heroicon-o-eye-slash', color: 'text-gray-400' },
    'mist': { icon: 'heroicon-o-eye-slash', color: 'text-gray-400' },
    'haze': { icon: 'heroicon-o-eye-slash', color: 'text-gray-400' }
};

function getWeatherIcon(condition) {
    if (!condition) return { icon: 'heroicon-o-sun', color: 'text-yellow-500' };
    
    const conditionLower = condition.toLowerCase();
    
    for (const [key, value] of Object.entries(weatherIconMap)) {
        if (conditionLower.includes(key)) {
            return value;
        }
    }
    
    return { icon: 'heroicon-o-sun', color: 'text-yellow-500' };
}

function getHeroiconSVG(iconName) {
    const svgPaths = {
        'heroicon-o-sun': '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />',
        'heroicon-o-cloud': '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" />',
        'heroicon-o-cloud-rain': '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 004.5 4.5H18a3.75 3.75 0 001.332-7.257 3 3 0 00-3.758-3.848 5.25 5.25 0 00-10.233 2.33A4.502 4.502 0 002.25 15z" /><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 19.5L12 15.75l3.75 3.75" />',
        'heroicon-o-bolt': '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />',
        'heroicon-o-snowflake': '<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />',
        'heroicon-o-eye-slash': '<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 11-4.243-4.243m4.242 4.242L9.88 9.88" />'
    };
    
    return svgPaths[iconName] || svgPaths['heroicon-o-sun'];
}

function updateWeatherData(weatherData) {
    const weatherSection = document.querySelector('.weather-section');
    if (!weatherSection || !weatherData) {
        return;
    }

    const { current, forecast, error } = weatherData;
    
    if (error) {
        showWeatherError();
        return;
    }

    // Update current weather
    updateCurrentWeather(current);
    
    // Update forecast
    updateForecastData(forecast);
    
    // Update footer with weather timestamp
    updateWeatherFooter(current);
}

function updateCurrentWeather(weatherData) {
    const actualCurrentDetails = weatherData.current || {};
    const locationDetails = weatherData.location || {};
    
    // Get elements
    const tempElement = document.querySelector('.current-temp');
    const feelsLikeElement = document.querySelector('.feels-like');
    const conditionElement = document.querySelector('.weather-condition');
    const locationElement = document.querySelector('.weather-location');
    
    if (!tempElement || !feelsLikeElement || !conditionElement || !locationElement) {
        return;
    }
    
    // Update temperature
    tempElement.textContent = actualCurrentDetails.temperature + '°C';

    // Update feels like
    const feelsLikeText = '{{ __('weather.feels_like') }}';
    feelsLikeElement.textContent = feelsLikeText + ' ' + actualCurrentDetails.feels_like + '°C';

    // Update condition
    conditionElement.textContent = actualCurrentDetails.condition;

    // Update location
    locationElement.textContent = locationDetails.city + ', ' + locationDetails.country;

    // Update weather icon
    updateWeatherIcon(actualCurrentDetails.icon, actualCurrentDetails.condition);

    // Update weather details
    updateWeatherDetails(actualCurrentDetails);
}

function updateWeatherIcon(iconCode, condition) {
    const iconContainer = document.querySelector('.weather-icon-container');
    if (!iconContainer) return;

    const weatherIcon = getWeatherIcon(condition);
    
    // Determine background color based on weather condition
    let bgClass = 'bg-yellow-100 dark:bg-yellow-900/30';
    
    if (!condition) {
        condition = 'sunny';
    }
    
    switch (condition.toLowerCase()) {
        case 'clear':
        case 'sunny':
            bgClass = 'bg-yellow-100 dark:bg-yellow-900/30';
            break;
        case 'clouds':
        case 'cloudy':
            bgClass = 'bg-gray-100 dark:bg-gray-700/30';
            break;
        case 'rain':
        case 'drizzle':
            bgClass = 'bg-blue-100 dark:bg-blue-900/30';
            break;
        case 'thunderstorm':
            bgClass = 'bg-purple-100 dark:bg-purple-900/30';
            break;
        case 'snow':
            bgClass = 'bg-blue-50 dark:bg-blue-800/30';
            break;
        default:
            bgClass = 'bg-yellow-100 dark:bg-yellow-900/30';
    }
    
    iconContainer.className = `w-12 h-12 rounded-full flex items-center justify-center weather-icon-container ${bgClass}`;
    
    // Update icon with heroicon SVG
    iconContainer.innerHTML = `
        <svg class="w-8 h-8 ${weatherIcon.color}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            ${getHeroiconSVG(weatherIcon.icon)}
        </svg>
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
    const forecast = weatherData.forecast || [];
    const forecastContainer = document.getElementById('forecast-container');
    if (!forecastContainer || !Array.isArray(forecast)) {
        return;
    }

    let forecastHTML = '';
    const today = new Date();
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    // Limit to 5 days
    const limitedForecast = forecast.slice(0, 5);
    
    limitedForecast.forEach((day, index) => {
        const isLastItem = index === limitedForecast.length - 1;
        const isToday = index === 0;
        const borderClass = (isLastItem || isToday) ? '' : 'border-b border-gray-200 dark:border-gray-600';
        
        // Day labeling
        let dayLabel;
        if (index === 0) {
            dayLabel = 'Today';
        } else if (index === 1) {
            dayLabel = 'Tomorrow';
        } else {
            const forecastDate = new Date(today);
            forecastDate.setDate(today.getDate() + index);
            dayLabel = dayNames[forecastDate.getDay()];
        }
        
        const todayClasses = isToday ? 'rounded-lg bg-gray-100 dark:bg-gray-700' : '';
        const weatherIcon = getWeatherIcon(day.condition);
        
        forecastHTML += `
            <div class="flex items-center justify-between p-3 ${todayClasses} ${borderClass}">
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">${dayLabel}</span>
                    <div class="w-6 h-6 ${weatherIcon.color}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${getHeroiconSVG(weatherIcon.icon)}
                        </svg>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 flex-1 min-w-0">${day.description}</span>
                </div>
                <div class="text-sm font-medium text-gray-900 dark:text-white text-right">
                    <div>${day.max_temp}°C / ${day.min_temp}°C</div>
                </div>
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

function updateWeatherFooter(weatherData) {
    const footerElement = document.getElementById('weather-footer-text');
    if (!footerElement || !weatherData) {
        console.log('Footer element not found or no weather data');
        return;
    }

    // Get the localized text from the data attribute
    const lastUpdatedText = footerElement.getAttribute('data-last-updated-text') || 'Last Weather updated';

    // Extract timestamp from weather data
    const timestamp = weatherData.timestamp;
    if (!timestamp) {
        console.log('No timestamp found in weather data');
        return;
    }

    try {
        // Parse the ISO timestamp and format it
        const date = new Date(timestamp);
        const formattedDate = date.toLocaleDateString('en-US', {
            month: 'numeric',
            day: 'numeric',
            year: '2-digit'
        });
        const formattedTime = date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });

        footerElement.textContent = `${lastUpdatedText}: ${formattedDate} ${formattedTime}`;
        console.log('Updated footer with timestamp:', `${lastUpdatedText}: ${formattedDate} ${formattedTime}`);
    } catch (error) {
        console.error('Error formatting timestamp:', error);
        footerElement.textContent = `${lastUpdatedText}: Unknown`;
    }
}

async function fetchWeatherData(retryCount = 0) {
    try {
        const weatherSection = document.querySelector('.weather-section');
        if (!weatherSection) {
            if (retryCount < 5) {
                setTimeout(() => fetchWeatherData(retryCount + 1), 100);
                return;
            }
            return;
        }
        
        const response = await fetch('/weather/data', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error(`Weather API request failed: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            updateWeatherData(result.data);
        } else {
            showWeatherError();
        }
    } catch (error) {
        showWeatherError();
    }
}

async function refreshWeatherData() {
    try {
        const refreshButton = document.querySelector('button[onclick="refreshWeatherData()"]');
        if (refreshButton) {
            refreshButton.disabled = true;
            refreshButton.innerHTML = `@svg('heroicon-o-arrow-path', 'w-5 h-5 animate-spin')`;
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
    } catch (error) {
        showWeatherError();
    } finally {
        const refreshButton = document.querySelector('button[onclick="refreshWeatherData()"]');
        if (refreshButton) {
            refreshButton.disabled = false;
            refreshButton.innerHTML = `@svg('heroicon-o-arrow-path', 'w-5 h-5')`;
        }
    }
}

async function checkUserLocationAndFetchWeather() {
    try {
        const response = await fetch('/weather/user-location', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });
        
        const data = await response.json();
        
        if (data.hasLocation && data.latitude && data.longitude) {
            setTimeout(() => fetchWeatherData(), 500);
        } else {
            detectUserLocation();
            setTimeout(() => fetchWeatherData(), 1000);
        }
    } catch (error) {
        detectUserLocation();
        setTimeout(() => fetchWeatherData(), 1000);
    }
}

function detectUserLocation() {
    if (!navigator.geolocation) {
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            
            try {
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
            } catch (error) {
                // Silent fail for location update
            }
        },
        (error) => {
            // Silent fail for geolocation error
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

// Setup greeting background image based on dark/light mode
function setupGreetingBackgroundImage() {
    const greetingSection = document.querySelector('[data-bg-light]');
    if (!greetingSection) return;
    
    // Function to update background image based on theme
    function updateBackgroundImage() {
        // Simple check: if dark class is present, use dark image, otherwise use light image
        const isDarkMode = document.documentElement.classList.contains('dark') || 
                          document.body.classList.contains('dark');
        
        const bgImage = isDarkMode ? 
            greetingSection.getAttribute('data-bg-dark') : 
            greetingSection.getAttribute('data-bg-light');
        
        greetingSection.style.backgroundImage = `url('${bgImage}')`;
    }
    
    // Set initial background image
    updateBackgroundImage();
    
    // Listen for theme changes
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && 
                (mutation.attributeName === 'class' || mutation.attributeName === 'data-theme')) {
                updateBackgroundImage();
            }
        });
    });
    
    // Observe document element and body for theme changes
    observer.observe(document.documentElement, { attributes: true });
    observer.observe(document.body, { attributes: true });
    
    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateBackgroundImage);
}

// Make functions globally available
window.openGreetingModal = openGreetingModal;
window.closeGreetingModal = closeGreetingModal;
window.refreshWeatherData = refreshWeatherData;
</script>
