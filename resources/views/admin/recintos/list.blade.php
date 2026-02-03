<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 80px;">ID</th>
                    <th style="text-align: center;">Código TSE</th>
                    <th style="text-align: center;">Nombre</th>
                    <th style="text-align: center;">Dirección</th>
                    <th style="text-align: center;">Municipio</th>
                    <th style="text-align: center; width: 150px;">Ubicación</th>
                    <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $recinto)
                    <tr>
                        <td>{{ $recinto->id_recinto }}</td>
                        <td>{{ $recinto->codigo_tse }}</td>
                        <td>{{ $recinto->nombre }}</td>
                        <td>{{ $recinto->direccion ?? '-' }}</td>
                        <td>{{ $recinto->geografia ? $recinto->geografia->nombre : '-' }}</td>
                        <td style="text-align: center;">
                            @if($recinto->latitud && $recinto->longitud)
                                <div class="mini-map-container" style="height: 80px; width: 120px; margin: 0 auto;">
                                    <div id="mini-map-{{ $recinto->id_recinto }}" style="height: 100%; width: 100%; border-radius: 4px;"></div>
                                    <input type="hidden" data-lat="{{ $recinto->latitud }}" data-lon="{{ $recinto->longitud }}" data-id="{{ $recinto->id_recinto }}" class="mini-map-data">
                                </div>
                            @else
                                <span class="badge badge-default">
                                    <i class="voyager-x"></i> Sin ubicación
                                </span>
                            @endif
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.recintos.show', $recinto->id_recinto) }}" title="Ver" class="btn btn-sm btn-success view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                            </a>
                            <a href="{{ route('admin.recintos.edit', $recinto->id_recinto) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <form action="{{ route('admin.recintos.destroy', $recinto->id_recinto) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Borrar" onclick="return confirm('¿Está seguro de eliminar a {{ addslashes($recinto->nombre) }}?')">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron recintos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4 text-muted">
        @if($items->count() > 0)
            <p class="text-muted">Mostrando del {{ $items->firstItem() }} al {{ $items->lastItem() }} de {{ $items->total() }} registros.</p>
        @endif
    </div>
    <div class="col-md-8 text-right">
        <nav class="text-right">
            {{ $items->links() }}
        </nav>
    </div>
</div>
