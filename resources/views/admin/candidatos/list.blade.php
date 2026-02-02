<div class="col-md-12">
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center; width: 80px;">ID</th>
                    <th style="text-align: center;">Imagen</th>
                    <th style="text-align: center;">CI</th>
                    <th style="text-align: center;">Nombre Completo</th>
                    <th style="text-align: center;">Partido</th>
                    <th style="text-align: center;">Cargo</th>
                    <th style="text-align: center;">Geografía</th>
                    <th style="text-align: center;">Estado</th>
                    <th style="text-align: center; width: 200px;" class="actions text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $candidato)
                    <tr>
                        <td>{{ $candidato->id_candidato }}</td>
                        <td style="text-align: center;">
                            @if($candidato->imagen)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($candidato->imagen) }}" alt="Imagen" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $candidato->ci }}</td>
                        <td>{{ $candidato->nombre_completo }}</td>
                        <td>{{ $candidato->partido ? $candidato->partido->sigla : '-' }}</td>
                        <td>{{ $candidato->cargo ? $candidato->cargo->descripcion : '-' }}</td>
                        <td>{{ $candidato->geografiaPostulacion ? $candidato->geografiaPostulacion->nombre : '-' }}</td>
                        <td style="text-align: center;">
                            <span class="label label-{{ $candidato->estado == 'Activo' ? 'success' : 'danger' }}">
                                {{ $candidato->estado }}
                            </span>
                        </td>
                        <td class="no-sort no-click bread-actions text-right">
                            <a href="{{ route('admin.candidatos.show', $candidato->id_candidato) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                            </a>
                            <a href="{{ route('admin.candidatos.edit', $candidato->id_candidato) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                            <form action="{{ route('admin.candidatos.destroy', $candidato->id_candidato) }}" method="POST" style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Borrar" onclick="return confirm('¿Está seguro de eliminar a {{ addslashes($candidato->nombre_completo) }}?')">
                                    <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Borrar</span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No se encontraron candidatos.</td>
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
