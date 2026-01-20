@extends('voyager::master')

@section('page_title', ($organizacion->exists ? 'Editar' : 'Agregar') . ' Organización Política')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-group"></i>
            {{ $organizacion->exists ? 'Editar Organización Política' : 'Agregar Organización Política' }}
        </h1>
        <a href="{{ route('admin.organizaciones_politicas.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $organizacion->exists ? route('admin.organizaciones_politicas.update', $organizacion->id_partido) : route('admin.organizaciones_politicas.store') }}" enctype="multipart/form-data">
            @csrf
            @if($organizacion->exists)
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

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="codigo_tse">Código TSE <span class="text-danger">*</span></label>
                            <input type="text" name="codigo_tse" id="codigo_tse" class="form-control" value="{{ old('codigo_tse', $organizacion->codigo_tse) }}" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="sigla">Sigla <span class="text-danger">*</span></label>
                            <input type="text" name="sigla" id="sigla" class="form-control" value="{{ old('sigla', $organizacion->sigla) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="nombre" class="form-control" value="{{ old('nombre', $organizacion->nombre) }}" required>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="color_hex">Color Hexadecimal <span class="text-danger">*</span></label>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" name="color_hex" id="color_hex" class="form-control" value="{{ old('color_hex', $organizacion->color_hex) }}" required>
                                <div id="color_preview" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid #ddd; background-color: {{ old('color_hex', $organizacion->color_hex) }};"></div>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="logo_url">Logo</label>
                            @if($organizacion->logo_url)
                                <img src="{{ Storage::url($organizacion->logo_url) }}" style="width: 100px; display: block; margin-bottom: 10px;">
                            @endif
                            <input type="file" name="logo_url" id="logo_url" accept="image/*">
                        </div>

                        <div class="form-group col-md-4">
                            <label for="estado">Estado <span class="text-danger">*</span></label>
                            <select name="estado" id="estado" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <option value="Activo" @if(old('estado', $organizacion->estado) == 'Activo') selected @endif>Activo</option>
                                <option value="Inactivo" @if(old('estado', $organizacion->estado) == 'Inactivo') selected @endif>Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $organizacion->exists ? 'Actualizar Organización' : 'Guardar Organización' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function() {
            $('#color_hex').on('input', function() {
                $('#color_preview').css('background-color', $(this).val());
            });
        });
    </script>
@stop
