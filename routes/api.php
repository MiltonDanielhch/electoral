<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActaController;
use App\Http\Controllers\Api\MesaController;
use App\Http\Controllers\Api\CatalogoController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/catalogos', [CatalogoController::class, 'index'])->name('api.v1.catalogos');

    Route::get('/mesa/{codigo}', [MesaController::class, 'show'])->name('api.v1.mesa.show');

    Route::post('/acta', [ActaController::class, 'store'])->name('api.v1.acta.store');
});
