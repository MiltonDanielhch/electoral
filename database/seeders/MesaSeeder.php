<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        // Traemos todos los recintos para asegurar que ninguno se quede sin mesas
        $recintos = DB::table('recintos')->get();

        foreach ($recintos as $recinto) {
            $cantidadMesas = $this->obtenerCantidadMesas($recinto->nombre);

            for ($i = 1; $i <= $cantidadMesas; $i++) {
                // SINTONÍA DE CÓDIGO:
                // Código Recinto (supongamos 3 dígitos) + relleno + número mesa
                // Total: 11 dígitos para que el TRIGGER no lo rechace
                $codigoMesa = str_pad($recinto->codigo_tse, 6, "0", STR_PAD_LEFT) . str_pad($i, 5, "0", STR_PAD_LEFT);

                DB::table('mesas')->updateOrInsert(
                    ['codigo_tse' => $codigoMesa],
                    [
                        'id_recinto' => $recinto->id_recinto,
                        'numero_mesa' => $i,
                        'estado'     => 'Habilitada',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $this->command->info('Mesas habilitadas en todos los recintos del Beni.');
    }

    /**
     * Define la cantidad de mesas según el nombre del recinto (Sintonía Manual)
     */
    private function obtenerCantidadMesas($nombre)
    {
        return match ($nombre) {
            'Unidad Educativa 6 de Junio'         => 10,
            'Unidad Educativa Bolivia'            => 6,
            'Colegio Nacional San Ignacio'        => 5,
            'Unidad Educativa Petrolera'          => 4,
            'Centro Cultural René Moreno'         => 4,
            'Unidad Educativa General Ballivián'  => 8,
            default                               => 3, // Cantidad mínima para recintos no especificados
        };
    }
}
