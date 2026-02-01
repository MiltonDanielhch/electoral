@extends('voyager::master')

@section('page_title', 'Ver Cargo')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Cargo: {{ $cargo->descripcion }}
            </h3>
        </div>

        <div class="panel-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID</th>
                        <td>{{ $cargo->id_cargo }}</td>
                    </tr>
                    <tr>
                        <th>Descripción</th>
                        <td>{{ $cargo->descripcion }}</td>
                    </tr>
                    <tr>
                        <th>Nivel</th>
                        <td>
                            <span class="label label-info">{{ $cargo->nivel_texto }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>Tipo de Acta</th>
                        <td>
                            <span class="label label-{{ $cargo->tipo_acta == 'Normal' ? 'primary' : 'warning' }}">
                                {{ $cargo->tipo_acta }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Acta Única</th>
                        <td>
                            <span class="label label-{{ $cargo->acta_unica ? 'success' : 'danger' }}">
                                {{ $cargo->acta_unica ? 'Sí' : 'No' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>


        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.cargos.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $cargo)
                <a href="{{ route('admin.cargos.edit', $cargo) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
