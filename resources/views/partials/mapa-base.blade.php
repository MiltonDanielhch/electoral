@php
$mapId = $mapId ?? 'mapa';
$zoom = $zoom ?? 6;
$lat = $lat ?? -16.290154;
$lon = $lon ?? -63.588653;
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<div id="{{ $mapId }}" style="height: {{ $height ?? '500px' }}; border-radius: 8px; border: 1px solid #ddd;"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@stack('map-scripts')

@push('map-scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const {{ $mapId }} = L.map('{{ $mapId }}').setView([{{ $lat }}, {{ $lon }}], {{ $zoom }});

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo({{ $mapId }});

    window['{{ $mapId }}Instance'] = {{ $mapId }};
});
</script>
@endpush
