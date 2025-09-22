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

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .hero-slider {
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .login-form-container {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.5);
        }

        /* Auto-filled input field styling */
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
            -webkit-text-fill-color: #3f3f46 !important;
            background-color: white !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Dark mode auto-filled input styling */
        .dark input:-webkit-autofill,
        .dark input:-webkit-autofill:hover,
        .dark input:-webkit-autofill:focus,
        .dark input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #27272a inset !important;
            -webkit-text-fill-color: #fafafa !important;
            background-color: #27272a !important;
        }
    </style>
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
                
                {{-- Hero Image Container (Bottom Right) --}}
                <div class="absolute bottom-0 right-0 w-7/8 h-3/4">
                    <img id="heroImage"
                         src="{{ asset('images/hero-images/01.png') }}"
                         alt="Hero"
                         class="w-full h-full object-cover object-center rounded-tl-3xl border-l-2 border-t-2 border-white/20 dark:border-white/10 transition-all duration-500">
                </div>
                
                {{-- Content Container (Full Width/Height, avoiding hero image) --}}
                <div class="relative z-10 flex flex-col w-1/2 h-full">
                    {{-- Top Content Area (full width, height calculated to avoid hero image) --}}
                    <div class="flex-1 flex flex-col justify-start p-12">
                        {{-- Content positioned to avoid bottom hero image overlap --}}
                        <div class="flex flex-col justify-start space-y-6 max-w-2xl">
                            {{-- Title --}}
                            <div>
                                <h1 id="heroTitle" class="text-3xl font-bold dark:text-white text-gray-600 mb-2 transition-all duration-500 leading-tight">
                                    Welcome to CheQQme Data Center
                                </h1>
                            </div>

                            {{-- Description --}}
                            <div>
                                <p id="heroDescription" class="text-md dark:text-white text-gray-600 transition-all duration-500 leading-relaxed">
                                    Streamline your workflow and manage your data with our powerful and intuitive platform.
                                </p>
                            </div>

                            {{-- Slider Navigation --}}
                            <div class="flex items-center space-x-3 mt-8" id="sliderNav">
                                <button data-slide="0" class="w-12 h-1 bg-primary-400 rounded-full transition-all duration-300"></button>
                                <button data-slide="1" class="w-4 h-1 bg-gray-400 dark:bg-white/50 rounded-full transition-all duration-300 hover:bg-primary-400"></button>
                                <button data-slide="2" class="w-4 h-1 bg-gray-400 dark:bg-white/50 rounded-full transition-all duration-300 hover:bg-primary-400"></button>
                                <button data-slide="3" class="w-4 h-1 bg-gray-400 dark:bg-white/50 rounded-full transition-all duration-300 hover:bg-primary-400"></button>
                                <button data-slide="4" class="w-4 h-1 bg-gray-400 dark:bg-white/50 rounded-full transition-all duration-300 hover:bg-primary-400"></button>
                                <button data-slide="5" class="w-4 h-1 bg-gray-400 dark:bg-white/50 rounded-full transition-all duration-300 hover:bg-primary-400"></button>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Bottom spacer to ensure content doesn't overlap with hero image --}}
                    <div class="h-1/5"></div>
                </div>
            </div>
        </div>

        {{-- Right Section (30%) - Login Form --}}
        <div class="w-full lg:w-[30%] flex flex-col bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-700 custom-scrollbar overflow-y-auto">
            {{-- Header Section --}}
            <div class="flex-shrink-0 p-6 pb-4">
                {{-- Theme Toggle Buttons --}}
                <div class="flex justify-center">                    
                    <div class="flex flex-row gap-2 p-2 rounded-lg bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50">
                        {{-- Light Theme (Sun) --}}
                        <button class="theme-toggle-btn p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" data-theme="light" title="Enable light theme">
                            <x-heroicon-m-sun class="w-5 h-5" />
                        </button>

                        {{-- Dark Theme (Moon) --}}
                        <button class="theme-toggle-btn p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" data-theme="dark" title="Enable dark theme">
                            <x-heroicon-m-moon class="w-5 h-5" />
                        </button>

                        {{-- System Theme (Desktop) --}}
                        <button class="theme-toggle-btn p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" data-theme="system" title="Enable system theme">
                            <x-heroicon-m-computer-desktop class="w-5 h-5" />
                        </button>
                    </div>
                </div>
            </div>

            {{-- Main Content Section --}}
            <div class="flex-1 flex items-center justify-center px-8">
                <div class="w-full max-w-md login-form-container">

                {{-- Logo Overlay --}}
                <div class="flex justify-center mb-8">
                    <img id="loginLogo"
                         src="{{ asset('logos/logo-dark.png') }}"
                         alt="{{ config('app.name') }}"
                         class="h-32 w-auto transition-all duration-300">
                </div>
                
                    {{-- Sign In Header --}}
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white text-center">
                        {{ __('login.title')}}
                    </h2>
                </div>

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
                                <div class="relative">
                                    <input id="remember" type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                                           class="sr-only">
                                    <div class="w-11 h-6 bg-gray-200 dark:bg-gray-600 rounded-full shadow-inner transition-colors duration-200 ease-in-out"></div>
                                    <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 ease-in-out"></div>
                                </div>
                                <span class="ml-3 text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('login.form.remember') }}
                                </span>
                            </label>
                        </div>
                        
                        {{-- Forgot Password --}}
                        <div>
                            <a href="{{ route('password.request') }}"
                               class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:underline transition-colors duration-200">
                                {{ __('login.actions.forgotPassword') }}
                            </a>
                        </div>
                    </div>


                    {{-- Login Button --}}
                    <button type="submit" class="w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-primary-900 font-medium rounded-md shadow-sm transition-colors duration-200">
                        {{ __('login.actions.login') }}
                    </button>

                    {{-- Separator --}}
                    <div class="flex items-center justify-center my-4">
                        <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                        <span class="px-4 text-[10px] font-light text-gray-500 dark:text-gray-400">{{ __('login.form.or') }}</span>
                        <div class="flex-1 border-t border-gray-300 dark:border-gray-600"></div>
                    </div>

                    {{-- Social Sign-in Buttons --}}
                    <div class="space-y-3">
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
                                      class="block w-full text-center font-semibold px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800">
                                        <span class="text-center p-1.5 mr-2 text-primary-500 bg-primary-100/25 dark:bg-primary-100/5 rounded-lg">MS</span>{{ __('auth.malay') }}
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <!-- Toggle -->
                        <button @click="open = !open" type="button"
                          class="flex items-center justify-center w-10 h-10 language-switch-trigger text-primary-600 bg-white/50 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 hover:border-gray-300/50 dark:hover:border-gray-600/50 rounded-lg transition font-semibold">
                          {{ strtoupper(app()->getLocale()) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hero Slider JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slides = [
                {
                    title: "Welcome to CheQQme Data Center",
                    description: "Streamline your workflow and manage your data with our powerful and intuitive platform.",
                    image: "{{ asset('images/hero-images/01.png') }}"
                },
                {
                    title: "Powerful Task Management",
                    description: "Organize, track, and complete tasks efficiently. Stay on top of deadlines and collaborate seamlessly.",
                    image: "{{ asset('images/hero-images/02.png') }}"
                },
                {
                    title: "Comprehensive Reporting",
                    description: "Generate detailed reports and gain valuable insights. Make data-driven decisions with our advanced...",
                    image: "{{ asset('images/hero-images/03.png') }}"
                },
                {
                    title: "Advanced Analytics Dashboard",
                    description: "Monitor key performance indicators and track progress with real-time data visualization and interactive.",
                    image: "{{ asset('images/hero-images/04.png') }}"
                },
                {
                    title: "Seamless Collaboration",
                    description: "Work together effortlessly with your team using integrated communication tools and shared workspaces.",
                    image: "{{ asset('images/hero-images/05.png') }}"
                },
                {
                    title: "Secure Data Management",
                    description: "Protect your sensitive information with enterprise-grade security features and encrypted storage.",
                    image: "{{ asset('images/hero-images/06.png') }}"
                }
            ];

            let currentSlide = 0;
            const heroImage = document.getElementById('heroImage');
            const heroTitle = document.getElementById('heroTitle');
            const heroDescription = document.getElementById('heroDescription');
            const sliderButtons = document.querySelectorAll('#sliderNav button');

            // Handle slider navigation clicks
            sliderButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    currentSlide = parseInt(button.dataset.slide);
                    updateSlider();
                });
            });

            function updateSlider() {
                // Fade out effect
                heroImage.style.opacity = '0';
                heroTitle.style.opacity = '0';
                heroDescription.style.opacity = '0';

                setTimeout(() => {
                    // Update content
                    const slide = slides[currentSlide];
                    heroImage.src = slide.image;
                    heroTitle.textContent = slide.title;
                    heroDescription.innerHTML = slide.description;

                    // Fade in effect
                    heroImage.style.opacity = '1';
                    heroTitle.style.opacity = '1';
                    heroDescription.style.opacity = '1';

                    // Update button states
                    sliderButtons.forEach((button, index) => {
                        if (index === currentSlide) {
                            button.classList.remove('w-4', 'bg-gray-400', 'dark:bg-white/50');
                            button.classList.add('w-12', 'bg-primary-400');
                        } else {
                            button.classList.remove('w-12', 'bg-primary-400');
                            button.classList.add('w-4', 'bg-gray-400', 'dark:bg-white/50');
                        }
                    });
                }, 300);
            }

            // Auto-advance slides every 10 seconds
            setInterval(() => {
                currentSlide = (currentSlide + 1) % slides.length;
                updateSlider();
            }, 10000);
        });
    </script>

{{-- Google Sign-in JavaScript --}}
<script src="{{ asset('js/google-signin.js') }}"></script>

{{-- Remember Me Toggle JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rememberToggle = document.getElementById('remember');
        const toggleTrack = rememberToggle.nextElementSibling;
        const toggleThumb = toggleTrack.nextElementSibling;

        function updateToggle() {
            if (rememberToggle.checked) {
                toggleTrack.classList.remove('bg-gray-200', 'dark:bg-gray-600');
                toggleTrack.classList.add('bg-primary-600');
                toggleThumb.classList.remove('translate-x-0');
                toggleThumb.classList.add('translate-x-5');
            } else {
                toggleTrack.classList.remove('bg-primary-600');
                toggleTrack.classList.add('bg-gray-200', 'dark:bg-gray-600');
                toggleThumb.classList.remove('translate-x-5');
                toggleThumb.classList.add('translate-x-0');
            }
        }

        // Initialize toggle state
        updateToggle();

        // Handle toggle click
        rememberToggle.addEventListener('change', updateToggle);
    });
</script>

{{-- Theme Toggle JavaScript --}}
<script>
    function updateLogos(isDark) {
        const loginLogo = document.getElementById('loginLogo');
        
        if (loginLogo) {
            if (isDark) {
                loginLogo.src = "{{ asset('logos/logo-dark.png') }}";
            } else {
                loginLogo.src = "{{ asset('logos/logo-light.png') }}";
            }
        }
    }

    function applyTheme(theme) {
        const html = document.documentElement;
        localStorage.setItem('theme', theme);

        if (theme === 'dark') {
            html.classList.add('dark');
            html.classList.remove('light');
            updateLogos(true);
        } else if (theme === 'light') {
            html.classList.remove('dark');
            html.classList.add('light');
            updateLogos(false);
        } else if (theme === 'system') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            html.classList.toggle('dark', prefersDark);
            html.classList.remove('light');
            updateLogos(prefersDark);
        }
        
        // Update debug info
        const debugTheme = document.getElementById('debugTheme');
        const debugClasses = document.getElementById('debugClasses');
        if (debugTheme && debugClasses) {
            debugTheme.textContent = `Theme: ${theme}`;
            debugClasses.textContent = `Classes: ${html.className}`;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const storedTheme = localStorage.getItem('theme');
        const currentTheme = storedTheme || 'system';
        
        // Apply initial theme
        applyTheme(currentTheme);

        const buttons = document.querySelectorAll('.theme-toggle-btn');

        function setActiveButton(activeTheme) {
            buttons.forEach((btn) => {
                const icon = btn.querySelector('svg');
                if (btn.dataset.theme === activeTheme) {
                    btn.classList.add('active');
                    icon?.classList.add('text-primary-500');
                } else {
                    btn.classList.remove('active');
                    icon?.classList.remove('text-primary-500');
                }
            });
        }

        // Set initial active button
        setActiveButton(currentTheme);

        // Add click handlers
        buttons.forEach((btn) => {
            btn.addEventListener('click', () => {
                const theme = btn.dataset.theme;
                applyTheme(theme);
                setActiveButton(theme);
            });
        });

        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (localStorage.getItem('theme') === 'system') {
                applyTheme('system');
            }
        });
    });
</script>
</body>
</html>
