@extends('voyager::master')

@section('page_title', 'Ver Recinto')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Recinto: {{ $recinto->nombre }}
            </h3>
        </div>

        <div class="panel-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID</th>
                        <td>{{ $recinto->id_recinto }}</td>
                    </tr>
                    <tr>
                        <th>Código TSE</th>
                        <td>{{ $recinto->codigo_tse }}</td>
                    </tr>
                    <tr>
                        <th>Nombre</th>
                        <td>{{ $recinto->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Dirección</th>
                        <td>{{ $recinto->direccion ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Municipio</th>
                        <td>{{ $recinto->geografia ? $recinto->geografia->nombre : '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">Relaciones</h5>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Mesas</th>
                        <td>
                            <span class="label label-info">{{ $recinto->mesas->count() }}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.recintos.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $recinto)
                <a href="{{ route('admin.recintos.edit', $recinto) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
