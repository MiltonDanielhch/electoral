<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MesaResource;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MesaController extends Controller
{
    /**
     * Dashboard de Sintonía de Transmisión
     * Retorna estadísticas en tiempo real de mesas
     */
    public function index()
    {
        $estadisticas = DB::table('mesas')
            ->selectRaw('
                COUNT(*) as total_mesas,
                SUM(CASE WHEN estado = "Escrutada" THEN 1 ELSE 0 END) as escrutadas,
                SUM(CASE WHEN estado = "Habilitada" THEN 1 ELSE 0 END) as habilitadas,
                SUM(CASE WHEN estado = "Anulada" THEN 1 ELSE 0 END) as anuladas,
                SUM(CASE WHEN estado = "Observada" THEN 1 ELSE 0 END) as observadas,
                SUM(cantidad_electores) as total_electores
            ')
            ->first();

        $estadisticas->porcentaje_escrutadas = $estadisticas->total_mesas > 0
            ? round(($estadisticas->escrutadas / $estadisticas->total_mesas) * 100, 2)
            : 0;

        $estadisticas->faltantes = $estadisticas->total_mesas - $estadisticas->escrutadas;
        $estadisticas->porcentaje_faltantes = $estadisticas->total_mesas > 0
            ? round(($estadisticas->faltantes / $estadisticas->total_mesas) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => $estadisticas,
        ], 200);
    }

    public function show($codigo)
    {
        $mesa = Mesa::with([
            'recinto:id_recinto,nombre,direccion,id_geografia',
            'recinto.geografia:id_geografia,nombre,tipo',
            'actasEscrutinio:id_acta,id_mesa,id_cargo,codigo_acta,estado,foto_frontal,foto_reverso,total_sobres,total_votantes,votos_validos,votos_blancos,votos_nulos',
            'actasEscrutinio.cargo:id_cargo,descripcion'
        ])->where('codigo_tse', $codigo)
            ->first(['id_mesa', 'id_recinto', 'codigo_tse', 'estado', 'cantidad_electores', 'numero_mesa', 'created_at', 'updated_at']);

        if (!$mesa) {
            return response()->json([
                'success' => false,
                'message' => 'Mesa no encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new MesaResource($mesa),
        ], 200);
    }
}
