<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Reset Password - CheQQme Data Center</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
  <style>
    html {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>

<body class="bg-neutral-950 text-white min-h-screen flex items-center justify-center">
  <div class="w-full px-10 py-10 bg-neutral-900 rounded-2xl shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10 sm:rounded-xl sm:px-12 max-w-lg">
    <div class="text-center mb-6">
      <h1 class="text-xl font-bold tracking-tight text-gray-100 mb-1">CheQQme Data Center</h1>
      <h2 class="text-2xl font-bold text-white">Reset Password</h2>
    </div>

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

      <div class="mb-5">
        <label for="password" class="block text-sm font-medium text-gray-300 mb-3">New Password <span
            class="text-red-500">*</span></label>
        <input id="password" type="password" name="password" required
          class="w-full px-4 py-3 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm" />
      </div>

      <div class="mb-5">
        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-3">Confirm New Password
          <span class="text-red-500">*</span></label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
          class="w-full px-4 py-3 bg-neutral-800 text-white rounded-lg border border-neutral-700 focus:ring-2 focus:ring-amber-500 focus:outline-none text-sm" />
      </div>

      <button type="submit"
        class="w-full py-5 px-4 bg-amber-500 hover:bg-amber-400 text-white font-semibold text-sm rounded-lg transition">
        Reset Password
      </button>
    </form>

    <div class="mt-6 text-center">
      <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-300 hover:underline transition">
        Back to Sign In
      </a>
    </div>
  </div>
</body>

</html>