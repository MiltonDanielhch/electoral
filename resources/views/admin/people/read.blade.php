@extends('voyager::master')

@section('page_title', 'Ver Persona')

@section('page_header')
    <h1 class="page-title">
        <i class="voyager-person"></i> Datos de la Persona
    </h1>
    @if (auth()->user()->hasPermission('edit_people'))
    <a href="{{ route('admin.people.edit', $person->id) }}" class="btn btn-info">
        <i class="glyphicon glyphicon-pencil"></i> <span class="hidden-xs hidden-sm">Editar</span>
    </a>
    @endif
    <a href="{{ route('admin.people.index') }}" class="btn btn-warning">
        <i class="glyphicon glyphicon-list"></i> <span class="hidden-xs hidden-sm">Volver a la lista</span>
    </a>
@stop

@section('content')
    <div class="page-content read container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Imagen</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    @if($person->image)
                                        <img src="{{ asset('storage/' . $person->image) }}" style="width:100%; max-width:200px; border-radius: 5px;">
                                    @else
                                        <p class="text-muted">Sin imagen</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Tipo de Persona</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->person_type ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Nombre Completo</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->full_name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Tipo de Documento</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->tipo_doc ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">CI / Pasaporte</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->ci }}{{ $person->ci_complemento ? '-' . $person->ci_complemento : '' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">NIT</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->nit ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Padrón</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->padron ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Fecha de Nacimiento</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->birth_date ? date('d/m/Y', strtotime($person->birth_date)) : 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Teléfono / Celular</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->phone ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Email</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->email ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Dirección</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->address ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Género</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->gender ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Estado</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            @php
                                                $statusLabel = \App\Models\Person::getStatusLabel($person->status);
                                                $labelColor = match($person->status) {
                                                    \App\Models\Person::STATUS_ACTIVE => 'success',
                                                    \App\Models\Person::STATUS_INACTIVE => 'danger',
                                                    \App\Models\Person::STATUS_PENDING => 'warning',
                                                    default => 'default'
                                                };
                                            @endphp
                                            <span class="label label-{{ $labelColor }}">{{ $statusLabel }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
