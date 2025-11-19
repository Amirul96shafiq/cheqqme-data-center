@props(['action'])

<button
    type="button"
    onclick="console.log('Microsoft button clicked'); openMicrosoftSignIn()"
    class="w-full py-3 -mt-4 microsoft-signin-button flex items-center justify-center gap-3 px-4 border border-gray-300 rounded-md bg-white hover:bg-gray-50 transition-colors duration-200"
>
    <img src="{{ asset('images/microsoft-icon.svg') }}" alt="Microsoft" class="w-6 h-6" />
    <span class="text-gray-700 font-medium">{{ __('login.actions.microsoftSignin') }}</span>
</button>

<style>
.microsoft-signin-button {
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.25px;
    transition: all 0.2s ease-in-out;
}

.microsoft-signin-button:focus {
    outline: 2px solid #0078d4;
    outline-offset: 2px;
}
</style>
