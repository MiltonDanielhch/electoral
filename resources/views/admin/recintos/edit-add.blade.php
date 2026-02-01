@extends('voyager::master')

@section('page_title', ($recinto->exists ? 'Editar' : 'Agregar') . ' Recinto')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-home"></i>
            {{ $recinto->exists ? 'Editar Recinto' : 'Agregar Recinto' }}
        </h1>
        <a href="{{ route('admin.recintos.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $recinto->exists ? route('admin.recintos.update', $recinto->id_recinto) : route('admin.recintos.store') }}">
            @csrf
            @if($recinto->exists)
                @method('PUT')
            @endif
            <div class="panel panel-bordered">
                <div class="panel-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="codigo_tse">Código TSE <span class="text-danger">*</span></label>
                        <input type="text" name="codigo_tse" id="codigo_tse" class="form-control" value="{{ old('codigo_tse', $recinto->codigo_tse) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $recinto->nombre) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" name="direccion" id="direccion" class="form-control" value="{{ old('direccion', $recinto->direccion) }}">
                    </div>

                    <div class="form-group">
                        <label for="id_geografia">Municipio <span class="text-danger">*</span></label>
                        <select name="id_geografia" id="id_geografia" class="form-control" required>
                            <option value="">-- Seleccione un municipio --</option>
                            @foreach($geografias ?? [] as $geografia)
                                <option value="{{ $geografia->id_geografia }}" @if(old('id_geografia', $recinto->id_geografia) == $geografia->id_geografia) selected @endif>{{ $geografia->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Ubicación en el Mapa</label>
                        <div id="mapaRecinto" style="height: 350px; border: 1px solid #ccc; border-radius: 4px;"></div>
                        <small class="form-text text-muted">Haz clic en el mapa para seleccionar la ubicación</small>
                    </div>

                    <div class="row" style="margin-top: 10px;">
                        <div class="col-md-6">
                            <label>Latitud</label>
                            <input type="number" step="0.000001" name="latitud" id="latitud" class="form-control"
                                   value="{{ old('latitud', $recinto->latitud) }}" placeholder="-16.290154">
                        </div>
                        <div class="col-md-6">
                            <label>Longitud</label>
                            <input type="number" step="0.000001" name="longitud" id="longitud" class="form-control"
                                   value="{{ old('longitud', $recinto->longitud) }}" placeholder="-63.588653">
                        </div>
                    </div>

                    <button type="button" id="btnMiUbicacion" class="btn btn-info btn-sm" style="margin-top: 10px;">
                        <i class="voyager-location"></i> Usar mi ubicación actual
                    </button>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $recinto->exists ? 'Actualizar Recinto' : 'Guardar Recinto' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endsection

@push('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let mapaRecinto, markerRecinto;
    const defaultCenter = [-16.290154, -63.588653];

    function initMapaRecinto() {
        mapaRecinto = L.map('mapaRecinto').setView(defaultCenter, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(mapaRecinto);

        const lat = parseFloat(document.getElementById('latitud').value) || defaultCenter[0];
        const lon = parseFloat(document.getElementById('longitud').value) || defaultCenter[1];

        if (!isNaN(lat) && !isNaN(lon)) {
            mapaRecinto.setView([lat, lon], 13);
            markerRecinto = L.marker([lat, lon]).addTo(mapaRecinto);
        }

        mapaRecinto.on('click', function(ev) {
            const {lat, lng} = ev.latlng;
            document.getElementById('latitud').value = lat.toFixed(6);
            document.getElementById('longitud').value = lng.toFixed(6);
            if (markerRecinto) mapaRecinto.removeLayer(markerRecinto);
            markerRecinto = L.marker([lat, lng]).addTo(mapaRecinto);
        });
    }

    document.getElementById('btnMiUbicacion').addEventListener('click', function() {
        if (!navigator.geolocation) {
            alert('Tu navegador no soporta geolocalización');
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude;
                const lon = pos.coords.longitude;
                document.getElementById('latitud').value = lat.toFixed(6);
                document.getElementById('longitud').value = lon.toFixed(6);
                if (markerRecinto) mapaRecinto.removeLayer(markerRecinto);
                markerRecinto = L.marker([lat, lon]).addTo(mapaRecinto);
                mapaRecinto.setView([lat, lon], 15);
            },
            function(err) {
                alert('No se pudo obtener tu ubicación: ' + err.message);
            },
            {enableHighAccuracy: true, timeout: 10000}
        );
    });

    initMapaRecinto();
});
</script>
@endpush
