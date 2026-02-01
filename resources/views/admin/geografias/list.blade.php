<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 60px;">ID</th>
                    <th style="text-align: center;">Código TSE</th>
                    <th style="text-align: center;">Nombre</th>
                    <th style="text-align: center;">Tipo de Territorio</th>
                    <th style="text-align: center;">Se encuentra en (Ubicación)</th>
                    <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $geografia)
                    <tr>
                        <td class="text-center">{{ $geografia->id_geografia }}</td>
                        <td class="text-center"><code>{{ $geografia->codigo_tse }}</code></td>
                        <td>
                            <strong>{{ $geografia->nombre }}</strong>
                            <br>
                            <small class="text-muted">Nivel: {{ $geografia->nivel_jerarquico }}</small>
                        </td>
                        <td style="text-align: center;">
                            <span class="label label-{{ $geografia->tipo == 'Departamento' ? 'success' : ($geografia->tipo == 'Provincia' ? 'primary' : ($geografia->tipo == 'Municipio' ? 'warning' : 'default')) }}">
                                {{ $geografia->tipo }}
                            </span>
                        </td>
                        <td>
                            @if($geografia->parent)
                                <i class="voyager-angle-right"></i> {{ $geografia->parent->nombre }}
                                <small class="text-muted">({{ $geografia->parent->tipo }})</small>
                            @else
                                <span class="text-muted">Territorio Principal</span>
                            @endif
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.geografias.show', $geografia->id_geografia) }}" title="Ver detalles" class="btn btn-sm btn-warning view">
                                {{-- <i class="voyager-eye"></i> --}}
                                 <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                            </a>
                            <a href="{{ route('admin.geografias.edit', $geografia->id_geografia) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                {{-- <i class="voyager-edit"></i> --}}
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <button title="Borrar" class="btn btn-sm btn-danger delete" data-id="{{ $geografia->id_geografia }}" data-toggle="modal" data-target="#delete_modal" onclick="deleteItem('{{ route('admin.geografias.destroy', $geografia->id_geografia) }}', '{{ $geografia->nombre }}')">
                                {{-- <i class="voyager-trash"></i> --}}
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron registros geográficos en la base de datos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-6 text-muted">
        @if($items->count() > 0)
            <p>Mostrando <strong>{{ $items->firstItem() }}</strong> al <strong>{{ $items->lastItem() }}</strong> de un total de <strong>{{ $items->total() }}</strong> territorios.</p>
        @endif
    </div>
    <div class="col-md-6 text-right">
        {{ $items->links() }}
    </div>
</div>
