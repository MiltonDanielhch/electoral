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
