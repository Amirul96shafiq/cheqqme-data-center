<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <title>Reset Password - CheQQme Data Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/images/favicon.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@tabler/icons@latest/iconfont/tabler-icons.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

  <style>
    html {
      font-family: 'Roboto';
    }

    .theme-toggle-btn {
      padding: 0.5rem;
      border-radius: 0.5rem;
      transition: all 0.2s ease-in-out;
    }

    .theme-toggle-btn.active { background-color: rgba(255,255,255,0.1); }
    .theme-toggle-btn.active svg { color: #fbb43e; /* match brand primary-500 like login/reset */ }

    .password-toggle-btn {
      padding: 0.5rem;
      border-radius: 0.5rem;
      transition: all 0.2s ease-in-out;
    }
  </style>

  <!-- Theme Script -->
  <script>
    function applyTheme(theme) {
      const html = document.documentElement
      localStorage.setItem('theme', theme)

      if (theme === 'dark') {
        html.classList.add('dark')
        html.classList.remove('light')
      } else if (theme === 'light') {
        html.classList.remove('dark')
        html.classList.add('light')
      } else if (theme === 'system') {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
        html.classList.toggle('dark', prefersDark)
        html.classList.remove('light')
      }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const storedTheme = localStorage.getItem('theme')
      if (storedTheme) {
        applyTheme(storedTheme)
      }

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

      // Initial highlighting
      setActiveButton(storedTheme || 'system');

      // Add click event to toggle
      buttons.forEach((btn) => {
        btn.addEventListener('click', () => {
          const selected = btn.dataset.theme;
          applyTheme(selected);
          setActiveButton(selected);
        });
      });
    })
  </script>

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

  <!-- Custom Tailwind CSS Configuration -->
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            primary: {
              50: '#fff8eb',
              100: '#fde7c3',
              200: '#fcd39b',
              300: '#fbbe72',
              400: '#fab54f',
              500: '#fbb43e',
              600: '#e6a135',
              700: '#c5862c',
              800: '#a56b23',
              900: '#844f1a',
            }
          }
        }
      }
    }
  </script>
</head>

<body class="min-h-screen bg-gray-100 dark:bg-zinc-950 flex items-center justify-center">
  @include('components.global-loader')
  <div class="max-w-md w-full space-y-8">

    <!-- Theme Toggle Buttons -->
    <div class="absolute top-2 left-0 right-0 flex justify-center z-50">
      <div class="flex flex-row gap-1 p-2 rounded-lg">

        <!-- Light Theme (Sun) -->
        <button class="theme-toggle-btn" data-theme="light" title="Enable light theme">
          <x-heroicon-s-sun class="w-5 h-5 text-gray-400 hover:text-primary-500" />
        </button>

        <!-- Dark Theme (Moon) -->
        <button class="theme-toggle-btn" data-theme="dark" title="Enable dark theme">
          <x-heroicon-s-moon class="w-5 h-5 text-gray-400 hover:text-primary-500" />
        </button>

        <!-- System Theme (Desktop) -->
        <button class="theme-toggle-btn" data-theme="system" title="Enable system theme">
          <x-heroicon-s-computer-desktop class="w-5 h-5 text-gray-400 hover:text-primary-500" />
        </button>

      </div>
    </div>

    <!-- Card section-->
    <div
      class="bg-white dark:bg-[rgb(24_24_27_/_1)] rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 sm:rounded-xl px-12 py-16 space-y-6">
      <!-- Header section -->
      <div class="text-center">
        <!-- Logo Light -->
        <img src="/logos/logo-light.png" alt="CheQQme Data Center Logo"
        class="h-32 dark:hidden mx-auto">
        <!-- Logo Dark -->
        <img src="/logos/logo-dark.png" alt="CheQQme Data Center Logo"
        class="h-32 hidden dark:block mx-auto">
        <h2 class="text-2xl font-bold text-black dark:text-white m-3">{{ __('auth.reset_password') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ __('auth.description_reset_password') }}</p>
      </div>

      <!-- Form section -->
      @if (session('status'))
      <div class="mb-4 text-sm text-primary-500 font-medium text-center">
      {{ session('status') }}
      </div>
    @endif

      @if ($errors->any())
      <div class="mb-4 text-sm text-red-400">
      <ul class="list-disc pl-5 space-y-1">
        @foreach ($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
      </ul>
      </div>
    @endif

      <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email Field -->
        <div class="mb-5">
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('auth.email') }} <span class="text-red-500">*</span>
          </label>
          <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required readonly
            class="w-full px-4 py-2 bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 rounded-lg border border-neutral-300 dark:border-neutral-700 text-sm cursor-not-allowed" />
        </div>

        <!-- New Password Field -->
        <div class="mb-5 relative">
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('auth.new_password') }} <span class="text-red-500">*</span>
          </label>
          <input id="password" type="password" name="password" required
            class="w-full px-4 py-2 bg-white text-black dark:bg-[rgb(255_255_255_/_0.05)] dark:text-white rounded-lg border border-neutral-300 dark:border-neutral-700 focus:ring-2 focus:ring-primary-600 focus:outline-none text-sm" />

          <button type="button" onclick="togglePassword('password')"
            class="absolute top-7 right-1 password-toggle-btn text-gray-500 hover:text-gray-700 dark:hover:text-white">
            <x-heroicon-s-eye-slash class="h-5 w-5" id="password-eye-slash" />
            <x-heroicon-s-eye class="h-5 w-5 hidden" id="password-eye" />
          </button>
        </div>

        <!-- Confirm Password Field -->
        <div class="mb-5 relative">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ __('auth.confirm_new_password') }} <span class="text-red-500">*</span>
          </label>
          <input id="password_confirmation" type="password" name="password_confirmation" required
            class="w-full px-4 py-2 bg-white text-black dark:bg-[rgb(255_255_255_/_0.05)] dark:text-white rounded-lg border border-neutral-300 dark:border-neutral-700 focus:ring-2 focus:ring-primary-600 focus:outline-none text-sm" />

          <button type="button" onclick="togglePassword('password_confirmation')"
            class="absolute top-7 right-1 password-toggle-btn text-gray-500 hover:text-gray-700 dark:hover:text-white">
            <x-heroicon-s-eye-slash class="h-5 w-5" id="password_confirmation-eye-slash" />
            <x-heroicon-s-eye class="h-5 w-5 hidden" id="password_confirmation-eye" />
          </button>
        </div>

        <!-- Submit button -->
        <button type="submit"
          class="w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-white dark:text-white font-semibold text-sm rounded-lg transition">
          {{ __('auth.send_reset_link') }}
        </button>
      </form>

      <!-- Footer -->
      <div class="text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300 hover:underline">
          {{ __('auth.back_to_login') }}
        </a>
      </div>
      <div class="text-center">
        <a href="/forgot-password" class="text-sm font-semibold text-gray-700 dark:text-gray-300 hover:underline">
          {{ __('auth.back_to_forgot_password') }}
        </a>
      </div>
    </div>

    <!-- Language Switcher URL Link logic -->
  @php
    $currentPath = Request::path();
    $strippedPath = preg_replace('#^(en|ms|id|zh)(/)?#', '', $currentPath);
  @endphp

  <!-- Language Switcher -->
  <div class="absolute bottom-4 left-0 right-0 flex justify-center z-50">
    <div x-data="{ open: false }" class="relative">
        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false"
            class="absolute bottom-full mb-2 w-40 rounded-md shadow-lg bg-white dark:bg-neutral-900 ring-1 ring-gray-950/5 dark:ring-white/10 z-50"
            x-cloak
            style="left: 50%; transform: translateX(-50%);">
            <div class="py-1 text-sm text-gray-700 dark:text-gray-100">
                <form method="POST" action="{{ route('locale.set') }}">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800 {{ app()->getLocale() === 'en' ? 'font-bold' : '' }}">
                        <span class="text-primary-500">EN </span>English
                    </button>
                </form>
                <form method="POST" action="{{ route('locale.set') }}">
                    @csrf
                    <input type="hidden" name="locale" value="ms">
                    <button type="submit"
                          class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800 {{ app()->getLocale() === 'ms' ? 'font-bold' : '' }}">
                        <span class="text-primary-500">MS </span>Bahasa Melayu
                    </button>
                </form>
            </div>
        </div>

        <!-- Toggle -->
        <button @click="open = !open"
          class="flex items-center justify-center w-9 h-9 language-switch-trigger text-primary-600 bg-white dark:bg-[rgb(255_255_255_/_0.05)] border border-[rgb(3_7_18_/_0.1)] hover:border-[rgb(3_7_18_/_0.2)] dark:border-[rgb(255_255_255_/_0.2)] dark:hover:border-[rgb(255_255_255_/_0.3)] rounded-lg transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40 font-semibold">
          {{ strtoupper(app()->getLocale()) }}
        </button>
  </div>

  </div>
</body>

</html>