<button
    type="button"
    x-data="{ isSubmitting: false }"
    x-on:click="if (isSubmitting) return; isSubmitting = true; $el.setAttribute('aria-busy', 'true'); $el.setAttribute('disabled', 'disabled'); $el.form && $el.form.submit();"
    x-bind:disabled="isSubmitting"
    x-bind:aria-busy="isSubmitting ? 'true' : 'false'"
    {{ $attributes->merge(['class' => 'w-full py-4 px-4 bg-primary-600 hover:bg-primary-500 text-primary-900 font-medium rounded-md shadow-sm transition-colors duration-200 disabled:opacity-70 disabled:cursor-not-allowed']) }}
>
    <span x-show="!isSubmitting" x-cloak>
        {{ $label ?? '' }}
    </span>
    <span x-show="isSubmitting" class="inline-flex items-center justify-center gap-2" x-cloak>
        <x-heroicon-o-arrow-path class="h-5 w-5 animate-spin text-primary-900" />
        {{ __('auth.loading') }}
    </span>
    @isset($sr)
        <span class="sr-only">{{ $sr }}</span>
    @endisset
</button>


