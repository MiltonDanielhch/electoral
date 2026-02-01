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
                                        <label for="person_type">Tipo de Persona</label>
                                        <select name="person_type" id="person_type" class="form-control" required>
                                            <option value="Natural" {{ (old('person_type', $person->person_type) == 'Natural') ? 'selected' : '' }}>Natural</option>
                                            <option value="Jurídica" {{ (old('person_type', $person->person_type) == 'Jurídica') ? 'selected' : '' }}>Jurídica</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Estado</label> <br>
                                        <input type="checkbox" name="status" id="status" class="toggleswitch"
                                               data-on="Activo" data-off="Inactivo"
                                               {{ (old('status', $person->status) == 1 || !$person->id) ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                            {{-- Campos Persona Natural --}}
                            <div id="natural_fields">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="first_name">Nombre</label>
                                            <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $person->first_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="middle_name">Segundo Nombre</label>
                                            <input type="text" class="form-control" name="middle_name" value="{{ old('middle_name', $person->middle_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="paternal_surname">Apellido Paterno</label>
                                            <input type="text" class="form-control" name="paternal_surname" value="{{ old('paternal_surname', $person->paternal_surname) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="maternal_surname">Apellido Materno</label>
                                            <input type="text" class="form-control" name="maternal_surname" value="{{ old('maternal_surname', $person->maternal_surname) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipo_doc">Tipo Documento</label>
                                            <select name="tipo_doc" class="form-control">
                                                <option value="CI" {{ (old('tipo_doc', $person->tipo_doc) == 'CI') ? 'selected' : '' }}>CI</option>
                                                <option value="Pasaporte" {{ (old('tipo_doc', $person->tipo_doc) == 'Pasaporte') ? 'selected' : '' }}>Pasaporte</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="ci">Nro Documento</label>
                                            <input type="text" class="form-control" name="ci" value="{{ old('ci', $person->ci) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="ci_complemento">Complemento</label>
                                            <input type="text" class="form-control" name="ci_complemento" value="{{ old('ci_complemento', $person->ci_complemento) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="birth_date">Fecha de Nacimiento</label>
                                            <input type="date" class="form-control" name="birth_date" value="{{ old('birth_date', optional($person->birth_date)->format('Y-m-d')) }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="gender">Género</label>
                                            <select name="gender" class="form-control">
                                                <option value="">Seleccione</option>
                                                <option value="Masculino" {{ (old('gender', $person->gender) == 'Masculino') ? 'selected' : '' }}>Masculino</option>
                                                <option value="Femenino" {{ (old('gender', $person->gender) == 'Femenino') ? 'selected' : '' }}>Femenino</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="padron">Padrón Electoral</label>
                                            <input type="text" class="form-control" name="padron" value="{{ old('padron', $person->padron) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Campos Persona Jurídica --}}
                            <div id="juridica_fields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="legal_name">Razón Social</label>
                                            <input type="text" class="form-control" name="legal_name" value="{{ old('legal_name', $person->legal_name) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nit">NIT</label>
                                            <input type="text" class="form-control" name="nit" value="{{ old('nit', $person->nit) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" class="form-control" name="email" value="{{ old('email', $person->email) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Teléfono / Celular</label>
                                        <input type="text" class="form-control" name="phone" value="{{ old('phone', $person->phone) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Dirección</label>
                                <textarea class="form-control" name="address" rows="3">{{ old('address', $person->address) }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="image">Imagen</label>
                                @if($person->image)
                                    <br>
                                    <img src="{{ asset('storage/' . $person->image) }}" width="100">
                                    <br>
                                @endif
                                <input type="file" name="image" accept="image/*">
                            </div>

                            <div class="panel-footer">
                                <button type="submit" class="btn btn-primary save">Guardar</button>
                                <a href="{{ route('admin.people.index') }}" class="btn btn-default">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
    <script>
        $(document).ready(function(){
            function togglePersonType() {
                var type = $('#person_type').val();
                if(type === 'Jurídica') {
                    $('#natural_fields').hide();
                    $('#juridica_fields').show();
                } else {
                    $('#natural_fields').show();
                    $('#juridica_fields').hide();
                }
            }

            $('#person_type').change(togglePersonType);
            togglePersonType();
        });
    </script>
@stop
