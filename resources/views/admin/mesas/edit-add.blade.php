@extends('voyager::master')

@section('page_title', ($mesa->exists ? 'Editar' : 'Agregar') . ' Mesa')

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-list"></i>
            {{ $mesa->exists ? 'Editar Mesa' : 'Agregar Mesa' }}
        </h1>
        <a href="{{ route('admin.mesas.index') }}" class="btn btn-warning btn-add-new">
            <i class="voyager-list"></i> <span>Volver a la lista</span>
        </a>
    </div>
@stop

@section('content')
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        <form method="POST" action="{{ $mesa->exists ? route('admin.mesas.update', $mesa->id_mesa) : route('admin.mesas.store') }}">
            @csrf
            @if($mesa->exists)
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
                            <label for="codigo_tse">Código TSE <span class="text-danger">* (11 dígitos)</span></label>
                            <input type="text" name="codigo_tse" id="codigo_tse" class="form-control"
                                   placeholder="Ej: 00001010001" maxlength="11"
                                   value="{{ old('codigo_tse', $mesa->codigo_tse) }}" required>
                            <small class="text-muted">Debe contener exactamente 11 números para cumplir la sintonía del sistema.</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="numero_mesa">Número de Mesa <span class="text-danger">*</span></label>
                            <input type="number" name="numero_mesa" id="numero_mesa" class="form-control"
                                   min="1" placeholder="Ej: 1"
                                   value="{{ old('numero_mesa', $mesa->numero_mesa) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="id_recinto">Recinto <span class="text-danger">*</span></label>
                        <select name="id_recinto" id="id_recinto" class="form-control select2" required>
                            <option value="">-- Seleccione un recinto --</option>
                            @foreach($recintos ?? [] as $recinto)
                                <option value="{{ $recinto->id_recinto }}"
                                    @if(old('id_recinto', $mesa->id_recinto) == $recinto->id_recinto) selected @endif>
                                    {{ $recinto->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="estado">Estado <span class="text-danger">*</span></label>
                        <select name="estado" id="estado" class="form-control" required>
                            <option value="">-- Seleccione un estado --</option>
                            @foreach(['Habilitada', 'Escrutada', 'Anulada', 'Observada'] as $estado)
                                <option value="{{ $estado }}"
                                    @if(old('estado', $mesa->estado) == $estado) selected @endif>
                                    {{ $estado }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="voyager-check"></i> {{ $mesa->exists ? 'Actualizar Mesa' : 'Guardar Mesa' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
@stop
