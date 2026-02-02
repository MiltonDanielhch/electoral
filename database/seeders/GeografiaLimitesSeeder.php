<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Geografia;

class GeografiaLimitesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // GeoJSON simplificados (Cajas delimitadoras aproximadas) para pruebas
        $departamentos = [
            'Beni' => '{"type":"Polygon","coordinates":[[[-67.5,-10.5],[-63.0,-10.5],[-63.0,-16.0],[-67.5,-16.0],[-67.5,-10.5]]]}',
            'La Paz' => '{"type":"Polygon","coordinates":[[[-69.5,-12.5],[-66.5,-12.5],[-66.5,-17.5],[-69.5,-17.5],[-69.5,-12.5]]]}',
            'Santa Cruz' => '{"type":"Polygon","coordinates":[[[-65.0,-13.0],[-57.5,-13.0],[-57.5,-20.5],[-65.0,-20.5],[-65.0,-13.0]]]}',
            'Cochabamba' => '{"type":"Polygon","coordinates":[[[-67.5,-16.0],[-64.5,-16.0],[-64.5,-18.5],[-67.5,-18.5],[-67.5,-16.0]]]}',
            'Chuquisaca' => '{"type":"Polygon","coordinates":[[[-65.5,-18.5],[-63.5,-18.5],[-63.5,-21.0],[-65.5,-21.0],[-65.5,-18.5]]]}',
            'Tarija' => '{"type":"Polygon","coordinates":[[[-65.5,-21.0],[-62.5,-21.0],[-62.5,-22.5],[-65.5,-22.5],[-65.5,-21.0]]]}',
            'Potosí' => '{"type":"Polygon","coordinates":[[[-68.5,-18.0],[-65.0,-18.0],[-65.0,-22.5],[-68.5,-22.5],[-68.5,-18.0]]]}',
            'Oruro' => '{"type":"Polygon","coordinates":[[[-69.0,-17.0],[-66.5,-17.0],[-66.5,-19.5],[-69.0,-19.5],[-69.0,-17.0]]]}',
            'Pando' => '{"type":"Polygon","coordinates":[[[-69.5,-9.5],[-65.0,-9.5],[-65.0,-12.5],[-69.5,-12.5],[-69.5,-9.5]]]}',
        ];

        foreach ($departamentos as $nombre => $geojson) {
            // Buscamos la geografía por nombre
            $geo = Geografia::where('nombre', $nombre)->first();

            if ($geo) {
                // Insertamos o actualizamos el límite geográfico
                DB::table('geografias_limites')->updateOrInsert(
                    ['id_geografia' => $geo->id_geografia],
                    [
                        'geojson' => $geojson,
                        'centro_latitud' => 0, // Se podría calcular el centroide real
                        'centro_longitud' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $this->command->info("Mapa cargado para: $nombre");
            } else {
                $this->command->warn("No se encontró el departamento: $nombre (Asegúrate de tener cargadas las geografías)");
            }
        }
    }
}
