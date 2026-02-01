@php
$mapId = $mapId ?? 'mapaPicker';
$inputLat = $inputLat ?? 'lat';
$inputLon = $inputLon ?? 'lon';
$defaultLat = $defaultLat ?? -16.290154;
$defaultLon = $defaultLon ?? -63.588653;
@endphp

<div class="form-group">
    <label>{{ $label ?? 'Seleccionar ubicación en el mapa' }}</label>
    <div id="{{ $mapId }}" style="height: 350px; border: 1px solid #ccc; border-radius: 4px;"></div>
    <small class="form-text text-muted">Haz clic en el mapa para seleccionar la ubicación</small>
</div>

<div class="row" style="margin-top: 10px;">
    <div class="col-md-6">
        <label>Latitud</label>
        <input type="number" step="0.000001" name="{{ $inputLat }}" id="{{ $inputLat }}" class="form-control"
               value="{{ old($inputLat, $lat ?? '') }}" placeholder="{{ $defaultLat }}">
    </div>
    <div class="col-md-6">
        <label>Longitud</label>
        <input type="number" step="0.000001" name="{{ $inputLon }}" id="{{ $inputLon }}" class="form-control"
               value="{{ old($inputLon, $lon ?? '') }}" placeholder="{{ $defaultLon }}">
    </div>
</div>

<button type="button" id="btnMiUbicacion_{{ $mapId }}" class="btn btn-info btn-sm" style="margin-top: 10px;">
    <i class="voyager-location"></i> Usar mi ubicación actual
</button>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

@push('javascript')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let map{{ $mapId }}, marker{{ $mapId }};
    const defaultCenter = [{{ $defaultLat }}, {{ $defaultLon }}];

    function initMap{{ $mapId }}() {
        map{{ $mapId }} = L.map('{{ $mapId }}').setView(defaultCenter, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map{{ $mapId }});

        const lat = parseFloat($('#{{ $inputLat }}').val()) || defaultCenter[0];
        const lon = parseFloat($('#{{ $inputLon }}').val()) || defaultCenter[1];

        if (!isNaN(lat) && !isNaN(lon)) {
            map{{ $mapId }}.setView([lat, lon], 13);
            marker{{ $mapId }} = L.marker([lat, lon]).addTo(map{{ $mapId }});
        }

        map{{ $mapId }}.on('click', function(ev) {
            const {lat, lng} = ev.latlng;
            $('#{{ $inputLat }}').val(lat.toFixed(6));
            $('#{{ $inputLon }}').val(lng.toFixed(6));
            if (marker{{ $mapId }}) map{{ $mapId }}.removeLayer(marker{{ $mapId }});
            marker{{ $mapId }} = L.marker([lat, lng]).addTo(map{{ $mapId }});
        });
    }

    $('#btnMiUbicacion_{{ $mapId }}').on('click', function() {
        if (!navigator.geolocation) {
            alert('Tu navegador no soporta geolocalización');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                $('#{{ $inputLat }}').val(lat.toFixed(6));
                $('#{{ $inputLon }}').val(lon.toFixed(6));
                if (marker{{ $mapId }}) map{{ $mapId }}.removeLayer(marker{{ $mapId }});
                marker{{ $mapId }} = L.marker([lat, lon]).addTo(map{{ $mapId }});
                map{{ $mapId }}.setView([lat, lon], 15);
            },
            function(err) {
                alert('No se pudo obtener tu ubicación: ' + err.message);
            },
            {enableHighAccuracy: true, timeout: 10000}
        );
    });

    initMap{{ $mapId }}();
});
</script>
@endpush
