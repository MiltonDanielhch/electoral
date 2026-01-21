<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use Illuminate\Http\Request;

class MesaController extends Controller
{
    public function show($codigo)
    {
        $mesa = Mesa::with([
            'recinto:id_recinto,nombre,direccion,id_geografia',
            'recinto.geografia:id_geografia,nombre,tipo',
            'actasEscrutinio:id_acta,id_mesa,id_cargo,codigo_acta,estado',
            'actasEscrutinio.cargo:id_cargo,descripcion'
        ])->where('codigo_tse', $codigo)
            ->first(['id_mesa', 'id_recinto', 'codigo_tse', 'estado']);

        if (!$mesa) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_mesa' => $mesa->id_mesa,
                'codigo_tse' => $mesa->codigo_tse,
                'estado' => $mesa->estado,
                'recinto' => [
                    'id_recinto' => $mesa->recinto->id_recinto,
                    'nombre' => $mesa->recinto->nombre,
                    'direccion' => $mesa->recinto->direccion,
                    'geografia' => [
                        'id_geo' => $mesa->recinto->geografia->id_geografia,
                        'nombre' => $mesa->recinto->geografia->nombre,
                        'tipo' => $mesa->recinto->geografia->tipo,
                    ],
                ],
                'actas' => $mesa->actasEscrutinio->map(function ($acta) {
                    return [
                        'id_acta' => $acta->id_acta,
                        'codigo_acta' => $acta->codigo_acta,
                        'cargo' => $acta->cargo->descripcion,
                        'estado' => $acta->estado,
                    ];
                }),
            ],
        ], 200);
    }
}
