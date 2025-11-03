<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
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
<body class="h-full antialiased font-sans bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            {{-- Header --}}
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Submit an Issue / Bug
                </h1>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Project: <span class="font-semibold">{{ $project->title }}</span>
                </p>
            </div>

            {{-- Success Message --}}
            @if (session('success'))
                <div class="rounded-md bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Form --}}
            <form method="POST" action="{{ route('issue-tracker.store') }}" enctype="multipart/form-data" class="mt-8 space-y-6 bg-white dark:bg-gray-800 shadow-lg rounded-lg p-6">
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
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>

                {{-- Email Field --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Your Email <span class="text-red-500">*</span>
                    </label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        required autocomplete="email"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>

                {{-- Title Field --}}
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Issue Title <span class="text-red-500">*</span>
                    </label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}"
                        required placeholder="Brief description of the issue"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white">
                </div>

                {{-- Description Field --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                        <span class="text-xs text-gray-500 dark:text-gray-400">(Optional, max 500 characters)</span>
                    </label>
                    <textarea id="description" name="description" rows="4"
                        placeholder="Provide more details about the issue..."
                        maxlength="500"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:text-white resize-y">{{ old('description') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        <span id="char-count">0</span> / 500 characters
                    </p>
                </div>

                {{-- Attachments Field --}}
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Attachments
                        <span class="text-xs text-gray-500 dark:text-gray-400">(Optional, max 5 files, 20MB each)</span>
                    </label>
                    <div class="mt-1 flex items-center justify-center px-6 py-8 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-md hover:border-primary-400 dark:hover:border-primary-600 transition-colors">
                        <div class="space-y-1 text-center w-full flex flex-col items-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex gap-2 text-sm text-gray-600 dark:text-gray-400">
                                <label for="attachments" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-primary-600 dark:text-primary-400 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Upload files</span>
                                    <input id="attachments" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.mp4" class="sr-only">
                                </label>
                                <p>or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                JPG, JPEG, PNG, PDF, MP4 up to 20MB each
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
            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>Powered by {{ config('app.name') }}</p>
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
                if (length > 450) {
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
        });
    </script>
</body>
</html>

