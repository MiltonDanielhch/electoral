@extends('voyager::master')

@section('page_title', 'Ver Candidato')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Candidato: {{ $candidato->nombre_completo }}
            </h3>
        </div>

        <div class="panel-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID</th>
                        <td>{{ $candidato->id_candidato }}</td>
                    </tr>
                    <tr>
                        <th>CI</th>
                        <td>{{ $candidato->ci }}</td>
                    </tr>
                    <tr>
                        <th>Nombre Completo</th>
                        <td>{{ $candidato->nombre_completo }}</td>
                    </tr>
                    <tr>
                        <th>Partido</th>
                        <td>
                            @if($candidato->partido)
                                {{ $candidato->partido->sigla }} - {{ $candidato->partido->nombre }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Cargo</th>
                        <td>{{ $candidato->cargo ? $candidato->cargo->descripcion : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Geografía de Postulación</th>
                        <td>{{ $candidato->geografiaPostulacion ? $candidato->geografiaPostulacion->nombre . ' (' . $candidato->geografiaPostulacion->tipo . ')' : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <span class="label label-{{ $candidato->estado == 'Activo' ? 'success' : 'danger' }}">
                                {{ $candidato->estado }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">Metadatos</h5>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Creado</th>
                        <td>{{ $candidato->created_at ? $candidato->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Última Actualización</th>
                        <td>{{ $candidato->updated_at ? $candidato->updated_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.candidatos.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $candidato)
                <a href="{{ route('admin.candidatos.edit', $candidato) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
