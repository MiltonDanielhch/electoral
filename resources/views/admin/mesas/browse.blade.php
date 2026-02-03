@extends('voyager::master')

@section('page_title', 'Mesas')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0;">
                        <div class="col-md-8" style="padding: 0;">
                            <h1 class="page-title">
                                <i class="voyager-list"></i> Mesas
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px;">
                            @can('create', App\Models\Mesa::class)
                                <a href="{{ route('admin.mesas.create') }}" class="btn btn-success">
                                    <i class="voyager-plus"></i> Nueva Mesa
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
    {{-- Dashboard de Estadísticas --}}
    <div class="page-content container-fluid" style="padding-bottom: 0;">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            <i class="voyager-pie-chart"></i> Transmisión - Estadísticas en Tiempo Real
                        </h3>
                    </div>
                    <div class="panel-body" id="stats-container">
                        <div class="row text-center">
                            <div class="col-md-2 col-sm-4 col-xs-6">
                                <div class="well well-sm">
                                    <h3 style="margin: 0; color: #333;" id="stat-total">-</h3>
                                    <small class="text-muted">Total Mesas</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6">
                                <div class="well well-sm" style="background-color: #d4edda; border-color: #c3e6cb;">
                                    <h3 style="margin: 0; color: #155724;" id="stat-escrutadas">-</h3>
                                    <small style="color: #155724;">Escrutadas</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6">
                                <div class="well well-sm" style="background-color: #fff3cd; border-color: #ffeeba;">
                                    <h3 style="margin: 0; color: #856404;" id="stat-habilitadas">-</h3>
                                    <small style="color: #856404;">Habilitadas</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6">
                                <div class="well well-sm" style="background-color: #f8d7da; border-color: #f5c6cb;">
                                    <h3 style="margin: 0; color: #721c24;" id="stat-observadas">-</h3>
                                    <small style="color: #721c24;">Observadas</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6">
                                <div class="well well-sm" style="background-color: #f5f5f5; border-color: #ddd;">
                                    <h3 style="margin: 0; color: #333;" id="stat-faltantes">-</h3>
                                    <small class="text-muted">Faltantes</small>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-xs-6">
                                <div class="well well-sm" style="background-color: #cce5ff; border-color: #b3d7ff;">
                                    <h3 style="margin: 0; color: #004085;" id="stat-porcentaje">-%</h3>
                                    <small style="color: #004085;">Progreso</small>
                                </div>
                            </div>
                        </div>
                        <div class="progress" style="margin-bottom: 0; margin-top: 10px;">
                            <div id="progress-bar" class="progress-bar progress-bar-striped active" role="progressbar"
                                 style="width: 0%; background-color: #28a745;"
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="progress-text">0%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Lista de Mesas --}}
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
    .well-sm {
        padding: 10px;
        border-radius: 3px;
        margin-bottom: 10px;
    }
    .well-sm h3 {
        font-size: 24px;
        font-weight: bold;
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
                    <h4 class="modal-title"><i class="voyager-trash"></i> ¿Desea eliminar esta mesa?</h4>
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

    // Cargar estadísticas
    loadEstadisticas();
});

function loadEstadisticas() {
    fetch('/api/v1/mesas/estadisticas', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const stats = data.data;
            document.getElementById('stat-total').textContent = stats.total_mesas.toLocaleString();
            document.getElementById('stat-escrutadas').textContent = stats.escrutadas.toLocaleString();
            document.getElementById('stat-habilitadas').textContent = stats.habilitadas.toLocaleString();
            document.getElementById('stat-observadas').textContent = stats.observadas.toLocaleString();
            document.getElementById('stat-faltantes').textContent = stats.faltantes.toLocaleString();
            document.getElementById('stat-porcentaje').textContent = stats.porcentaje_escrutadas + '%';

            // Actualizar barra de progreso
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            progressBar.style.width = stats.porcentaje_escrutadas + '%';
            progressBar.setAttribute('aria-valuenow', stats.porcentaje_escrutadas);
            progressText.textContent = stats.porcentaje_escrutadas + '% Completado';
        }
    })
    .catch(error => console.error('Error cargando estadísticas:', error));
}

// Recargar estadísticas cada 30 segundos
setInterval(loadEstadisticas, 30000);
</script>

@push('javascript')
    @include('admin.partials.list-browse-script', ['listUrl' => route('admin.mesas.ajax.list')])
@endpush
