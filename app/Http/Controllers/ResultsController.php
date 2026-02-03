<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ResumenVoto;
use App\Models\Cargo;
use App\Models\Geografia;
use Illuminate\Support\Facades\Cache;

class ResultsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // TTL reducido a 60 segundos para conteo "en vivo"
        $cacheKey = 'election_live_results';

        $results = Cache::remember($cacheKey, 60, function () {
            return ResumenVoto::query()
                ->with(['geografia:id_geografia,nombre', 'cargo:id_cargo,descripcion'])
                ->select(['id_cargo', 'id_geografia', 'id_partido', 'total_votos', 'total_mesas_escrutadas', 'porcentaje_votos'])
                ->orderBy('id_geografia')
                ->orderBy('id_cargo')
                ->get()
                ->groupBy(['id_cargo', 'id_geografia']);
        });

        $cargos = Cache::remember('cargos:all', 3600, function () {
            return Cargo::all(['id_cargo', 'descripcion', 'nivel']);
        });

        return view('results.index', compact('results', 'cargos'));
    }

    public function showByCargo($cargoId)
    {
        $cacheKey = "results:cargo:{$cargoId}";

        // TTL reducido a 60 segundos para resultados en tiempo real
        $results = Cache::remember($cacheKey, 60, function () use ($cargoId) {
            return ResumenVoto::query()
                ->with(['geografia:id_geografia,nombre', 'organizacionPolitica:id_partido,nombre,sigla,color_hex'])
                ->where('id_cargo', $cargoId)
                ->select(['id_cargo', 'id_geografia', 'id_partido', 'total_votos', 'total_mesas_escrutadas', 'porcentaje_votos'])
                ->orderBy('total_votos', 'desc')
                ->get();
        });

        $cargo = Cargo::find($cargoId);

        return response()->json([
            'success' => true,
            'data' => [
                'cargo' => $cargo,
                'results' => $results,
            ],
        ]);
    }

    public function showByGeografia($geografiaId)
    {
        $cacheKey = "results:geografia:{$geografiaId}";

        // TTL reducido a 60 segundos para resultados en tiempo real
        $results = Cache::remember($cacheKey, 60, function () use ($geografiaId) {
            return ResumenVoto::query()
                ->with(['cargo:id_cargo,descripcion,nivel', 'organizacionPolitica:id_partido,nombre,sigla,color_hex'])
                ->where('id_geografia', $geografiaId)
                ->select(['id_cargo', 'id_geografia', 'id_partido', 'total_votos', 'total_mesas_escrutadas', 'porcentaje_votos'])
                ->orderBy('id_cargo')
                ->orderBy('total_votos', 'desc')
                ->get();
        });

        $geografia = Geografia::find($geografiaId);

        return response()->json([
            'success' => true,
            'data' => [
                'geografia' => $geografia,
                'results' => $results,
            ],
        ]);
    }

    public function clearCache()
    {
        // Limpiar la nueva clave de caché en vivo
        Cache::forget('election_live_results');

        return response()->json([
            'success' => true,
            'message' => 'Caché de resultados limpiada exitosamente',
        ]);
    }
}
