@props(['action'])

<x-tooltip position="top" :text="__('login.actions.githubSignin')">
    <button
        type="button"
        disabled
        class="w-full py-3 github-signin-button flex items-center justify-center gap-3 px-4 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200 opacity-60 cursor-not-allowed"
        aria-label="{{ __('login.actions.githubSignin') }}"
    >
        <img src="{{ asset('images/github-icon.svg') }}" alt="GitHub" class="w-6 h-6 dark:invert" />
    </button>
</x-tooltip>

<style>
.github-signin-button {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.25px;
    transition: all 0.2s ease-in-out;
}

.github-signin-button:focus {
    outline: 2px solid #fbb43e;
    outline-offset: 2px;
}
</style>
