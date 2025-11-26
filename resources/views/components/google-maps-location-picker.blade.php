@props([
    'title' => null,
    'address' => null,
    'latitude' => null,
    'longitude' => null,
    'height' => '400px',
    'zoom' => 15,
    'id' => 'google-map-location-picker'
])

{{-- Set API key globally for JavaScript --}}
<script>
    window.GOOGLE_MAPS_API_KEY = '{{ config("services.google_maps.api_key") }}';
</script>

<div 
    class="google-maps-location-picker"
    x-data="googleMapsLocationPicker('{{ $id }}', null, null, '{{ addslashes($address ?? '') }}', {{ $zoom }}, '{{ config("services.google_maps.api_key") }}')"
    x-init="init()"
>
    <!-- Search Box -->
    <div class="mb-4">
        <input
            type="text"
            x-ref="searchInput"
            placeholder="Search Event's location here"
            class="w-full px-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        />
    </div>

    <!-- Map Container -->
    <div
        id="{{ $id }}"
        wire:ignore
        style="height: {{ $height }}; width: 100%;"
        class="rounded-lg border border-gray-200 dark:border-gray-700"
    ></div>

</div>
