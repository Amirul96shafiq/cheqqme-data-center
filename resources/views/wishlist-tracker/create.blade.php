<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100vh; max-height: 100vh; overflow: hidden; margin: 0; padding: 0; box-sizing: border-box;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Submit Wishlist - {{ $project->title }} - {{ config('app.name') }}</title>

  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" href="{{ optimized_asset('images/favicon.png') }}">

  @vite(['resources/css/app.css'])

  {{-- Alpine.js --}}
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="antialiased font-sans bg-auto bg-no-repeat m-0 p-0"
      style="height: 100vh; margin: 0; padding: 0; background-image: url('{{ optimized_asset('images/issue-tracker-bg.png') }}'); background-position: top center; display: flex; flex-direction: column;"
      x-data="{
        showTrackingTokensModal: false,
        loadingTokens: false,
        trackingTokensData: null,
        tokensError: null,
        wishlistsCount: 0,
        async fetchWishlistsCount() {
          try {
            const response = await fetch('/api/wishlist-trk/{{ $project->wishlist_tracker_code }}/tokens/count');
            if (!response.ok) {
              throw new Error('Failed to fetch wishlists count');
            }
            const data = await response.json();
            this.wishlistsCount = data.count;
          } catch (error) {
            console.error('Error fetching wishlists count:', error);
          }
        },
        async fetchTrackingTokens() {
          this.loadingTokens = true;
          this.tokensError = null;
          try {
            const response = await fetch('/api/wishlist-trk/{{ $project->wishlist_tracker_code }}/tokens');
            if (!response.ok) {
              throw new Error('Failed to fetch tracking tokens');
            }
            this.trackingTokensData = await response.json();
          } catch (error) {
            this.tokensError = error.message;
            console.error('Error fetching tracking tokens:', error);
          } finally {
            this.loadingTokens = false;
          }
        },
        init() {
          // Wait for window to fully load before fetching count
          window.addEventListener('load', () => {
            this.fetchWishlistsCount();
          });
        }
      }">

  {{-- Loading Transition --}}
  <x-auth-loading />

  {{-- Content area --}}
  <div style="flex: 1; overflow-y: auto; min-height: 100vh;">
    <div class="flex items-center justify-center min-h-full py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">

        {{-- Header --}}
        <div class="text-center">
          <img src="{{ optimized_asset('logos/logo-light.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-32 w-auto mb-8">
          <h1 class="text-3xl font-bold text-gray-900">
            Wishlist Submission
          </h1>
          <p class="mt-2 text-sm text-gray-600">
            Project: <span class="inline-block px-3 py-1.5 bg-white rounded-full font-semibold text-primary-500">{{ $project->title }} ({{ $project->wishlist_tracker_code }})</span>
          </p>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
          <div class="rounded-md bg-teal-50 p-4 border border-teal-200 mb-6">
            <div class="flex flex-col items-center">
              <div class="mb-3">
                <x-heroicon-s-check-circle class="h-20 w-20 text-teal-400" />
              </div>
              <div class="w-full">
                <p class="text-sm font-medium text-teal-800 mb-3 text-center">
                  {{ session('success') }}
                </p>

                @if (session('tracking_token'))
                  <div class="mt-3 space-y-3">
                    <div>
                      <p class="text-xs font-medium text-teal-700 mb-1">Tracking Code:</p>
                      <div class="flex items-center space-x-2">
                        <code class="flex-1 px-3 py-2 bg-white border border-teal-200 rounded-md text-sm font-mono text-teal-900">
                          {{ session('tracking_token') }}
                        </code>
                      </div>
                    </div>
                    <div>
                      <p class="text-xs font-medium text-teal-700 mb-1">View Status:</p>
                      <div class="flex items-center space-x-2">
                        <input type="text"
                               id="tracking-url"
                               value="{{ route('wishlist-tracker.status', ['token' => session('tracking_token')]) }}"
                               readonly
                               class="flex-1 px-3 py-2 bg-white border border-teal-200 rounded-md text-sm text-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <button type="button"
                                id="copy-tracking-link"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-900 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                          <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                          </svg>
                          Copy Link
                        </button>
                      </div>
                      <p id="copy-success" class="mt-2 text-xs text-teal-600 hidden">Link copied to clipboard!</p>
                    </div>
                  </div>
                @endif

              </div>
            </div>
          </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('wishlist-tracker.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6 bg-white shadow-lg rounded-lg p-6">
          @csrf

            {{-- Hidden Project ID --}}
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            {{-- Error Messages --}}
            @if ($errors->any())
              <div class="rounded-md bg-red-50 p-4 border border-red-200">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                      Please correct the following errors:
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                      <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                          <li>{{ $error }}</li>
                        @endforeach
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              @endif

              {{-- Name Field --}}
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                  Your Name <span class="text-red-500">*</span>
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                  required autofocus
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm">
              </div>

              {{-- Communication Preference --}}
              <div x-data="{ communicationPreference: '{{ old('communication_preference', 'whatsapp') }}' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Preferred Communication Method <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-6">

                  {{-- WhatsApp Radio Button --}}
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="communication_preference" value="whatsapp"
                           x-model="communicationPreference"
                           class="cursor-pointer">
                    <span class="text-sm text-gray-700">WhatsApp</span>
                  </label>

                  {{-- Email Radio Button --}}
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="communication_preference" value="email"
                           x-model="communicationPreference"
                           class="cursor-pointer">
                    <span class="text-sm text-gray-700">Email</span>
                  </label>

                  {{-- Both Radio Button --}}
                  <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" name="communication_preference" value="both"
                           x-model="communicationPreference"
                           class="cursor-pointer">
                    <span class="text-sm text-gray-700">Both</span>
                  </label>

                </div>

                {{-- Email Field (conditional) --}}
                <div x-show="communicationPreference === 'email' || communicationPreference === 'both'" x-transition>
                  <label for="email" class="block text-sm font-medium text-gray-700 mb-2 mt-4">
                    Your Email <span class="text-red-500">*</span>
                  </label>
                  <input id="email" type="email" name="email" value="{{ old('email') }}"
                        x-bind:required="communicationPreference === 'email' || communicationPreference === 'both'"
                        autocomplete="email"
                        placeholder="e.g., john.doe@example.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm">
                </div>

                {{-- WhatsApp Number Field (conditional) --}}
                <div x-show="communicationPreference === 'whatsapp' || communicationPreference === 'both'" x-transition>
                  <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-2 mt-4">
                    WhatsApp Number <span class="text-red-500">*</span>
                  </label>
                  <input id="whatsapp_number" type="tel" name="whatsapp_number" value="{{ old('whatsapp_number') }}"
                        x-bind:required="communicationPreference === 'whatsapp' || communicationPreference === 'both'"
                        pattern="^\+[1-9]\d{7,14}$"
                        title="Enter a valid WhatsApp number with + and country code, e.g. +60123456789"
                        autocomplete="tel"
                        inputmode="tel"
                        placeholder="e.g., +60123456789"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm">
                  <p class="mt-1 text-xs text-gray-500">
                    Include country code (e.g., +60 for Malaysia)
                  </p>
                </div>
              </div>

              {{-- Title Field --}}
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                  Wishlist Title <span class="text-red-500">*</span>
                </label>
                <input id="title" type="text" name="title" value="{{ old('title') }}"
                  required placeholder="Brief description of your wishlist item"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm">
              </div>

              {{-- Description Field --}}
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700">
                    Description <span class="text-red-500">*</span>
                </label>
                <p class="mb-2 text-xs text-gray-500">
                  Be concise and to the point
                </p>
                <textarea id="description" name="description" rows="10"
                  placeholder="Provide more details about your wishlist item..."
                  maxlength="700"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 text-sm resize-y">{{ old('description', "Describe your wishlist item in detail...\n\nWhy do you need this feature?\n- \n\nHow would it benefit the project?\n- ") }}</textarea>
                <p class="mt-1 text-xs text-gray-500">
                  <span id="char-count">0</span> / 700 characters
                </p>
              </div>

              {{-- Attachments Field --}}
              <div>
                <label for="attachments" class="block text-sm font-medium text-gray-700">
                  Attachments
                </label>
                <p class="mb-2 text-xs text-gray-500">
                  Screenshots, mockups, or reference materials (optional)
                </p>
                <div id="upload-box" class="mt-1 flex items-center justify-center px-6 py-8 border-2 border-gray-300 border-dashed rounded-md hover:border-primary-400 transition-colors">
                  <div class="space-y-1 text-center w-full flex flex-col items-center">
                    <x-heroicon-o-arrow-up-tray class="mx-auto h-8 w-8 text-gray-400" />
                    <div class="flex gap-2 text-sm text-gray-600">
                      <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                        <span>Upload files</span>
                        <input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.mp4" class="sr-only" required>
                      </label>
                      <p>or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">
                      JPG, JPEG, PNG, WebP, PDF, MP4 (max 8MB each)
                    </p>
                  </div>
                </div>
                <div id="file-list" class="mt-3 space-y-2"></div>
              </div>

              {{-- Search Confirmation --}}
              <div class="bg-primary-50 border border-primary-200 rounded-md p-4">
                <div class="flex items-start space-x-3">
                  <div class="flex-shrink-0">
                    <input id="search_confirmation" name="search_confirmation" type="checkbox" value="1"
                           @click="showTrackingTokensModal = true; fetchTrackingTokens()"
                           class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded cursor-pointer">
                  </div>
                  <div class="flex-1 min-w-0">
                    <label for="search_confirmation" class="text-xs text-gray-700">
                      <span class="font-medium">I have done a search for similar wishlist items</span>
                      <span class="text-primary-600 hover:text-primary-800 underline cursor-pointer ml-0.5"
                            @click="showTrackingTokensModal = true; fetchTrackingTokens()">
                        (search for similar suggestion)
                      </span>
                    </label>
                  </div>
                </div>
              </div>

              {{-- Submit Button --}}
              <div>
                <x-loading-submit-button :label="'Submit Wishlist'" :sr="'Submit Wishlist'"
                  class="w-full py-2 px-4 text-sm bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500" />
              </div>

            </form>

            {{-- Footer --}}
            <div class="text-center">
              <span class="block mb-4 text-xs text-gray-500">Powered by:</span>
              <img src="{{ optimized_asset('logos/logo-dark-vertical.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-auto">
            </div>

      </div>
    </div>

    {{-- Floating Action Button --}}
    <button type="button"
            @click="showTrackingTokensModal = true; fetchTrackingTokens()"
            class="fixed top-6 right-6 z-40 inline-flex items-center justify-center w-12 h-12 bg-primary-500 hover:bg-primary-600 text-primary-900 rounded-full shadow-lg hover:shadow-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            title="View All Submitted Wishlists">
      <x-heroicon-m-inbox class="h-6 w-6" />

      {{-- Wishlists Count Badge --}}
      <span x-show="wishlistsCount > 0"
            x-text="wishlistsCount"
            class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold leading-none text-white bg-danger-500 rounded-full min-w-[18px] h-[18px]">
      </span>

    </button>

  </div>

  {{-- Submitted Wishlists Slide Panel --}}
  <div x-show="showTrackingTokensModal"
       x-cloak
       @keydown.escape.window="showTrackingTokensModal = false"
       class="fixed inset-0 z-50"
       style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"
         x-show="showTrackingTokensModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="showTrackingTokensModal = false"></div>

    {{-- Slide Panel --}}
    <div class="absolute right-0 top-0 bottom-0 w-full max-w-md bg-white shadow-2xl ring-1 ring-black/5 transform transition-transform duration-300 ease-out flex flex-col"
         x-show="showTrackingTokensModal"
         x-transition:enter="translate-x-full"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="translate-x-full"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

      {{-- Header --}}
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center space-x-3">
          <div class="flex-shrink-0">
            <x-heroicon-o-inbox class="h-6 w-6 text-primary-500" />
          </div>
          <div>
            <h2 class="text-base font-semibold leading-6 text-gray-900">
              Submitted Wishlists
            </h2>
            <p class="text-xs text-gray-600 mt-1">
              <span x-text="trackingTokensData?.project?.title || 'Loading...'" class="font-medium"></span>
              <span class="text-xs text-primary-500 ml-1" x-text="'(' + (trackingTokensData?.project?.code || '') + ')'"></span>
            </p>
          </div>
        </div>
        <button type="button"
                @click="showTrackingTokensModal = false"
                class="text-gray-400 hover:text-gray-600 transition-colors duration-200 p-1 rounded-md hover:bg-gray-50 flex-shrink-0">
          <x-heroicon-o-x-mark class="h-5 w-5" />
        </button>
      </div>

      {{-- Content --}}
      <div class="flex-1 overflow-y-auto min-h-0">

        {{-- Loading State --}}
        <div x-show="loadingTokens" class="flex flex-col items-center justify-center py-12 px-6 space-y-4">

          {{-- Loading Spinner --}}
          <div class="relative">
            <x-icons.custom-icon name="refresh" class="w-8 h-8" color="text-primary-500" />
          </div>

          {{-- Loading Text --}}
          <div class="text-center space-y-1">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ __('auth.loading') }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              Loading submitted wishlists...
            </p>
          </div>

        </div>

        {{-- Tokens List --}}
        <div x-show="!loadingTokens && trackingTokensData?.tracking_tokens?.length > 0" class="divide-y divide-gray-200">
          <template x-for="token in (trackingTokensData?.tracking_tokens || [])" :key="token.token">
            <div class="group relative px-6 py-8 hover:bg-gray-50 transition-colors">
              <div class="flex-1 min-w-0">
                  <div class="flex items-center space-x-2 mb-2">

                    {{-- Wishlist ID --}}
                    <code class="text-xs font-mono text-gray-600 bg-gray-100 px-2 py-0.5 rounded-full" x-text="token.token"></code>

                    {{-- Status Badge --}}
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize bg-primary-50 text-primary-700"
                          x-text="token.status.replace('_', ' ')"></span>
                  </div>

                  {{-- Created At --}}
                  <div class="flex items-center mb-4">
                    <span class="text-[10px] text-gray-500" x-text="token.created_at"></span>
                  </div>

                  {{-- Title --}}
                  <div class="flex items-center">
                    <p class="text-sm font-medium text-gray-900 w-5/6 pr-2" x-text="token.title.length > 100 ? token.title.substring(0, 100) + '...' : token.title"></p>

                    {{-- View Button --}}
                    <a :href="token.url"
                       target="_blank"
                       class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-medium text-primary-900 bg-primary-500 hover:bg-primary-600 rounded-md shadow-sm transition-all duration-200 opacity-0 group-hover:opacity-100 w-1/6 text-center">
                      View →
                    </a>

                  </div>

                </div>
            </div>
          </template>
        </div>

        {{-- Empty State --}}
        <div x-show="!loadingTokens && (!trackingTokensData?.tracking_tokens || trackingTokensData.tracking_tokens.length === 0)" class="flex flex-col items-center justify-center py-12 px-6 text-center">
          <x-heroicon-o-inbox class="h-12 w-12 text-gray-400 mb-4" />
          <h3 class="text-sm font-medium text-gray-900 mb-1">No submitted wishlists found</h3>
          <p class="text-sm text-gray-500">No wishlists have been submitted for this project yet.</p>
        </div>

        {{-- Error State --}}
        <div x-show="tokensError" class="flex flex-col items-center justify-center py-12 px-6 text-center">
          <svg class="h-12 w-12 text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
          <h3 class="text-sm font-medium text-gray-900 mb-1">Error loading submitted wishlists</h3>
          <p class="text-sm text-red-500" x-text="tokensError"></p>
        </div>
      </div>
    </div>
  </div>

  {{-- Character Counter Script --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const descriptionField = document.getElementById('description');
        const charCount = document.getElementById('char-count');

        function updateCharCount() {
            const length = descriptionField.value.length;
            charCount.textContent = length;
            if (length > 630) {
                charCount.classList.add('text-yellow-600');
                charCount.classList.remove('text-gray-500');
            } else {
                charCount.classList.remove('text-yellow-600');
                charCount.classList.add('text-gray-500');
            }
        }

        descriptionField.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count

        // File upload handling with AJAX
        const fileInput = document.getElementById('attachments');
        const fileList = document.getElementById('file-list');
        const maxFiles = 5;
        const maxSize = 8 * 1024 * 1024; // 8MB in bytes
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'application/pdf', 'video/mp4'];
        let uploadedTempFiles = [];

        // Check for existing temp files from previous failed submission
        const existingTempIds = @json(session('temp_file_ids', old('temp_file_ids', [])));
        if (existingTempIds.length > 0) {
          // Load existing temp files
          fetchExistingTempFiles(existingTempIds);
        }

        function formatFileSize(bytes) {
          if (bytes === 0) return '0 Bytes';
          const k = 1024;
          const sizes = ['Bytes', 'KB', 'MB'];
          const i = Math.floor(Math.log(bytes) / Math.log(k));
          return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        async function fetchExistingTempFiles(tempIds) {
          try {
            const response = await fetch('/api/wishlist-trk/temp-files');
            const result = await response.json();

            if (result.success) {
              // Filter temp files to only include the ones that were previously uploaded
              uploadedTempFiles = result.temp_files.filter(file => tempIds.includes(file.temp_id));
              updateFileList();
            } else {
              console.error('Failed to load temp files:', result.error);
              // Fallback: create placeholder entries
              uploadedTempFiles = tempIds.map(id => ({ temp_id: id, original_name: 'File', size: 0 }));
              updateFileList();
            }
          } catch (error) {
            console.error('Error loading existing temp files:', error);
            // Fallback: create placeholder entries
            uploadedTempFiles = tempIds.map(id => ({ temp_id: id, original_name: 'File', size: 0 }));
            updateFileList();
          }
        }

        function updateFileList() {
          fileList.innerHTML = '';
          uploadedTempFiles.forEach((tempFile, index) => {
              const fileItem = document.createElement('div');
              fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-md';
              const fileUrl = tempFile.path ? `/storage/${tempFile.path}` : '';
              const isImage = tempFile.mime_type && tempFile.mime_type.startsWith('image/');
              fileItem.innerHTML = `
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                  ${fileUrl ? `<a href="${fileUrl}" target="_blank" class="text-primary-600 hover:text-primary-800 cursor-pointer flex-shrink-0" title="Preview in new tab">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                    </svg>
                  </a>` : `<svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                  </svg>`}
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${tempFile.original_name || 'Uploading...'}</p>
                    <p class="text-xs text-gray-500">${tempFile.size ? formatFileSize(tempFile.size) : 'Processing...'}</p>
                  </div>
                </div>
                <div class="flex items-center ml-3">
                  <button type="button" onclick="removeTempFile('${tempFile.temp_id}')" class="text-red-600 hover:text-red-800" title="Remove file">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
                `;
                fileList.appendChild(fileItem);
            });

            // Update hidden input with temp file IDs
            updateTempFileIdsInput();

            // Show/hide upload box based on file count
            const uploadBox = document.getElementById('upload-box');
            if (uploadedTempFiles.length >= maxFiles) {
              uploadBox.style.display = 'none';
            } else {
              uploadBox.style.display = 'flex';
            }
        }

        function updateTempFileIdsInput() {
          // Remove existing hidden inputs
          document.querySelectorAll('input[name="temp_file_ids[]"]').forEach(el => el.remove());

          // Add new hidden inputs for each temp file
          uploadedTempFiles.forEach(tempFile => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'temp_file_ids[]';
            hiddenInput.value = tempFile.temp_id;
            document.querySelector('form').appendChild(hiddenInput);
          });
        }

        async function uploadFile(file) {
          const formData = new FormData();
          formData.append('file', file);
          formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

          try {
            const response = await fetch('/api/wishlist-trk/upload-temp-file', {
              method: 'POST',
              body: formData
            });

            const result = await response.json();

            if (result.success) {
              return result.temp_file;
            } else {
              throw new Error(result.error || 'Upload failed');
            }
          } catch (error) {
            console.error('Upload error:', error);
            throw error;
          }
        }

        window.removeTempFile = async function(tempId) {
            try {
              // Remove from temp array
              uploadedTempFiles = uploadedTempFiles.filter(file => file.temp_id !== tempId);
              updateFileList();
            } catch (error) {
              console.error('Error removing temp file:', error);
            }
        };

        fileInput.addEventListener('change', async function(e) {
          const files = Array.from(e.target.files);

          for (const file of files) {
            // Check file count
            if (uploadedTempFiles.length >= maxFiles) {
              alert(`You can only upload a maximum of ${maxFiles} files.`);
              break;
            }

            // Check file size
            if (file.size > maxSize) {
              alert(`File "${file.name}" exceeds the maximum size of 8MB.`);
              continue;
            }

            // Check file type
            if (!allowedTypes.includes(file.type)) {
              alert(`File "${file.name}" is not an allowed type. Only JPG, JPEG, PNG, WebP, PDF, and MP4 files are allowed.`);
              continue;
            }

            try {
              // Upload file immediately
              const tempFile = await uploadFile(file);
              uploadedTempFiles.push(tempFile);
              updateFileList();
            } catch (error) {
              alert(`Failed to upload "${file.name}": ${error.message}`);
            }
          }

          // Clear the file input
          fileInput.value = '';
        });

        // Handle pasted images anywhere inside the form
        const formElement = document.querySelector('form');

        if (formElement) {
        formElement.addEventListener('paste', async function(e) {
          if (!e.clipboardData || !e.clipboardData.files || e.clipboardData.files.length === 0) {
            return;
          }

          const files = Array.from(e.clipboardData.files).filter(file => file.type.startsWith('image/'));

          if (files.length === 0) {
            return;
          }

          e.preventDefault();

          for (const file of files) {
            // Check file count
            if (uploadedTempFiles.length >= maxFiles) {
              alert(`You can only upload a maximum of ${maxFiles} files.`);
              break;
            }

            // Check file size
            if (file.size > maxSize) {
              alert(`File "${file.name}" exceeds the maximum size of 8MB.`);
              continue;
            }

            // Check file type
            if (!allowedTypes.includes(file.type)) {
              alert(`File "${file.name}" is not an allowed type. Only JPG, JPEG, PNG, WebP, PDF, and MP4 files are allowed.`);
              continue;
            }

            try {
              const tempFile = await uploadFile(file);
              uploadedTempFiles.push(tempFile);
              updateFileList();
            } catch (error) {
              alert(`Failed to upload "${file.name}": ${error.message}`);
            }
          }
        });
        }

        // Drag and drop
        const dropZone = fileInput.closest('.border-dashed');
        if (dropZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
          dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
          dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-primary-500', 'bg-primary-50');
          }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
          dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-primary-500', 'bg-primary-50');
          }, false);
        });

          dropZone.addEventListener('drop', async function(e) {
            const files = Array.from(e.dataTransfer.files);

            for (const file of files) {
              // Check file count
              if (uploadedTempFiles.length >= maxFiles) {
                alert(`You can only upload a maximum of ${maxFiles} files.`);
                break;
              }

              // Check file size
              if (file.size > maxSize) {
                alert(`File "${file.name}" exceeds the maximum size of 8MB.`);
                continue;
              }

              // Check file type
              if (!allowedTypes.includes(file.type)) {
                alert(`File "${file.name}" is not an allowed type. Only JPG, JPEG, PNG, WebP, PDF, and MP4 files are allowed.`);
                continue;
              }

              try {
                // Upload file immediately
                const tempFile = await uploadFile(file);
                uploadedTempFiles.push(tempFile);
                updateFileList();
              } catch (error) {
                alert(`Failed to upload "${file.name}": ${error.message}`);
              }
            }
          });
        }

        // Copy tracking link functionality
        const copyTrackingLinkBtn = document.getElementById('copy-tracking-link');
        const trackingUrlInput = document.getElementById('tracking-url');
        const copySuccessMessage = document.getElementById('copy-success');

        if (copyTrackingLinkBtn && trackingUrlInput) {
          copyTrackingLinkBtn.addEventListener('click', function() {
            trackingUrlInput.select();
            trackingUrlInput.setSelectionRange(0, 99999); // For mobile devices

            try {
              navigator.clipboard.writeText(trackingUrlInput.value).then(function() {
                // Show success message
                if (copySuccessMessage) {
                  copySuccessMessage.classList.remove('hidden');
                  setTimeout(function() {
                    copySuccessMessage.classList.add('hidden');
                  }, 3000);
                }
              }).catch(function(err) {
                // Fallback for older browsers
                document.execCommand('copy');
                if (copySuccessMessage) {
                  copySuccessMessage.classList.remove('hidden');
                  setTimeout(function() {
                    copySuccessMessage.classList.add('hidden');
                  }, 3000);
                }
              });
            } catch (err) {
              // Fallback for older browsers
              document.execCommand('copy');
              if (copySuccessMessage) {
                copySuccessMessage.classList.remove('hidden');
                setTimeout(function() {
                  copySuccessMessage.classList.add('hidden');
                }, 3000);
              }
            }
          });
        }
    });
  </script>
</body>
</html>
