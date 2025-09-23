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
                <div class="flex justify-center">                    
                    <fieldset class="flex flex-row gap-2 p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 border border-gray-200/50 dark:border-gray-700/50">
                        <legend class="sr-only">Theme selection</legend>
                        {{-- Light Theme (Sun) --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.lightTheme')">
                            <button type="button" 
                                    class="theme-toggle-btn p-2 rounded-lg transition-colors" 
                                    data-theme="light" 
                                    aria-label="Enable light theme"
                                    title="Enable light theme">
                                <x-heroicon-m-sun class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </x-tooltip>

                        {{-- Dark Theme (Moon) --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.darkTheme')">
                            <button type="button" 
                                    class="theme-toggle-btn p-2 rounded-lg transition-colors" 
                                    data-theme="dark" 
                                    aria-label="Enable dark theme"
                                    title="Enable dark theme">
                                <x-heroicon-m-moon class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </x-tooltip>

                        {{-- System Theme (Desktop) --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.systemTheme')">
                            <button type="button" 
                                    class="theme-toggle-btn p-2 rounded-lg transition-colors" 
                                    data-theme="system" 
                                    aria-label="Enable system theme"
                                    title="Enable system theme">
                                <x-heroicon-m-computer-desktop class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </x-tooltip>
                    </fieldset>
                </div>
            </div>

            {{-- Main Content Section --}}
            <div class="flex-1 flex items-center justify-center px-8">
                <div class="w-full max-w-md">

                    {{-- Logo Overlay --}}
                    <div class="flex justify-center mb-20">
                        <img id="forgotPasswordLogo"
                            src="{{ asset('logos/logo-dark.png') }}"
                            alt="{{ config('app.name') }} Logo"
                            class="h-32 w-auto transition-all duration-300"
                            loading="eager">
                    </div>
                    
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
                                class="w-full p-3 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-primary-900 font-medium rounded-md shadow-sm transition-colors duration-200"
                                aria-describedby="submit-button-description">
                            {{ __('auth.send_link') }}
                            <span id="submit-button-description" class="sr-only">Send password reset link to your email</span>
                        </button>

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
                <div class="flex justify-center">
                    <div x-data="{ open: false }" class="relative">
                        <!-- Dropdown -->
                        <div x-show="open" @click.away="open = false"
                            role="menu"
                            aria-orientation="vertical"
                            class="absolute bottom-full mb-2 w-max rounded-md shadow-lg bg-white dark:bg-neutral-900 ring-1 ring-gray-950/5 dark:ring-white/10 z-10"
                            x-cloak
                            style="left: 50%; transform: translateX(-50%);">
                            <div class="py-2 text-sm text-gray-700 dark:text-gray-100">
                                @if(app()->getLocale() !== 'en')
                                <form method="POST" action="{{ route('locale.set') }}" class="inline-block w-full">
                                    @csrf
                                    <input type="hidden" name="locale" value="en">
                                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                    <button type="submit"
                                            role="menuitem"
                                            class="block w-full text-center font-semibold px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800">
                                    <span class="text-center p-1.5 mr-2 text-primary-500 bg-primary-100/25 dark:bg-primary-100/5 rounded-lg">EN</span>{{ __('auth.english') }}
                                    </button>
                                </form>
                                @endif
                                @if(app()->getLocale() !== 'ms')
                                <form method="POST" action="{{ route('locale.set') }}" class="inline-block w-full">
                                    @csrf
                                    <input type="hidden" name="locale" value="ms">
                                    <input type="hidden" name="redirect" value="{{ request()->fullUrl() }}">
                                    <button type="submit"
                                            role="menuitem"
                                            class="block w-full text-center font-semibold px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800">
                                        <span class="text-center p-1.5 mr-2 text-primary-500 bg-primary-100/25 dark:bg-primary-100/5 rounded-lg">MS</span>{{ __('auth.malay') }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Toggle -->
                        <button @click="open = !open" 
                                type="button"
                                aria-label="Change language"
                                aria-expanded="false"
                                aria-haspopup="true"
                                class="flex items-center justify-center w-10 h-10 language-switch-trigger text-primary-600 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 hover:border-gray-300/50 dark:hover:border-gray-600/50 rounded-lg transition font-semibold">
                          {{ strtoupper(app()->getLocale()) }}
                        </button>
                    </div>
                </div>
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
</body>
</html>