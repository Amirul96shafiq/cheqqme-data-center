@props([
    'title' => null,
    'address' => null,
    'height' => '300px',
    'zoom' => 15,
    'id' => 'google-map-location-viewer'
])

{{-- Set API key globally for JavaScript --}}
<script>
    window.GOOGLE_MAPS_API_KEY = '{{ config("services.google_maps.api_key") }}';
</script>

<div
    class="google-maps-location-viewer"
    x-data="googleMapsLocationViewer('{{ $id }}', '{{ addslashes($title ?? '') }}', '{{ addslashes($address ?? '') }}', {{ $zoom }}, '{{ config("services.google_maps.api_key") }}')"
    x-init="init()"
>
    <!-- Static Map Container -->
    <div
        id="{{ $id }}"
        class="relative rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
        style="height: {{ $height }}; width: 100%;"
    >
        <!-- Loading placeholder -->
        <div class="absolute inset-0 bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
            <div class="text-sm text-gray-500 dark:text-gray-400">Loading map...</div>
        </div>
    </div>

</div>
