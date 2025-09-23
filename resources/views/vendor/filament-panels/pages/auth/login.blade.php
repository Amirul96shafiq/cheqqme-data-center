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
    <div class="flex h-screen overflow-hidden">
        {{-- Left Section (70%) - Hero Section --}}
        <div class="relative w-[70%] hidden lg:flex flex-col justify-between overflow-hidden bg-gray-50 dark:bg-gray-900 p-6">
            {{-- Hero Section with Gradient Background --}}
            <div class="relative w-full h-full rounded-2xl overflow-hidden">
                {{-- Gradient Mesh Background - Light Mode --}}
                <div class="light:block dark:hidden">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-100 via-blue-100 to-indigo-100"></div>
                    <div class="absolute top-0 left-0 w-full h-full opacity-40">
                        <div class="absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-blue-300/30 to-purple-300/30 rounded-full blur-3xl animate-float-1"></div>
                        <div class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-l from-indigo-300/30 to-pink-300/30 rounded-full blur-3xl animate-float-2"></div>
                        <div class="absolute top-1/2 left-1/3 w-80 h-80 bg-gradient-to-br from-cyan-300/25 to-blue-300/25 rounded-full blur-3xl animate-float-3"></div>
                    </div>
                </div>
                
                {{-- Gradient Mesh Background - Dark Mode --}}
                <div class="hidden dark:block">
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-800 via-slate-800/30 to-indigo-800/20"></div>
                    <div class="absolute top-0 left-0 w-full h-full opacity-40">
                        <div class="absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-blue-500/30 to-purple-500/30 rounded-full blur-3xl animate-float-1"></div>
                        <div class="absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-l from-indigo-500/30 to-pink-500/30 rounded-full blur-3xl animate-float-2"></div>
                        <div class="absolute top-1/2 left-1/3 w-80 h-80 bg-gradient-to-br from-cyan-500/25 to-blue-500/25 rounded-full blur-3xl animate-float-3"></div>
                    </div>
                </div>
                
                {{-- Top Section: Content Container and Navigation Controls --}}
                <div class="relative z-10 flex flex-col w-full h-1/4 justify-end">
                    {{-- Content Container --}}
                    <div class="flex-1 flex flex-col justify-start p-12 w-3/4">
                        <div class="flex flex-col justify-start space-y-6 max-w-2xl">
                            {{-- Title --}}
                            <div>
                                <h1 id="heroTitle" class="text-2xl font-bold dark:text-white text-gray-600 -mb-4 transition-all duration-500 leading-tight">
                                    Loading...
                                </h1>
                            </div>

                            {{-- Description --}}
                            <div class="min-h-14">
                                <p id="heroDescription" class="text-md dark:text-white text-gray-600 transition-all duration-500 leading-relaxed">
                                    Loading...
                                </p>
                            </div>

                            {{-- Slider Navigation --}}
                            <nav class="flex items-center space-x-3 mt-8" id="sliderNav" aria-label="Slide navigation">
                                <button type="button" data-slide="0" aria-label="Go to slide 1" class="relative w-12 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                                    <div id="progressBar0" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-gray-200 rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                                </button>
                                <button type="button" data-slide="1" aria-label="Go to slide 2" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                                    <div id="progressBar1" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-gray-200 rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                                </button>
                                <button type="button" data-slide="2" aria-label="Go to slide 3" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                                    <div id="progressBar2" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-gray-200 rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                                </button>
                                <button type="button" data-slide="3" aria-label="Go to slide 4" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                                    <div id="progressBar3" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-white rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                                </button>
                                <button type="button" data-slide="4" aria-label="Go to slide 5" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                                    <div id="progressBar4" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-white rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                                </button>
                                <button type="button" data-slide="5" aria-label="Go to slide 6" class="relative w-4 h-1 dark:bg-gray-200 rounded-full transition-all duration-300 bg-primary-400 hover:bg-primary-400 overflow-hidden">
                                    <div id="progressBar5" class="absolute top-0 left-0 h-full bg-gray-400 dark:bg-white rounded-full transition-all duration-100 ease-linear" style="width: 0%"></div>
                                </button>
                            </nav>
                        </div>
                    </div>
                    
                    {{-- Navigation Controls (Bottom Right) --}}
                    <nav class="absolute bottom-12 right-12 w-1/4 z-20 flex justify-end" aria-label="Hero slider navigation">
                        <div class="flex items-center space-x-3">
                            {{-- Previous Button --}}
                            <button id="prevSlide" 
                                    type="button"
                                    aria-label="Previous slide"
                                    class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                                @svg('heroicon-m-arrow-left', 'w-5 h-5 text-primary-900 transition-colors')
                            </button>
                            
                            {{-- Pause/Play Button --}}
                            <button id="pausePlaySlide" 
                                    type="button"
                                    aria-label="Pause auto-slide"
                                    class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                                {{-- Play Icon (shown when paused) --}}
                                <div id="playIcon">
                                    @svg('heroicon-o-play', 'w-4 h-4 text-primary-900 transition-colors')
                                </div>
                                {{-- Pause Icon (shown when playing) --}}
                                <div id="pauseIcon" class="hidden">
                                    @svg('heroicon-o-pause', 'w-4 h-4 text-primary-900 transition-colors')
                                </div>
                            </button>
                            
                            {{-- Next Button --}}
                            <button id="nextSlide" 
                                    type="button"
                                    aria-label="Next slide"
                                    class="w-10 h-10 bg-primary-500/80 dark:bg-primary-500/80 hover:bg-primary-400 dark:hover:bg-primary-400 rounded-lg flex items-center justify-center transition-all duration-300 group">
                                @svg('heroicon-m-arrow-right', 'w-5 h-5 text-primary-900 transition-colors')
                            </button>
                        </div>
                    </nav>
                </div>

                {{-- Bottom Section: Hero Images --}}
                <div class="absolute bottom-0 left-0 w-full h-3/4 pl-12">
                    <img id="heroImage"
                         src="{{ asset('images/hero-images/light/01.png') }}"
                         alt="CheQQme Data Center platform showcase"
                         class="w-full h-full object-cover object-center rounded-tl-3xl border-l-8 border-t-8 border-white/50 dark:border-white/10 transition-all duration-500"
                         loading="eager">
                </div>
            </div>
        </div>

        {{-- Right Section (30%) - Login Form --}}
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
                                    class="theme-toggle-btn p-2 rounded-lgtransition-colors" 
                                    data-theme="light" 
                                    aria-label="Enable light theme"
                                    title="Enable light theme">
                                <x-heroicon-m-sun class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </x-tooltip>

                        {{-- Dark Theme (Moon) --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.darkTheme')">
                            <button type="button" 
                                    class="theme-toggle-btn p-2 rounded-lgtransition-colors" 
                                    data-theme="dark" 
                                    aria-label="Enable dark theme"
                                    title="Enable dark theme">
                                <x-heroicon-m-moon class="w-5 h-5" aria-hidden="true" />
                            </button>
                        </x-tooltip>

                        {{-- System Theme (Desktop) --}}
                        <x-tooltip position="bottom" :text="__('login.tooltips.systemTheme')">
                            <button type="button" 
                                    class="theme-toggle-btn p-2 rounded-lgtransition-colors" 
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
                <div class="w-full max-w-md login-form-container">

                {{-- Logo Overlay --}}
                <div class="flex justify-center mb-8">
                    <img id="loginLogo"
                         src="{{ asset('logos/logo-dark.png') }}"
                         alt="{{ config('app.name') }} Logo"
                         class="h-32 w-auto transition-all duration-300"
                         loading="eager">
                </div>
                
                    {{-- Sign In Header --}}
                <header class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white text-center">
                        {{ __('login.title')}}
                    </h1>
                </header>

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
                                <span id="remember-description" class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('login.form.remember') }}
                                </span>
                            </label>
                        </div>
                        
                        {{-- Forgot Password --}}
                        <div>
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:underline transition-colors duration-200"
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
    <script src="{{ asset('js/google-signin.js') }}"></script>
    <script src="{{ asset('js/remember-me-toggle.js') }}"></script>
</body>
</html>