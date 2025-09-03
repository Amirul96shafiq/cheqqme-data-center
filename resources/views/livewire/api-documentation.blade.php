<div>
    <div class="space-y-4">
        <!-- Base URL -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base URL:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400">{{ $baseUrl }}</code>
            </div>
            <button 
                type="button"
                wire:click="copyToClipboard('{{ $baseUrl }}')"
                class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- API Header -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Header:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400">Accept: application/json</code>
            </div>
            <button 
                type="button"
                wire:click="copyToClipboard('Accept: application/json')"
                class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- Authentication -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Authentication:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400">Authorization: Bearer {{ $maskedApiKey }}</code>
            </div>
            <button 
                type="button"
                wire:click="copyToClipboard('Authorization: Bearer {{ $apiKey }}')"
                class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- Example Request -->
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Example Request:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400">
                    GET {{ $baseUrl }}/clients<br>
                    Accept: application/json<br>
                    Authorization: Bearer {{ $maskedApiKey }}
                </code>
            </div>
            <button 
                type="button"
                wire:click="copyToClipboard('GET {{ $baseUrl }}/clients\nAccept: application/json\nAuthorization: Bearer {{ $apiKey }}')"
                class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
            </button>
        </div>

        <!-- Sample Screenshot -->
        <div class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sample Screenshot:</p>
            <a href="/images/api-sample-screenshot.png" target="_blank" class="block">
                <img src="/images/api-sample-screenshot.png" alt="API Documentation: Sample Screenshot" class="w-full h-auto rounded-lg cursor-pointer hover:opacity-90 transition-opacity">
            </a>
        </div>

        <!-- List of Supported API -->
        <div class="bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">List of Supported API:</p>
            
            <!-- User Endpoints -->
            <div class="space-y-2 mb-4">
                <h4 class="text-[10px] font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">User Endpoints</h4>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/profile</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/profile')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/api-key-info</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/api-key-info')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
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
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/clients</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/clients')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/projects</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/projects')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/documents</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/documents')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/important-urls</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/important-urls')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/phone-numbers</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/phone-numbers')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/users</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/users')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/tasks</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/tasks')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/comments</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/comments')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/comments/{comment}</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/comments/{comment}')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/trello-boards</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/trello-boards')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
                    >
                        <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                        <x-heroicon-o-check x-show="copied" class="w-4 h-4" />
                    </button>
                </div>
                
                <div class="flex items-center justify-between">
                    <code class="text-sm text-gray-600 dark:text-gray-400">GET {{ $baseUrl }}/openai-logs</code>
                    <button 
                        type="button"
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/openai-logs')"
                        class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
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
                        wire:click="copyToClipboard('GET {{ $baseUrl }}/profile\nGET {{ $baseUrl }}/api-key-info\nGET {{ $baseUrl }}/clients\nGET {{ $baseUrl }}/projects\nGET {{ $baseUrl }}/documents\nGET {{ $baseUrl }}/important-urls\nGET {{ $baseUrl }}/phone-numbers\nGET {{ $baseUrl }}/users\nGET {{ $baseUrl }}/tasks\nGET {{ $baseUrl }}/comments\nGET {{ $baseUrl }}/comments/{comment}\nGET {{ $baseUrl }}/trello-boards\nGET {{ $baseUrl }}/openai-logs')"
                        class="inline-flex items-center px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                        x-data="{ copied: false }"
                        x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
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
        document.addEventListener('livewire:init', () => {
            Livewire.on('copy-to-clipboard', (event) => {
                const text = event.text;
                navigator.clipboard.writeText(text).then(() => {
                    // Success - the button will show "Copied!" via Alpine.js
                }).catch(() => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                });
            });
        });
    </script>
</div>
