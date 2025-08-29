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
                    <td>{{ $item->ci }}</td>
                    <td>
                        <table>
                            @php
                                $image = asset('images/default.jpg');
                                if($item->image){
                                    $image = $item->image
                                        ? asset('storage/' . $item->image)
                                        : asset('images/default.jpg');
                                }
                                $now = \Carbon\Carbon::now();
                                $birthday = new \Carbon\Carbon($item->birth_date);
                                $age = $birthday->diffInYears($now);
                            @endphp
                            <tr>
                                <td><img src="{{ $image }}" alt="{{ $item->first_name }} " style="width: 60px; height: 60px; border-radius: 30px; margin-right: 10px"></td>
                                <td>
                                    {{-- {{ strtoupper($item->first_name) }} {{ $item->middle_name??strtoupper($item->middle_name) }} {{ strtoupper($item->paternal_surname) }}  {{ strtoupper($item->maternal_surname) }} --}}
                                    {{ strtoupper($item->full_name) }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="text-align: center">
                        @if ($item->birth_date)
                            {{ date('d/m/Y', strtotime($item->birth_date)) }} <br> <small>{{ $age }} años</small>
                        @else
                            Sin Datos
                        @endif
                    </td>
                    <td style="text-align: center">{{ $item->phone?$item->phone:'SN' }}</td>
                    <td style="text-align: center">
                       <span class="label label-{{ $item->status == 1 ? 'success' : 'warning' }}">
                            {{ $item->status == 1 ? 'Activo' : 'Inactivo' }}
                        </span>


                    </td>
                    <td style="width: 18%" class="no-sort no-click bread-actions text-right">
                        @if (auth()->user()->hasPermission('read_people'))
                            <a href="{{ route('voyager.people.show', ['id' => $item->id]) }}" title="Ver" class="btn btn-sm btn-warning view">
                                <i class="voyager-eye"></i> <span class="hidden-xs hidden-sm">Ver</span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('edit_people'))
                            <a href="{{ route('voyager.people.edit', ['id' => $item->id]) }}" title="Editar" class="btn btn-sm btn-primary edit">
                                <i class="voyager-edit"></i> <span class="hidden-xs hidden-sm">Editar</span>
                            </a>
                        @endif
                        @if (auth()->user()->hasPermission('delete_people'))
                            <a href="#" onclick="deleteItem('{{ route('voyager.people.destroy', ['id' => $item->id]) }}')" title="Eliminar" data-toggle="modal" data-target="#modal-delete" class="btn btn-sm btn-danger delete">
                                <i class="voyager-trash"></i> <span class="hidden-xs hidden-sm">Eliminar</span>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <h5 class="text-center" style="margin-top: 50px">
                                <img src="{{ asset('images/empty.png') }}" width="120px" alt="" style="opacity: 0.8">
                                <br><br>
                                No hay resultados
                            </h5>
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

   var page = "{{ request('page') }}";
    $(document).ready(function(){
        $('.page-link').click(function(e){
            e.preventDefault();
            // let link = $(this).attr('href');
            let url = new URL($(this).attr('href'));
            if(link){
                // page = link.split('=')[1];
                let page = url.searchParams.get('page') || 1;
                list(page);
            }
        });
    });
</script>
