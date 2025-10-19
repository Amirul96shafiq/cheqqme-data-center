@php
    $user = auth()->user();
@endphp

<x-filament-panels::page
    wire:loading.class="animate-pulse"
    class="fi-resource-edit-record-page"
>
    {{-- Cover Image Section --}}
    <div class="relative h-[30vh] md:h-64 lg:h-80 xl:h-96 w-full overflow-visible rounded-2xl z-5">
        <img
            src="{{ $user && $user->cover_image ? $user->getFilamentCoverImageUrl() : asset('storage/default-cover-img.png') }}"
            alt="Cover Image"
            class="w-full h-full object-cover rounded-2xl"
        >

        {{-- Dark transparent background box --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-full max-w-xl h-full bg-gradient-to-t from-black/35 to-transparent"></div>
        </div>

        {{-- Avatar, username, and email at center middle --}}
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white relative z-10">

                {{-- User ID and Avatar --}}
                <div class="mb-4 mt-8 md:mt-0 relative inline-block">

                    {{-- User ID above avatar --}}
                    <div class="mb-2 text-center">
                        <p class="text-xs md:text-sm text-white/90 drop-shadow-md px-3 py-1 bg-black/40 rounded-full inline-block">
                            ID: {{ $user->id ?? 'N/A' }}
                        </p>
                    </div>

                    <x-filament::avatar
                        :src="$user ? filament()->getUserAvatarUrl($user) : null"
                        alt="Avatar"
                        size="w-20 h-20 md:w-24 md:h-24 lg:w-32 lg:h-32"
                        class="mx-auto border-[6px] border-white dark:border-gray-900"
                    />
                    
                    <!-- Interactive Online Status Indicator -->
                    <div class="absolute -bottom-1 right-2">
                        <x-interactive-online-status-indicator :user="$user" size="xl" />
                    </div>

                </div>

                {{-- User information --}}
                <div class="space-y-3">

                    {{-- Username --}}
                    <h1 class="text-lg md:text-2xl lg:text-3xl font-bold drop-shadow-lg">
                        {{ $user->username ?? 'Username' }}
                    </h1>

                    {{-- Name --}}
                    @if ($user && $user->name)
                        <p class="text-xs md:text-sm text-white/90 drop-shadow !mt-0">
                            {{ $user->name }}
                        </p>
                    @endif

                    {{-- Email & Phone --}}
                    <div class="space-y-0">
                        @if ($user && $user->email)
                            <p class="text-[11px] md:text-lg font-semibold text-white/80 drop-shadow">
                                {{ $user->email }}
                            </p>
                        @endif

                        @if ($user && $user->phone)
                            <p class="text-[11px] md:text-lg font-semibold text-white/80 drop-shadow">
                                @php
                                    $country = $user->phone_country ?? 'MY';
                                    $flag = match($country) {
                                        'MY' => '🇲🇾',
                                        'ID' => '🇮🇩',
                                        'SG' => '🇸🇬',
                                        'PH' => '🇵🇭',
                                        'US' => '🇺🇸',
                                        default => '🌐',
                                    };
                                @endphp
                                {{ $flag }} {{ $user->phone }}
                            </p>
                        @endif
                    </div>

                    {{-- Badges --}}
                    <x-user-badges :user="$user" size="md" gap="2" :showIcons="true" />

                </div>

            </div>
        </div>

    </div>

    {{-- Profile Form Sections --}}
    <form
        wire:submit="save"
        class="space-y-6 relative z-10"
    >
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </form>
</x-filament-panels::page>
