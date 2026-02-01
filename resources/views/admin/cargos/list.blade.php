<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th style="text-align: center; width: 80px;">ID</th>
                <th style="text-align: center;">Descripción</th>
                <th style="text-align: center;">Nivel</th>
                <th style="text-align: center;">Tipo Acta</th>
                <th style="text-align: center;">Acta Única</th>
                <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $cargo)
                <tr>
                    <td>{{ $cargo->id_cargo }}</td>
                    <td>{{ $cargo->descripcion }}</td>
                    <td style="text-align: center;">
                        <span class="label label-info">{{ $cargo->nivel_texto }}</span>
                    </td>
                    <td style="text-align: center;">
                        <span class="label label-{{ $cargo->tipo_acta == 'Normal' ? 'primary' : 'warning' }}">
                            {{ $cargo->tipo_acta }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="label label-{{ $cargo->acta_unica ? 'success' : 'danger' }}">
                            {{ $cargo->acta_unica ? 'Sí' : 'No' }}
                        </span>
                    </td>
                    <td class="no-sort no-click bread-actions text-right">
                        <a href="{{ route('admin.cargos.show', $cargo->id_cargo) }}" 
                           title="Ver" 
                           aria-label="Ver cargo {{ $cargo->descripcion }}"
                           class="btn btn-sm btn-warning view">
                            <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                        </a>
                        <a href="{{ route('admin.cargos.edit', $cargo->id_cargo) }}" 
                           title="Editar" 
                           aria-label="Editar cargo {{ $cargo->descripcion }}"
                           class="btn btn-sm btn-primary edit">
                            <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                        </a>
                        <button type="button"
                                title="Borrar" 
                                aria-label="Borrar cargo {{ $cargo->descripcion }}"
                                class="btn btn-sm btn-danger delete" 
                                data-toggle="modal" 
                                data-target="#delete_modal" 
                                data-delete-url="{{ route('admin.cargos.destroy', $cargo->id_cargo) }}"
                                data-item-name="{{ $cargo->descripcion }}">
                            <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 30px;">
                        <div class="text-muted">
                            <i class="voyager-search" style="font-size: 40px;"></i>
                            <p>No se encontraron cargos con los criterios de búsqueda.</p>
                            <button class="btn btn-sm btn-info" onclick="$('#search').val('').trigger('input');">
                                Limpiar búsqueda
                            </button>
                        </div>
                    </td>
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
