@extends('voyager::master')

@section('page_title', 'Ver Recinto')

@section('content')
<div class="page-content container-fluid">
    <div class="row">
        <div class="col-md-6">
            <div class="panel panel-bordered panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="voyager-eye"></i> Información del Recinto
                    </h3>
                </div>

                <div class="panel-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 250px;">ID</th>
                                <td>{{ $recinto->id_recinto }}</td>
                            </tr>
                            <tr>
                                <th>Código TSE</th>
                                <td>{{ $recinto->codigo_tse }}</td>
                            </tr>
                            <tr>
                                <th>Nombre</th>
                                <td>{{ $recinto->nombre }}</td>
                            </tr>
                            <tr>
                                <th>Dirección</th>
                                <td>{{ $recinto->direccion ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Municipio</th>
                                <td>
                                    @if($recinto->geografia)
                                        {{ $recinto->geografia->nombre }}
                                        @if($recinto->geografia->parent)
                                            <br><small>{{ $recinto->geografia->parent->nombre }}</small>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @if($recinto->latitud && $recinto->longitud)
                            <tr>
                                <th>Latitud</th>
                                <td>{{ number_format($recinto->latitud, 6) }}</td>
                            </tr>
                            <tr>
                                <th>Longitud</th>
                                <td>{{ number_format($recinto->longitud, 6) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($recinto->latitud && $recinto->longitud)
        <div class="col-md-6">
            <div class="panel panel-bordered panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="voyager-location"></i> Ubicación en el Mapa
                    </h3>
                </div>

                <div class="panel-body" style="padding: 0;">
                    <div id="mapaRecinto" style="height: 450px; border-radius: 4px;"></div>
                </div>

                <div class="panel-footer" style="text-align: center;">
                    <a href="https://www.google.com/maps?q={{ $recinto->latitud }},{{ $recinto->longitud }}" target="_blank" class="btn btn-info btn-sm">
                        <i class="voyager-external"></i> Ver en Google Maps
                    </a>
                    <a href="https://www.openstreetmap.org/?mlat={{ $recinto->latitud }}&mlon={{ $recinto->longitud }}&zoom=15" target="_blank" class="btn btn-default btn-sm">
                        <i class="voyager-external"></i> Ver en OpenStreetMap
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="col-md-6">
            <div class="panel panel-bordered panel-warning">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="voyager-warning"></i> Sin Ubicación
                    </h3>
                </div>

                <div class="panel-body" style="padding: 40px; text-align: center;">
                    <i class="voyager-location voyager-4x" style="color: #ddd; margin-bottom: 20px;"></i>
                    <h4>Este recinto no tiene coordenadas registradas</h4>
                    <p class="text-muted">Para mostrar la ubicación en el mapa, primero debes agregar las coordenadas del recinto.</p>
                    <br>
                    @can('update', $recinto)
                        <a href="{{ route('admin.recintos.edit', $recinto) }}" class="btn btn-primary">
                            <i class="voyager-edit"></i> Agregar Ubicación
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-md-12">
            <div class="panel panel-bordered panel-success">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <i class="voyager-list"></i> Relaciones
                    </h3>
                </div>

                <div class="panel-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th style="width: 250px;">Mesas Asociadas</th>
                                <td>
                                    @if($recinto->mesas->count() > 0)
                                        <span class="label label-info">{{ $recinto->mesas->count() }} mesas</span>
                                        <ul style="margin-top: 10px;">
                                            @foreach($recinto->mesas->take(10) as $mesa)
                                                <li>Mesa {{ $mesa->numero_mesa }} ({{ $mesa->electores ?? '-' }} electores)</li>
                                            @endforeach
                                            @if($recinto->mesas->count() > 10)
                                                <li>... y {{ $recinto->mesas->count() - 10 }} más</li>
                                            @endif
                                        </ul>
                                    @else
                                        <span class="label label-default">Sin mesas</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-md-12 text-right">
            <a href="{{ route('admin.recintos.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver a la lista
            </a>

            @can('update', $recinto)
                <a href="{{ route('admin.recintos.edit', $recinto) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar Recinto
                </a>
            @endcan

            <button title="Borrar" class="btn btn-danger delete" data-id="{{ $recinto->id_recinto }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.recintos.destroy', $recinto->id_recinto) }}', '{{ $recinto->nombre }}')">
                <i class="voyager-trash"></i> Borrar
            </button>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endsection

@push('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
@if($recinto->latitud && $recinto->longitud)
document.addEventListener('DOMContentLoaded', function() {
    const lat = {{ $recinto->latitud }};
    const lon = {{ $recinto->longitud }};

    const map = L.map('mapaRecinto', {
        center: [lat, lon],
        zoom: 16
    });

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

    const marker = L.marker([lat, lon], {
        title: '{{ $recinto->nombre }}',
        draggable: false
    }).addTo(map);

    const popupContent = `
        <div style="min-width: 200px;">
            <h4 style="margin: 0 0 10px 0;">{{ $recinto->nombre }}</h4>
            <strong>Código TSE:</strong> {{ $recinto->codigo_tse }}<br>
            <strong>Dirección:</strong> {{ $recinto->direccion ?? '-' }}<br>
            <strong>Municipio:</strong> {{ $recinto->geografia ? $recinto->geografia->nombre : '-' }}<br>
            <strong>Coordenadas:</strong> ${lat.toFixed(6)}, ${lon.toFixed(6)}
        </div>
    `;

    marker.bindPopup(popupContent);
    marker.openPopup();
});
@endif

function deleteItem(url, itemName) {
    $('#delete_form').attr('action', url);
    $('#delete_modal .modal-title').html(`<i class="voyager-trash"></i> ¿Estás seguro de que quieres eliminar "${itemName}"?`);
}
</script>
@endpush

<div id="delete-modal-wrapper" style="display:none;">
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> ¿Desea eliminar este recinto?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        @method('DELETE') @csrf
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('delete-modal-wrapper').style.display = '';
});
</script>
