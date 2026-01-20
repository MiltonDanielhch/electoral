<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 80px;">ID</th>
                    <th style="text-align: center;">Sigla</th>
                    <th style="text-align: center;">Nombre</th>
                    <th style="text-align: center;">Código TSE</th>
                    <th style="text-align: center;">Color</th>
                    <th style="text-align: center;">Estado</th>
                    <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $organizacion)
                    <tr>
                        <td>{{ $organizacion->id_partido }}</td>
                        <td>
                            <strong>{{ $organizacion->sigla }}</strong>
                        </td>
                        <td>{{ $organizacion->nombre }}</td>
                        <td style="text-align: center;">{{ $organizacion->codigo_tse }}</td>
                        <td style="text-align: center;">
                            <div style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; border: 2px solid #ddd; background-color: {{ $organizacion->color_hex }};"></div>
                            <small>{{ $organizacion->color_hex }}</small>
                        </td>
                        <td style="text-align: center;">
                            <span class="label label-{{ $organizacion->estado == 'Activo' ? 'success' : 'danger' }}">
                                {{ $organizacion->estado }}
                            </span>
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.organizaciones_politicas.edit', $organizacion->id_partido) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <button title="Borrar" class="btn btn-sm btn-danger delete" data-id="{{ $organizacion->id_partido }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.organizaciones_politicas.destroy', $organizacion->id_partido) }}', '{{ $organizacion->nombre }}')">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron organizaciones políticas.</td>
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
