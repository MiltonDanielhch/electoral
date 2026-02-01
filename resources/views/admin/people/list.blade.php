<style>
    #dataTable th {
        background-color: #28a745 !important;
        color: white !important;
        border: 1px solid #1e7e34 !important;
    }
    #dataTable tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>

<div class="col-md-12">
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th style="text-align: center">ID</th>
                    <th style="text-align: center">CI/Pasaporte</th>
                    <th style="text-align: center">Nombre completo</th>
                    <th style="text-align: center">Fecha nac.</th>
                    <th style="text-align: center">Telefono/Celular</th>
                    <th style="text-align: center">Estado</th>
                    <th style="text-align: center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $item)
                <tr>
                    <td>{{ $item->id }}</td>
                    <td>{{ $item->ci_formatted }}</td>
                    <td>
                        <table>
                            @php
                                $image = $item->image 
                                    ? asset('storage/' . $item->image)
                                    : asset('images/default.jpg');
                            @endphp
                            <tr>
                                <td><img src="{{ $image }}" alt="{{ $item->full_name }}" loading="lazy" style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px; object-fit: cover;"></td>
                                <td>{{ strtoupper($item->full_name) }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="text-align: center">
                        @if ($item->birth_date)
                            {{ $item->birth_date->format('d/m/Y') }} <br> 
                            <small>{{ $item->age }} años</small>
                        @else
                            Sin Datos
                        @endif
                    </td>
                    <td style="text-align: center">{{ $item->phone?$item->phone:'SN' }}</td>
                    <td style="text-align: center">
                        @php
                            $statusLabel = \App\Models\Person::getStatusLabel($item->status);
                            $labelColor = match($item->status) {
                                \App\Models\Person::STATUS_ACTIVE => 'success',
                                \App\Models\Person::STATUS_INACTIVE => 'danger',
                                \App\Models\Person::STATUS_PENDING => 'warning',
                                default => 'default'
                            };
                        @endphp
                       <span class="label label-{{ $labelColor }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td style="width: 18%" class="no-sort no-click bread-actions text-right">
                        @if (auth()->user()->hasPermission('read_people'))
                            <a href="{{ route('admin.people.show', $item->id) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('edit_people'))
                            <a href="{{ route('admin.people.edit', $item->id) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_people'))
                            <a href="#" data-url="{{ route('admin.people.destroy', $item->id) }}" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete-item">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Eliminar</span>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 40px;">
                            <div class="text-muted">
                                <i class="voyager-search" style="font-size: 50px; margin-bottom: 10px; display: block;"></i>
                                <p>No se encontraron personas con esos criterios.</p>
                                <button class="btn btn-sm btn-info" onclick="$('#input-search').val('').trigger('input')">
                                    <i class="voyager-refresh"></i> Limpiar filtros
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
    <div class="col-md-4" style="overflow-x:auto">
        @if(count($data)>0)
            <p class="text-muted">Mostrando del {{$data->firstItem()}} al {{$data->lastItem()}} de {{$data->total()}} registros.</p>
        @endif
    </div>
    <div class="col-md-8" style="overflow-x:auto">
        <nav class="text-right">
            {{ $data->links() }}
        </nav>
    </div>
</div>

<script>
    // Delegación de eventos para paginación AJAX - funciona con contenido dinámico
    $(document).off('click', '.page-link').on('click', '.page-link', function(e){
        e.preventDefault();
        let link = $(this).attr('href');
        if(link){
            let url = new URL(link);
            let page = url.searchParams.get('page') || 1;
            list(page);
        }
    });

    // Delegación de eventos para botón eliminar
    $(document).off('click', '.delete-item').on('click', '.delete-item', function(e){
        e.preventDefault();
        let url = $(this).data('url');
        deleteItem(url);
    });
</script>
