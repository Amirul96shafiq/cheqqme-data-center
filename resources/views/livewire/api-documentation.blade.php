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
                <code class="text-sm text-gray-600 dark:text-gray-400">Authorization: Bearer {{ $apiKey }}</code>
            </div>
            <button 
                type="button"
                wire:click="copyToClipboard('Authorization: Bearer {{ $apiKey }}')"
                class="ml-3 inline-flex items-center p-1.5 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded transition-colors"
                x-data="{ copied: false }"
                x-on:click="copied = true; setTimeout(() => copied = false, 2000)"
            >
                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path h stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
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
                    Authorization: Bearer {{ $apiKey }}
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
        <div class="flex items-center justify-between bg-white dark:bg-gray-900 border rounded-lg border-gray-300 dark:border-white/10 py-2 px-4">
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">List of Supported API:</p>
                <code class="text-sm text-gray-600 dark:text-gray-400">
                    GET {{ $baseUrl }}/clients<br>
                    GET {{ $baseUrl }}/projects<br>
                    GET {{ $baseUrl }}/documents<br>
                    GET {{ $baseUrl }}/important-urls<br>
                    GET {{ $baseUrl }}/phone-numbers<br>
                    GET {{ $baseUrl }}/users<br>
                    GET {{ $baseUrl }}/comments<br>
                    GET {{ $baseUrl }}/comments/{comment}
                </code>
            </div>
            <button 
                type="button"
                wire:click="copyToClipboard('GET {{ $baseUrl }}/clients\nGET {{ $baseUrl }}/projects\nGET {{ $baseUrl }}/documents\nGET {{ $baseUrl }}/important-urls\nGET {{ $baseUrl }}/phone-numbers\nGET {{ $baseUrl }}/users\nGET {{ $baseUrl }}/comments\nGET {{ $baseUrl }}/comments/{comment}')"
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
