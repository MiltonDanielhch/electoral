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

// Cambiamos el throttle de IP a Usuario Autenticado para el día de la elección
Route::prefix('v1')
    ->middleware(['auth:sanctum', 'throttle:120,1']) // Doblamos el límite y lo atamos al usuario
    ->group(function () {
        Route::get('/catalogos', [CatalogoController::class, 'index'])->name('api.v1.catalogos');

        Route::get('/mesas/estadisticas', [MesaController::class, 'index'])->name('api.v1.mesas.estadisticas');
        Route::get('/mesa/{codigo}', [MesaController::class, 'show'])->name('api.v1.mesa.show');

        Route::post('/acta', [ActaController::class, 'store'])->name('api.v1.acta.store');
    });

Route::prefix('mapas')->group(function () {
    Route::get('/geojson', [MapaController::class, 'geojson'])->name('api.mapas.geojson');
    Route::get('/resultados', [MapaController::class, 'resultados'])->name('api.mapas.resultados');
    Route::get('/geografias/{geografia}/recintos', [MapaController::class, 'recintosPorGeografia'])->name('api.mapas.recintos');
    Route::get('/geografias/{geografia}/geojson', [MapaController::class, 'recintosGeojson'])->name('api.mapas.recintos-geojson');
});

// Rutas públicas con protección contra abuso (bots)
Route::prefix('public/mapas')
    ->middleware('throttle:30,1') // Máximo 30 refrescos por minuto por usuario
    ->group(function () {
        Route::get('/geojson', [MapaController::class, 'geojson'])->name('api.public.mapas.geojson');
        Route::get('/resultados', [MapaController::class, 'resultados'])->name('api.public.mapas.resultados');
    });

