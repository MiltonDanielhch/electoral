<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeografiaLimiteSeeder extends Seeder
{
    public function run()
    {
        // Limpiamos los límites previos para evitar duplicados
        DB::table('geografias_limites')->truncate();

        // Obtenemos las geografías para mapear por nombre -> ID
        $geos = DB::table('geografias')->pluck('id_geografia', 'nombre');

        // 1. Límite General del BENI (ID 1 en tu seeder anterior)
        if (isset($geos['Beni'])) {
            DB::table('geografias_limites')->insert([
                'id_geografia' => $geos['Beni'],
                'geojson' => json_encode([
                    'type' => 'Polygon',
                    'coordinates' => [[[-67.5,-15.5],[-62.0,-15.5],[-62.0,-10.5],[-67.5,-10.5],[-67.5,-15.5]]]
                ]),
                'created_at' => now(), 'updated_at' => now()
            ]);
        }

        // 2. Límites de Provincias (Formas simplificadas para visualización)
        $limitesProvincias = [
            'Cercado' => [[[-65.2,-15.2],[-64.5,-15.2],[-64.5,-14.5],[-65.2,-14.5],[-65.2,-15.2]]],
            'Vaca Díez' => [[[-66.5,-12.0],[-65.0,-12.0],[-65.0,-10.8],[-66.5,-10.8],[-66.5,-12.0]]],
            'Iténez' => [[[-64.5,-14.0],[-62.5,-14.0],[-62.5,-12.5],[-64.5,-12.5],[-64.5,-14.0]]],
            'José Ballivián' => [[[-67.5,-15.0],[-66.2,-15.0],[-66.2,-13.5],[-67.5,-13.5],[-67.5,-15.0]]],
        ];

        foreach ($limitesProvincias as $nombre => $coord) {
            if (isset($geos[$nombre])) {
                DB::table('geografias_limites')->insert([
                    'id_geografia' => $geos[$nombre],
                    'geojson' => json_encode([
                        'type' => 'Polygon',
                        'coordinates' => $coord
                    ]),
                    'created_at' => now(), 'updated_at' => now()
                ]);
            }
        }
    }
}
