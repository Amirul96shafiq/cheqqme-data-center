<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('auth.forgot_password') }} - {{ config('app.name') }}</title>

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

        {{-- Right Section (30%) - Forgot Password Form --}}
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
                        <img id="forgotPasswordLogo"
                            src="{{ asset('logos/logo-dark.png') }}"
                            alt="{{ config('app.name') }} Logo"
                            class="h-32 w-auto transition-all duration-300"
                            loading="eager"
                            draggable="false">
                    </div>

                    {{-- Forgot Password Header --}}
                    <header class="mb-10">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white text-center">
                            {{ __('auth.forgot_password')}}
                        </h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center">
                            {{ __('auth.forgot_password_subtitle')}}
                        </h1>
                    </header>
                    
                    {{-- Forgot Password Form --}}
                    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                        @csrf

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
                                {{ __('auth.email_address') }}
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                required autofocus autocomplete="email"
                                class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-1 focus:outline-primary-500 focus:ring-1 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                        </div>

                        {{-- Submit Button --}}
                        <x-loading-submit-button :label="__('auth.send_link')" :sr="__('auth.send_link')" />

                        {{-- Back to Login Link --}}
                        <div class="text-center">
                            <a href="{{ route('login') }}"
                               class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-900 dark:hover:text-primary-400 hover:underline transition-colors duration-200"
                               aria-label="Back to login">
                                {{ __('auth.back_to_login') }}
                            </a>
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

    {{-- Optimized JavaScript Files --}}
    <script src="{{ asset('js/theme-toggle.js') }}"></script>
    <script src="{{ asset('js/hero-slider.js') }}"></script>
    <script src="{{ asset('js/auth-partial-nav.js') }}"></script>
</body>
</html>