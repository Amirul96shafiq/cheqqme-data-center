<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="height: 100%;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Submit Issue - {{ $project->title }} - {{ config('app.name') }}</title>

  {{-- Favicon --}}
  <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

  @vite(['resources/css/app.css'])
</head>
<body class="antialiased font-sans bg-auto bg-no-repeat m-0 p-0" style="height: 100vh; margin: 0; padding: 0; background-image: url('{{ asset('images/issue-tracker-bg.png') }}'); background-position: top center; display: flex; flex-direction: column;">
    
  {{-- Content area --}}
  <div style="flex: 1; overflow-y: auto; min-height: 100vh;">
    {{-- Top spacer: 20% of viewport height --}}
    <div style="height: 20vh; flex-shrink: 0; min-height: 20vh;"></div>
    <div class="flex items-center justify-center min-h-full py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full space-y-8">

        {{-- Header --}}
        <div class="text-center">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            SUBMIT AN ISSUE / BUG
          </h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Project: <span class="font-semibold text-primary-500">{{ $project->title }}</span>
          </p>
        </div>

                {{-- Success Message --}}
        @if (session('success'))
          <div class="rounded-md bg-teal-50 dark:bg-teal-900/20 p-4 border border-teal-200 dark:border-teal-800 mb-6">
            <div class="flex flex-col items-center">
              <div class="mb-3">
                <x-heroicon-s-check-circle class="h-20 w-20 text-teal-400" />
              </div>
              <div class="w-full">
                <p class="text-sm font-medium text-teal-800 dark:text-teal-200 mb-3 text-center">
                  {{ session('success') }}
                </p>

                @if (session('tracking_token'))
                  <div class="mt-3 space-y-3">
                    <div>
                      <p class="text-xs font-medium text-teal-700 dark:text-teal-300 mb-1">Tracking Code:</p>
                      <div class="flex items-center space-x-2">
                        <code class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-700 rounded-md text-sm font-mono text-teal-900 dark:text-teal-100">
                          {{ session('tracking_token') }}
                        </code>
                      </div>
                    </div>
                    <div>
                      <p class="text-xs font-medium text-teal-700 dark:text-teal-300 mb-1">View Status:</p>
                      <div class="flex items-center space-x-2">
                        <input type="text"
                               id="tracking-url"
                               value="{{ route('issue-tracker.status', ['token' => session('tracking_token')]) }}"
                               readonly
                               class="flex-1 px-3 py-2 bg-white dark:bg-gray-800 border border-teal-200 dark:border-teal-700 rounded-md text-sm text-teal-900 dark:text-teal-100 focus:outline-none focus:ring-2 focus:ring-teal-500">
                        <button type="button"
                                id="copy-tracking-link"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-900 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                          <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                          </svg>
                          Copy Link
                        </button>
                      </div>
                      <p id="copy-success" class="mt-2 text-xs text-teal-600 dark:text-teal-400 hidden">Link copied to clipboard!</p>
                    </div>
                  </div>
                @endif
              </div>
            </div>
          </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('issue-tracker.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
          @csrf

            {{-- Hidden Project ID --}}
            <input type="hidden" name="project_id" value="{{ $project->id }}">

            {{-- Error Messages --}}
            @if ($errors->any())
              <div class="rounded-md bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                      Please correct the following errors:
                    </h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
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
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Your Name <span class="text-red-500">*</span>
                </label>
                <input id="name" type="text" name="name" value="{{ old('name') }}"
                  required autofocus
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
              </div>

              {{-- Email Field --}}
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Your Email <span class="text-red-500">*</span>
                </label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                      required autocomplete="email"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
              </div>

              {{-- Title Field --}}
              <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Issue Title <span class="text-red-500">*</span>
                </label>
                <input id="title" type="text" name="title" value="{{ old('title') }}"
                  required placeholder="Brief description of the issue"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm">
              </div>

              {{-- Description Field --}}
              <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Description <span class="text-red-500">*</span>
                </label>
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                  Be concise and to the point
                </p>
                <textarea id="description" name="description" rows="10"
                  placeholder="Provide more details about the issue..."
                  maxlength="700"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white text-sm resize-y">{{ old('description', "Steps to Reproduce:-\n1- \n\nExpected Result\n- \n\nActual Result\n- ") }}
                </textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  <span id="char-count">0</span> / 700 characters
                </p>
              </div>

              {{-- Attachments Field --}}
              <div>
                <label for="attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                  Attachments <span class="text-red-500">*</span>
                </label>
                <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">
                  Proof of issues or bugs - screenshots, videos, etc.
                </p>
                <div class="mt-1 flex items-center justify-center px-6 py-8 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-md hover:border-primary-400 dark:hover:border-primary-600 transition-colors">
                  <div class="space-y-1 text-center w-full flex flex-col items-center">
                    <x-heroicon-o-arrow-up-tray class="mx-auto h-8 w-8 text-gray-400" />
                    <div class="flex gap-2 text-sm text-gray-600 dark:text-gray-400">
                      <label for="attachments" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                        <span>Upload files</span>
                        <input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.mp4" class="sr-only" required>
                      </label>
                      <p>or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                      JPG, JPEG, PNG, PDF, MP4 (max 20MB each)
                    </p>
                  </div>
                </div>
                <div id="file-list" class="mt-3 space-y-2"></div>
              </div>

              {{-- Submit Button --}}
              <div>
                <button type="submit"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-primary-900 bg-primary-500 hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                  Submit Issue
                </button>
              </div>

            </form>

            {{-- Footer --}}
            <div class="text-center">
              <span class="block mb-4 text-xs text-gray-500 dark:text-gray-400">Powered by:</span>
              <img src="{{ asset('logos/logo-dark-vertical.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-auto dark:hidden">
              <img src="{{ asset('logos/logo-dark-vertical.png') }}" alt="{{ config('app.name') }}" class="mx-auto h-16 w-auto hidden dark:inline-block">
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
                charCount.classList.add('text-yellow-600', 'dark:text-yellow-400');
                charCount.classList.remove('text-gray-500', 'dark:text-gray-400');
            } else {
                charCount.classList.remove('text-yellow-600', 'dark:text-yellow-400');
                charCount.classList.add('text-gray-500', 'dark:text-gray-400');
            }
        }

        descriptionField.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count

        // File upload handling
        const fileInput = document.getElementById('attachments');
        const fileList = document.getElementById('file-list');
        const maxFiles = 5;
        const maxSize = 20 * 1024 * 1024; // 20MB in bytes
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'video/mp4'];
        let selectedFiles = [];

        function formatFileSize(bytes) {
          if (bytes === 0) return '0 Bytes';
          const k = 1024;
          const sizes = ['Bytes', 'KB', 'MB'];
          const i = Math.floor(Math.log(bytes) / Math.log(k));
          return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }

        function updateFileList() {
          fileList.innerHTML = '';
          selectedFiles.forEach((file, index) => {
              const fileItem = document.createElement('div');
              fileItem.className = 'flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md';
              fileItem.innerHTML = `
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                  <svg class="h-5 w-5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">${file.name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">${formatFileSize(file.size)}</p>
                  </div>
                </div>
                  <button type="button" onclick="removeFile(${index})" class="ml-3 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                  <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
                `;
                fileList.appendChild(fileItem);
            });

            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
        }

        window.removeFile = function(index) {
            selectedFiles.splice(index, 1);
            updateFileList();
        };

        fileInput.addEventListener('change', function(e) {
          const files = Array.from(e.target.files);
          let validFiles = [];

          files.forEach(file => {
            // Check file count
            if (selectedFiles.length + validFiles.length >= maxFiles) {
              alert(`You can only upload a maximum of ${maxFiles} files.`);
              return;
              }

            // Check file size
            if (file.size > maxSize) {
              alert(`File "${file.name}" exceeds the maximum size of 20MB.`);
              return;
            }

            // Check file type
            if (!allowedTypes.includes(file.type)) {
              alert(`File "${file.name}" is not an allowed type. Only JPG, JPEG, PNG, PDF, and MP4 files are allowed.`);
              return;
            }

            validFiles.push(file);
          });

          selectedFiles = [...selectedFiles, ...validFiles];
          updateFileList();
        });

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
            dropZone.classList.add('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
          }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
          dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-primary-500', 'bg-primary-50', 'dark:bg-primary-900/20');
          }, false);
        });

          dropZone.addEventListener('drop', function(e) {
            const files = Array.from(e.dataTransfer.files);
            const dataTransfer = new DataTransfer();
            files.forEach(file => dataTransfer.items.add(file));
            fileInput.files = dataTransfer.files;
            fileInput.dispatchEvent(new Event('change', { bubbles: true }));
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

