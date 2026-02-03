<?php

namespace App\Http\Controllers;

use App\Models\Geografia;
use App\Models\GeografiaLimite;
use App\Models\Recinto;
use App\Models\ResumenVoto;
use App\Models\OrganizacionPolitica;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MapaController extends Controller
{
    public function __construct()
    {
    }

    public function geojson(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo');
        $parentId = $request->input('parent_id');

        // Caché de GeoJSON - Los datos geográficos no cambian cada segundo
        $cacheKey = "geojson:{$tipo}:{$parentId}";

        $data = Cache::remember($cacheKey, 3600, function () use ($tipo, $parentId) {
            $query = GeografiaLimite::with('geografia')
                ->conGeojson();

            if ($tipo) {
                $query->porTipo($tipo);
            }

            if ($parentId) {
                $query->whereHas('geografia', function ($q) use ($parentId) {
                    $q->where('parent_id', $parentId);
                });
            }

            $limites = $query->get();

            $features = $limites->map(function ($limite) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $limite->id_geografia,
                        'nombre' => $limite->geografia->nombre,
                        'tipo' => $limite->geografia->tipo,
                        'codigo_tse' => $limite->geografia->codigo_tse,
                    ],
                    'geometry' => $limite->geojson,
                ];
            });

            return [
                'type' => 'FeatureCollection',
                'features' => $features,
            ];
        });

        return response()->json($data);
    }

    public function recintosGeojson(Geografia $geografia): JsonResponse
    {
        // Caché de GeoJSON - Cachear por geografía (versión 2 - con cascada)
        $cacheKey = "recintos_geojson_v2:{$geografia->id_geografia}";

        $data = Cache::remember($cacheKey, 1800, function () use ($geografia) {
            // Buscar recintos en cascada según el nivel jerárquico
            $recintos = $this->obtenerRecintosEnCascada($geografia);

            $features = $recintos->map(function ($recinto) {
                return [
                    'type' => 'Feature',
                    'properties' => [
                        'id' => $recinto->id_recinto,
                        'nombre' => $recinto->nombre,
                        'codigo_tse' => $recinto->codigo_tse,
                    ],
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [
                            (float)$recinto->longitud,
                            (float)$recinto->latitud,
                        ],
                    ],
                ];
            });

            return [
                'type' => 'FeatureCollection',
                'features' => $features,
            ];
        });

        return response()->json($data);
    }

   public function resultados(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo', 'Departamento');
        $cargoId = $request->input('cargo_id');

        $geografias = Geografia::where('tipo', $tipo)
            ->with([
                'resumenVotos' => function ($q) use ($cargoId) {
                    if ($cargoId) {
                        $q->where('id_cargo', $cargoId);
                    }
                    // Ordenamos por total_votos descendente para que el .first() sea el ganador
                    $q->orderBy('total_votos', 'desc');
                    $q->with('partido:id_partido,sigla,color_hex');
                },
                'limite' // Importante para la geometría
            ])
            ->get();

        $features = $geografias->map(function ($geo) {
            // El ganador es el primer registro (gracias al orderBy desc)
            $votosGanador = $geo->resumenVotos->first();

            // Calculamos el total de votos sumando todos los partidos en esa geo
            $totalVotosGeo = $geo->resumenVotos->sum('total_votos');

            $color = $this->getColorForResults($votosGanador);

            return [
                'type' => 'Feature',
                'properties' => [
                    'id' => $geo->id_geografia,
                    'nombre' => $geo->nombre,
                    'tipo' => $geo->tipo,
                    'total_votos' => (int)$totalVotosGeo,
                    'color' => $color,
                    'ganador' => $votosGanador?->partido?->sigla ?? 'Sin datos',
                    'id_partido_ganador' => $votosGanador?->id_partido,
                ],
                'geometry' => $geo->limite?->geojson ?? null,
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->filter(fn($f) => $f['geometry'] !== null)->values(),
        ]);
    }

    private function getColorForResults($votosGanador): string
    {
        // Si no hay votos o el ganador tiene 0, devolvemos gris
        if (!$votosGanador || $votosGanador->total_votos == 0) {
            return '#cccccc';
        }

        // Retornamos el color hexadecimal del partido desde la relación
        return $votosGanador->partido?->color_hex ?? '#666666';
    }

    public function recintosPorGeografia(Geografia $geografia): JsonResponse
    {
        // Caché de recintos por geografía (versión 2 - con cascada)
        $cacheKey = "recintos_por_geo_v2:{$geografia->id_geografia}";

        $data = Cache::remember($cacheKey, 1800, function () use ($geografia) {
            // Buscar recintos en cascada según el nivel jerárquico
            $recintos = $this->obtenerRecintosEnCascada($geografia);

            return [
                'geografia' => [
                    'id' => $geografia->id_geografia,
                    'nombre' => $geografia->nombre,
                    'tipo' => $geografia->tipo,
                ],
                'recintos' => $recintos->map(fn($r) => [
                    'id' => $r->id_recinto,
                    'nombre' => $r->nombre,
                    'codigo_tse' => $r->codigo_tse,
                    'direccion' => $r->direccion,
                    'lat' => (float)$r->latitud,
                    'lon' => (float)$r->longitud,
                ]),
                'total' => $recintos->count(),
            ];
        });

        return response()->json($data);
    }

    /**
     *  Obtener recintos en cascada según el nivel jerárquico
     * - Municipio: Recintos directos
     * - Provincia: Recintos de todos sus municipios
     * - Departamento: Recintos de todos los municipios de todas sus provincias
     */
    private function obtenerRecintosEnCascada(Geografia $geografia)
    {
        $query = Recinto::conCoordenadasValidas();

        if ($geografia->tipo === 'Municipio') {
            // Municipio: recintos directos
            $query->where('id_geografia', $geografia->id_geografia);
        }
        elseif ($geografia->tipo === 'Provincia') {
            // Provincia: recintos de todos los municipios hijos
            $municipiosIds = Geografia::where('parent_id', $geografia->id_geografia)
                ->where('tipo', 'Municipio')
                ->pluck('id_geografia');
            $query->whereIn('id_geografia', $municipiosIds);
        }
        elseif ($geografia->tipo === 'Departamento') {
            // Departamento: recintos de todos los municipios de todas las provincias
            $provinciasIds = Geografia::where('parent_id', $geografia->id_geografia)
                ->where('tipo', 'Provincia')
                ->pluck('id_geografia');

            $municipiosIds = Geografia::whereIn('parent_id', $provinciasIds)
                ->where('tipo', 'Municipio')
                ->pluck('id_geografia');

            $query->whereIn('id_geografia', $municipiosIds);
        }

        return $query->get();
    }
}
