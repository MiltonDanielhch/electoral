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
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.recintos.edit', $recinto->id_recinto) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <button title="Borrar" class="btn btn-sm btn-danger delete" data-id="{{ $recinto->id_recinto }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.recintos.destroy', $recinto->id_recinto) }}', '{{ $recinto->nombre }}')">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron recintos.</td>
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
