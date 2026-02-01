@extends('voyager::master')

@section('page_title', ($person->id ? 'Editar' : 'Crear') . ' Persona')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-person"></i>
        {{ $person->id ? 'Editar' : 'Crear' }} Persona
    </h1>
@stop

@section('content')
    <div class="page-content edit-add container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <form role="form"
                              action="{{ $person->id ? route('admin.people.update', $person->id) : route('admin.people.store') }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            @if($person->id)
                                @method('PUT')
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">Nombre <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="first_name" id="first_name" 
                                               value="{{ old('first_name', $person->first_name) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="middle_name">Segundo Nombre</label>
                                        <input type="text" class="form-control" name="middle_name" id="middle_name"
                                               value="{{ old('middle_name', $person->middle_name) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="paternal_surname">Apellido Paterno <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="paternal_surname" id="paternal_surname"
                                               value="{{ old('paternal_surname', $person->paternal_surname) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="maternal_surname">Apellido Materno</label>
                                        <input type="text" class="form-control" name="maternal_surname" id="maternal_surname"
                                               value="{{ old('maternal_surname', $person->maternal_surname) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="tipo_doc">Tipo Documento <span class="text-danger">*</span></label>
                                        <select name="tipo_doc" id="tipo_doc" class="form-control" required>
                                            <option value="CI" {{ (old('tipo_doc', $person->tipo_doc) == 'CI') ? 'selected' : '' }}>CI</option>
                                            <option value="Pasaporte" {{ (old('tipo_doc', $person->tipo_doc) == 'Pasaporte') ? 'selected' : '' }}>Pasaporte</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="ci">Nro Documento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="ci" id="ci"
                                               value="{{ old('ci', $person->ci) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="ci_complemento">Complemento</label>
                                        <input type="text" class="form-control" name="ci_complemento" id="ci_complemento"
                                               value="{{ old('ci_complemento', $person->ci_complemento) }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="birth_date">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="birth_date" id="birth_date"
                                               value="{{ old('birth_date', optional($person->birth_date)->format('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="gender">Género <span class="text-danger">*</span></label>
                                        <select name="gender" id="gender" class="form-control" required>
                                            <option value="">Seleccione</option>
                                            <option value="Masculino" {{ (old('gender', $person->gender) == 'Masculino') ? 'selected' : '' }}>Masculino</option>
                                            <option value="Femenino" {{ (old('gender', $person->gender) == 'Femenino') ? 'selected' : '' }}>Femenino</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="padron">Padrón Electoral</label>
                                        <input type="text" class="form-control" name="padron" id="padron"
                                               value="{{ old('padron', $person->padron) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status">Estado</label> <br>
                                        <input type="checkbox" name="status" id="status" class="toggleswitch"
                                               data-on="Activo" data-off="Inactivo"
                                               {{ (old('status', $person->status) == 1 || !$person->id) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" name="email" id="email"
                                               value="{{ old('email', $person->email) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Teléfono / Celular</label>
                                        <input type="text" class="form-control" name="phone" id="phone"
                                               value="{{ old('phone', $person->phone) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Dirección</label>
                                <textarea class="form-control" name="address" id="address" rows="3">{{ old('address', $person->address) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="image">Imagen</label>
                                @if($person->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $person->image) }}" width="100" class="img-thumbnail">
                                        <label class="checkbox-inline" style="margin-left: 15px;">
                                            <input type="checkbox" name="remove_image" value="1"> Eliminar imagen actual
                                        </label>
                                    </div>
                                @endif
                                <input type="file" name="image" id="image" accept="image/*" class="form-control">
                                <small class="text-muted">Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB</small>
                            </div>

                            <div class="panel-footer">
                                <button type="submit" class="btn btn-primary save">
                                    <i class="voyager-check"></i> Guardar
                                </button>
                                <a href="{{ route('admin.people.index') }}" class="btn btn-default">
                                    <i class="voyager-x"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
