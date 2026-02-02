<?php

namespace App\Observers;

use App\Models\VotoXPartido;
use App\Models\ActaEscrutinio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VotoXPartidoObserver
{
    /**
     * Handle the VotoXPartido "created" event.
     */
    public function created(VotoXPartido $voto)
    {
        $this->actualizarResumen($voto);
    }

    /**
     * Handle the VotoXPartido "updated" event.
     */
    public function updated(VotoXPartido $voto)
    {
        $this->actualizarResumen($voto);
    }

    private function actualizarResumen($voto)
    {
        // 1. Obtener la mesa y su geografía (Municipio) con toda la jerarquía
        // Usamos eager loading para evitar N+1 queries en la navegación hacia arriba
        $acta = ActaEscrutinio::with(['mesa.recinto.geografia.parent.parent'])->find($voto->id_acta);

        if (!$acta || !$acta->mesa || !$acta->mesa->recinto || !$acta->mesa->recinto->geografia) {
            return;
        }

        $municipio = $acta->mesa->recinto->geografia;
        $provincia = $municipio->parent;
        $departamento = $provincia ? $provincia->parent : null;

        // Creamos un array limpio con los niveles que existen
        $niveles = array_filter([$municipio, $provincia, $departamento]);

        foreach ($niveles as $geo) {
            // Obtener IDs de recintos que pertenecen a esta geografía (usando el helper del modelo Geografia)
            $recintosIds = $geo->todos_los_recintos->pluck('id_recinto');

            if ($recintosIds->isEmpty()) continue;

            // A. Calcular Total Votos para este partido en esta geografía
            $totalVotos = VotoXPartido::where('id_partido', $voto->id_partido)
                ->whereHas('actaEscrutinio.mesa', function($q) use ($recintosIds) {
                    $q->whereIn('id_recinto', $recintosIds);
                })
                ->sum('votos');

            // B. Calcular Total Mesas Escrutadas (que tienen actas digitadas)
            $totalMesas = ActaEscrutinio::where('id_cargo', $acta->id_cargo)
                ->where('estado', 'Digitada')
                ->whereHas('mesa', function($q) use ($recintosIds) {
                    $q->whereIn('id_recinto', $recintosIds);
                })
                ->count();

            // C. Actualizar o Crear el resumen
            DB::table('resumen_votos')->updateOrInsert(
                [
                    'id_geografia' => $geo->id_geografia,
                    'id_cargo' => $acta->id_cargo,
                    'id_partido' => $voto->id_partido,
                ],
                [
                    'total_votos' => $totalVotos,
                    'total_mesas_escrutadas' => $totalMesas,
                    'ultima_actualizacion' => now(),
                ]
            );

            // D. Recalcular porcentajes para esta geografía (Sintonía Fina)
            $totalVotosGeo = DB::table('resumen_votos')
                ->where('id_geografia', $geo->id_geografia)
                ->where('id_cargo', $acta->id_cargo)
                ->sum('total_votos');

            if ($totalVotosGeo > 0) {
                DB::table('resumen_votos')
                    ->where('id_geografia', $geo->id_geografia)
                    ->where('id_cargo', $acta->id_cargo)
                    ->update([
                        'porcentaje_votos' => DB::raw("ROUND((total_votos / $totalVotosGeo) * 100, 2)")
                    ]);
            }
        }

        // 3. Limpiar la sintonía del mapa para que el frontend vea los cambios
        Cache::flush();
    }
}
