<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md p-6 bg-white rounded-xl shadow-md">
        <h2 class="text-2xl font-bold text-center mb-6">Forgot Password</h2>

        @if (session('status'))
            <div class="mb-4 text-green-600 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" name="email" id="email" required value="{{ old('email') }}"
                       class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring focus:ring-amber-500 focus:border-amber-500">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-2 px-4 bg-amber-500 text-white font-semibold rounded-md hover:bg-amber-600">
                Send Reset Link
            </button>
        </form>
    </div>
</body>
</html>
