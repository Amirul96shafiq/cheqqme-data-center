<script>
function openGreetingModal(forceOpen = false) {
    // Check if user has enabled 'no show greeting today' for today
    // Skip this check if forceOpen is true (manual click)
    if (!forceOpen) {
        const today = new Date().toDateString();
        const lastDismissed = localStorage.getItem('greetingModalDismissed');
        
        if (lastDismissed === today) {
            return; // Don't show modal if dismissed today
        }
    }
    
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
            <div class="flex flex-col lg:flex-row flex-1 overflow-y-auto">
                
                <!-- Weather Information Section (40% width on desktop, 100% on mobile) - Order 2 on small devices, Order 1 on large devices -->
                <div class="p-6 border-r-0 lg:border-r border-gray-200 dark:border-gray-700 border-b lg:border-b-0 weather-section w-full lg:w-2/5 order-2 lg:order-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <button onclick="refreshWeatherData()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" title="{{ __('weather.refresh_weather') }}">
                                @svg('heroicon-o-arrow-path', 'w-5 h-5')
                            </button>
                            <div class="text-xs text-gray-500 dark:text-gray-400" id="weather-last-updated" data-last-updated-text="{{ __('weather.last_weather_updated') }}">
                                {{ __('greetingmodal.footer-text') }}
                            </div>
                        </div>
                    </div>
                    
                    <!-- Weather Content -->
                    <div class="space-y-4">
                        <!-- Current Weather -->
                        <div class="bg-gradient-to-r from-white to-teal-100/50 dark:from-transparent dark:to-teal-700/50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center weather-icon-container">
                                        @svg('heroicon-o-arrow-path', 'w-6 h-6 text-primary-600 dark:text-primary-400 animate-spin')
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
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-primary-600 dark:text-primary-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Tomorrow</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-primary-600 dark:text-primary-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Day 3</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-primary-600 dark:text-primary-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Day 4</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-primary-600 dark:text-primary-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ __('weather.loading') }} / {{ __('weather.loading') }}</div>
                            </div>
                            <div class="flex items-center justify-between p-3">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16">Day 5</span>
                                    @svg('heroicon-o-arrow-path', 'w-6 h-6 text-primary-600 dark:text-primary-400 animate-spin')
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Greeting Section (60% width on desktop, 100% on mobile) - Order 1 on small devices, Order 2 on large devices -->
                <div class="p-6 flex flex-col justify-end bg-cover bg-center bg-no-repeat w-full lg:w-3/5 order-1 lg:order-2" 
                     style="background-image: url('/images/greeting-light.png'); background-position: top center; background-size: contain;" 
                     data-bg-light="/images/greeting-light.png" 
                     data-bg-dark="/images/greeting-dark.png">            
                    
                    <!-- Weather Scroll Button - Only visible on small devices -->
                    <div class="lg:hidden mb-4 flex justify-end">
                        <button onclick="scrollToWeatherSection()" 
                                class="flex items-center space-x-2 px-4 py-2 bg-primary-500 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg border border-white dark:border-gray-500 transition-all duration-200">
                            @svg('heroicon-o-cloud', 'w-4 h-4')
                            <span class="text-sm font-medium">{{ __('weather.view_weather') }}</span>
                        </button>
                    </div>
                    
                    <!-- Greeting Content -->
                    <!-- Avatar -->
                    <div class="flex justify-center items-center mb-4 mt-6 lg:mt-12">
                        <x-filament::avatar
                            :src="filament()->getUserAvatarUrl(auth()->user())"
                            :alt="__('filament-panels::layout.avatar.alt', ['name' => filament()->getUserName(auth()->user())])"
                            :circular="true"
                            size="h-16 w-16"
                            class="border-4 border-white dark:border-gray-800"
                            draggable="false"
                        />
                        <!-- Online Status Indicator -->
                        <div class="relative -bottom-4 right-4">
                            <x-interactive-online-status-indicator :user="auth()->user()" size="md" />
                        </div>
                    </div>
                    <!-- Greeting Time icon and text -->
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-center space-x-2">
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
                        <!-- Greeting Title (Name and "ready to get started?") -->
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                            <span class="font-bold text-primary-600 dark:text-primary-400">{{ \App\Helpers\ClientFormatter::formatClientName(auth()->user()?->name) }}</span>{{ __('greetingmodal.content-title') }}
                        </h4>
                        <!-- Greeting description -->
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
                        
                        <!-- Resources Quick Action -->
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
                    <div class="flex items-center">
                        <input type="checkbox" id="noShowGreetingToday" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <label for="noShowGreetingToday" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            {{ __('greetingmodal.no-show-today') }}
                        </label>
                    </div>
                    <button 
                        onclick="closeGreetingModal()" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-600 rounded-lg transition-colors duration-200"
                    >
                        {{ __('greetingmodal.action-dismiss') }}
                    </button>
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
        // Check if "no show greeting today" is checked
        const noShowCheckbox = modal.querySelector('#noShowGreetingToday');
        if (noShowCheckbox && noShowCheckbox.checked) {
            // Store dismissal for today
            const today = new Date().toDateString();
            localStorage.setItem('greetingModalDismissed', today);
        }
        
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
                openGreetingModal(true); // Force open when manually clicked
            });
        }
    }, 2000);
    
    // Auto-open greeting modal on dashboard
    setTimeout(function() {
        const currentPath = window.location.pathname;
        if (currentPath === '/admin' || currentPath === '/admin/') {
            openGreetingModal();
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
                openGreetingModal(true); // Force open when manually clicked
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

// Scroll to weather section function
window.scrollToWeatherSection = function() {
    const weatherSection = document.querySelector('.weather-section');
    if (weatherSection) {
        weatherSection.scrollIntoView({ 
            behavior: 'smooth',
            block: 'start'
        });
    }
};

// Toggle Resources video container
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
    'sunny': { icon: 'heroicon-o-sun', color: 'text-primary-500' },
    'clear': { icon: 'heroicon-o-sun', color: 'text-primary-500' },
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

// Get weather icon
function getWeatherIcon(condition) {
    if (!condition) return { icon: 'heroicon-o-sun', color: 'text-primary-500' };
    
    const conditionLower = condition.toLowerCase();
    
    for (const [key, value] of Object.entries(weatherIconMap)) {
        if (conditionLower.includes(key)) {
            return value;
        }
    }
    
    return { icon: 'heroicon-o-sun', color: 'text-primary-500' };
}

// Custom heroicon SVG
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

// Update weather data
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

// Update current weather
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

// Update weather icon
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

// Update weather details
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

// Update forecast data
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

// Show weather error
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

// Update weather footer
function updateWeatherFooter(weatherData) {
    const weatherElement = document.getElementById('weather-last-updated');
    if (!weatherElement || !weatherData) {
        console.log('Weather element not found or no weather data');
        return;
    }

    // Get the localized text from the data attribute
    const lastUpdatedText = weatherElement.getAttribute('data-last-updated-text') || 'Last Weather updated';

    // Extract timestamp from weather data
    const timestamp = weatherData.timestamp;
    if (!timestamp) {
        console.log('No timestamp found in weather data');
        return;
    }

    try {
        // Parse the ISO timestamp and format it
        const date = new Date(timestamp);
        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear().toString().slice(-2);
        const formattedDate = `${day}/${month}/${year}`;
        
        const formattedTime = date.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });

        weatherElement.textContent = `${lastUpdatedText}: ${formattedDate}, ${formattedTime}`;
        // console.log('Updated weather section with timestamp:', `${lastUpdatedText}: ${formattedDate}, ${formattedTime}`);
    } catch (error) {
        console.error('Error formatting timestamp:', error);
        weatherElement.textContent = `${lastUpdatedText}: Unknown`;
    }
}

// Fetch weather data
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

// Refresh weather data
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

// Check user location and fetch weather
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

// Detect user location
function detectUserLocation() {
    if (!navigator.geolocation) {
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            
            try {
                // First, try to get city and country from reverse geocoding
                let city = null;
                let country = null;
                
                try {
                    const geocodeResponse = await fetch(`https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`);
                    if (geocodeResponse.ok) {
                        const geocodeData = await geocodeResponse.json();
                        city = geocodeData.city || geocodeData.locality || null;
                        country = geocodeData.countryCode || null;
                    }
                } catch (geocodeError) {
                    // Silent fail for reverse geocoding
                    console.log('Reverse geocoding failed, proceeding with coordinates only');
                }
                
                // Update location with city and country if available
                await fetch('/weather/location', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        latitude: latitude,
                        longitude: longitude,
                        city: city,
                        country: country
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

// Global notification function
window.showNotification = function(type, message) {
    // Use Filament's notification system if available
    if (window.$wire && window.$wire.$dispatch) {
        window.$wire.$dispatch('notify', {
            type: type,
            message: message
        });
    } else {
        // Fallback to console log
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
};

// Global function to update online status via AJAX with presence channels
window.updateOnlineStatus = function(status) {
    // console.log('updateOnlineStatus called with status:', status);
    // Show loading state
    const button = event.target.closest('button');
    let originalContent = null;
    if (button) {
        originalContent = button.innerHTML;
        button.innerHTML = '<div class="w-4 h-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>';
        button.disabled = true;
    }
    
    // Use presence status manager if available, otherwise fallback to AJAX
    if (window.presenceStatusManager && window.presenceStatusManager.isInitialized) {
        // Use presence channel for real-time updates
        window.presenceStatusManager.updateUserStatus(status)
            .then(() => {
                // Update only the current user's status indicators
                updateAllStatusIndicators(status, true);
                
                // Show success notification
                if (window.showNotification) {
                    window.showNotification('success', '{{ __("user.indicator.online_status_updated") }}');
                }
                
                // Restore button content immediately after status update
                if (button && originalContent) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
                
                // Dispatch Livewire event to update form fields
                if (window.Livewire) {
                    window.Livewire.dispatch('online-status-updated');
                }
            })
            .catch(error => {
                console.error('Error updating status via presence channel:', error);
                if (window.showNotification) {
                    window.showNotification('error', '{{ __("user.indicator.online_status_update_failed") }}');
                }
            })
            .finally(() => {
                // Button state is restored in the success handler
                // Only restore here if there was an error
                if (button && originalContent && button.innerHTML.includes('animate-spin')) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
            });
    } else {
        // Fallback to AJAX request
        console.log('Making AJAX request to update status:', status);
        fetch('/admin/profile/update-online-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                online_status: status
            })
        })
        .then(response => {
            console.log('AJAX response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('AJAX response data:', data);
            if (data.success) {
                // Update only the current user's status indicators
                updateAllStatusIndicators(status, true);
                
                 // Show success notification
                 if (window.showNotification) {
                     window.showNotification('success', '{{ __("user.indicator.online_status_updated") }}');
                 }
                
                // Restore button content immediately after status update
                if (button && originalContent) {
                    button.innerHTML = originalContent;
                    button.disabled = false;
                }
                
                // Dispatch Livewire event to update form fields
                if (window.Livewire) {
                    window.Livewire.dispatch('online-status-updated');
                }
                
                // No need to refresh page - real-time updates handle this
             } else {
                 // Show error notification
                 if (window.showNotification) {
                     window.showNotification('error', data.message || '{{ __("user.indicator.online_status_update_failed") }}');
                 }
             }
        })
        .catch(error => {
            console.error('Error updating status:', error);
            if (window.showNotification) {
                window.showNotification('error', '{{ __("user.indicator.online_status_update_failed") }}');
            }
        })
        .finally(() => {
            // Button state is restored in the success handler
            // Only restore here if there was an error
            if (button && originalContent && button.innerHTML.includes('animate-spin')) {
                button.innerHTML = originalContent;
                button.disabled = false;
            }
        });
    }
};

// Function to update all status indicators on the page
window.updateAllStatusIndicators = function(newStatus, currentUserOnly = false) {
     // console.log('🔄 updateAllStatusIndicators called with status:', newStatus, 'currentUserOnly:', currentUserOnly);
     // Get status configuration from backend
        const statusConfig = @json(\App\Services\OnlineStatus\StatusConfig::getJavaScriptConfig());
        // console.log('📋 Status config:', statusConfig);
        
        // Make status config available globally
        window.statusConfig = statusConfig;
     
     // Update all status indicator buttons
     document.querySelectorAll('[data-status-indicator]').forEach(indicator => {
         // Skip non-current user indicators if currentUserOnly is true
         if (currentUserOnly) {
             const isCurrentUser = indicator.getAttribute('data-is-current-user') === 'true';
             if (!isCurrentUser) {
                 return; // Skip this indicator
             }
         }
         
         // Remove all possible status classes
         const statusColors = @json(\App\Services\OnlineStatus\StatusConfig::getStatusColors());
         statusColors.forEach(colorClass => {
             indicator.classList.remove(colorClass);
         });
         
         // Add new status class
         if (statusConfig[newStatus]) {
             indicator.classList.add(statusConfig[newStatus].color);
         }
     });
     
     // Update all tooltip texts
     const tooltips = document.querySelectorAll('.tooltip[data-tooltip-text]');
     // console.log('🔍 Found tooltips to update:', tooltips.length);
     tooltips.forEach(tooltip => {
         // Skip non-current user tooltips if currentUserOnly is true
         if (currentUserOnly) {
             // Find the associated status indicator
             const indicator = tooltip.closest('.tooltip-container')?.querySelector('.online-status-indicator, [data-status-indicator]') ||
                              tooltip.parentElement?.querySelector('.online-status-indicator, [data-status-indicator]') ||
                              document.querySelector(`[data-tooltip-text="${tooltip.getAttribute('data-tooltip-text')}"]`);
             
             if (indicator) {
                 const isCurrentUser = indicator.getAttribute('data-is-current-user') === 'true';
                 if (!isCurrentUser) {
                     return; // Skip this tooltip
                 }
             } else {
                 return; // Skip if no associated indicator found
             }
         }
         
         if (statusConfig[newStatus]) {
             // console.log('💬 Updating tooltip to:', statusConfig[newStatus].label);
             tooltip.setAttribute('data-tooltip-text', statusConfig[newStatus].label);
             tooltip.textContent = statusConfig[newStatus].label;
         }
     });
     
     // Update interactive dropdown selection states
     const alpineComponents = document.querySelectorAll('[x-data]');
     // console.log('🏔️ Found Alpine.js components:', alpineComponents.length);
     alpineComponents.forEach(component => {
         // Look for status dropdown buttons in the new component structure
         const statusButtons = component.querySelectorAll('button[class*="space-x-3"][class*="text-left"]');
         // console.log('📋 Found status buttons in component:', statusButtons.length);
         if (statusButtons.length > 0) {
             // Remove current selection styling from all buttons
             statusButtons.forEach(button => {
                 button.classList.remove('bg-primary-50', 'dark:bg-primary-900/10');
                 const currentIndicator = button.querySelector('.w-2.h-2.rounded-full.bg-primary-500');
                 if (currentIndicator) {
                     currentIndicator.remove();
                 }
             });
             
             // Add selection styling to the new status button
             statusButtons.forEach(button => {
                 const statusText = button.textContent.trim();
                 const statusKey = Object.keys(statusConfig).find(key => 
                     statusConfig[key].label === statusText
                 );
                 
                 if (statusKey === newStatus) {
                     button.classList.add('bg-primary-50', 'dark:bg-primary-900/10');
                     
                     // Add current status indicator dot
                     const indicator = document.createElement('div');
                     indicator.className = 'w-2 h-2 rounded-full bg-primary-500 flex-shrink-0';
                     button.appendChild(indicator);
                 }
             });
         }
     });
     
     // Update tooltip text in interactive components
     document.querySelectorAll('[x-data]').forEach(component => {
         const tooltip = component.querySelector('.tooltip[data-tooltip-text]');
         if (tooltip && statusConfig[newStatus]) {
             // Skip non-current user tooltips if currentUserOnly is true
             if (currentUserOnly) {
                 const indicator = component.querySelector('.online-status-indicator, [data-status-indicator]');
                 if (indicator) {
                     const isCurrentUser = indicator.getAttribute('data-is-current-user') === 'true';
                     if (!isCurrentUser) {
                         return; // Skip this tooltip
                     }
                 } else {
                     return; // Skip if no associated indicator found
                 }
             }
             
             // console.log('💬 Updating interactive tooltip to:', statusConfig[newStatus].label);
             tooltip.setAttribute('data-tooltip-text', statusConfig[newStatus].label);
             tooltip.textContent = statusConfig[newStatus].label;
             tooltip.setAttribute('title', statusConfig[newStatus].label);
         }
     });
     
     // Update only the current user's status indicator
     document.querySelectorAll('.online-status-indicator').forEach(indicator => {
         const isCurrentUser = indicator.getAttribute('data-is-current-user') === 'true';
         
         // Only update the current user's indicator
         if (isCurrentUser) {
             const currentStatus = indicator.getAttribute('data-current-status');
             
             if (currentStatus !== newStatus) {
                 // Update the data attribute
                 indicator.setAttribute('data-current-status', newStatus);
                 
                // Get status configuration from backend
                const statusConfig = @json(\App\Services\OnlineStatus\StatusConfig::getJavaScriptConfig());
                 
                 // Update the CSS classes using the same logic as the component
                 const sizeClasses = indicator.className.match(/w-\d+ h-\d+/);
                 const baseClasses = sizeClasses ? sizeClasses[0] : 'w-4 h-4';
                 const borderClasses = 'border-2 border-white dark:border-gray-900';
                 const roundedClasses = 'rounded-full';
                 
                 // Remove old status classes and add new ones
                 const statusColors = @json(\App\Services\OnlineStatus\StatusConfig::getStatusColors());
                 statusColors.forEach(colorClass => {
                     indicator.classList.remove(colorClass);
                 });
                 if (statusConfig[newStatus]) {
                     indicator.className = `${baseClasses} ${borderClasses} ${roundedClasses} ${statusConfig[newStatus].color} online-status-indicator`;
                 }
             }
         }
     });
};

// Function to sync all users' statuses from database
window.syncAllUserStatuses = function() {
    // console.log('🔄 Syncing all user statuses from database...');
    
    // Get all user IDs from the page
    const indicators = document.querySelectorAll('.online-status-indicator');
    // console.log('🔍 Found status indicators:', indicators.length);
    
    const userIds = Array.from(indicators)
        .map(indicator => indicator.getAttribute('data-user-id'))
        .filter(id => id);
    
    // console.log('👥 User IDs found:', userIds);
    
    if (userIds.length === 0) {
        console.log('❌ No user indicators found on page');
        return;
    }
    
    // Fetch current statuses from API
    fetch('/api/user/statuses', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Requested-With': 'XMLHttpRequest',
            'Authorization': 'Bearer ' + (window.chatbotApiToken || ''),
        },
        body: JSON.stringify({
            user_ids: userIds
        })
    })
    .then(response => {
        // console.log('📡 API Response status:', response.status);
        return response.json();
    })
    .then(data => {
        // console.log('📊 API Response data:', data);
        if (data.success && data.statuses) {
            // console.log('✅ Received status data:', data.statuses);
            
            // Update each indicator with its actual status
            document.querySelectorAll('.online-status-indicator').forEach(indicator => {
                const userId = indicator.getAttribute('data-user-id');
                const isCurrentUser = indicator.getAttribute('data-is-current-user') === 'true';
                const actualStatus = data.statuses[userId];
                
                if (actualStatus) {
                    const currentStatus = indicator.getAttribute('data-current-status');
                    
                    if (currentStatus !== actualStatus) {
                        console.log(`Updating user ${userId} status: ${currentStatus} -> ${actualStatus}`);
                        
                        // Update the data attribute
                        indicator.setAttribute('data-current-status', actualStatus);
                        
                // Get status configuration from backend
                const statusConfig = @json(\App\Services\OnlineStatus\StatusConfig::getJavaScriptConfig());
                        
                        // Update the CSS classes using the same logic as the component
                        const sizeClasses = indicator.className.match(/w-\d+ h-\d+/);
                        const baseClasses = sizeClasses ? sizeClasses[0] : 'w-4 h-4';
                        const borderClasses = 'border-2 border-white dark:border-gray-900';
                        const roundedClasses = 'rounded-full';
                        
                         // Remove old status classes and add new ones
                         const statusColors = @json(\App\Services\OnlineStatus\StatusConfig::getStatusColors());
                         statusColors.forEach(colorClass => {
                             indicator.classList.remove(colorClass);
                         });
                         if (statusConfig[actualStatus]) {
                             indicator.className = `${baseClasses} ${borderClasses} ${roundedClasses} ${statusConfig[actualStatus].color} online-status-indicator`;
                         }
                         
                         // Update tooltip text for this indicator
                         const tooltipContainer = indicator.closest('.tooltip-container');
                         // console.log(`🔍 Looking for tooltip container for user ${userId}:`, tooltipContainer);
                         if (tooltipContainer) {
                             const tooltip = tooltipContainer.querySelector('.tooltip[data-tooltip-text]');
                             // console.log(`🔍 Found tooltip element for user ${userId}:`, tooltip);
                             if (tooltip && statusConfig[actualStatus]) {
                                 // console.log(`💬 Updating tooltip for user ${userId} from "${tooltip.textContent}" to:`, statusConfig[actualStatus].label);
                                 tooltip.setAttribute('data-tooltip-text', statusConfig[actualStatus].label);
                                 tooltip.textContent = statusConfig[actualStatus].label;
                                 // console.log(`✅ Tooltip updated for user ${userId}:`, tooltip.textContent);
                             } else {
                                 // console.log(`❌ Could not update tooltip for user ${userId} - tooltip:`, tooltip, 'statusConfig:', statusConfig[actualStatus]);
                             }
                         } else {
                             // console.log(`❌ No tooltip container found for user ${userId}`);
                         }
                    }
                }
            });
        } else {
            // console.log('❌ Failed to sync user statuses:', data.message);
        }
    })
    .catch(error => {
        // console.log('❌ Error syncing user statuses:', error);
    });
};

// Test function to manually test status updates (can be called from browser console)
window.testStatusUpdate = function(status) {
    // console.log('🧪 Testing status update for:', status);
    if (window.updateAllStatusIndicators) {
        window.updateAllStatusIndicators(status, true); // true = current user only
    } else {
        console.error('updateAllStatusIndicators function not found');
    }
};

// Test function to manually sync user statuses (can be called from browser console)
window.testSyncUserStatuses = function() {
    // console.log('🧪 Manually syncing user statuses from database...');
    if (window.syncAllUserStatuses) {
        window.syncAllUserStatuses();
    } else {
        console.error('syncAllUserStatuses function not found');
    }
};

// Test function to debug tooltip structure (can be called from browser console)
window.debugTooltipStructure = function() {
    // console.log('🔍 Debugging tooltip structure...');
    
    const indicators = document.querySelectorAll('.online-status-indicator');
    // console.log('📊 Found indicators:', indicators.length);
    
    indicators.forEach((indicator, index) => {
        const userId = indicator.getAttribute('data-user-id');
        const currentStatus = indicator.getAttribute('data-current-status');
        const tooltipContainer = indicator.closest('.tooltip-container');
        
        // console.log(`👤 User ${userId} (${index + 1}):`, {
        //     currentStatus: currentStatus,
        //     tooltipContainer: tooltipContainer,
        //     hasTooltip: tooltipContainer ? tooltipContainer.querySelector('.tooltip[data-tooltip-text]') : null
        // });
    });
};

// User Activity Tracking for Online Status
// Following Microsoft Teams behavior: any interaction resets auto-away to online
window.trackUserActivity = function() {
    if (window.userActivityTimeout) {
        clearTimeout(window.userActivityTimeout);
    }
    
    // Clear any pending auto-away timeout
    if (window.autoAwayTimeout) {
        clearTimeout(window.autoAwayTimeout);
        window.autoAwayTimeout = null;
    }
    
    // Debounce activity tracking to avoid excessive requests
    window.userActivityTimeout = setTimeout(() => {
        fetch('/admin/profile/track-activity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status indicators if status changed (only for current user)
                if (data.status && window.updateAllStatusIndicators) {
                    window.updateAllStatusIndicators(data.status, true); // true = current user only
                }
                
                // Start auto-away timer if user is online
                if (data.status === 'online') {
                    window.startAutoAwayTimer();
                }
            }
        }).catch(error => {
            // console.log('Activity tracking failed:', error);
        });
    }, 500); // Reduced debounce for more responsive behavior
};

// Auto-Away Timer - Set to 10 seconds (for testing) | 5 minutes (300000ms) - for production
window.startAutoAwayTimer = function() {
    // Clear existing timer
    if (window.autoAwayTimeout) {
        clearTimeout(window.autoAwayTimeout);
    }
    
    window.autoAwayTimeout = setTimeout(() => {
        console.log('Auto-away timer triggered - setting user to away');
        
        // Call API to set user as away due to inactivity
        fetch('/api/user/auto-away', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Authorization': 'Bearer ' + (window.chatbotApiToken || ''),
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('User auto-set to away due to inactivity');
                // Update status indicators (current user only)
                if (window.updateAllStatusIndicators) {
                    window.updateAllStatusIndicators('away', true);
                }
            } else {
                console.log('Auto-away failed:', data.message);
            }
        }).catch(error => {
            console.log('Auto-away API call failed:', error);
        });
    }, 300000); // 10 seconds (10000ms) - for testing | 5 minutes (300000ms) - for production
};

// Polling fallback for status updates when real-time is not available
window.startStatusPolling = function() {
    // console.log('🔄 Starting status polling fallback...');
    
    // Clear any existing polling interval
    if (window.statusPollingInterval) {
        clearInterval(window.statusPollingInterval);
    }
    
    // Poll every 10 seconds
    window.statusPollingInterval = setInterval(() => {
        // console.log('🔄 Polling for status updates...');
        window.syncAllUserStatuses();
    }, 10000); // 10 seconds
    
    // Initial sync
    window.syncAllUserStatuses();
};

// Stop polling when real-time becomes available
window.stopStatusPolling = function() {
    if (window.statusPollingInterval) {
        // console.log('🔄 Stopping status polling fallback...');
        clearInterval(window.statusPollingInterval);
        window.statusPollingInterval = null;
    }
};

// Track user activity on various events
document.addEventListener('DOMContentLoaded', function() {
    // Track activity on user interactions
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'].forEach(event => {
        document.addEventListener(event, window.trackUserActivity, true);
    });
    
    // Track activity when page becomes visible
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            window.trackUserActivity();
        }
    });
    
    // Track activity when window gains focus
    window.addEventListener('focus', window.trackUserActivity);
    
    // Fallback polling for status updates when real-time is not available
    // Check if presence status manager is available and working
    if (typeof window.presenceStatusManager === 'undefined' || !window.presenceStatusManager.isInitialized) {
        // console.log('🔄 Real-time updates not available, starting polling fallback...');
        window.startStatusPolling();
    }
    
    // Handle browser tab close - set user to invisible
    // Use visibilitychange event for more reliable tab close detection
    let hasSetInvisible = false;
    let tabSwitchTimeout = null;
    let isPageRefreshing = false;
    let refreshKeyPressed = false;
    let isNavigating = false;
    let navigationStartTime = 0;
    
    // Persistent logging function
    function persistentLog(message, data = null) {
        const timestamp = new Date().toISOString();
        const logEntry = {
            timestamp: timestamp,
            message: message,
            data: data
        };
        
        // Store in localStorage for persistence
        const logs = JSON.parse(localStorage.getItem('tabCloseLogs') || '[]');
        logs.push(logEntry);
        
        // Keep only last 50 entries
        if (logs.length > 50) {
            logs.splice(0, logs.length - 50);
        }
        
        localStorage.setItem('tabCloseLogs', JSON.stringify(logs));
        
        // Also log to console
        console.log(`[${timestamp}] ${message}`, data || '');
    }
    
    // Detect refresh key combinations
    document.addEventListener('keydown', function(event) {
        // Detect Ctrl+R, F5, Ctrl+F5, Ctrl+Shift+R
        if ((event.ctrlKey && event.key === 'r') || 
            event.key === 'F5' || 
            (event.ctrlKey && event.key === 'F5') ||
            (event.ctrlKey && event.shiftKey && event.key === 'R')) {
            // persistentLog('Refresh key combination detected', {
            //     key: event.key,
            //     ctrlKey: event.ctrlKey,
            //     shiftKey: event.shiftKey
            // });
            // refreshKeyPressed = true;
        }
    });
    
    // Detect page refresh using performance API
    // This is more reliable than keyboard detection
    let isPageRefresh = false;
    
    // Check if this is a page refresh on page load
    if (performance.navigation && performance.navigation.type === 1) {
        isPageRefresh = true;
        // persistentLog('Page refresh detected on page load', {
        //    navigationType: performance.navigation.type
        // });
    }
    
    // Also check navigation entries
    if (performance.getEntriesByType('navigation').length > 0) {
        const navEntry = performance.getEntriesByType('navigation')[0];
        if (navEntry.type === 'reload') {
            isPageRefresh = true;
            // persistentLog('Page reload detected via navigation entry', {
            //    type: navEntry.type
            // });
        }
    }
    
    // Reset navigation flag on page load
    isNavigating = false;
    
    // Detect navigation within the application
    document.addEventListener('click', function(event) {
        const target = event.target.closest('a');
        if (target && target.href) {
            // Check if it's an internal link (same domain)
            try {
                const url = new URL(target.href);
                const currentUrl = new URL(window.location.href);
                
                // if (url.origin === currentUrl.origin && url.pathname !== currentUrl.pathname) {
                //     isNavigating = true;
                //     navigationStartTime = Date.now();
                //     persistentLog('Navigation detected to internal page', {
                //         from: currentUrl.pathname,
                //         to: url.pathname
                //     });
                // }
            } catch (e) {
                // Invalid URL, ignore
            }
        }
    });
    
    // Better approach: Use multiple events to detect browser/tab close
    let isPageClosing = false;
    let closeDetectionTimeout = null;
    
    // Function to set user to invisible
    function setUserToInvisible(reason) {
        if (hasSetInvisible) return;
        
        // console.log('✅ Setting user to invisible status:', reason);
        // persistentLog('Setting user to invisible status', { reason: reason });
        
        hasSetInvisible = true;
        
        // Use sendBeacon for reliable delivery during page unload
        if (navigator.sendBeacon) {
            // console.log('📡 Using sendBeacon to set invisible status');
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
            
            const success = navigator.sendBeacon('/admin/profile/set-invisible-on-close', formData);
            // console.log('📡 sendBeacon result:', { success: success });
        } else {
            // console.log('📡 Using XMLHttpRequest fallback to set invisible status');
            // Fallback for browsers that don't support sendBeacon
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '/admin/profile/set-invisible-on-close', false);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
            xhr.send('_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''));
            // console.log('📡 XMLHttpRequest completed:', { status: xhr.status });
        }
    }
    
    // Method 1: Use pagehide event (more reliable than beforeunload)
    window.addEventListener('pagehide', function(event) {
        // console.log('🔍 pagehide event fired');
        // persistentLog('pagehide event fired', { 
        //     persisted: event.persisted,
        //     hasSetInvisible: hasSetInvisible,
        //     isPageRefreshing: isPageRefreshing,
        //     refreshKeyPressed: refreshKeyPressed,
        //     isNavigating: isNavigating
        // });
        
        // If page is being persisted (cached), it's not a close
        if (event.persisted) {
            // console.log('📄 Page is being persisted (cached) - not a close');
            return;
        }
        
        // Skip if it's a refresh
        if (refreshKeyPressed || isPageRefresh) {
            // console.log('🔄 Page refresh detected - NOT setting to invisible');
            return;
        }
        
        // Skip if it's navigation within the application
        if (isNavigating) {
            // console.log('🧭 Page navigation detected - NOT setting to invisible');
            return;
        }
        
        // Set invisible status for tab/browser close
        // console.log('🚪 Tab/browser close detected - setting to invisible');
        setUserToInvisible('tab_close');
    });
    
    // Method 2: Use unload event as backup
    window.addEventListener('unload', function(event) {
        // console.log('🔍 unload event fired');
        // persistentLog('unload event fired', { 
        //     hasSetInvisible: hasSetInvisible,
        //     isPageRefreshing: isPageRefreshing,
        //     refreshKeyPressed: refreshKeyPressed,
        //     isNavigating: isNavigating
        // });
        
        // Skip if it's a refresh
        if (refreshKeyPressed || isPageRefresh) {
            // console.log('🔄 Page refresh detected - NOT setting to invisible');
            return;
        }
        
        // Skip if it's navigation within the application
        if (isNavigating) {
            // console.log('🧭 Page navigation detected - NOT setting to invisible');
            return;
        }
        
        // Set invisible status for tab/browser close
        // console.log('🚪 Tab/browser close detected - setting to invisible');
        setUserToInvisible('tab_close');
    });
    
    // Method 3: Use beforeunload as final fallback (simplified)
    window.addEventListener('beforeunload', function(event) {
        // console.log('🔍 beforeunload event fired - simplified detection');
        // persistentLog('beforeunload event fired', {
        //     hasSetInvisible: hasSetInvisible,
        //     isPageRefreshing: isPageRefreshing,
        //     refreshKeyPressed: refreshKeyPressed,
        //     isNavigating: isNavigating
        // });
        
        // Skip if it's a refresh
        if (refreshKeyPressed || isPageRefresh) {
            // console.log('🔄 Page refresh detected - NOT setting to invisible');
            return;
        }
        
        // Skip if it's navigation within the application
        if (isNavigating) {
            // console.log('🧭 Page navigation detected - NOT setting to invisible');
            return;
        }
        
        // Set invisible status for tab/browser close
        // console.log('🚪 Tab/browser close detected - setting to invisible');
        setUserToInvisible('tab_close');
    });
    
    // Handle tab visibility changes for activity tracking
    document.addEventListener('visibilitychange', function() {
        // persistentLog('Visibility changed', { hidden: document.hidden });
        
        if (document.hidden) {
            // Tab became hidden - maintain online status for proper away time counting
            // persistentLog('Tab became hidden - maintaining online status (not setting to invisible)');
            
            // Clear any existing timeout
            if (tabSwitchTimeout) {
                clearTimeout(tabSwitchTimeout);
            }
            
            // Do not change status when tab loses focus - let the 5-minute away timer handle it
            // This allows proper away time counting instead of immediately going invisible
            // persistentLog('Tab switch detected - status remains online for away time counting');
        } else {
            // Tab became visible again, cancel any timeouts
            // persistentLog('Tab became visible, cancelling any timeouts...');
            if (tabSwitchTimeout) {
                clearTimeout(tabSwitchTimeout);
                tabSwitchTimeout = null;
            }
            
            // Reset the flag when tab becomes visible again
            hasSetInvisible = false;
            
            // Check if user should be restored from auto-status
            checkAndRestoreFromAutoStatus();
            
            // Track activity when tab becomes visible
            window.trackUserActivity();
        }
    });
    
    // Tab switching detection - do NOT set to invisible on tab switch
    // Only set to invisible on actual tab/browser close
    let tabCloseDetectionTimeout = null;
    
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Tab became hidden - this could be a tab switch or tab close
            // We will NOT set to invisible here to allow proper away time counting
            // persistentLog('Tab became hidden - maintaining online status for away time counting');
            
            // Clear any existing timeout
            if (tabCloseDetectionTimeout) {
                clearTimeout(tabCloseDetectionTimeout);
            }
            
            // Note: Removed the 5-second timeout that was incorrectly setting status to invisible
            // Tab switches should not change status - only actual tab/browser close should
        } else {
            // Tab became visible - cancel any timeouts
            if (tabCloseDetectionTimeout) {
                clearTimeout(tabCloseDetectionTimeout);
                tabCloseDetectionTimeout = null;
            }
        }
    });
    
    // Function to check and restore user from auto-status when returning to tab
    function checkAndRestoreFromAutoStatus() {
        // console.log('Checking if user should be restored from auto-status...');
        
        // Call API to restore from auto-status if applicable
        fetch('/api/user/restore-auto-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Authorization': 'Bearer ' + (window.chatbotApiToken || ''),
            }
        }).then(response => response.json())
        .then(data => {
            if (data.success && data.data && data.data.previous_status !== data.data.status) {
                // console.log('User restored from auto-status:', data.data.previous_status, '->', data.data.status);
                // Update status indicators (current user only)
                if (window.updateAllStatusIndicators) {
                    window.updateAllStatusIndicators(data.data.status, true);
                }
                // Restart auto-away timer
                if (data.data.status === 'online') {
                    window.startAutoAwayTimer();
                }
            } else {
                // console.log('User status unchanged on tab return:', data.data?.status || 'unknown');
            }
        }).catch(error => {
            console.log('Failed to restore from auto-status:', error);
        });
    }
    
    // Initialize presence status manager for real-time online status
    if (window.presenceStatusManager) {
        window.presenceStatusManager.init();
    }
    
    // Legacy activity tracking (will be replaced by presence channels)
    window.trackUserActivity();
    
            // Check if user should be restored from auto-status on page load
            checkAndRestoreFromAutoStatus();

            // Start auto-away timer on page load
            window.startAutoAwayTimer();
            
            // Sync all user statuses from database on page load
            setTimeout(() => {
                if (window.syncAllUserStatuses) {
                    window.syncAllUserStatuses();
                }
            }, 1000); // 1 second delay to ensure page is fully loaded
});

// Make functions globally available
window.openGreetingModal = openGreetingModal;
window.closeGreetingModal = closeGreetingModal;
window.refreshWeatherData = refreshWeatherData;
</script>
