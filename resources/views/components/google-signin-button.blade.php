@props(['action'])

<button 
    type="button"
    onclick="openGoogleSignIn()"
    class="w-full py-3 mt-0 google-signin-button flex items-center justify-center gap-3 px-4 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
>
    <img src="{{ asset('images/google-icon.svg') }}" alt="Google" class="w-6 h-6" />
    <span class="text-gray-700 font-medium">Sign in with Google</span>
</button>

<style>
.google-signin-button {
    border: 1px solid #ebedf0;
    background-color: #ebedf0;
    color: #3c4043;
    font-family: 'Google Sans', Roboto, Arial, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.25px;
    transition: all 0.2s ease-in-out;
}

.google-signin-button:hover {
    background-color: #ffffff;
    border-color: #ffffff;
}

.google-signin-button:focus {
    outline: none;
}

.google-signin-button:active {
    background-color: #f1f3f4;
}
</style>
