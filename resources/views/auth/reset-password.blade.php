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
    <div class="auth-frame flex h-screen overflow-hidden">
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
                <div class="relative z-10 flex w-full h-1/4 justify-end">
                    {{-- Left Content Container (65% width) --}}
                    <div class="flex flex-col justify-start p-12 w-[65%]">
                        <div class="flex flex-col justify-start space-y-6 max-w-2xl">
                            {{-- Version Text --}}
                            <div class="text-xs text-gray-600/20 dark:text-gray-500">{{ $gitVersion ?? 'v0.3_local' }}</div>
                            {{-- Title --}}
                            <div>
                                <h1 id="heroTitle" class="text-2xl font-bold dark:text-white text-gray-600 -mb-4 transition-all duration-500 leading-tight hero-title">
                                    Loading...
                                </h1>
                            </div>

                            {{-- Description --}}
                            <div class="min-h-14">
                                <p id="heroDescription" class="text-md dark:text-white text-gray-600 transition-all duration-500 leading-relaxed hero-description">
                                    Loading...
                                </p>
                            </div>

                            {{-- Slider Navigation --}}
                            <nav class="flex items-center space-x-3 mt-8 slider-navigation" id="sliderNav" aria-label="Slide navigation">
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

                    {{-- Right Content Container (35% width) --}}
                    <div class="relative flex flex-col justify-start w-[35%]">
                        {{-- New Update Button (Top) --}}
                        <nav class="flex justify-end" aria-label="Whats new action button">
                            <x-tooltip position="bottom" :text="__('login.tooltips.comingSoon')">
                                <div class="inline-flex items-start cursor-not-allowed" aria-label="What's New (Coming Soon)">
                                    <img src="{{ asset('images/actions/whats-news.png') }}" alt="What's New" class="h-28 w-auto opacity-80 hover:opacity-100 transition-all duration-300 bounce-bounce whats-new-button" loading="eager">
                                </div>
                            </x-tooltip>
                        </nav>
                        
                        {{-- Navigation Controls (Bottom) --}}
                        <nav class="flex justify-end mt-4 px-12" aria-label="Hero slider navigation">
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
                </div>

                {{-- Bottom Section: Hero Images --}}
                <div class="absolute bottom-0 left-0 w-full h-3/4 pl-12 hero-image-container">
                    <img id="heroImage"
                         src="{{ asset('images/hero-images/light/01.png') }}"
                         alt="CheQQme Data Center platform showcase"
                         class="w-full h-full object-cover object-center rounded-tl-3xl border-l-8 border-t-8 border-white/50 dark:border-white/10 transition-all duration-500"
                         loading="eager">
                </div>
            </div>
        </div>

        {{-- Sticky Version Text for Responsive (1024px and below) --}}
        <div class="version-text-sticky hidden">
            <div class="text-[11px] text-gray-600/20 dark:text-gray-500 pt-1">
                {{ $gitVersion ?? 'v0.3_local' }}
            </div>
        </div>

        {{-- Sticky What's New Button for Responsive (1024px and below) --}}
        <div class="whats-new-sticky hidden">
            <x-tooltip position="bottom" :text="__('login.tooltips.comingSoon')">
                <div class="inline-flex items-start cursor-not-allowed" aria-label="What's New (Coming Soon)">
                    <img src="{{ asset('images/actions/whats-news.png') }}" alt="What's New" class="transition-all duration-300 bounce-bounce" loading="eager">
                </div>
            </x-tooltip>
        </div>

        {{-- Right Section (30%) - Reset Password Form --}}
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
                        <img id="resetPasswordLogo"
                            src="{{ asset('logos/logo-dark.png') }}"
                            alt="{{ config('app.name') }} Logo"
                            class="h-32 w-auto transition-all duration-300"
                            loading="eager">
                    </div>
                    
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
                                    class="w-full p-3 pr-12 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
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
                                    class="w-full p-3 pr-12 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent dark:bg-gray-800 dark:text-white text-sm">
                                <button type="button" onclick="togglePassword('password_confirmation')"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                    <x-heroicon-o-eye-slash id="password_confirmation-eye-slash" class="h-5 w-5" />
                                    <x-heroicon-o-eye id="password_confirmation-eye" class="h-5 w-5 hidden" />
                                </button>
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                class="w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-primary-900 font-medium rounded-md shadow-sm transition-colors duration-200"
                                aria-describedby="submit-button-description">
                            {{ __('auth.send_reset_link') }}
                            <span id="submit-button-description" class="sr-only">Reset your password</span>
                        </button>

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
</body>
</html>