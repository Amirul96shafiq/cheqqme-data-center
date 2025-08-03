<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="scroll-smooth">

<head>
  <meta charset="UTF-8" />
  <title>Forgot Password - CheQQme Data Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@tabler/icons@latest/iconfont/tabler-icons.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

  <style>
    html {
      font-family: 'Inter', sans-serif;
    }

    .theme-toggle-btn {
      padding: 0.5rem;
      border-radius: 0.5rem;
      transition: all 0.2s ease-in-out;
    }

    .theme-toggle-btn.active {
      background-color: rgba(255, 255, 255, 0.1);
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

<body class="min-h-screen bg-gray-100 dark:bg-neutral-950 flex items-center justify-center">
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

    <!-- Card section -->
    <div
      class="bg-white dark:bg-neutral-900 rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 sm:rounded-xl p-8 space-y-6">
      <!-- Header section -->
      <div class="text-center">
        <!-- Logo Light -->
        <img src="/logos/logo-light.png" alt="CheQQme Data Center Logo"
        class="h-32 dark:hidden mx-auto">
        <!-- Logo Dark -->
        <img src="/logos/logo-dark.png" alt="CheQQme Data Center Logo"
        class="h-32 hidden dark:block mx-auto">
        <h2 class="text-2xl font-bold text-black dark:text-white m-3">{{ __('auth.forgot_password') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ __('auth.description_forgot_password') }}</p>
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

      <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <!-- Email Address Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mt-6 mb-2">{{ __('auth.email_address') }}</label>
          <input id="email" name="email" type="email" required autofocus
            class="w-full px-4 py-2 bg-white text-black dark:bg-neutral-800 dark:text-white rounded-lg border border-neutral-300 dark:border-neutral-700 focus:ring-2 focus:ring-primary-600 focus:outline-none text-sm" />
        </div>

        <!-- Submit button -->
        <div>
          <button type="submit"
            class="w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-white dark:text-black font-semibold text-sm rounded-lg transition">
            {{ __('auth.send_link') }}
          </button>
        </div>
      </form>

      <!-- Footer -->
      <div class="text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300 hover:underline">
          {{ __('auth.back_to_login') }}
        </a>
      </div>
    </div>
  </div>

  <!-- Language Switcher URL Link logic -->
  @php
    $currentPath = Request::path();
    $strippedPath = preg_replace('#^(en|ms|id|zh)(/)?#', '', $currentPath);
  @endphp

  <!-- Language Switcher -->
  <div class="absolute bottom-2 left-0 right-0 flex justify-center z-50">
    <div x-data="{ open: false }" class="relative">
        <!-- Dropdown -->
        <div x-show="open" @click.away="open = false"
            class="absolute bottom-full mb-2 w-40 rounded-md shadow-lg bg-white dark:bg-neutral-900 ring-1 ring-gray-950/5 dark:ring-white/10 z-50"
            x-cloak>
            <div class="py-1 text-sm text-gray-700 dark:text-gray-100">
                <form method="POST" action="{{ route('locale.set') }}">
                    @csrf
                    <input type="hidden" name="locale" value="en">
                    <button type="submit"
                        class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800 {{ app()->getLocale() === 'en' ? 'font-bold' : '' }}">
                        English
                    </button>
                </form>
                <form method="POST" action="{{ route('locale.set') }}">
                    @csrf
                    <input type="hidden" name="locale" value="ms">
                    <button type="submit"
                          class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-neutral-800 {{ app()->getLocale() === 'ms' ? 'font-bold' : '' }}">
                        Bahasa Melayu
                    </button>
                </form>
            </div>
        </div>

        <!-- Toggle -->
        <button @click="open = !open"
            class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-2xl sm:rounded-xl  text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-neutral-800 ring-1 ring-gray-950/5 dark:ring-white/10">
            {{ strtoupper(app()->getLocale()) }}
        </button>
  </div>
</div>


</div>

</body>

</html>