<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('auth.reset_password') }} - {{ config('app.name') }}</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">

    @vite(['resources/css/app.css'])
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full antialiased font-sans">
    {{-- Loading Transition --}}
    <x-auth-loading />
    
    <div class="auth-frame flex h-screen overflow-hidden">
        {{-- Left Section (70%) - Hero Section --}}
        <x-auth-hero />

        {{-- Right Section (30%) - Reset Password Form --}}
        <div class="w-full lg:w-[30%] flex flex-col bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 custom-scrollbar overflow-y-auto">
            {{-- Header Section --}}
            <div class="flex-shrink-0 p-6 pb-4">
                {{-- Theme Toggle Buttons --}}
                <x-theme-toggle />
            </div>

            {{-- Main Content Section --}}
            <div class="flex-1 flex items-center justify-center px-8">
                <div id="auth-form-root" class="w-full max-w-md">

                    {{-- Logo Overlay --}}
                    <div class="flex justify-center mb-6">
                        <img id="resetPasswordLogo"
                            src="{{ asset('logos/logo-dark.png') }}"
                            alt="{{ config('app.name') }} Logo"
                            class="h-32 w-auto transition-all duration-300"
                            loading="eager"
                            draggable="false">
                    </div>
                    
                    {{-- Reset Password Header --}}
                    <header class="mb-20">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white text-center">
                            {{ __('auth.reset_password')}}
                        </h1>
                    </header>

                    {{-- Reset Password Form --}}
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        {{-- Status Messages --}}
                        @if (session('status'))
                            <div class="mb-4 text-sm text-primary-500 font-medium text-center">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{-- Error Messages --}}
                        @if ($errors->any())
                            <div class="mb-4 text-sm text-red-600 dark:text-red-400">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Email Field --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('auth.email') }}
                            </label>
                            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
                                class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 text-sm cursor-not-allowed">
                        </div>

                        {{-- New Password Field --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('auth.new_password') }}
                            </label>
                            <div class="relative">
                                <input id="password" type="password" name="password" required autofocus
                                    class="w-full p-3 pr-12 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-1 focus:outline-primary-500 focus:ring-1 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                                <button type="button" onclick="togglePassword('password')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <x-heroicon-o-eye-slash id="password-eye-slash" class="h-5 w-5" />
                                    <x-heroicon-o-eye id="password-eye" class="h-5 w-5 hidden" />
                                </button>
                            </div>
                        </div>

                        {{-- Confirm Password Field --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('auth.confirm_new_password') }}
                            </label>
                            <div class="relative">
                                <input id="password_confirmation" type="password" name="password_confirmation" required
                                    class="w-full p-3 pr-12 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-1 focus:outline-primary-500 focus:ring-1 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                                <button type="button" onclick="togglePassword('password_confirmation')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <x-heroicon-o-eye-slash id="password_confirmation-eye-slash" class="h-5 w-5" />
                                    <x-heroicon-o-eye id="password_confirmation-eye" class="h-5 w-5 hidden" />
                                </button>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <x-loading-submit-button :label="__('auth.send_reset_link')" :sr="__('auth.send_reset_link')" />

                        {{-- Navigation Links --}}
                        <div class="text-center space-y-2">
                            <div>
                                <a href="{{ route('login') }}"
                                   class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-900 dark:hover:text-primary-400 hover:underline transition-colors duration-200"
                                   aria-label="Back to login">
                                    {{ __('auth.back_to_login') }}
                                </a>
                            </div>
                            <div>
                                <a href="{{ route('password.request') }}"
                                   class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-900 dark:hover:text-primary-400 hover:underline transition-colors duration-200"
                                   aria-label="Back to forgot password">
                                    {{ __('auth.back_to_forgot_password') }}
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Footer Section --}}
            <div class="flex-shrink-0 p-6 pt-4">
                {{-- Language Switcher --}}
                <x-language-switcher />
            </div>
        </div>
    </div>

    {{-- Hero Slider Translation Data --}}
    <script>
        // Set hero slider language data
        window.heroSliderLang = {
            title1: @json(__('heroslider.title1')),
            description1: @json(__('heroslider.description1')),
            title2: @json(__('heroslider.title2')),
            description2: @json(__('heroslider.description2')),
            title3: @json(__('heroslider.title3')),
            description3: @json(__('heroslider.description3')),
            title4: @json(__('heroslider.title4')),
            description4: @json(__('heroslider.description4')),
            title5: @json(__('heroslider.title5')),
            description5: @json(__('heroslider.description5')),
            title6: @json(__('heroslider.title6')),
            description6: @json(__('heroslider.description6'))
        };
    </script>

    {{-- Password Toggle Function --}}
    <script>
        function togglePassword(id) {
            const input = document.getElementById(id);
            const eyeIcon = document.getElementById(id + '-eye');
            const eyeOffIcon = document.getElementById(id + '-eye-slash');

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';

            eyeIcon.classList.toggle('hidden', !isHidden);
            eyeOffIcon.classList.toggle('hidden', isHidden);
        }
    </script>

    {{-- Optimized JavaScript Files --}}
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    <script src="{{ asset('js/hero-slider.js') }}"></script>
    <script src="{{ asset('js/auth-partial-nav.js') }}"></script>
</body>
</html>