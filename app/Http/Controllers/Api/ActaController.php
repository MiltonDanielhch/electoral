<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreActaRequest;
use App\Models\ActaEscrutinio;
use App\Models\Mesa;
use App\Models\VotoXPartido;
use App\Models\AuditoriaActa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ActaController extends Controller
{
    public function store(StoreActaRequest $request)
    {
        try {
            DB::beginTransaction();

            $mesa = Mesa::where('codigo_tse', $request->codigo_mesa)->first();

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
                'ip_origen' => $request->ip(),
            ]);

            DB::commit();

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
}
