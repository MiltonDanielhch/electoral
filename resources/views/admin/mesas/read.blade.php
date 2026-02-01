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
                                    <i class="voyager-warning"></i> Error de sintonía: El estado es "Escrutada" pero no hay actas.
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
