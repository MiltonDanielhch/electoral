@extends('voyager::master')

@section('page_title', 'Ver Geografía')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Geografía: {{ $geografia->nombre }}
            </h3>
        </div>

        <div class="panel-body">
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID</th>
                        <td>{{ $geografia->id_geografia }}</td>
                    </tr>
                    <tr>
                        <th>Código TSE</th>
                        <td>{{ $geografia->codigo_tse }}</td>
                    </tr>
                    <tr>
                        <th>Nombre</th>
                        <td>{{ $geografia->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Tipo</th>
                        <td>
                            <span class="label label-{{ $geografia->tipo == 'Departamento' ? 'success' : ($geografia->tipo == 'Provincia' ? 'primary' : ($geografia->tipo == 'Municipio' ? 'warning' : 'default') }}">
                                {{ $geografia->tipo }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Nivel Jerárquico</th>
                        <td>{{ $geografia->nivel_jerarquico }}</td>
                    </tr>
                    <tr>
                        <th>Geografía Padre</th>
                        <td>{{ $geografia->parent ? $geografia->parent->nombre : '-' }}</td>
                    </tr>
                </tbody>
            </table>

            <h5 style="border-bottom: 1px solid #eee; padding-bottom: 5px; margin-top: 20px;">Relaciones</h5>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">Geografías Hijas</th>
                        <td>
                            @if($geografia->children->count() > 0)
                                <ul>
                                    @foreach($geografia->children->take(10) as $child)
                                        <li>{{ $child->nombre }} ({{ $child->tipo }})</li>
                                    @endforeach
                                    @if($geografia->children->count() > 10)
                                        <li>... y {{ $geografia->children->count() - 10 }} más</li>
                                    @endif
                                </ul>
                            @else
                                Sin registros
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Recintos</th>
                        <td>
                            @if($geografia->recintos->count() > 0)
                                <ul>
                                    @foreach($geografia->recintos->take(10) as $recinto)
                                        <li>{{ $recinto->nombre }}</li>
                                    @endforeach
                                    @if($geografia->recintos->count() > 10)
                                        <li>... y {{ $geografia->recintos->count() - 10 }} más</li>
                                    @endif
                                </ul>
                            @else
                                Sin registros
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Candidatos</th>
                        <td>
                            @if($geografia->candidatos->count() > 0)
                                <span class="label label-success">{{ $geografia->candidatos->count() }} candidatos</span>
                            @else
                                Sin registros
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.geografias.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $geografia)
                <a href="{{ route('admin.geografias.edit', $geografia) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
