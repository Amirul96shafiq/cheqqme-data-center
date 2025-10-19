@php
    $user = auth()->user();
@endphp

<x-filament-panels::page
    wire:loading.class="animate-pulse"
    class="fi-resource-edit-record-page"
>
    {{-- Cover Image Section --}}
    <div class="relative h-[30vh] md:h-64 lg:h-80 xl:h-96 w-full overflow-visible rounded-2xl z-10">
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
                    <div class="flex flex-wrap gap-2 justify-center">

                        <!-- Country -->
                        @if ($user && $user->country)
                            <x-tooltip position="top" text="{{ __('user.badge.country') }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-medium bg-blue-100/90 text-blue-900 shadow-sm">
                                    <x-icons.custom-icon name="map-pin" class="w-3 h-3" color="" />
                                    <span>{{ $user->country }}</span>
                                </span>
                            </x-tooltip>
                        @endif

                        <!-- Timezone -->
                        @if ($user && $user->timezone)
                            <x-tooltip position="top" text="{{ __('user.badge.timezone') }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-medium bg-purple-100/90 text-purple-900 shadow-sm">
                                    <x-icons.custom-icon name="clock" class="w-3 h-3" color="" />
                                    <span>{{ $user->timezone }}</span>
                                </span>
                            </x-tooltip>
                        @endif

                        <!-- Google OAuth -->
                        @if ($user->google_id && $user->google_connected_at)
                            <x-tooltip position="top" text="{{ __('user.badge.google_oauth') }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-medium bg-gray-100/90 text-gray-900 shadow-sm">
                                    <x-icons.custom-icon name="google" class="w-3 h-3" color="" />
                                    <span>Google</span>
                                </span>
                            </x-tooltip>
                        @endif

                        <!-- Google Calendar API -->
                        @if ($user->google_calendar_token && $user->google_calendar_connected_at)
                            <x-tooltip position="top" text="{{ __('user.badge.google_calendar') }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-medium bg-sky-100/90 text-sky-900 shadow-sm">
                                    <x-icons.custom-icon name="google-calendar" class="w-3 h-3" color="" />
                                    <span>Calendar</span>
                                </span>
                            </x-tooltip>
                        @endif
                        
                        <!-- Zoom API -->
                        @if ($user->zoom_token && $user->zoom_connected_at)
                            <x-tooltip position="top" text="{{ __('user.badge.zoom_api') }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-medium bg-indigo-100/90 text-indigo-900 shadow-sm">
                                    <x-icons.custom-icon name="video-camera" class="w-3 h-3" color="" />
                                    <span>Zoom</span>
                                </span>
                            </x-tooltip>
                        @endif

                        <!-- Spotify -->
                        @if ($user->spotify_id && $user->spotify_connected_at)
                            <x-tooltip position="top" text="{{ __('user.badge.spotify') }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] md:text-xs font-medium bg-green-100/90 text-green-900 shadow-sm">
                                    <x-icons.custom-icon name="spotify" class="w-3 h-3" color="" />
                                    <span>Spotify</span>
                                </span>
                            </x-tooltip>
                        @endif

                    </div>

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
