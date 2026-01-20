<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 80px;">ID</th>
                    <th style="text-align: center;">Código TSE</th>
                    <th style="text-align: center;">Nombre</th>
                    <th style="text-align: center;">Tipo</th>
                    <th style="text-align: center;">Nivel</th>
                    <th style="text-align: center;">Padre</th>
                    <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $geografia)
                    <tr>
                        <td>{{ $geografia->id_geografia }}</td>
                        <td>{{ $geografia->codigo_tse }}</td>
                        <td>{{ $geografia->nombre }}</td>
                        <td style="text-align: center;">
                            <span class="label label-{{ $geografia->tipo == 'Departamento' ? 'success' : ($geografia->tipo == 'Provincia' ? 'primary' : ($geografia->tipo == 'Municipio' ? 'warning' : 'default')) }}">
                                {{ $geografia->tipo }}
                            </span>
                        </td>
                        <td style="text-align: center;">{{ $geografia->nivel_jerarquico }}</td>
                        <td>{{ $geografia->parent ? $geografia->parent->nombre : '-' }}</td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.geografias.edit', $geografia->id_geografia) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <button title="Borrar" class="btn btn-sm btn-danger delete" data-id="{{ $geografia->id_geografia }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.geografias.destroy', $geografia->id_geografia) }}', '{{ $geografia->nombre }}')">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron geografías.</td>
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
