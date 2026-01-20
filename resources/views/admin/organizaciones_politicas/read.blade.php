@extends('voyager::master')

@section('page_title', 'Ver Organización Política')

@section('content')
<div class="page-content container-fluid">
    <div class="panel panel-bordered panel-primary">
        <div class="panel-heading">
            <h3 class="panel-title">
                <i class="voyager-eye"></i> Ver Organización Política: {{ $organizacion->nombre }}
            </h3>
        </div>

        <div class="panel-body">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
                <div style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid #ddd; display: flex; align-items: center; justify-content: center; background-color: {{ $organizacion->color_hex }};">
                    @if($organizacion->logo_url)
                        <img src="{{ Storage::url($organizacion->logo_url) }}" alt="{{ $organizacion->nombre }}" style="max-width: 80%; max-height: 80%;">
                    @else
                        <span style="font-size: 24px; color: #fff;">{{ $organizacion->sigla }}</span>
                    @endif
                </div>
                <div>
                    <h2>{{ $organizacion->nombre }}</h2>
                    <p><strong>Sigla:</strong> {{ $organizacion->sigla }}</p>
                </div>
            </div>

            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th style="width: 250px;">ID</th>
                        <td>{{ $organizacion->id_partido }}</td>
                    </tr>
                    <tr>
                        <th>Código TSE</th>
                        <td>{{ $organizacion->codigo_tse }}</td>
                    </tr>
                    <tr>
                        <th>Color</th>
                        <td>
                            <div style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; border: 2px solid #ddd; background-color: {{ $organizacion->color_hex }};"></div>
                            {{ $organizacion->color_hex }}
                        </td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <span class="label label-{{ $organizacion->estado == 'Activo' ? 'success' : 'danger' }}">
                                {{ $organizacion->estado }}
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
                        <td>{{ $organizacion->created_at ? $organizacion->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Última Actualización</th>
                        <td>{{ $organizacion->updated_at ? $organizacion->updated_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="panel-footer text-right">
            <a href="{{ route('admin.organizaciones_politicas.index') }}" class="btn btn-default">
                <i class="voyager-angle-left"></i> Volver
            </a>

            @can('update', $organizacion)
                <a href="{{ route('admin.organizaciones_politicas.edit', $organizacion) }}" class="btn btn-primary">
                    <i class="voyager-edit"></i> Editar
                </a>
            @endcan
        </div>
    </div>
</div>
@stop
