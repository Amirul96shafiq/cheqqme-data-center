@props(['action'])

<x-tooltip position="top" :text="__('login.actions.microsoftSignin')">
    <button
        type="button"
        onclick="console.log('Microsoft button clicked'); openMicrosoftSignIn()"
        class="w-full py-3 microsoft-signin-button flex items-center justify-center gap-3 px-4 border border-gray-300 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
        aria-label="{{ __('login.actions.microsoftSignin') }}"
    >
        <img src="{{ asset('images/microsoft-icon.svg') }}" alt="Microsoft" class="w-6 h-6" />
    </button>
</x-tooltip>

<style>
.microsoft-signin-button {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.25px;
    transition: all 0.2s ease-in-out;
}

.microsoft-signin-button:focus {
    outline: 2px solid #fbb43e;
    outline-offset: 2px;
}
</style>
