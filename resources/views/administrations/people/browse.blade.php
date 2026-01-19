@extends('voyager::master')

@section('page_title', 'Viendo Datos Personales')

@section('page_header')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" style="padding: 0px">
                        <div class="col-md-8" style="padding: 0px">
                            <h1 class="page-title">
                                <i class="voyager-person"></i> Datos Personales
                            </h1>
                        </div>
                        <div class="col-md-4 text-right" style="margin-top: 30px">
                            @if (auth()->user()->hasPermission('add_people'))
                            <a href="{{ route('voyager.people.create') }}" class="btn btn-success">
                                <i class="voyager-plus"></i> <span>Crear</span>
                            </a>
                            @endif
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
                            <div class="col-sm-9">
                                <div class="dataTables_length" id="dataTable_length">
                                    <label>Mostrar <select id="select-paginate" class="form-control input-sm" wire:model="paginate">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select> registros</label>
                                </div>
                            </div>
                            <div class="col-sm-3" style="margin-bottom: 10px">
                                <input type="text" id="input-search" placeholder="🔍 Buscar..." class="form-control" wire:model="search" autocomplete="off">
                            </div>
                        </div>
                        <div class="row" id="div-results" style="min-height: 120px">
                            <livewire:components.people.person-table />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials.modal-delete')
@stop

@section('css')
    <style>


    </style>
    @livewireStyles
@stop

@section('javascript')
    {{-- <script src="{{ url('js/main.js') }}"></script> --}}

    {{-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script> --}}
    <script>
        var countPage = 10, order = 'id', typeOrder = 'desc';
        var timeout = null;
        var isLoading = false;
        
        $(document).ready(() => {
            list();
            
            $('#input-search').on('keyup', function(e){
                if(e.keyCode == 13) {
                    clearTimeout(timeout);
                    list();
                }
            });

            $('#select-paginate').change(function(){
                countPage = $(this).val();
                list();
            });

            $('#input-search').on('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    list();
                }, 500); // Reducido de 2000ms a 500ms para mejor UX
            });
        });
        
        function list(page = 1){
            if(isLoading) return;
            
            isLoading = true;
            $('#div-results').loading({message: 'Cargando...'});

            let url = '{{ url("admin/people/ajax/list") }}';
            let search = $('#input-search').val() ? $('#input-search').val() : '';

            $.ajax({
                url: `${url}?search=${search}&paginate=${countPage}&page=${page}`,
                type: 'get',
                timeout: 10000, // Timeout de 10 segundos
                
                success: function(result){
                    $("#div-results").html(result);
                    $('#div-results').loading('toggle');
                },
                
                error: function(xhr, status, error){
                    $('#div-results').loading('toggle');
                    $('#div-results').html('<div class="alert alert-danger">Error al cargar los datos: ' + error + '</div>');
                },
                
                complete: function(){
                    isLoading = false;
                }
            });
        }
        
        function deleteItem(url){
            $('#delete_form').attr('action', url);
        }
    </script>
@stop
