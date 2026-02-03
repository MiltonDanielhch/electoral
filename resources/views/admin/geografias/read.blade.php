@extends('voyager::master')

@section('page_title', 'Ver Detalles de ' . $geografia->nombre)

@section('css')
    <style>
        .table-label { background: #f9f9f9; font-weight: bold; width: 200px; }
        .relation-card { border-left: 4px solid #2ecc71; padding: 10px; background: #f4fbf7; margin-bottom: 10px; }
    </style>
@stop

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-location"></i> Información Territorial: {{ $geografia->nombre }}
            </h3>
        </div>

        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td class="table-label">Código TSE</td>
                                <td><code>{{ $geografia->codigo_tse }}</code></td>
                            </tr>
                            <tr>
                                <td class="table-label">Tipo de Territorio</td>
                                <td>
                                    <span class="label label-{{ $geografia->tipo == 'Departamento' ? 'success' : ($geografia->tipo == 'Provincia' ? 'primary' : ($geografia->tipo == 'Municipio' ? 'warning' : 'default')) }}">
                                        {{ $geografia->tipo }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label">Ubicación Superior</td>
                                <td>
                                    @if($geografia->parent)
                                        <a href="{{ route('admin.geografias.show', $geografia->parent_id) }}">
                                            <i class="voyager-angle-right"></i> {{ $geografia->parent->nombre }}
                                            <small class="text-muted">({{ $geografia->parent->tipo }})</small>
                                        </a>
                                    @else
                                        <span class="text-muted">Territorio Principal</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="table-label">Coordenadas</td>
                                <td>
                                    @if($geografia->latitud)
                                        <i class="voyager-world"></i> {{ $geografia->latitud }}, {{ $geografia->longitud }}
                                    @else
                                        <span class="text-muted">No geolocalizado</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="col-md-6">
                    {{-- Mini Resumen Electoral de Cascada --}}
                    <div class="relation-card">
                        <h5><i class="voyager-group"></i> Resumen del Territorio</h5>
                        <p>Este <strong>{{ $geografia->tipo }}</strong> cuenta actualmente con:</p>
                        <ul class="list-unstyled">
                            {{-- Usamos el atributo dinámico que suma hacia abajo --}}
                            <li>
                                <i class="voyager-company"></i>
                                <strong>{{ $geografia->contador_recintos }}</strong> Recintos Electorales.
                            </li>

                            <li>
                                <i class="voyager-person"></i>
                                <strong>{{ $geografia->contador_candidatos }}</strong> Candidatos registrados.
                            </li>

                            <li>
                                <i class="voyager-categories"></i>
                                <strong>{{ $geografia->children->count() }}</strong>
                                {{ $geografia->tipo == 'Departamento' ? 'Provincias' : ($geografia->tipo == 'Provincia' ? 'Municipios' : 'Localidades') }} dependientes.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Sección de Listados Detallados --}}
            <div class="row" style="margin-top: 20px;">
                <div class="col-md-12">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a data-toggle="tab" href="#hijos">
                                Dependencias ({{ $geografia->children->count() }})
                            </a>
                        </li>
                        <li>
                            <a data-toggle="tab" href="#recintos">
                                {{-- Usamos contador_recintos para el total en cascada --}}
                                Puntos de Votación ({{ $geografia->contador_recintos }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" style="padding: 20px; border: 1px solid #ddd; border-top: none;">
                        <div id="hijos" class="tab-pane fade in active">
                            @if($geografia->children->count() > 0)
                                <div class="row">
                                    @foreach($geografia->children as $child)
                                        <div class="col-md-4">
                                            <i class="voyager-angle-right"></i> {{ $child->nombre }} <small>({{ $child->tipo }})</small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted">No existen territorios que dependan de este registro.</p>
                            @endif
                        </div>

                        <div id="recintos" class="tab-pane fade">
                            @php
                                $listaRecintos = $geografia->todos_los_recintos;
                            @endphp

                            <div class="row">
                                @forelse($listaRecintos as $recinto)
                                    <div class="col-md-6" style="margin-bottom: 10px;">
                                        <div style="padding: 10px; border: 1px solid #f1f1f1; border-left: 3px solid #22a7f0; background: #fafafa;">
                                            <i class="voyager-home"></i> <strong>{{ $recinto->nombre }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="voyager-location"></i>
                                                {{ $geografia->tipo != 'Municipio' ? ($recinto->geografia->nombre ?? '') : $recinto->direccion }}
                                            </small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-md-12">
                                        <p class="text-muted">No hay recintos electorales registrados en este territorio.</p>
                                    </div>
                                @endforelse
                            </div>

                            @if($geografia->contador_recintos > 0)
                                <div class="col-md-12" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                                    <a href="{{ route('admin.geografias.mapa', $geografia) }}" class="btn btn-info">
                                        <i class="voyager-location"></i> Ver Mapa Estratégico del Beni
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.geografias.index') }}" class="btn btn-default">Volver a la Lista</a>
            @can('update', $geografia)
                <a href="{{ route('admin.geografias.edit', $geografia) }}" class="btn btn-primary">Editar Datos</a>
            @endcan
        </div>
    </div>
</div>
@stop
