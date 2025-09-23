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