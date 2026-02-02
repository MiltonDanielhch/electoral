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
                                <div id="mini-map-{{ $recinto->id_recinto }}" style="height: 80px; width: 120px; margin: 0 auto; border-radius: 4px;"></div>
                                <input type="hidden" data-lat="{{ $recinto->latitud }}" data-lon="{{ $recinto->longitud }}" data-id="{{ $recinto->id_recinto }}" class="mini-map-data">
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

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endsection

@push('javascript')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const miniMaps = document.querySelectorAll('.mini-map-data');

    miniMaps.forEach(function(input) {
        const lat = parseFloat(input.getAttribute('data-lat'));
        const lon = parseFloat(input.getAttribute('data-lon'));
        const id = input.getAttribute('data-id');

        if (!isNaN(lat) && !isNaN(lon)) {
            const map = L.map('mini-map-' + id, {
                center: [lat, lon],
                zoom: 15,
                zoomControl: false,
                attributionControl: false,
                scrollWheelZoom: false,
                dragging: false,
                doubleClickZoom: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: ''
            }).addTo(map);

            L.marker([lat, lon], {
                icon: L.divIcon({
                    className: 'custom-div-icon',
                    html: '<div style="background-color: #26e07f; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>',
                    iconSize: [12, 12],
                    iconAnchor: [6, 6]
                })
            }).addTo(map);
        }
    });
});
</script>
@endpush
