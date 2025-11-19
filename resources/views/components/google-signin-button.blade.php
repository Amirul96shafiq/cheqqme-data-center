@props(['action'])

<x-tooltip position="top" :text="__('login.actions.googleSignin')">
    <button
        type="button"
        onclick="openGoogleSignIn()"
        class="w-full py-3 google-signin-button flex items-center justify-center gap-3 px-4 border border-gray-300 rounded-md bg-white hover:bg-gray-50 transition-colors duration-200"
        aria-label="{{ __('login.actions.googleSignin') }}"
    >
        <img src="{{ asset('images/google-icon.svg') }}" alt="Google" class="w-6 h-6" />
    </button>
</x-tooltip>

<style>
.google-signin-button {
    font-family: 'Google Sans', Roboto, Arial, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.25px;
    transition: all 0.2s ease-in-out;
}

.google-signin-button:focus {
    outline: 2px solid #fbb43e;
    outline-offset: 2px;
}
</style>
