@props(['action'])

<button
    type="button"
    onclick="showMicrosoftComingSoon()"
    class="w-full py-3 -mt-4 microsoft-signin-button microsoft-signin flex items-center justify-center gap-3 px-4 border border-gray-200 rounded-lg bg-gray-100 opacity-60 transition-colors duration-200"
>
    <img src="{{ asset('images/microsoft-icon.svg') }}" alt="Microsoft" class="w-6 h-6 opacity-50" />
    <span class="text-gray-500 font-medium">{{ __('login.actions.microsoftSignin') }}</span>

    <script>
    function showMicrosoftComingSoon() {
        // Get the current language from multiple sources
        let currentLang = 'en'; // Default to English

        // Try to get language from various sources
        if (document.documentElement.lang) {
            currentLang = document.documentElement.lang.split('-')[0]; // Get language code without region
        } else if (window.navigator.language) {
            currentLang = window.navigator.language.split('-')[0];
        } else if (window.navigator.userLanguage) {
            currentLang = window.navigator.userLanguage.split('-')[0];
        }

        // Additional check for Filament language switcher or locale
        if (window.Alpine && window.Alpine.store && window.Alpine.store('languageSwitch')) {
            const langStore = window.Alpine.store('languageSwitch');
            if (langStore && langStore.currentLocale) {
                currentLang = langStore.currentLocale;
            }
        }

        // Check for Filament's current locale in data attributes or global variables
        const htmlElement = document.documentElement;
        if (htmlElement.dataset.locale) {
            currentLang = htmlElement.dataset.locale;
        }

        // Check for any global language variables
        if (window.CURRENT_LOCALE) {
            currentLang = window.CURRENT_LOCALE;
        }

        // Ensure we only use supported languages
        if (!['en', 'ms'].includes(currentLang)) {
            currentLang = 'en';
        }

        // Define messages in both languages
        const messages = {
            en: 'Microsoft Sign-in: This feature is coming soon!',
            ms: 'Log Masuk Microsoft: Ciri ini akan datang tidak lama lagi!'
        };

        const message = messages[currentLang] || messages.en; // Fallback to English

        // Redirect to the Microsoft coming soon route to show the notification
        const url = new URL('{{ route("microsoft.coming-soon") }}', window.location.origin);
        url.searchParams.append('message', message);
        window.location.href = url.toString();
    }
    </script>
</button>

<style>
.microsoft-signin-button {
    border: 1px solid #ebedf0;
    background-color: #ebedf0;
    color: #3c4043;
    font-family: 'Segoe UI', Roboto, Arial, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: 0.25px;
    transition: all 0.2s ease-in-out;
}

/* Hover effects for enabled state */
/* .microsoft-signin-button:not(.microsoft-signin-disabled):hover {
    background-color: #eef1f5;
    border-color: #eef1f5;
}

.dark .microsoft-signin-button:not(.microsoft-signin-disabled):hover {
    background-color: #ffffff;
    border-color: #ffffff;
}

.microsoft-signin-button:focus {
    outline: none;
}

.microsoft-signin-button:active:not(.microsoft-signin-disabled) {
    background-color: #f1f3f4;
} */

/* Disabled state styles */
/* .microsoft-signin-disabled {
    pointer-events: none;
    user-select: none;
}

.microsoft-signin-disabled:hover {
    background-color: #ebedf0 !important;
    border-color: #ebedf0 !important;
    cursor: not-allowed !important;
}

.dark .microsoft-signin-disabled:hover {
    background-color: #ebedf0 !important;
    border-color: #ebedf0 !important;
} */
</style>
