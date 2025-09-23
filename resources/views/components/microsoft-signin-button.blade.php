@props(['action'])

<x-tooltip position="top" :text="__('login.tooltips.comingSoon')">
    <button
        type="button"
        onclick="preventMicrosoftClick(event)"
        class="w-full py-3 -mt-4 microsoft-signin-button microsoft-signin-disabled flex items-center justify-center gap-3 px-4 border border-gray-300 rounded-md bg-gray-200 transition-colors duration-200"
        disabled
        aria-disabled="true"
    >
        <img src="{{ asset('images/microsoft-icon.svg') }}" alt="Microsoft" class="w-6 h-6 opacity-40" />
        <span class="text-gray-400 font-medium">{{ __('login.actions.microsoftSignin') }}</span>
    </button>
</x-tooltip>

<script>
function preventMicrosoftClick(event) {
    // Prevent the default button behavior
    event.preventDefault();
    event.stopPropagation();
    return false;
}
</script>

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

/* Disabled state styles */
.microsoft-signin-disabled {
    cursor: not-allowed !important;
    user-select: none;
    opacity: 0.6;
    background-color: #f5f5f5 !important;
    border-color: #d1d5db !important;
    color: #9ca3af !important;
}

.microsoft-signin-disabled:hover {
    background-color: #f5f5f5 !important;
    border-color: #d1d5db !important;
    cursor: not-allowed !important;
    opacity: 0.6 !important;
}

.dark .microsoft-signin-disabled {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    color: #6b7280 !important;
}

.dark .microsoft-signin-disabled:hover {
    background-color: #374151 !important;
    border-color: #4b5563 !important;
    cursor: not-allowed !important;
}

/* Focus styles for disabled button */
.microsoft-signin-disabled:focus {
    outline: none;
    box-shadow: none;
}

/* Ensure disabled state overrides any other styles */
.microsoft-signin-disabled * {
    pointer-events: none;
}

/* Tooltip hover effects */
.relative:hover .microsoft-tooltip {
    opacity: 1;
    visibility: visible;
    transform: translateX(-50%) translateY(-4px);
}
</style>
