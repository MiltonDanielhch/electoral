<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\OrganizacionPoliticaController;
use App\Http\Controllers\GeografiaController;
use App\Http\Controllers\RecintoController;
use App\Http\Controllers\MesaController;
use App\Http\Controllers\CandidatoController;
use TCG\Voyager\Facades\Voyager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirección raíz y login
Route::redirect('login', 'admin/login')->name('login');
Route::redirect('/', 'admin');


// Rutas públicas
Route::get('/mapa-resultados', function () {
    return view('dashboard.mapa-resultados');
})->name('mapa.resultados');

// Grupo principal con middleware personalizado
Route::prefix('admin')->middleware(['loggin', 'system'])->group(function () {

    // Rutas de Voyager (Colocadas al final para que no sobrescriban tus rutas personalizadas como 'people')
    Voyager::routes();
    // ──────────────── PERSONAS ────────────────
    Route::get('people', [PersonController::class, 'index'])->name('admin.people.index');
    Route::get('people/create', [PersonController::class, 'create'])->name('admin.people.create');
    Route::post('people', [PersonController::class, 'store'])->name('admin.people.store');
    Route::get('people/{person}', [PersonController::class, 'show'])->name('admin.people.show');
    Route::get('people/{person}/edit', [PersonController::class, 'edit'])->name('admin.people.edit');
    Route::put('people/{person}', [PersonController::class, 'update'])->name('admin.people.update');
    Route::delete('people/{person}', [PersonController::class, 'destroy'])->name('admin.people.destroy');
    Route::get('people/ajax/list', [PersonController::class, 'list'])->name('admin.people.ajax.list');


    // ──────────────── USUARIOS ────────────────
    Route::prefix('users')->group(function () {
        Route::get('/ajax/list', [UserController::class, 'list'])->name('voyager.users.ajax.list');
        Route::post('/store', [UserController::class, 'store'])->name('voyager.users.store');
        Route::put('/{id}', [UserController::class, 'update'])->name('voyager.users.update');
        Route::delete('/{id}/deleted', [UserController::class, 'destroy'])->name('voyager.users.destroy');
    });

    // ──────────────── ROLES ────────────────
    Route::prefix('roles')->group(function () {
        Route::get('/ajax/list', [RoleController::class, 'list'])->name('voyager.roles.ajax.list');
    });

    // ──────────────── CARGOS ────────────────
    Route::prefix('cargos')->group(function () {
        Route::get('/ajax/list', [CargoController::class, 'list'])->name('admin.cargos.ajax.list');
        Route::get('/', [CargoController::class, 'index'])->name('admin.cargos.index');
        Route::post('/', [CargoController::class, 'store'])->name('admin.cargos.store');
        Route::get('/{cargo}/edit', [CargoController::class, 'edit'])->name('admin.cargos.edit');
        Route::put('/{cargo}', [CargoController::class, 'update'])->name('admin.cargos.update');
        Route::get('/create', [CargoController::class, 'create'])->name('admin.cargos.create');
        Route::get('/{cargo}', [CargoController::class, 'show'])->name('admin.cargos.show');
        Route::delete('/{cargo}', [CargoController::class, 'destroy'])->name('admin.cargos.destroy');
    });

    // ──────────────── ORGANIZACIONES POLÍTICAS ────────────────
    Route::prefix('organizaciones_politicas')->group(function () {
        Route::get('/ajax/list', [OrganizacionPoliticaController::class, 'list'])->name('admin.organizaciones_politicas.ajax.list');
        Route::get('/', [OrganizacionPoliticaController::class, 'index'])->name('admin.organizaciones_politicas.index');
        Route::post('/', [OrganizacionPoliticaController::class, 'store'])->name('admin.organizaciones_politicas.store');
        Route::get('/{organizacion}/edit', [OrganizacionPoliticaController::class, 'edit'])->name('admin.organizaciones_politicas.edit');
        Route::put('/{organizacion}', [OrganizacionPoliticaController::class, 'update'])->name('admin.organizaciones_politicas.update');
        Route::get('/create', [OrganizacionPoliticaController::class, 'create'])->name('admin.organizaciones_politicas.create');
        Route::get('/{organizacion}', [OrganizacionPoliticaController::class, 'show'])->name('admin.organizaciones_politicas.show');
        Route::delete('/{organizacion}', [OrganizacionPoliticaController::class, 'destroy'])->name('admin.organizaciones_politicas.destroy');
    });

    // ──────────────── GEOGRAFÍAS ────────────────
    Route::prefix('geografias')->group(function () {
        Route::get('/ajax/list', [GeografiaController::class, 'list'])->name('admin.geografias.ajax.list');
        Route::get('/ajax/parents', [GeografiaController::class, 'ajaxParents'])->name('admin.geografias.parents');
        Route::get('/', [GeografiaController::class, 'index'])->name('admin.geografias.index');
        Route::post('/', [GeografiaController::class, 'store'])->name('admin.geografias.store');
        Route::get('/{geografia}/edit', [GeografiaController::class, 'edit'])->name('admin.geografias.edit');
        Route::put('/{geografia}', [GeografiaController::class, 'update'])->name('admin.geografias.update');
        Route::get('/create', [GeografiaController::class, 'create'])->name('admin.geografias.create');
        Route::get('/{geografia}', [GeografiaController::class, 'show'])->name('admin.geografias.show');
        Route::get('/{geografia}/mapa', [GeografiaController::class, 'mapaRecintos'])->name('admin.geografias.mapa');
        Route::delete('/{geografia}', [GeografiaController::class, 'destroy'])->name('admin.geografias.destroy');
    });

    // ──────────────── RECINTOS ────────────────
    Route::prefix('recintos')->group(function () {
        // Sintonía: Throttle para evitar abusos en AJAX (60 peticiones/min)
        Route::get('/ajax/list', [RecintoController::class, 'list'])
            ->name('admin.recintos.ajax.list')
            ->middleware('throttle:60,1');
        Route::get('/', [RecintoController::class, 'index'])->name('admin.recintos.index');
        Route::post('/', [RecintoController::class, 'store'])->name('admin.recintos.store');
        Route::get('/{recinto}/edit', [RecintoController::class, 'edit'])->name('admin.recintos.edit');
        Route::put('/{recinto}', [RecintoController::class, 'update'])->name('admin.recintos.update');
        Route::get('/create', [RecintoController::class, 'create'])->name('admin.recintos.create');
        Route::get('/{recinto}', [RecintoController::class, 'show'])->name('admin.recintos.show');
        Route::delete('/{recinto}', [RecintoController::class, 'destroy'])->name('admin.recintos.destroy');
    });

    // ──────────────── MESAS ────────────────
    Route::prefix('mesas')->group(function () {
        Route::get('/ajax/list', [MesaController::class, 'list'])->name('admin.mesas.ajax.list');
        Route::get('/', [MesaController::class, 'index'])->name('admin.mesas.index');
        Route::post('/', [MesaController::class, 'store'])->name('admin.mesas.store');
        Route::get('/{mesa}/edit', [MesaController::class, 'edit'])->name('admin.mesas.edit');
        Route::put('/{mesa}', [MesaController::class, 'update'])->name('admin.mesas.update');
        Route::get('/create', [MesaController::class, 'create'])->name('admin.mesas.create');
        Route::get('/{mesa}', [MesaController::class, 'show'])->name('admin.mesas.show');
        Route::delete('/{mesa}', [MesaController::class, 'destroy'])->name('admin.mesas.destroy');
    });

    // ──────────────── CANDIDATOS ────────────────
    Route::prefix('candidatos')->group(function () {
        Route::get('/ajax/list', [CandidatoController::class, 'list'])->name('admin.candidatos.ajax.list');
        Route::get('/', [CandidatoController::class, 'index'])->name('admin.candidatos.index');
        Route::get('/create', [CandidatoController::class, 'create'])->name('admin.candidatos.create');
        Route::post('/', [CandidatoController::class, 'store'])->name('admin.candidatos.store');
        Route::get('/{candidato}/edit', [CandidatoController::class, 'edit'])->name('admin.candidatos.edit');
        Route::put('/{candidato}', [CandidatoController::class, 'update'])->name('admin.candidatos.update');
        Route::get('/{candidato}', [CandidatoController::class, 'show'])->name('admin.candidatos.show');
        Route::delete('/{candidato}', [CandidatoController::class, 'destroy'])->name('admin.candidatos.destroy');
    });

    // ──────────────── AJAX GENÉRICO ────────────────
    Route::prefix('ajax')->group(function () {
        Route::get('/personList', [AjaxController::class, 'personList']);
        Route::post('/person/store', [AjaxController::class, 'personStore']);
    });

    // ──────────────── UTILIDADES ────────────────
    Route::get('/clear-cache', function () {
        Artisan::call('optimize:clear');
        return redirect('/admin/profile')->with([
            'message' => 'Cache eliminada.',
            'alert-type' => 'success'
        ]);
    })->name('clear.cache');
});
