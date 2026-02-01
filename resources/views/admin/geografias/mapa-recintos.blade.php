@extends('voyager::master')

@section('page_title', 'Mapa de Recintos - {{ $geografia->nombre }}')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-location"></i>
                Recintos en {{ $geografia->nombre }}
            </h3>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <i class="voyager-info"></i>
                        Mostrando <strong id="total-recintos">{{ $geografia->recintos->count() }}</strong> recintos
                        en {{ $geografia->nombre }} ({{ $geografia->tipo }})
                    </div>
                </div>
            </div>

            <div id="mapaRecintos" style="height: 600px; border: 1px solid #ddd; border-radius: 8px;"></div>
        </div>

        <div class="panel-footer">
            <a href="{{ route('admin.geografias.show', $geografia) }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>
            @can('update', $geografia)
                <a href="{{ route('admin.geografias.edit', $geografia) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endsection

@push('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/mapa-config.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const map = L.map('mapaRecintos').setView([{{ $geografia->limite?->centro_latitud ?? -16.290154 }}, {{ $geografia->limite?->centro_longitud ?? -63.588653 }}], 10);

    const baseMaps = {
        "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap'
        }),
        "Satélite": L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri'
        }),
        "Terreno": L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data: &copy; OpenStreetMap'
        })
    };

    baseMaps["OpenStreetMap"].addTo(map);
    L.control.layers(baseMaps).addTo(map);

    const recintoIcon = MapaConfig.crearIcono('recinto');

    try {
        const response = await fetch('{{ route('api.mapas.recintos', $geografia) }}', {
            credentials: 'include',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();

        data.recintos.forEach(recinto => {
            const marker = L.marker([recinto.lat, recinto.lon], { icon: recintoIcon }).addTo(map);

            const popupContent = MapaConfig.crearPopup({
                nombre: recinto.nombre,
                codigo_tse: recinto.codigo_tse,
                direccion: recinto.direccion
            });

            marker.bindPopup(popupContent);
        });

        if (data.recintos.length > 0) {
            const group = new L.featureGroup(
                data.recintos.map(r => L.marker([r.lat, r.lon]))
            );
            map.fitBounds(group.getBounds(), { padding: [50, 50] });
        }

        $('#total-recintos').text(data.total);
    } catch (error) {
        console.error('Error al cargar recintos:', error);
        alert('Error al cargar los recintos en el mapa');
    }
});
</script>
@endpush
