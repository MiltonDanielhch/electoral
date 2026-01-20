<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 80px;">ID</th>
                    <th style="text-align: center;">Código TSE</th>
                    <th style="text-align: center;">Estado</th>
                    <th style="text-align: center;">Recinto</th>
                    <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $mesa)
                    <tr>
                        <td>{{ $mesa->id_mesa }}</td>
                        <td>{{ $mesa->codigo_tse }}</td>
                        <td style="text-align: center;">
                            <span class="label label-{{ $mesa->estado == 'Activa' ? 'success' : 'danger' }}">
                                {{ $mesa->estado }}
                            </span>
                        </td>
                        <td>{{ $mesa->recinto ? $mesa->recinto->nombre : '-' }}</td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.mesas.edit', $mesa->id_mesa) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <button title="Borrar" class="btn btn-sm btn-danger delete" data-id="{{ $mesa->id_mesa }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.mesas.destroy', $mesa->id_mesa) }}', 'Mesa {{ $mesa->codigo_tse }}')">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No se encontraron mesas.</td>
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
