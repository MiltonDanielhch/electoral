<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 60px;">ID</th>
                    <th style="text-align: center;">Código TSE</th>
                    <th style="text-align: center;">Nº Mesa</th> {{-- Sintonía: Nueva columna --}}
                    <th style="text-align: center;">Estado</th>
                    <th style="text-align: center;">Recinto</th>
                    <th style="text-align: center; width: 220px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $mesa)
                    <tr>
                        <td class="text-center">{{ $mesa->id_mesa }}</td>
                        <td class="text-center"><code>{{ $mesa->codigo_tse }}</code></td>
                        <td class="text-center"><strong>{{ $mesa->numero_mesa }}</strong></td>
                        <td style="text-align: center;">
                            @php
                                $labelClass = match($mesa->estado) {
                                    'Habilitada' => 'success',
                                    'Escrutada'  => 'primary',
                                    'Anulada'    => 'danger',
                                    'Observada'  => 'warning',
                                    default      => 'default'
                                };
                            @endphp
                            <span class="label label-{{ $labelClass }}">
                                {{ $mesa->estado }}
                            </span>
                        </td>
                        <td>
                            @if($mesa->recinto)
                                {{ $mesa->recinto->nombre }}
                                <br>
                                <small class="text-muted">{{ $mesa->recinto->geografia->nombre ?? '' }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.mesas.show', $mesa->id_mesa) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs">Ver</span>
                            </a>
                            <a href="{{ route('admin.mesas.edit', $mesa->id_mesa) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs">Editar</span>
                            </a>
                            <button title="Borrar" class="btn btn-sm btn-danger delete"
                                    onclick="deleteItem('{{ route('admin.mesas.destroy', $mesa->id_mesa) }}', 'Mesa {{ $mesa->numero_mesa }} - {{ $mesa->codigo_tse }}')">
                                <i class="voyager-trash"></i> <span class="hidden-xs">Borrar</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No se encontraron mesas en la sintonía actual.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="col-md-4">
        @if($items->count() > 0)
            <p class="text-muted" style="margin-top: 20px;">
                Mostrando del {{ $items->firstItem() }} al {{ $items->lastItem() }} de {{ $items->total() }} mesas.
            </p>
        @endif
    </div>
    <div class="col-md-8 text-right">
        {{ $items->links() }}
    </div>
</div>
