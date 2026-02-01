<?php

namespace App\Http\Controllers;

use App\Models\Geografia;
use App\Models\GeografiaLimite;
use App\Models\Recinto;
use App\Models\ResumenVoto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapaController extends Controller
{
    public function __construct()
    {
    }

    public function geojson(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo');
        $parentId = $request->input('parent_id');

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

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function recintosGeojson(Geografia $geografia): JsonResponse
    {
        $recintos = $geografia->recintos()
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get();

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

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
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
                }
            ])
            ->get();

        $features = $geografias->map(function ($geo) {
            $votos = $geo->resumenVotos->first();
            $color = $this->getColorForResults($votos);

            return [
                'type' => 'Feature',
                'properties' => [
                    'id' => $geo->id_geografia,
                    'nombre' => $geo->nombre,
                    'tipo' => $geo->tipo,
                    'votos_validos' => $votos?->votos_validos ?? 0,
                    'votos_blancos' => $votos?->votos_blancos ?? 0,
                    'votos_nulos' => $votos?->votos_nulos ?? 0,
                    'color' => $color,
                    'ganador' => $votos?->ganador ?? null,
                ],
                'geometry' => $geo->limite?->geojson ?? null,
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features->filter(fn($f) => $f['geometry'] !== null)->values(),
        ]);
    }

    public function recintosPorGeografia(Geografia $geografia): JsonResponse
    {
        $recintos = Recinto::where('id_geografia', $geografia->id_geografia)
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->where('latitud', '!=', 0)
            ->where('longitud', '!=', 0)
            ->get();

        return response()->json([
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
        ]);
    }

    private function getColorForResults($votos): string
    {
        if (!$votos) {
            return '#cccccc';
        }

        $total = $votos->votos_validos;
        if ($total == 0) {
            return '#cccccc';
        }

        $ganador = $votos->ganador;
        return match($ganador) {
            'MAS' => '#009739',
            'CC' => '#0066cc',
            'FPV' => '#ffcc00',
            default => '#666666',
        };
    }
}
