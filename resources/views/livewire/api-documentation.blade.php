<div>
    <div class="space-y-4">

        <!-- Base URL -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1 min-w-0 overflow-x-auto">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.api.documentation_content.base_url') }}:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $baseUrl }}</code>
            </div>
            <button 
                type="button"
                data-copy-text="{{ $baseUrl }}"
                wire:click="copyToClipboard('{{ $baseUrl }}')"
                class="ml-3 flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- API Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1 min-w-0 overflow-x-auto">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.api.documentation_content.api_header') }}:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">Accept: application/json</code>
            </div>
            <button 
                type="button"
                data-copy-text="Accept: application/json"
                wire:click="copyToClipboard('Accept: application/json')"
                class="ml-3 flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- Authentication -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1 min-w-0 overflow-x-auto">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.api.documentation_content.authentication') }}:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">Authorization: Bearer {{ $maskedApiKey }}</code>
            </div>
            <button 
                type="button"
                data-copy-text="Authorization: Bearer {{ $apiKey }}"
                wire:click="copyToClipboard('Authorization: Bearer {{ $apiKey }}')"
                class="ml-3 flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- Example Request -->
        <div class="flex items-start justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1 min-w-0 overflow-x-auto">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.api.documentation_content.example_request') }}:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre">
                    GET {{ $baseUrl }}/clients
                    Accept: application/json
                    Authorization: Bearer {{ $maskedApiKey }}
                </code>
            </div>
            <button 
                type="button"
                data-copy-text="GET {{ $baseUrl }}/clients\nAccept: application/json\nAuthorization: Bearer {{ $apiKey }}"
                wire:click="copyToClipboard('GET {{ $baseUrl }}/clients\nAccept: application/json\nAuthorization: Bearer {{ $apiKey }}')"
                class="ml-3 flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- Sample Screenshot -->
        <div class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('settings.api.documentation_content.sample_screenshot') }}:</p>
            <div class="overflow-x-auto">
                <a href="/images/api-sample-screenshot.png" target="_blank" class="block min-w-max">
                    <img src="/images/api-sample-screenshot.png" alt="API Documentation: Sample Screenshot" class="max-w-full h-auto rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
                </a>
            </div>
        </div>

        <!-- List of Supported API -->
        <div class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('settings.api.documentation_content.list_of_supported_api') }}:</p>
            
            <!-- User Endpoints -->
            <div class="space-y-2 mb-4">
                <h4 class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">User Endpoints</h4>
                
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/profile</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/profile"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/profile')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/api-key-info</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/api-key-info"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/api-key-info')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
            </div>
            
            <!-- Resource Endpoints -->
            <div class="space-y-2 mb-4">
                <h4 class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Resource Endpoints</h4>
                
                <!-- Clients -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/clients</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/clients"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/clients')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Projects -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/projects</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/projects"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/projects')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Documents -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/documents</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/documents"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/documents')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Important URLs -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/important-urls</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/important-urls"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/important-urls')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/phone-numbers</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/phone-numbers"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/phone-numbers')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Users -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/users</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/users"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/users')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Tasks -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/tasks</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/tasks"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/tasks')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Comments -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/comments</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/comments"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/comments')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Comments {comment} -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/comments/{comment}</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/comments/{comment}"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/comments/{comment}')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- Trello Boards -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/trello-boards</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/trello-boards"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/trello-boards')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <!-- OpenAI Logs -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-x-auto">
                        <code class="text-sm text-gray-600 dark:text-gray-400 whitespace-nowrap">GET {{ $baseUrl }}/openai-logs</code>
                    </div>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/openai-logs"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/openai-logs')"
                        class="flex-shrink-0 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>

            </div>
            
            <!-- Copy All Endpoints -->
            <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Copy all endpoints:</span>
                    <button 
                        type="button"
                        data-copy-text="GET {{ $baseUrl }}/profile\nGET {{ $baseUrl }}/api-key-info\nGET {{ $baseUrl }}/clients\nGET {{ $baseUrl }}/projects\nGET {{ $baseUrl }}/documents\nGET {{ $baseUrl }}/important-urls\nGET {{ $baseUrl }}/phone-numbers\nGET {{ $baseUrl }}/users\nGET {{ $baseUrl }}/tasks\nGET {{ $baseUrl }}/comments\nGET {{ $baseUrl }}/comments/{comment}\nGET {{ $baseUrl }}/trello-boards\nGET {{ $baseUrl }}/openai-logs"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/profile\nGET {{ $baseUrl }}/api-key-info\nGET {{ $baseUrl }}/clients\nGET {{ $baseUrl }}/projects\nGET {{ $baseUrl }}/documents\nGET {{ $baseUrl }}/important-urls\nGET {{ $baseUrl }}/phone-numbers\nGET {{ $baseUrl }}/users\nGET {{ $baseUrl }}/tasks\nGET {{ $baseUrl }}/comments\nGET {{ $baseUrl }}/comments/{comment}\nGET {{ $baseUrl }}/trello-boards\nGET {{ $baseUrl }}/openai-logs')"
                        class="inline-flex items-center px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="window.copyTextImmediate($event, $el.dataset.copyText); copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4 mr-1" />
                        <span x-show="!copied">Copy All</span>
                        <span x-show="copied">Copied!</span>
                    </button>
                </div>
            </div>

        </div>
        
    </div>

    <script>
        // Copy text immediately on click for mobile compatibility
        // This function executes synchronously within the user gesture context
        window.copyTextImmediate = function(event, text) {
            if (!text) {
                console.error('No text provided to copy');
                return; // Let Livewire action proceed normally
            }

            // Convert \n escape sequences to actual newlines
            // This handles multi-line text stored in data attributes
            const textToCopy = text.replace(/\\n/g, '\n');

            // Copy immediately using clipboard API with mobile fallback
            const copyToClipboard = async (textParam) => {
                // Try modern clipboard API first
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    try {
                        await navigator.clipboard.writeText(textParam);
                        return true;
                    } catch (err) {
                        console.warn('Clipboard API failed, trying fallback:', err);
                    }
                }

                // Fallback for mobile browsers
                try {
                    const textArea = document.createElement('textarea');
                    textArea.value = textParam;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    textArea.style.top = '-9999px';
                    textArea.style.opacity = '0';
                    textArea.setAttribute('readonly', '');
                    document.body.appendChild(textArea);

                    // For iOS Safari
                    if (navigator.userAgent.match(/ipad|iphone/i)) {
                        const range = document.createRange();
                        range.selectNodeContents(textArea);
                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);
                        textArea.setSelectionRange(0, 999999);
                    } else {
                        textArea.select();
                        textArea.setSelectionRange(0, 99999); // For mobile devices
                    }

                    const successful = document.execCommand('copy');
                    document.body.removeChild(textArea);

                    if (successful) {
                        return true;
                    }
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }

                return false;
            };

            // Execute copy immediately (don't await to keep it synchronous)
            copyToClipboard(textToCopy).catch(err => {
                console.error('Copy failed:', err);
            });

            // Don't prevent default - let Livewire action proceed for any logging/notifications
            // The copy happens immediately, preserving the user gesture context
        };

        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                // This event handler is kept for backward compatibility
                // The immediate copy already happened via Alpine.js x-on:click
                const text = event.text;
                // Fallback if immediate copy didn't work
                if (navigator.clipboard && text) {
                    navigator.clipboard.writeText(text).catch(() => {
                        // Final fallback
                        const textArea = document.createElement('textarea');
                        textArea.value = text;
                        document.body.appendChild(textArea);
                        textArea.select();
                        document.execCommand('copy');
                        document.body.removeChild(textArea);
                    });
                }
            });
        });
    </script>
</div>
