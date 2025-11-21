<script>
// Preload greeting modal background images when page loads
(function() {
    const lightImg = new Image();
    const darkImg = new Image();

    lightImg.src = '{{ \App\Services\ImageOptimizationService::getCachedPublicImageUrl("images/greeting-light.png") }}';
    darkImg.src = '{{ \App\Services\ImageOptimizationService::getCachedPublicImageUrl("images/greeting-dark.png") }}';

    // Store references globally for potential reuse
    window.greetingLightImg = lightImg;
    window.greetingDarkImg = darkImg;

    // Initialize status config for JavaScript modules
    window.greetingStatusConfig = {
        config: @json(\App\Services\OnlineStatus\StatusConfig::getJavaScriptConfig()),
        colors: @json(\App\Services\OnlineStatus\StatusConfig::getStatusColors())
    };

    // Initialize weather localization strings for JavaScript modules
    window.weatherLocalization = {
        feels_like: '{{ __("weather.feels_like") }}',
        loading: '{{ __("weather.loading") }}',
        humidity: '{{ __("weather.humidity") }}',
        wind: '{{ __("weather.wind") }}',
        uv_index: '{{ __("weather.uv_index") }}',
        sunset: '{{ __("weather.sunset") }}',
        forecast: '{{ __("weather.forecast") }}',
        forecast_high_low: '{{ __("weather.forecast_high_low") }}',
        today: '{{ __("weather.today") }}',
        last_weather_updated: '{{ __("weather.last_weather_updated") }}',
        error_title: 'Failed to retrieve weather data',
        error_message: 'Weather information temporarily unavailable'
    };

    // Initialize status update localization strings for JavaScript modules
    window.statusLocalization = {
        online_status_updated: '{{ __("user.indicator.online_status_updated") }}',
        online_status_update_failed: '{{ __("user.indicator.online_status_update_failed") }}'
    };

    // Store the modal HTML template for JavaScript modules
    window.greetingModalHTML = `
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-5xl w-full max-h-[90vh] transform transition-all duration-300 scale-95 opacity-0 border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
            <div class="flex flex-col lg:flex-row flex-1 overflow-y-auto">

                <!-- Weather Information Section (40% width on desktop, 100% on mobile) - Order 2 on small devices, Order 1 on large devices -->
                <div class="p-6 border-r-0 lg:border-r border-gray-200 dark:border-gray-700 border-b lg:border-b-0 weather-section w-full lg:w-2/5 order-2 lg:order-1">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">

                            <!-- Refresh Weather Button -->
                            <x-tooltip position="right" :text="__('weather.refresh_weather')">
                                <button onclick="refreshWeatherData()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                    @svg('heroicon-o-arrow-path', 'w-5 h-5')
                                </button>
                            </x-tooltip>

                            <!-- Last Updated Text -->
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

                                <!-- Weather Icon and Condition -->
                                <div class="flex items-center space-x-3">

                                    <!-- Weather Icon -->
                                    <div class="w-12 h-12 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center weather-icon-container">
                                        <x-icons.custom-icon name="refresh" class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                    </div>

                                    <!-- Weather Condition -->
                                    <div>
                                        <h4 class="text-lg font-semibold text-teal-700 dark:text-teal-100 weather-condition">{{ __('weather.loading') }}</h4>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 weather-location">{{ __('weather.loading') }}</p>
                                    </div>
                                </div>

                                <!-- Temperature and Feels Like -->
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-teal-700 dark:text-teal-100 current-temp">{{ __('weather.loading') }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 feels-like">{{ __('weather.feels_like') }} {{ __('weather.loading') }}</div>
                                </div>

                            </div>
                        </div>

                        <!-- Weather Details -->
                        <div class="grid grid-cols-2 gap-3">

                            <!-- Humidity -->
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <x-icons.custom-icon name="humidity" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.humidity') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white humidity-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Wind -->
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <x-icons.custom-icon name="wind" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.wind') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white wind-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- UV Index -->
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <x-icons.custom-icon name="uv-index" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('weather.uv_index') }}</div>
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white uv-value">{{ __('weather.loading') }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sunset -->
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <x-icons.custom-icon name="sunset" />
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

                        <!-- Forecast Container -->
                        <div class="space-y-2" id="forecast-container">

                            <!-- Today -->
                            <div class="flex items-center justify-between py-4 px-3 rounded-lg bg-gray-100 dark:bg-gray-700">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">{{ __('weather.today') }}</span>
                                    <x-icons.custom-icon name="refresh" class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>

                            <!-- Tomorrow -->
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Tomorrow</span>
                                    <x-icons.custom-icon name="refresh" class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>

                            <!-- Day 3 -->
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Day 3</span>
                                    <x-icons.custom-icon name="refresh" class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>

                            <!-- Day 4 -->
                            <div class="flex items-center justify-between p-3 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Day 4</span>
                                    <x-icons.custom-icon name="refresh" class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ __('weather.loading') }} / {{ __('weather.loading') }}</div>
                            </div>

                            <!-- Day 5 -->
                            <div class="flex items-center justify-between p-3">
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-600 dark:text-gray-400 w-24">Day 5</span>
                                    <x-icons.custom-icon name="refresh" class="w-6 h-6 text-primary-600 dark:text-primary-400"/>
                                </div>
                                <div class="text-sm font-medium text-gray-900 dark:text-white">- / -</div>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Greeting Section (60% width on desktop, 100% on mobile) - Order 1 on small devices, Order 2 on large devices -->
                <div class="p-6 flex flex-col justify-end bg-cover bg-center bg-no-repeat w-full lg:w-3/5 order-1 lg:order-2"
                     style="background-image: url('{{ \App\Services\ImageOptimizationService::getCachedPublicImageUrl("images/greeting-light.png") }}'); background-position: top center; background-size: contain;" data-bg-light="{{ \App\Services\ImageOptimizationService::getCachedPublicImageUrl('images/greeting-light.png') }}"
                     data-bg-dark="{{ \App\Services\ImageOptimizationService::getCachedPublicImageUrl('images/greeting-dark.png') }}">

                    <!-- Weather Scroll Button - Only visible on small devices -->
                    <div class="lg:hidden mb-4 flex justify-end">
                        <button onclick="scrollToWeatherSection()"
                                class="flex items-center space-x-2 px-4 py-2 bg-primary-500 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg border border-white dark:border-gray-500 transition-all duration-200">
                            <x-icons.custom-icon name="cloud" class="w-4 h-4" />
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
                        <div class="relative -bottom-5 right-4">
                            <x-interactive-online-status-indicator :user="auth()->user()" size="lg" />
                        </div>

                    </div>

                    <!-- Greeting Time icon and text -->
                    <div class="text-center mb-8">
                        <div class="flex items-center justify-center space-x-2">
                            <svg class="w-4 h-4 {{ now()->hour >= 7 && now()->hour <= 19 ? 'text-yellow-500' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if(now()->hour >= 7 && now()->hour <= 19)
                                    @svg('heroicon-o-sun', 'w-4 h-4')
                                @else
                                    @svg('heroicon-o-moon', 'w-4 h-4')
                                @endif
                            </svg>
                            <p class="text-sm text-gray-600 dark:text-gray-400 greeting-text">
                                @if(now()->hour >= 7 && now()->hour <= 11)
                                    {{ __('greetingmodal.good_morning') }}
                                @elseif(now()->hour >= 12 && now()->hour <= 17)
                                    {{ __('greetingmodal.good_afternoon') }}
                                @elseif(now()->hour >= 18 && now()->hour <= 23)
                                    {{ __('greetingmodal.good_evening') }}
                                @else
                                    {{ __('greetingmodal.good_night') }}
                                @endif
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
                    <div class="space-y-3 mb-6 max-h-80 overflow-y-auto">

                        <!-- Profile Quick Action -->
                        <button onclick="toggleProfileVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
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
                            <div class="flex-shrink-0 flex items-center" x-data="{ isVideoActive: false }" x-init="isVideoActive = !document.getElementById('profile-video')?.classList.contains('hidden')">
                                <span onclick="event.stopPropagation(); navigateToProfile()" class="text-sm text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors mr-2 cursor-pointer">{{ __('greetings.view_action') }}</span>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-all duration-200" x-bind:class="{ 'rotate-90': isVideoActive }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </button>

                        <!-- Settings Quick Action -->
                        <button onclick="toggleSettingsVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
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
                            <div class="flex-shrink-0 flex items-center" x-data="{ isVideoActive: false }" x-init="isVideoActive = !document.getElementById('settings-video')?.classList.contains('hidden')">
                                <span onclick="event.stopPropagation(); navigateToSettings()" class="text-sm text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors mr-2 cursor-pointer">{{ __('greetings.view_action') }}</span>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-all duration-200" x-bind:class="{ 'rotate-90': isVideoActive }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </button>

                        <!-- Action Board Quick Action -->
                        <button onclick="toggleActionBoardVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
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
                            <div class="flex-shrink-0 flex items-center" x-data="{ isVideoActive: false }" x-init="isVideoActive = !document.getElementById('action-board-video')?.classList.contains('hidden')">
                                <span onclick="event.stopPropagation(); navigateToActionBoard()" class="text-sm text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors mr-2 cursor-pointer">{{ __('greetings.view_action') }}</span>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-all duration-200" x-bind:class="{ 'rotate-90': isVideoActive }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </button>

                        <!-- Meeting Links Quick Action -->
                        <button onclick="toggleMeetingLinksVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
                            <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors flex-shrink-0">
                                @svg('heroicon-o-video-camera', 'w-5 h-5 text-primary-600 dark:text-primary-400')
                            </div>
                            <div class="flex-1 min-w-0">
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                    {{ __('greetingmodal.action-5-title') }}
                                </h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                    {{ __('greetingmodal.action-5-description') }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 flex items-center" x-data="{ isVideoActive: false }" x-init="isVideoActive = !document.getElementById('meeting-links-video')?.classList.contains('hidden')">
                                <span onclick="event.stopPropagation(); navigateToMeetingLinks()" class="text-sm text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors mr-2 cursor-pointer">{{ __('greetings.view_action') }}</span>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-all duration-200" x-bind:class="{ 'rotate-90': isVideoActive }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </button>

                        <!-- Resources Quick Action -->
                        <button onclick="toggleDataManagementVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
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
                            <div class="flex-shrink-0 flex items-center" x-data="{ isVideoActive: false }" x-init="isVideoActive = !document.getElementById('data-management-video')?.classList.contains('hidden')">
                                <span class="text-sm text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors mr-2">{{ __('greetings.view_action') }}</span>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-all duration-200" x-bind:class="{ 'rotate-90': isVideoActive }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </button>

                        <!-- Users Quick Action -->
                        <button onclick="toggleUsersVideo()" class="flex items-center space-x-4 p-4 bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 rounded-lg transition-colors duration-200 group border border-gray-200 dark:border-gray-600 w-full text-left">
                            <div class="w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg flex items-center justify-center group-hover:bg-primary-100 dark:group-hover:bg-primary-900/40 transition-colors flex-shrink-0">
                                @svg('heroicon-o-users', 'w-5 h-5 text-primary-600 dark:text-primary-400')
                            </div>
                            <div class="flex-1 min-w-0">
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white mb-1 group-hover:text-primary-700 dark:group-hover:text-primary-300 transition-colors">
                                    {{ __('greetingmodal.action-6-title') }}
                                </h5>
                                <p class="text-xs text-gray-500 dark:text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors">
                                    {{ __('greetingmodal.action-6-description') }}
                                </p>
                            </div>
                            <div class="flex-shrink-0 flex items-center" x-data="{ isVideoActive: false }" x-init="isVideoActive = !document.getElementById('users-video')?.classList.contains('hidden')">
                                <span onclick="event.stopPropagation(); navigateToUsers()" class="text-sm text-gray-400 dark:text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors mr-2 cursor-pointer">{{ __('greetings.view_action') }}</span>
                                <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-all duration-200" x-bind:class="{ 'rotate-90': isVideoActive }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
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
                                <x-close-button
                                    onclick="toggleDataManagementVideo()"
                                    aria-label="Close video"
                                />
                            </div>
                            <div class="relative group">
                                <video
                                    id="resource-tutorial-video"
                                    class="w-full rounded-lg shadow-sm cursor-pointer"
                                    preload="none"
                                    loop
                                    muted
                                    onclick="toggleVideoPlay(this)"
                                >
                                    {{ __('greetingmodal.video-not-supported') }}
                                </video>

                                <!-- Custom Play in Fullscreen Button -->
                                <button
                                    onclick="playVideoInFullscreen()"
                                    class="absolute bottom-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100 backdrop-blur-sm"
                                    title="Play in Fullscreen"
                                >
                                    @svg('heroicon-o-arrows-pointing-out', 'w-5 h-5')
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Profile Video Container -->
                    <div id="profile-video" class="hidden mt-[-20px] mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50/10 dark:bg-gray-700/10 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ __('greetingmodal.action-1-title') }} Tutorial
                                </h6>
                                <x-close-button
                                    onclick="toggleProfileVideo()"
                                    aria-label="Close video"
                                />
                            </div>
                            <div class="relative group">
                                <video
                                    id="profile-tutorial-video"
                                    class="w-full rounded-lg shadow-sm cursor-pointer"
                                    preload="none"
                                    loop
                                    muted
                                    onclick="toggleVideoPlay(this)"
                                >
                                    {{ __('greetingmodal.video-not-supported') }}
                                </video>

                                <!-- Custom Play in Fullscreen Button -->
                                <button
                                    onclick="playVideoInFullscreen()"
                                    class="absolute bottom-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100 backdrop-blur-sm"
                                    title="Play in Fullscreen"
                                >
                                    @svg('heroicon-o-arrows-pointing-out', 'w-5 h-5')
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Settings Video Container -->
                    <div id="settings-video" class="hidden mt-[-20px] mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50/10 dark:bg-gray-700/10 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ __('greetingmodal.action-2-title') }} Tutorial
                                </h6>
                                <x-close-button
                                    onclick="toggleSettingsVideo()"
                                    aria-label="Close video"
                                />
                            </div>
                            <div class="relative group">
                                <video
                                    id="settings-tutorial-video"
                                    class="w-full rounded-lg shadow-sm cursor-pointer"
                                    preload="none"
                                    loop
                                    muted
                                    onclick="toggleVideoPlay(this)"
                                >
                                    {{ __('greetingmodal.video-not-supported') }}
                                </video>

                                <!-- Custom Play in Fullscreen Button -->
                                <button
                                    onclick="playVideoInFullscreen()"
                                    class="absolute bottom-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100 backdrop-blur-sm"
                                    title="Play in Fullscreen"
                                >
                                    @svg('heroicon-o-arrows-pointing-out', 'w-5 h-5')
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Action Board Video Container -->
                    <div id="action-board-video" class="hidden mt-[-20px] mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50/10 dark:bg-gray-700/10 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ __('greetingmodal.action-3-title') }} Tutorial
                                </h6>
                                <x-close-button
                                    onclick="toggleActionBoardVideo()"
                                    aria-label="Close video"
                                />
                            </div>
                            <div class="relative group">
                                <video
                                    id="action-board-tutorial-video"
                                    class="w-full rounded-lg shadow-sm cursor-pointer"
                                    preload="none"
                                    loop
                                    muted
                                    onclick="toggleVideoPlay(this)"
                                >
                                    {{ __('greetingmodal.video-not-supported') }}
                                </video>

                                <!-- Custom Play in Fullscreen Button -->
                                <button
                                    onclick="playVideoInFullscreen()"
                                    class="absolute bottom-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100 backdrop-blur-sm"
                                    title="Play in Fullscreen"
                                >
                                    @svg('heroicon-o-arrows-pointing-out', 'w-5 h-5')
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Meeting Links Video Container -->
                    <div id="meeting-links-video" class="hidden mt-[-20px] mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50/10 dark:bg-gray-700/10 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ __('greetingmodal.action-5-title') }} Tutorial
                                </h6>
                                <x-close-button
                                    onclick="toggleMeetingLinksVideo()"
                                    aria-label="Close video"
                                />
                            </div>
                            <div class="relative group">
                                <video
                                    id="meeting-links-tutorial-video"
                                    class="w-full rounded-lg shadow-sm cursor-pointer"
                                    preload="none"
                                    loop
                                    muted
                                    onclick="toggleVideoPlay(this)"
                                >
                                    {{ __('greetingmodal.video-not-supported') }}
                                </video>

                                <!-- Custom Play in Fullscreen Button -->
                                <button
                                    onclick="playVideoInFullscreen()"
                                    class="absolute bottom-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100 backdrop-blur-sm"
                                    title="Play in Fullscreen"
                                >
                                    @svg('heroicon-o-arrows-pointing-out', 'w-5 h-5')
                                </button>

                            </div>
                        </div>
                    </div>

                    <!-- Users Video Container -->
                    <div id="users-video" class="hidden mt-[-20px] mb-6 opacity-0 transform scale-95 transition-all duration-300 ease-in-out">
                        <div class="bg-gray-50/10 dark:bg-gray-700/10 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-3">
                                <h6 class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ __('greetingmodal.action-6-title') }} Tutorial
                                </h6>
                                <x-close-button
                                    onclick="toggleUsersVideo()"
                                    aria-label="Close video"
                                />
                            </div>
                            <div class="relative group">
                                <video
                                    id="users-tutorial-video"
                                    class="w-full rounded-lg shadow-sm cursor-pointer"
                                    preload="none"
                                    loop
                                    muted
                                    onclick="toggleVideoPlay(this)"
                                >
                                    {{ __('greetingmodal.video-not-supported') }}
                                </video>

                                <!-- Custom Play in Fullscreen Button -->
                                <button
                                    onclick="playVideoInFullscreen()"
                                    class="absolute bottom-3 right-3 p-2 bg-black/60 hover:bg-black/80 text-white rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100 backdrop-blur-sm"
                                    title="Play in Fullscreen"
                                >
                                    @svg('heroicon-o-arrows-pointing-out', 'w-5 h-5')
                                </button>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-6 py-4 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-600 flex-shrink-0">
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
})();
</script>