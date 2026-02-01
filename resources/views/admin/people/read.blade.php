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
                            {{-- Columna de Imagen --}}
                            <div class="col-md-4 text-center">
                                <div class="panel-heading" style="border-bottom:0;">
                                    <h3 class="panel-title">Fotografía</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    @if($person->image)
                                        <img src="{{ asset('storage/' . $person->image) }}" 
                                             alt="{{ $person->full_name }}"
                                             style="width:100%; max-width:200px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                    @else
                                        <div style="width:200px; height:200px; margin:0 auto; background:#f5f5f5; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                                            <i class="voyager-person" style="font-size:80px; color:#ccc;"></i>
                                        </div>
                                        <p class="text-muted" style="margin-top:10px;">Sin imagen registrada</p>
                                    @endif
                                </div>
                                
                                {{-- Información de Registro --}}
                                <div class="panel-heading" style="border-bottom:0; margin-top:20px;">
                                    <h3 class="panel-title">Información de Registro</h3>
                                </div>
                                <div class="panel-body" style="padding-top:0;">
                                    <p class="text-muted">
                                        <small>
                                            <strong>ID:</strong> {{ $person->id }}<br>
                                            <strong>Registrado:</strong> {{ $person->created_at ? $person->created_at->format('d/m/Y H:i') : 'N/A' }}<br>
                                            @if($person->registerUser_id && $person->registerUser)
                                                <strong>Por:</strong> {{ $person->registerUser->name ?? 'Usuario #' . $person->registerUser_id }}
                                            @endif
                                        </small>
                                    </p>
                                </div>
                            </div>
                            
                            {{-- Columna de Datos --}}
                            <div class="col-md-8">
                                <div class="row">
                                    {{-- Nombre Completo --}}
                                    <div class="col-md-12">
                                        <div class="panel-heading" style="border-bottom:0; background:#f8f9fa; border-radius:5px; margin-bottom:10px;">
                                            <h3 class="panel-title" style="font-size:18px;">
                                                <i class="voyager-person"></i> {{ strtoupper($person->full_name) }}
                                            </h3>
                                        </div>
                                    </div>
                                    
                                    {{-- Documento de Identidad --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Documento de Identidad</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p style="font-size:16px; font-weight:bold;">
                                                {{ $person->tipo_doc ?? 'N/A' }}: {{ $person->ci_formatted }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Padrón Electoral --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Padrón Electoral</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>{{ $person->padron ?? 'No registrado' }}</p>
                                        </div>
                                    </div>
                                    
                                    {{-- Fecha de Nacimiento --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Fecha de Nacimiento</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>
                                                @if($person->birth_date)
                                                    {{ $person->birth_date->format('d/m/Y') }}
                                                    <span class="text-muted">({{ $person->age }} años)</span>
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Género --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Género</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>
                                                @if($person->gender)
                                                    <span class="label label-{{ $person->gender == 'Masculino' ? 'info' : 'danger' }}">
                                                        {{ $person->gender }}
                                                    </span>
                                                @else
                                                    N/A
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Teléfono --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Teléfono / Celular</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>
                                                @if($person->phone)
                                                    <i class="voyager-phone"></i> {{ $person->phone }}
                                                @else
                                                    <span class="text-muted">No registrado</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Email --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Correo Electrónico</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>
                                                @if($person->email)
                                                    <i class="voyager-mail"></i> {{ $person->email }}
                                                @else
                                                    <span class="text-muted">No registrado</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Dirección --}}
                                    <div class="col-md-12">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Dirección</h3>
                                        </div>
                                        <div class="panel-body" style="padding-top:0;">
                                            <p>
                                                @if($person->address)
                                                    <i class="voyager-location"></i> {{ $person->address }}
                                                @else
                                                    <span class="text-muted">No registrada</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Estado --}}
                                    <div class="col-md-6">
                                        <div class="panel-heading" style="border-bottom:0;">
                                            <h3 class="panel-title">Estado del Registro</h3>
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
                                            <span class="label label-{{ $labelColor }}" style="font-size:14px; padding:8px 12px;">
                                                <i class="voyager-{{ $person->status == 1 ? 'check' : ($person->status == 0 ? 'x' : 'clock') }}"></i>
                                                {{ $statusLabel }}
                                            </span>
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
