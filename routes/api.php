<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActaController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\MapaController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/catalogos', [CatalogoController::class, 'index'])->name('api.v1.catalogos');

    Route::get('/mesa/{codigo}', [MesaController::class, 'show'])->name('api.v1.mesa.show');

    Route::post('/acta', [ActaController::class, 'store'])->name('api.v1.acta.store');
});

Route::prefix('mapas')->group(function () {
    Route::get('/geojson', [MapaController::class, 'geojson'])->name('api.mapas.geojson');
    Route::get('/resultados', [MapaController::class, 'resultados'])->name('api.mapas.resultados');
    Route::get('/geografias/{geografia}/recintos', [MapaController::class, 'recintosPorGeografia'])->name('api.mapas.recintos');
    Route::get('/geografias/{geografia}/geojson', [MapaController::class, 'recintosGeojson'])->name('api.mapas.recintos-geojson');
});

Route::prefix('public/mapas')->group(function () {
    Route::get('/geojson', [MapaController::class, 'geojson'])->name('api.public.mapas.geojson');
    Route::get('/resultados', [MapaController::class, 'resultados'])->name('api.public.mapas.resultados');
});

