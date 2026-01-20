@extends('voyager::master')

@section('page_title', ($geografia->exists ? 'Editar' : 'Agregar') . ' Geografía')

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
                            <label for="parent_id">Geografía Padre</label>
                            <select name="parent_id" id="parent_id" class="form-control">
                                <option value="">-- Sin padre --</option>
                                @foreach($parents as $id => $nombre)
                                    <option value="{{ $id }}" @if(old('parent_id', $geografia->parent_id) == $id) selected @endif>{{ $nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $geografia->exists ? 'Actualizar Geografía' : 'Guardar Geografía' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop
