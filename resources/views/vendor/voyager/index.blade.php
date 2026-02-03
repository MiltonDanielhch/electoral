@extends('voyager::master')

@section('page_header')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h2>Hola, {{ Auth::user()->name }}</h2>
                                <p class="text-muted">Resumen de rendimiento - {{ now()->format('d F Y') }}</p>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="refresh-dashboard">
                                        <i class="voyager-refresh"></i> Actualizar
                                    </button>
                                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="#" data-range="today">Hoy</a></li>
                                        <li><a href="#" data-range="week">Esta semana</a></li>
                                        <li><a href="#" data-range="month">Este mes</a></li>
                                        <li><a href="#" data-range="year">Este año</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')

@stop

