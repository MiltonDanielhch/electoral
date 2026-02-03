@extends('voyager::master')

@section('page_title', 'Ver Mesa')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Detalle de Mesa: {{ $mesa->numero_mesa }} - {{ $mesa->codigo_tse }}
            </h3>
        </div>

        <div class="panel-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID Interno</th>
                        <td><span class="text-muted">#</span> {{ $mesa->id_mesa }}</td>
                    </tr>
                    <tr>
                        <th>Código TSE (Oficial)</th>
                        <td><code>{{ $mesa->codigo_tse }}</code></td>
                    </tr>
                    <tr>
                        <th>Número de Mesa Correlativo</th>
                        <td><strong>{{ $mesa->numero_mesa }}</strong></td>
                    </tr>
                    <tr>
                        <th>Cantidad de Electores</th>
                        <td>
                            <span class="label label-info" style="font-size: 14px;">
                                <i class="voyager-people"></i> {{ number_format($mesa->cantidad_electores) }} electores
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Estado de la Mesa</th>
                        <td>
                            @php
                                $labelClass = match($mesa->estado) {
                                    'Habilitada' => 'success',
                                    'Escrutada'  => 'primary',
                                    'Anulada'    => 'danger',
                                    'Observada'  => 'warning',
                                    default      => 'default'
                                };
                            @endphp
                            <span class="label label-{{ $labelClass }}" style="font-size: 14px;">
                                {{ $mesa->estado }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Ubicación (Recinto)</th>
                        <td>
                            @if($mesa->recinto)
                                <i class="voyager-location"></i> {{ $mesa->recinto->nombre }}
                                <br>
                                <small class="text-muted">
                                    {{ $mesa->recinto->geografia->nombre ?? 'Sin Municipio' }}
                                    | {{ $mesa->recinto->direccion }}
                                </small>
                            @else
                                <span class="text-muted">Sin recinto asignado</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 25px; font-weight: bold;">
                <i class="voyager-documentation"></i> Resumen de Operaciones
            </h5>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Actas de Escrutinio Registradas</th>
                        <td>
                            <span class="label label-info">{{ $mesa->actasEscrutinio->count() }}</span>
                            @if($mesa->estado == 'Escrutada' && $mesa->actasEscrutinio->count() == 0)
                                <span class="text-danger" style="margin-left: 10px;">
                                    <i class="voyager-warning"></i> Error: El estado es "Escrutada" pero no hay actas.
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Última Actualización</th>
                        <td>{{ $mesa->updated_at ? $mesa->updated_at->format('d/m/Y H:i:s') : 'No registrada' }}</td>
                    </tr>
                </tbody>
            </table>

            @if($mesa->actasEscrutinio->count() > 0)
            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 25px; font-weight: bold;">
                <i class="voyager-documentation"></i> Actas de Escrutinio Registradas
            </h5>
            <div class="row">
                @foreach($mesa->actasEscrutinio as $acta)
                <div class="col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <strong>Acta #{{ $acta->codigo_acta }}</strong>
                            @php
                                $actaLabelClass = match($acta->estado) {
                                    'Pendiente' => 'default',
                                    'Digitada'  => 'info',
                                    'Observada' => 'warning',
                                    'Validada'  => 'success',
                                    'Cerrada'   => 'primary',
                                    default     => 'default'
                                };
                            @endphp
                            <span class="label label-{{ $actaLabelClass }} pull-right">{{ $acta->estado }}</span>
                        </div>
                        <div class="panel-body">
                            <table class="table table-condensed table-bordered">
                                <tr>
                                    <th style="width: 40%;">Cargo</th>
                                    <td>{{ $acta->cargo->descripcion ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Sobres</th>
                                    <td>{{ number_format($acta->total_sobres) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Votantes</th>
                                    <td>{{ number_format($acta->total_votantes) }}</td>
                                </tr>
                                <tr>
                                    <th>Votos Válidos</th>
                                    <td><span class="text-success">{{ number_format($acta->votos_validos) }}</span></td>
                                </tr>
                                <tr>
                                    <th>Votos Blancos</th>
                                    <td>{{ number_format($acta->votos_blancos) }}</td>
                                </tr>
                                <tr>
                                    <th>Votos Nulos</th>
                                    <td><span class="text-danger">{{ number_format($acta->votos_nulos) }}</span></td>
                                </tr>
                            </table>

                            @if($acta->foto_frontal || $acta->foto_reverso)
                            <div class="row" style="margin-top: 10px;">
                                @if($acta->foto_frontal)
                                <div class="col-xs-6">
                                    <small class="text-muted">Foto Frontal</small><br>
                                    <a href="{{ asset('storage/' . $acta->foto_frontal) }}" target="_blank" class="btn btn-sm btn-default btn-block">
                                        <i class="voyager-eye"></i> Ver Frontal
                                    </a>
                                </div>
                                @endif
                                @if($acta->foto_reverso)
                                <div class="col-xs-6">
                                    <small class="text-muted">Foto Reverso</small><br>
                                    <a href="{{ asset('storage/' . $acta->foto_reverso) }}" target="_blank" class="btn btn-sm btn-default btn-block">
                                        <i class="voyager-eye"></i> Ver Reverso
                                    </a>
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="alert alert-warning" style="margin-top: 10px; margin-bottom: 0;">
                                <i class="voyager-warning"></i> Sin fotos registradas
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @if($loop->iteration % 2 == 0)
                </div><div class="row">
                @endif
                @endforeach
            </div>
            @else
            <div class="alert alert-info" style="margin-top: 25px;">
                <i class="voyager-info-circled"></i> <strong>Sin actas registradas:</strong> Esta mesa aún no tiene actas de escrutinio asociadas.
            </div>
            @endif
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.mesas.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver a la lista
            </a>

            @can('update', $mesa)
                <a href="{{ route('admin.mesas.edit', $mesa) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar Datos
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
