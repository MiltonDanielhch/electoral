@extends('voyager::master')

@section('page_title', ($geografia->exists ? 'Editar' : 'Agregar') . ' Geografía')

@section('css')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #map { height: 300px; width: 100%; border-radius: 5px; margin-top: 10px; border: 1px solid #ddd; }
    </style>
@stop

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-location"></i>
            {{ $geografia->exists ? 'Editar Geografía' : 'Agregar Geografía' }}
        </h1>
        <a href="{{ route('admin.geografias.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $geografia->exists ? route('admin.geografias.update', $geografia->id_geografia) : route('admin.geografias.store') }}">
            @csrf
            @if($geografia->exists)
                @method('PUT')
            @endif
            <div class="row">
                <div class="col-md-8">
                    <div class="panel panel-bordered">
                        <div class="panel-body">
                            <div class="form-group">
                                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $geografia->nombre) }}" required>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="codigo_tse">Código TSE <span class="text-danger">*</span></label>
                                    <input type="text" name="codigo_tse" id="codigo_tse" class="form-control" value="{{ old('codigo_tse', $geografia->codigo_tse) }}" required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="tipo">Tipo <span class="text-danger">*</span></label>
                                    <select name="tipo" id="tipo" class="form-control" required>
                                        <option value="">-- Seleccione un tipo --</option>
                                        @foreach($tipos as $tipo)
                                            <option value="{{ $tipo }}" @if(old('tipo', $geografia->tipo) == $tipo) selected @endif>{{ $tipo }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="parent_id" id="label_parent">Ubicación Superior (Donde se encuentra)</label>
                                    <select name="parent_id" id="parent_id" class="form-control select2">
                                        <option value="">-- Es una ubicación principal --</option>
                                        @foreach($parents as $id => $nombre)
                                            <option value="{{ $id }}" @if(old('parent_id', $geografia->parent_id) == $id) selected @endif>
                                                {{ $nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted" id="parent_help">Ej: Si es un municipio, elige su provincia.</small>
                                </div>
                            </div>

                            <hr>
                            <h4><i class="voyager-world"></i> Ubicación Geográfica</h4>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="latitud">Latitud</label>
                                    <input type="number" step="any" name="latitud" id="latitud" class="form-control" value="{{ old('latitud', $geografia->latitud) }}" placeholder="-14.8333">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="longitud">Longitud</label>
                                    <input type="number" step="any" name="longitud" id="longitud" class="form-control" value="{{ old('longitud', $geografia->longitud) }}" placeholder="-64.9167">
                                </div>
                            </div>
                        </div>
                        <div class="panel-footer">
                            <button type="submit" class="btn btn-primary save">
                                <i class="voyager-check"></i> {{ $geografia->exists ? 'Actualizar Geografía' : 'Guardar Geografía' }}
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Panel del Mapa --}}
                <div class="col-md-4">
                    <div class="panel panel-bordered">
                        <div class="panel-heading">
                            <h3 class="panel-title">Selector de Mapa</h3>
                        </div>
                        <div class="panel-body">
                            <p class="text-muted small">Haz clic en el mapa para capturar las coordenadas automáticamente.</p>
                            <div id="map"></div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('javascript')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var isEdit = {{ $geografia->exists ? 'true' : 'false' }};
        var initialLoad = true;

        $('#tipo').on('change', function() {
            var tipo = $(this).val();
            var label = $('#label_parent');
            var help = $('#parent_help');
            var parentSelect = $('#parent_id');
            var tipoPadre = '';

            if (tipo == 'Provincia') {
                label.html('Departamento al que pertenece <span class="text-danger">*</span>');
                help.text('Las provincias siempre pertenecen a un Departamento (Ej: Beni).');
                tipoPadre = 'Departamento';
            } else if (tipo == 'Municipio') {
                label.html('Provincia a la que pertenece <span class="text-danger">*</span>');
                help.text('Los municipios siempre pertenecen a una Provincia (Ej: Cercado).');
                tipoPadre = 'Provincia';
            } else if (tipo == 'Localidad') {
                label.html('Municipio al que pertenece <span class="text-danger">*</span>');
                help.text('Las localidades pertenecen a un Municipio.');
                tipoPadre = 'Municipio';
            } else {
                label.text('Ubicación Superior');
                help.text('Selecciona la ubicación de nivel superior.');
                parentSelect.html('<option value="">-- Es una ubicación principal --</option>');
                return;
            }

            // Carga dinámica de padres vía AJAX
            $.get('{{ url("admin/geografias/ajax/parents") }}', { tipo: tipoPadre }, function(data) {
                parentSelect.empty();
                parentSelect.append('<option value="">-- Seleccione --</option>');

                // Preservar selección anterior si existe (Edición o Error de validación)
                var selectedId = "{{ old('parent_id', $geografia->parent_id) }}";

                $.each(data, function(index, item) {
                    var isSelected = (selectedId == item.id_geografia) ? 'selected' : '';
                    // Agregamos data-lat y data-lng a las opciones
                    parentSelect.append('<option value="'+item.id_geografia+'" data-lat="'+item.latitud+'" data-lng="'+item.longitud+'" '+isSelected+'>'+item.nombre+'</option>');
                });

                // Actualizar Select2 visualmente
                parentSelect.trigger('change');

                // Desactivar la bandera de carga inicial después de la primera carga
                setTimeout(function(){ initialLoad = false; }, 500);
            }).fail(function() {
                console.error('Error: No se pudo cargar la lista de ubicaciones superiores. Verifique la ruta en web.php');
            });
        });

        // Ejecutar al cargar para ediciones
        $('#tipo').trigger('change');

        $(document).ready(function() {
            $('.select2').select2();

            // Configuración del Mapa (Centrado en Trinidad, Beni por defecto)
            var lat = {{ old('latitud', $geografia->latitud ?? -14.8333) }};
            var lng = {{ old('longitud', $geografia->longitud ?? -64.9167) }};

            var map = L.map('map').setView([lat, lng], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

            // Actualizar inputs al mover el marcador
            marker.on('dragend', function(e) {
                var position = marker.getLatLng();
                $('#latitud').val(position.lat.toFixed(8));
                $('#longitud').val(position.lng.toFixed(8));
            });

            // Actualizar marcador al hacer clic en el mapa
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                $('#latitud').val(e.latlng.lat.toFixed(8));
                $('#longitud').val(e.latlng.lng.toFixed(8));
            });

            // NUEVO: Actualizar mapa al seleccionar un padre
            $('#parent_id').on('change', function() {
                // Evitar mover el mapa al cargar la página en modo edición (para no perder la ubicación real del hijo)
                if(isEdit && initialLoad) return;

                var selected = $(this).find(':selected');
                var lat = selected.data('lat');
                var lng = selected.data('lng');

                if(lat && lng) {
                    var newLatLng = new L.LatLng(lat, lng);
                    map.setView(newLatLng, 13); // Zoom un poco más cerca
                    marker.setLatLng(newLatLng);
                    $('#latitud').val(lat);
                    $('#longitud').val(lng);
                }
            });
        });
    </script>
@stop
