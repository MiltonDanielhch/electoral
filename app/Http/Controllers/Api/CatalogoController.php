<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizacionPolitica;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogoController extends Controller
{
    public function index()
    {
        $catalogos = Cache::rememberForever('catalogos:all', function () {
            return [
                'partidos_politicos' => OrganizacionPolitica::where('estado', 'Activo')
                    ->get(['id_partido', 'codigo_tse', 'nombre', 'sigla', 'color_hex']),
                'cargos' => Cargo::all(['id_cargo', 'descripcion', 'nivel', 'tipo_acta', 'acta_unica']),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $catalogos,
        ], 200);
    }
}
