@extends('voyager::master')

@section('page_title', ($candidato->exists ? 'Editar' : 'Agregar') . ' Candidato')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-people"></i>
            {{ $candidato->exists ? 'Editar Candidato' : 'Agregar Candidato' }}
        </h1>
        <a href="{{ route('admin.candidatos.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $candidato->exists ? route('admin.candidatos.update', $candidato->id_candidato) : route('admin.candidatos.store') }}" enctype="multipart/form-data">
            @csrf
            @if($candidato->exists)
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
                            <label for="nombre_completo">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" value="{{ old('nombre_completo', $candidato->nombre_completo) }}" required>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="ci">CI <span class="text-danger">*</span></label>
                            <input type="text" name="ci" id="ci" class="form-control" value="{{ old('ci', $candidato->ci) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="imagen">Imagen</label>
                        <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
                        @if($candidato->imagen)
                            <div style="margin-top: 10px;">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($candidato->imagen) }}" alt="Imagen Candidato" style="max-width: 200px;">
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="id_partido">Partido <span class="text-danger">*</span></label>
                            <select name="id_partido" id="id_partido" class="form-control" required>
                                <option value="">-- Seleccione un partido --</option>
                                @foreach($partidos ?? [] as $partido)
                                    <option value="{{ $partido->id_partido }}" @if(old('id_partido', $candidato->id_partido) == $partido->id_partido) selected @endif>{{ $partido->sigla }} - {{ $partido->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="id_cargo">Cargo <span class="text-danger">*</span></label>
                            <select name="id_cargo" id="id_cargo" class="form-control" required>
                                <option value="">-- Seleccione un cargo --</option>
                                @foreach($cargos ?? [] as $cargo)
                                    <option value="{{ $cargo->id_cargo }}" @if(old('id_cargo', $candidato->id_cargo) == $cargo->id_cargo) selected @endif>{{ $cargo->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_geografia_postulacion">Geografía de Postulación <span class="text-danger">*</span></label>
                        <select name="id_geografia_postulacion" id="id_geografia_postulacion" class="form-control" required>
                            <option value="">-- Seleccione una geografía --</option>
                            @foreach($geografias ?? [] as $geografia)
                                <option value="{{ $geografia->id_geografia }}" @if(old('id_geografia_postulacion', $candidato->id_geografia_postulacion) == $geografia->id_geografia) selected @endif>{{ $geografia->nombre }} ({{ $geografia->tipo }})</option>
                                @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="estado" class="form-control" required>
                            <option value="">-- Seleccione un estado --</option>
                            <option value="Activo" @if(old('estado', $candidato->estado) == 'Activo') selected @endif>Activo</option>
                            <option value="Inactivo" @if(old('estado', $candidato->estado) == 'Inactivo') selected @endif>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $candidato->exists ? 'Actualizar Candidato' : 'Guardar Candidato' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop
