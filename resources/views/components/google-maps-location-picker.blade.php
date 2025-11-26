@props([
    'latitude' => null,
    'longitude' => null,
    'address' => null,
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
    x-data="googleMapsLocationPicker('{{ $id }}', {{ $latitude ?? 'null' }}, {{ $longitude ?? 'null' }}, '{{ addslashes($address ?? '') }}', {{ $zoom }}, '{{ config("services.google_maps.api_key") }}')"
    x-init="init()"
>
    <!-- Map Container -->
    <div
        id="{{ $id }}"
        wire:ignore
        style="height: {{ $height }}; width: 100%;"
        class="rounded-lg border border-gray-200 dark:border-gray-700"
    ></div>

</div>
