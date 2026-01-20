@extends('voyager::master')

@section('page_title', 'Ver Mesa')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Mesa: {{ $mesa->codigo_tse }}
            </h3>
        </div>

        <div class="panel-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID</th>
                        <td>{{ $mesa->id_mesa }}</td>
                    </tr>
                    <tr>
                        <th>Código TSE</th>
                        <td>{{ $mesa->codigo_tse }}</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <span class="label label-{{ $mesa->estado == 'Activa' ? 'success' : 'danger' }}">
                                {{ $mesa->estado }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Recinto</th>
                        <td>{{ $mesa->recinto ? $mesa->recinto->nombre : '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">Relaciones</h5>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Actas de Escrutinio</th>
                        <td>
                            <span class="label label-info">{{ $mesa->actasEscrutinio->count() }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.mesas.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $mesa)
                <a href="{{ route('admin.mesas.edit', $mesa) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
