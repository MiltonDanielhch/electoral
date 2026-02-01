@extends('voyager::master')

@section('page_title', 'Mapa de Resultados Electorales')

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-bordered">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="voyager-bar-chart"></i>
                        Resultados Electorales por Geografía
                    </h3>
                </div>

                <div class="panel-body">
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-4">
                            <label>Tipo de Geografía:</label>
                            <select id="filtroTipo" class="form-control">
                                <option value="Departamento">Departamentos</option>
                                <option value="Provincia">Provincias</option>
                                <option value="Municipio">Municipios</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Cargo:</label>
                            <select id="filtroCargo" class="form-control">
                                <option value="">Todos</option>
                                @foreach(\App\Models\Cargo::all() as $cargo)
                                    <option value="{{ $cargo->id_cargo }}">{{ $cargo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Referencia:</label>
                            <div style="padding-top: 10px;">
                                <span class="badge" style="background-color: #009739; color: white;">MAS</span>
                                <span class="badge" style="background-color: #0066cc; color: white;">CC</span>
                                <span class="badge" style="background-color: #ffcc00; color: black;">FPV</span>
                                <span class="badge" style="background-color: #cccccc; color: black;">Sin datos</span>
                            </div>
                        </div>
                    </div>

                    <div id="mapaResultados" style="height: 600px; border: 1px solid #ddd; border-radius: 8px;"></div>

                    <div id="infoPanel" style="margin-top: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 4px; display: none;">
                        <h4 id="infoNombre"></h4>
                        <p><strong>Votos Válidos:</strong> <span id="infoVotosValidos">0</span></p>
                        <p><strong>Votos Blancos:</strong> <span id="infoVotosBlancos">0</span></p>
                        <p><strong>Votos Nulos:</strong> <span id="infoVotosNulos">0</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css"/>
@endsection

@push('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script src="{{ asset('js/mapa-config.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('mapaResultados').setView([MapaConfig.bolivia.lat, MapaConfig.bolivia.lon], MapaConfig.bolivia.zoom);

    const baseMaps = {
        "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }),
        "Satélite": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 19
        }),
        "Terreno": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: &copy; OpenStreetMap',
            maxZoom: 17
        })
    };

    baseMaps["OpenStreetMap"].addTo(map);
    L.control.layers(baseMaps).addTo(map);

    L.Control.geocoder({
        defaultMarkGeocode: false,
        geocoder: L.Control.Geocoder.nominatim({
            geocodingQueryParams: {
                countrycodes: 'BO'
            }
        })
    }).on('markgeocode', function(e) {
        const bbox = e.geocode.bbox;
        const poly = L.polygon([
            [bbox.getSouthEast().lat, bbox.getSouthEast().lng],
            [bbox.getNorthEast().lat, bbox.getNorthEast().lng],
            [bbox.getNorthWest().lat, bbox.getNorthWest().lng],
            [bbox.getSouthWest().lat, bbox.getSouthWest().lng]
        ]);
        map.fitBounds(poly.getBounds());
    }).addTo(map);

    let geoJsonLayer = null;

    async function cargarResultados(tipo, cargoId) {
        const url = `/api/public/mapas/resultados?tipo=${tipo}`;
        const response = await fetch(url);
        const data = await response.json();

        if (geoJsonLayer) {
            map.removeLayer(geoJsonLayer);
        }

        geoJsonLayer = L.geoJson(data, {
            style: MapaConfig.estiloPoligono,
            onEachFeature: function(feature, layer) {
                layer.on({
                    mouseover: function(e) {
                        layer.setStyle(MapaConfig.estiloPoligonoHover);
                        layer.bringToFront();

                        const props = feature.properties;
                        $('#infoNombre').text(`${props.nombre} (${props.tipo})`);
                        $('#infoVotosValidos').text(props.votos_validos);
                        $('#infoVotosBlancos').text(props.votos_blancos);
                        $('#infoVotosNulos').text(props.votos_nulos);
                        $('#infoPanel').show();
                    },
                    mouseout: function(e) {
                        geoJsonLayer.resetStyle(e.target);
                        $('#infoPanel').hide();
                    },
                    click: function(e) {
                        map.fitBounds(e.target.getBounds());
                    }
                });

                const popupContent = MapaConfig.crearPopup({
                    nombre: feature.properties.nombre,
                    tipo: feature.properties.tipo,
                    votos_validos: feature.properties.votos_validos,
                    votos_blancos: feature.properties.votos_blancos,
                    votos_nulos: feature.properties.votos_nulos,
                    ganador: feature.properties.ganador || 'No definido'
                });

                layer.bindPopup(popupContent);
            }
        }).addTo(map);

        if (data.features.length > 0) {
            map.fitBounds(geoJsonLayer.getBounds(), { padding: [20, 20] });
        }
    }

    $('#filtroTipo').on('change', function() {
        cargarResultados($(this).val(), $('#filtroCargo').val());
    });

    $('#filtroCargo').on('change', function() {
        cargarResultados($('#filtroTipo').val(), $(this).val());
    });

    cargarResultados('Departamento', '');
});
</script>
@endpush
