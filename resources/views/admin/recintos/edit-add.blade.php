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
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}"/>
<style>
    /* Validación visual de geofencing */
    .coordenadas-validas {
        border-color: #28a745 !important;
        background-color: #d4edda !important;
    }
    .coordenadas-invalidas {
        border-color: #dc3545 !important;
        background-color: #f8d7da !important;
    }
    .geofencing-alert {
        display: none;
        padding: 8px 12px;
        margin-top: 5px;
        border-radius: 4px;
        font-size: 12px;
    }
    .geofencing-alert.error {
        display: block;
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
</style>
@endsection

@push('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script src="{{ asset('js/mapa-config.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sintonía: Geofencing - Límites del Departamento del Beni
    const LIMITES_BENI = {
        lat: { min: -16.5, max: -10.0 },
        lon: { min: -68.0, max: -60.0 }
    };

    let sintoniaMap, markerRecinto;
    const defaultCenter = [-16.290154, -63.588653];

    /**
     * Valida si las coordenadas están dentro del Beni
     */
    function validarGeofencing(lat, lon) {
        const latValida = lat >= LIMITES_BENI.lat.min && lat <= LIMITES_BENI.lat.max;
        const lonValida = lon >= LIMITES_BENI.lon.min && lon <= LIMITES_BENI.lon.max;
        return latValida && lonValida;
    }

    /**
     * Actualiza la UI según la validación
     */
    function actualizarValidacionUI(esValido, mensaje = '') {
        const latInput = document.getElementById('latitud');
        const lonInput = document.getElementById('longitud');
        
        // Remover clases previas
        latInput.classList.remove('coordenadas-validas', 'coordenadas-invalidas');
        lonInput.classList.remove('coordenadas-validas', 'coordenadas-invalidas');
        
        // Agregar nueva clase
        if (esValido) {
            latInput.classList.add('coordenadas-validas');
            lonInput.classList.add('coordenadas-validas');
        } else {
            latInput.classList.add('coordenadas-invalidas');
            lonInput.classList.add('coordenadas-invalidas');
        }

        // Mostrar/ocultar mensaje de error
        let alertDiv = document.getElementById('geofencing-alert');
        if (!alertDiv) {
            alertDiv = document.createElement('div');
            alertDiv.id = 'geofencing-alert';
            alertDiv.className = 'geofencing-alert';
            lonInput.parentNode.appendChild(alertDiv);
        }

        if (!esValido && mensaje) {
            alertDiv.textContent = mensaje;
            alertDiv.classList.add('error');
        } else {
            alertDiv.classList.remove('error');
        }
    }

    /**
     * Valida coordenadas en tiempo real
     */
    function validarCoordenadas() {
        const lat = parseFloat(document.getElementById('latitud').value);
        const lon = parseFloat(document.getElementById('longitud').value);

        if (!isNaN(lat) && !isNaN(lon)) {
            const esValido = validarGeofencing(lat, lon);
            if (!esValido) {
                actualizarValidacionUI(false, '⚠️ Coordenadas fuera del Departamento del Beni. Rango válido: Lat (-16.50 a -10.00), Lon (-68.00 a -60.00)');
            } else {
                actualizarValidacionUI(true);
            }
            return esValido;
        }
        return false;
    }

    function initMapaRecinto() {
        // Usar SintoniaMap para inicialización modular
        sintoniaMap = new SintoniaMap('mapaRecinto', {
            center: defaultCenter,
            zoom: 13,
            zoomControl: true,
            attributionControl: true
        });
        
        sintoniaMap.init();

        const lat = parseFloat(document.getElementById('latitud').value) || defaultCenter[0];
        const lon = parseFloat(document.getElementById('longitud').value) || defaultCenter[1];

        if (!isNaN(lat) && !isNaN(lon) && validarGeofencing(lat, lon)) {
            sintoniaMap.centrarEn(lat, lon, 13);
            markerRecinto = sintoniaMap.agregarMarcador(lat, lon);
        }

        sintoniaMap.map.on('click', function(ev) {
            const {lat, lng} = ev.latlng;
            
            // Validar antes de asignar
            if (!validarGeofencing(lat, lng)) {
                actualizarValidacionUI(false, '⚠️ No puedes seleccionar una ubicación fuera del Departamento del Beni');
                return;
            }
            
            document.getElementById('latitud').value = lat.toFixed(6);
            document.getElementById('longitud').value = lng.toFixed(6);
            
            if (markerRecinto) sintoniaMap.map.removeLayer(markerRecinto);
            markerRecinto = sintoniaMap.agregarMarcador(lat, lng);
            
            actualizarValidacionUI(true);
        });

        // Validar en cambios manuales
        document.getElementById('latitud').addEventListener('change', validarCoordenadas);
        document.getElementById('longitud').addEventListener('change', validarCoordenadas);
        document.getElementById('latitud').addEventListener('blur', validarCoordenadas);
        document.getElementById('longitud').addEventListener('blur', validarCoordenadas);
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
                
                // Validar geofencing
                if (!validarGeofencing(lat, lon)) {
                    actualizarValidacionUI(false, '⚠️ Tu ubicación actual está fuera del Departamento del Beni. Por favor, selecciona manualmente la ubicación correcta del recinto.');
                    return;
                }
                
                document.getElementById('latitud').value = lat.toFixed(6);
                document.getElementById('longitud').value = lon.toFixed(6);
                if (markerRecinto) sintoniaMap.map.removeLayer(markerRecinto);
                markerRecinto = sintoniaMap.agregarMarcador(lat, lon);
                sintoniaMap.centrarEn(lat, lon, 15);
                actualizarValidacionUI(true);
            },
            function(err) {
                alert('No se pudo obtener tu ubicación: ' + err.message);
            },
            {enableHighAccuracy: true, timeout: 10000}
        );
    });

    initMapaRecinto();
    
    // Validar coordenadas iniciales
    validarCoordenadas();
});
</script>
@endpush
