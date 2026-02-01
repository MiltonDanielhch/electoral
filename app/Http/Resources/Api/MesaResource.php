<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_mesa' => $this->id_mesa,
            'codigo_tse' => $this->codigo_tse,
            'estado' => $this->estado,
            'cantidad_electores' => $this->cantidad_electores,
            'numero_mesa' => $this->numero_mesa,
            'recinto' => [
                'id_recinto' => $this->recinto->id_recinto,
                'nombre' => $this->recinto->nombre,
                'direccion' => $this->recinto->direccion,
                'geografia' => $this->recinto->geografia ? [
                    'id_geo' => $this->recinto->geografia->id_geografia,
                    'nombre' => $this->recinto->geografia->nombre,
                    'tipo' => $this->recinto->geografia->tipo,
                ] : null,
            ],
            'actas' => $this->whenLoaded('actasEscrutinio', function () {
                return $this->actasEscrutinio->map(function ($acta) {
                    return [
                        'id_acta' => $acta->id_acta,
                        'codigo_acta' => $acta->codigo_acta,
                        'cargo' => $acta->cargo ? $acta->cargo->descripcion : null,
                        'estado' => $acta->estado,
                        'foto_frontal' => $acta->foto_frontal,
                        'foto_reverso' => $acta->foto_reverso,
                        'total_sobres' => $acta->total_sobres,
                        'total_votantes' => $acta->total_votantes,
                        'votos_validos' => $acta->votos_validos,
                        'votos_blancos' => $acta->votos_blancos,
                        'votos_nulos' => $acta->votos_nulos,
                    ];
                });
            }, []),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
