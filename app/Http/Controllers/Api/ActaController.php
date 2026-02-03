<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreActaRequest;
use App\Models\ActaEscrutinio;
use App\Models\Mesa;
use App\Models\VotoXPartido;
use App\Models\Geografia;
use App\Models\AuditoriaActa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Exception;

class ActaController extends Controller
{
    public function store(StoreActaRequest $request)
    {
        try {
            DB::beginTransaction();

            $mesa = Mesa::where('codigo_tse', $request->codigo_mesa)
                ->first(['id_mesa', 'codigo_tse', 'estado']);

            $fotoFrontalPath = null;
            $fotoReversoPath = null;

            if ($request->hasFile('foto_frontal')) {
                $fotoFrontalPath = $request->file('foto_frontal')->store('actas/fotos', 'public');
            }

            if ($request->hasFile('foto_reverso')) {
                $fotoReversoPath = $request->file('foto_reverso')->store('actas/fotos', 'public');
            }

            $acta = ActaEscrutinio::create([
                'id_mesa' => $mesa->id_mesa,
                'id_cargo' => $request->id_cargo,
                'codigo_acta' => $request->codigo_acta,
                'foto_frontal' => $fotoFrontalPath,
                'foto_reverso' => $fotoReversoPath,
                'total_sobres' => $request->total_sobres,
                'total_votantes' => $request->total_votantes,
                'votos_validos' => $request->votos_validos,
                'votos_blancos' => $request->votos_blancos,
                'votos_nulos' => $request->votos_nulos,
                'votos_impugnados' => $request->votos_impugnados ?? 0,
                'digitador' => $request->digitador,
                'estado' => 'Digitada',
            ]);

            foreach ($request->votos_partido as $voto) {
                VotoXPartido::create([
                    'id_acta' => $acta->id_acta,
                    'id_partido' => $voto['id_partido'],
                    'votos' => $voto['votos'],
                ]);
            }

            AuditoriaActa::create([
                'id_acta' => $acta->id_acta,
                'accion' => 'CREACION',
                'usuario' => $request->digitador,
                'detalles' => 'Acta creada exitosamente',
                'campo_modificado' => 'N/A',
                'ip_origen' => $request->ip(),
            ]);

            // Actualizar ResumenVoto para que el mapa se pinte en tiempo real
            // COMENTADO: Ahora esto lo maneja automáticamente el VotoXPartidoObserver
            // $this->actualizarResumenVotos($acta, $request->votos_partido);

            DB::commit();

            // Limpiar caché para ver resultados inmediatos en el mapa
            Cache::flush();

            return response()->json([
                'success' => true,
                'message' => 'Acta registrada exitosamente',
                'data' => [
                    'id_acta' => $acta->id_acta,
                    'codigo_acta' => $acta->codigo_acta,
                    'estado' => $acta->estado,
                ],
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();

            if ($fotoFrontalPath) {
                Storage::disk('public')->delete($fotoFrontalPath);
            }
            if ($fotoReversoPath) {
                Storage::disk('public')->delete($fotoReversoPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el acta',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualiza la tabla resumen_votos sumando los nuevos votos
     * y recalculando porcentajes para la geografía de la mesa y sus padres.
     */
    private function actualizarResumenVotos($acta, $votosPartido)
    {
        // Recuperamos la mesa con su jerarquía geográfica completa
        $mesa = Mesa::with('recinto.geografia')->find($acta->id_mesa);

        if (!$mesa || !$mesa->recinto || !$mesa->recinto->geografia) {
            return;
        }

        // Construimos la cadena de jerarquía (Municipio -> Provincia -> Departamento)
        $geografias = [];
        $geoActual = $mesa->recinto->geografia;

        while ($geoActual) {
            $geografias[] = $geoActual->id_geografia;
            // Navegamos hacia el padre
            $geoActual = Geografia::find($geoActual->parent_id);
        }

        // Para cada nivel geográfico, actualizamos el resumen
        foreach ($geografias as $geoId) {
            foreach ($votosPartido as $voto) {
                $partidoId = $voto['id_partido'];
                $cantidadVotos = $voto['votos'];

                // Usamos updateOrInsert para manejar la lógica de suma
                // Nota: updateOrInsert no permite sumar directamente en la query de forma atómica simple
                // así que leemos y luego escribimos (dentro de la transacción principal)
                $registro = DB::table('resumen_votos')
                    ->where('id_cargo', $acta->id_cargo)
                    ->where('id_geografia', $geoId)
                    ->where('id_partido', $partidoId)
                    ->first();

                $totalVotos = $registro ? $registro->total_votos + $cantidadVotos : $cantidadVotos;
                $totalMesas = $registro ? $registro->total_mesas_escrutadas + 1 : 1;

                DB::table('resumen_votos')->updateOrInsert(
                    ['id_cargo' => $acta->id_cargo, 'id_geografia' => $geoId, 'id_partido' => $partidoId],
                    ['total_votos' => $totalVotos, 'total_mesas_escrutadas' => $totalMesas, 'ultima_actualizacion' => now()]
                );
            }

            // Recalcular porcentajes para esta geografía
            $totalVotosGeo = DB::table('resumen_votos')
                ->where('id_cargo', $acta->id_cargo)->where('id_geografia', $geoId)->sum('total_votos');

            if ($totalVotosGeo > 0) {
                $partidos = DB::table('resumen_votos')->where('id_cargo', $acta->id_cargo)->where('id_geografia', $geoId)->get();
                foreach ($partidos as $p) {
                    DB::table('resumen_votos')->where('id_cargo', $p->id_cargo)->where('id_geografia', $p->id_geografia)->where('id_partido', $p->id_partido)
                        ->update(['porcentaje_votos' => round(($p->total_votos / $totalVotosGeo) * 100, 2)]);
                }
            }
        }
    }
}
