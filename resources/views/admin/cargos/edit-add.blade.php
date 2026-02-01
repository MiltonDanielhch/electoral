@extends('voyager::master')

@section('page_title', ($cargo->exists ? 'Editar' : 'Agregar') . ' Cargo')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-archive"></i>
            {{ $cargo->exists ? 'Editar Cargo' : 'Agregar Cargo' }}
        </h1>
        <a href="{{ route('admin.cargos.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $cargo->exists ? route('admin.cargos.update', $cargo->id_cargo) : route('admin.cargos.store') }}">
            @csrf
            @if($cargo->exists)
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
                        <label for="descripcion">Descripción <span class="text-danger">*</span></label>
                        <input type="text" name="descripcion" id="descripcion" class="form-control" value="{{ old('descripcion', $cargo->descripcion) }}" required>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label for="nivel">Nivel <span class="text-danger">*</span></label>
                            <select name="nivel" id="nivel" class="form-control" required>
                                <option value="">-- Seleccione un nivel --</option>
                                <option value="D" @if(old('nivel', $cargo->nivel) == 'D') selected @endif>Departamental (D)</option>
                                <option value="P" @if(old('nivel', $cargo->nivel) == 'P') selected @endif>Provincial (P)</option>
                                <option value="M" @if(old('nivel', $cargo->nivel) == 'M') selected @endif>Municipal (M)</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="tipo_acta">Tipo de Acta <span class="text-danger">*</span></label>
                            <select name="tipo_acta" id="tipo_acta" class="form-control" required>
                                <option value="">-- Seleccione un tipo --</option>
                                <option value="Normal" @if(old('tipo_acta', $cargo->tipo_acta) == 'Normal') selected @endif>Normal</option>
                                <option value="Especial" @if(old('tipo_acta', $cargo->tipo_acta) == 'Especial') selected @endif>Especial</option>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="acta_unica">Acta Única <span class="text-danger">*</span></label>
                            <select name="acta_unica" id="acta_unica" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                            <option value="1" @if(old('acta_unica', $cargo->acta_unica) == true) selected @endif>Sí</option>
                            <option value="0" @if(old('acta_unica', $cargo->acta_unica) == false) selected @endif>No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $cargo->exists ? 'Actualizar Cargo' : 'Guardar Cargo' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop
