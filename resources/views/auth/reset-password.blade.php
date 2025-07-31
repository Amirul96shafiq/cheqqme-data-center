<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <title>Reset Password - CheQQme Data Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/@tabler/icons@latest/iconfont/tabler-icons.min.js"></script>
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

    .theme-toggle-btn.active svg {
      color: #facc15;
    }
  </style>

  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>

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

      document.querySelectorAll('.theme-toggle-btn').forEach((btn) => {
        btn.addEventListener('click', () => {
          applyTheme(btn.dataset.theme)
        })
      })
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
</head>

<body class="min-h-screen bg-gray-100 dark:bg-neutral-950 flex items-center justify-center">
  <div class="max-w-md w-full space-y-8">

    <!-- Theme Toggle Buttons -->
    <div class="absolute top-2 left-0 right-0 flex justify-center z-50">
      <div class="flex flex-row gap-1 p-2 rounded-lg">

        <!-- Light Theme (Sun) -->
        <button class="theme-toggle-btn" data-theme="light" title="Enable light theme">
          <x-heroicon-s-sun class="w-5 h-5 text-gray-400 hover:text-yellow-400" />
        </button>

        <!-- Dark Theme (Moon) -->
        <button class="theme-toggle-btn" data-theme="dark" title="Enable dark theme">
          <x-heroicon-s-moon class="w-5 h-5 text-gray-400 hover:text-yellow-400" />
        </button>

        <!-- System Theme (Desktop) -->
        <button class="theme-toggle-btn" data-theme="system" title="Enable system theme">
          <x-heroicon-s-computer-desktop class="w-5 h-5 text-gray-400 hover:text-yellow-400" />
        </button>

      </div>
    </div>

    <!-- Card section-->
    <div
      class="bg-white dark:bg-neutral-900 rounded-2xl ring-1 ring-gray-950/5 dark:ring-white/10 sm:rounded-xl p-8 space-y-6">
      <!-- Header section -->
      <div class="text-center">
        <h1 class="text-xl font-bold text-black dark:text-gray-100 mb-1">CheQQme Data Center</h1>
        <h2 class="text-2xl font-bold text-black dark:text-white">Reset Password</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Enter new password</p>
      </div>

      <!-- Form section -->
      @if (session('status'))
      <div class="mb-4 text-sm text-amber-400 font-medium text-center">
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
        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

        <!-- New Password Field -->
        <div class="mb-5 relative">
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            New Password <span class="text-red-500">*</span>
          </label>
          <input id="password" type="password" name="password" required
            class="w-full px-4 py-3 pr-12 bg-white text-black dark:bg-neutral-800 dark:text-white rounded-lg border border-neutral-300 dark:border-neutral-700 focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm" />

          <button type="button" onclick="togglePassword('password')"
            class="absolute top-7 right-3 theme-toggle-btn text-gray-500 hover:text-gray-700 dark:hover:text-white">
            <x-heroicon-s-eye-slash class="h-5 w-5" id="password-eye-slash" />
            <x-heroicon-s-eye class="h-5 w-5 hidden" id="password-eye" />
          </button>
        </div>

        <!-- Confirm Password Field -->
        <div class="mb-5 relative">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Confirm New Password <span class="text-red-500">*</span>
          </label>
          <input id="password_confirmation" type="password" name="password_confirmation" required
            class="w-full px-4 py-3 pr-12 bg-white text-black dark:bg-neutral-800 dark:text-white rounded-lg border border-neutral-300 dark:border-neutral-700 focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm" />

          <button type="button" onclick="togglePassword('password_confirmation')"
            class="absolute top-7 right-3 theme-toggle-btn text-gray-500 hover:text-gray-700 dark:hover:text-white">
            <x-heroicon-s-eye-slash class="h-5 w-5" id="password_confirmation-eye-slash" />
            <x-heroicon-s-eye class="h-5 w-5 hidden" id="password_confirmation-eye" />
          </button>
        </div>

        <!-- Submit button -->
        <button type="submit"
              class="w-full py-5 px-4 bg-amber-500 hover:bg-amber-400 text-white font-semibold text-sm rounded-lg transition">
              Reset Password
          </button>
      </form>

      <!-- Footer -->
      <div class="text-center">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 dark:text-gray-300 hover:underline">
          Back to Sign in
        </a>
      </div>
      <div class="text-center">
        <a href="/forgot-password" class="text-sm font-semibold text-gray-700 dark:text-gray-300 hover:underline">
          Redo Forgot Password
        </a>
      </div>
    </div>

  </div>
</body>

</html>