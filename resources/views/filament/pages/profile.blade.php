@php
    $user = auth()->user();
@endphp

<x-filament-panels::page
    wire:loading.class="animate-pulse"
    class="fi-resource-edit-record-page"
>
    {{-- Cover Image Section --}}
    <div class="relative h-48 md:h-64 lg:h-80 xl:h-96 w-full overflow-visible rounded-2xl z-10">
        <img
            src="{{ $user && $user->cover_image ? $user->getFilamentCoverImageUrl() : asset('storage/default-cover-img.png') }}"
            alt="Cover Image"
            class="w-full h-full object-cover rounded-2xl"
        >
        {{-- User information overlay --}}
        {{-- User ID at top center --}}
        <div class="absolute top-4 left-1/2 transform -translate-x-1/2 text-center">
            <p class="text-xs md:text-sm text-white/80 drop-shadow-md px-3 py-1 bg-black/20 rounded-full">
                ID: {{ $user->id ?? 'N/A' }}
            </p>
        </div>

        {{-- Avatar, username, and email at center middle --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white">
                {{-- Avatar --}}
                <div class="mb-4 mt-8 md:mt-0 relative inline-block">
                    <x-filament::avatar
                        :src="$user ? filament()->getUserAvatarUrl($user) : null"
                        alt="Avatar"
                        size="w-20 h-20 md:w-24 md:h-24 lg:w-32 lg:h-32"
                        class="mx-auto border-[6px] border-white/80"
                    />
                    
                    <!-- Interactive Online Status Indicator -->
                    <div class="absolute -bottom-1 right-2">
                        <x-interactive-online-status-indicator :user="$user" size="xl" />
                    </div>
                </div>

                {{-- User information --}}
                <div class="space-y-1">
                    {{-- Username --}}
                    <h1 class="text-lg md:text-2xl lg:text-3xl font-bold drop-shadow-lg">
                        {{ $user->username ?? 'Username' }}
                    </h1>
                </div>
            </div>
        </div>
    </div>

    {{-- Profile Form Sections --}}
    <form
        wire:submit="save"
        class="space-y-6 relative z-15"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </form>
</x-filament-panels::page>
