@extends('voyager::master')

@section('page_title', 'Recintos')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0;">
                        <div class="col-md-8" style="padding: 0;">
                            <h1 class="page-title">
                                <i class="voyager-home"></i> Recintos
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px;">
                            @can('create', App\Models\Recinto::class)
                                <a href="{{ route('admin.recintos.create') }}" class="btn btn-success">
                                    <i class="voyager-plus"></i> Nuevo Recinto
                                </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-9" style="margin-bottom: 0;">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar
                                        <select id="select-paginate" class="form-control input-sm">
                                            <option value="10">10</option>
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select> registros
                                    </label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 0;">
                                <input type="text" id="search" class="form-control" placeholder="Buscar...">
                            </div>
                        </div>
                        <div class="row" id="list-container" style="min-height: 120px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css"/>
<link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}"/>
<style>
    .loading-icon {
        animation: spin 1.5s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .voyager-spin {
        animation: spin 1.5s linear infinite;
    }
    .custom-div-icon {
        background: transparent;
    }
</style>
@endsection

<div id="delete-modal-wrapper" style="display:none;">
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> ¿Desea eliminar este recinto?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        @method('DELETE') @csrf
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="Sí, eliminar">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('delete-modal-wrapper').style.display = '';
});
</script>

@push('javascript')
    @include('admin.partials.list-browse-script', ['listUrl' => route('admin.recintos.ajax.list')])

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
    <script src="{{ asset('js/mapa-config.js') }}"></script>
    <script>
    // Mapas inicializados (evitar doble inicialización)
    const mapasInicializados = new Set();

    /**
     * Inicializa un mini mapa individual
     */
    function initMiniMap(input) {
        const lat = parseFloat(input.getAttribute('data-lat'));
        const lon = parseFloat(input.getAttribute('data-lon'));
        const id = input.getAttribute('data-id');
        const containerId = 'mini-map-' + id;

        // Evitar inicializar el mismo mapa dos veces
        if (mapasInicializados.has(containerId)) {
            return;
        }

        const mapContainer = document.getElementById(containerId);

        if (!isNaN(lat) && !isNaN(lon) && mapContainer) {
            try {
                // Usar SintoniaMap para inicialización modular
                const sintoniaMap = new SintoniaMap(containerId, {
                    center: [lat, lon],
                    zoom: 15,
                    zoomControl: false,
                    attributionControl: false,
                    scrollWheelZoom: false,
                    dragging: false,
                    doubleClickZoom: false,
                    tap: false
                });

                sintoniaMap.init();

                // Agregar marcador con icono personalizado
                sintoniaMap.agregarMarcador(lat, lon, {
                    icono: SintoniaMap.crearIcono('punto', '#26e07f')
                });

                // Marcar como inicializado
                mapasInicializados.add(containerId);

            } catch (e) {
                console.error('Error inicializando mini mapa ' + id + ':', e);
            }
        }
    }

    /**
     * Lazy Loading de mapas con Intersection Observer
     * Los mapas solo se cargan cuando son visibles en el viewport
     */
    function initLazyLoadingMapas() {
        // Configuración del observer
        const observerOptions = {
            root: null, // viewport
            rootMargin: '50px', // carga 50px antes de ser visible
            threshold: 0.1 // al menos 10% visible
        };

        const mapObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const input = entry.target.querySelector('.mini-map-data');
                    if (input) {
                        initMiniMap(input);
                    }
                    // Dejar de observar una vez inicializado
                    mapObserver.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observar todos los contenedores de mini mapas
        document.querySelectorAll('.mini-map-container').forEach(container => {
            mapObserver.observe(container);
        });

        return mapObserver;
    }

    /**
     * Inicialización tradicional (fallback si IntersectionObserver no está disponible)
     */
    function initMiniMaps() {
        const miniMaps = document.querySelectorAll('.mini-map-data');
        miniMaps.forEach(initMiniMap);
    }

    // Inicialización cuando el DOM está listo
    document.addEventListener('DOMContentLoaded', function() {
        // Usar Intersection Observer si está disponible (mejor rendimiento)
        if ('IntersectionObserver' in window) {
            window.miniMapObserver = initLazyLoadingMapas();
        } else {
            // Fallback para navegadores antiguos
            initMiniMaps();
        }
    });

    // Reinicializar cuando se carga nueva lista
    document.addEventListener('list-loaded', function() {
        // Limpiar set de mapas inicializados para la nueva lista
        mapasInicializados.clear();

        setTimeout(function() {
            if ('IntersectionObserver' in window && window.miniMapObserver) {
                // Reconfigurar observer para nuevos elementos
                document.querySelectorAll('.mini-map-container').forEach(container => {
                    window.miniMapObserver.observe(container);
                });
            } else {
                initMiniMaps();
            }
        }, 100);
    });
    </script>
@endpush
