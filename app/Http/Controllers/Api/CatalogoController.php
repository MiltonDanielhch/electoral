<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrganizacionPolitica;
use App\Models\Cargo;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'partidos_politicos' => OrganizacionPolitica::where('estado', 'Activo')
                    ->get(['id_partido', 'codigo_tse', 'nombre', 'sigla', 'color_hex'])
                    ->map(function ($partido) {
                        return [
                            'id_partido' => $partido->id_partido,
                            'codigo_tse' => $partido->codigo_tse,
                            'nombre' => $partido->nombre,
                            'sigla' => $partido->sigla,
                            'color_hex' => $partido->color_hex,
                        ];
                    }),
                'cargos' => Cargo::all(['id_cargo', 'descripcion', 'nivel', 'tipo_acta', 'acta_unica'])
                    ->map(function ($cargo) {
                        return [
                            'id_cargo' => $cargo->id_cargo,
                            'descripcion' => $cargo->descripcion,
                            'nivel' => $cargo->nivel,
                            'tipo_acta' => $cargo->tipo_acta,
                            'acta_unica' => $cargo->acta_unica,
                        ];
                    }),
            ],
        ], 200);
    }
}
