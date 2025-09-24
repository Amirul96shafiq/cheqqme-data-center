<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ __('login.title')}} - {{ config('app.name') }}</title>

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

        {{-- Right Section (30%) - Login Form --}}
        <div class="w-full lg:w-[30%] flex flex-col bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 custom-scrollbar overflow-y-auto">
            {{-- Header Section --}}
            <div class="flex-shrink-0 p-6 pb-4">
                {{-- Theme Toggle Buttons --}}
                <x-theme-toggle />
            </div>

            {{-- Main Content Section --}}
            <div class="flex-1 flex items-center justify-center px-8">
                <div id="auth-form-root" class="w-full max-w-md login-form-container">

                    {{-- Logo Overlay --}}
                    <div class="flex justify-center mb-20">
                        <img id="loginLogo"
                            src="{{ asset('logos/logo-dark.png') }}"
                            alt="{{ config('app.name') }} Logo"
                            class="h-32 w-auto transition-all duration-300"
                            loading="eager">
                    </div>
                    
                    {{-- Sign In Header --}}
                    {{-- <header class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-800 dark:text-white text-center">
                            {{ __('login.title')}}
                        </h1>
                    </header> --}}

                    {{-- Login Form --}}
                    <form method="POST" action="{{ route('admin.login') }}" class="space-y-6">
                        @csrf

                        {{-- Email Field --}}
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('login.form.email') }}
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                required autofocus autocomplete="email"
                                class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Password Field --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('login.form.password') }}
                            </label>
                            <input id="password" type="password" name="password" required
                                autocomplete="password"
                                class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remember Me --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <label for="remember" class="flex items-center cursor-pointer">
                                    <div class="relative" role="switch" aria-checked="{{ old('remember') ? 'true' : 'false' }}">
                                        <input id="remember" 
                                            type="checkbox" 
                                            name="remember" 
                                            {{ old('remember') ? 'checked' : '' }}
                                            class="sr-only"
                                            aria-describedby="remember-description">
                                        <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full shadow-inner transition-colors duration-200 ease-in-out"></div>
                                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 ease-in-out"></div>
                                    </div>
                                    <span id="remember-description" class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {{ __('login.form.remember') }}
                                    </span>
                                </label>
                            </div>
                            
                            {{-- Forgot Password --}}
                            <div>
                                <a href="{{ route('password.request') }}"
                                class="text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-primary-900 dark:hover:text-primary-400 hover:underline transition-colors duration-200"
                                aria-label="Reset your password">
                                    {{ __('login.actions.forgotPassword') }}
                                </a>
                            </div>
                        </div>


                        {{-- Login Button --}}
                        <button type="submit" 
                                class="w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-primary-900 font-medium rounded-md shadow-sm transition-colors duration-200"
                                aria-describedby="login-button-description">
                            {{ __('login.actions.login') }}
                            <span id="login-button-description" class="sr-only">Sign in to your account</span>
                        </button>

                        {{-- Separator --}}
                        <div class="flex items-center justify-center my-4">
                            <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                            <span class="px-4 text-[10px] font-light text-gray-500 dark:text-gray-400">{{ __('login.form.or') }}</span>
                            <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                        </div>

                        {{-- Social Sign-in Buttons --}}
                        <div class="space-y-6">
                            {{-- Google Sign-in Button --}}
                            <x-google-signin-button />

                            {{-- Microsoft Sign-in Button --}}
                            <x-microsoft-signin-button />
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
    <script src="{{ asset('js/google-signin.js') }}"></script>
    <script src="{{ asset('js/remember-me-toggle.js') }}"></script>
    <script src="{{ asset('js/auth-partial-nav.js') }}"></script>
</body>
</html>