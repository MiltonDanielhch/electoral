<?php

namespace Database\Seeders;

use App\Models\Geografia;
use App\Models\GeografiaLimite;
use Illuminate\Database\Seeder;

class GeografiaLimitesSeeder extends Seeder
{
    public function run()
    {
        $departamentos = Geografia::where('tipo', 'Departamento')->get();

        foreach ($departamentos as $geografia) {
            $geojson = $this->getGeojsonForDepartamento($geografia->nombre);

            if ($geojson) {
                GeografiaLimite::updateOrCreate(
                    ['id_geografia' => $geografia->id_geografia],
                    [
                        'geojson' => $geojson,
                        'centro_latitud' => $this->getCentroLat($geografia->nombre),
                        'centro_longitud' => $this->getCentroLon($geografia->nombre),
                        'area_km2' => rand(50000, 200000),
                    ]
                );
            }
        }
    }

    private function getCentroLat($nombre)
    {
        $coords = [
            'La Paz' => -16.290154,
            'Cochabamba' => -17.3895,
            'Santa Cruz' => -17.7863,
            'Oruro' => -17.9687,
            'Potosí' => -19.5837,
            'Chuquisaca' => -19.0333,
            'Tarija' => -21.5356,
            'Beni' => -14.8333,
            'Pando' => -11.7533,
        ];
        return $coords[$nombre] ?? -16.290154;
    }

    private function getCentroLon($nombre)
    {
        $coords = [
            'La Paz' => -68.119854,
            'Cochabamba' => -66.1568,
            'Santa Cruz' => -63.1812,
            'Oruro' => -67.1096,
            'Potosí' => -65.7528,
            'Chuquisaca' => -64.7167,
            'Tarija' => -64.7296,
            'Beni' => -64.9,
            'Pando' => -68.2314,
        ];
        return $coords[$nombre] ?? -63.588653;
    }

    private function getGeojsonForDepartamento($nombre)
    {
        $offsets = [
            'La Paz' => [-69.5, -68.5, -16.5, -15.5],
            'Cochabamba' => [-67.5, -66.5, -17.5, -16.5],
            'Santa Cruz' => [-64.5, -63.5, -18.5, -17.5],
            'Oruro' => [-68.5, -67.5, -18.5, -17.5],
            'Potosí' => [-66.5, -65.5, -20.5, -19.5],
            'Chuquisaca' => [-65.5, -64.5, -19.5, -18.5],
            'Tarija' => [-65.5, -64.5, -22.5, -21.5],
            'Beni' => [-66.5, -65.5, -15.5, -14.5],
            'Pando' => [-69.5, -68.5, -12.5, -11.5],
        ];

        if (!isset($offsets[$nombre])) {
            return null;
        }

        $offset = $offsets[$nombre];

        return [
            'type' => 'Polygon',
            'coordinates' => [[
                [$offset[0], $offset[2]],
                [$offset[1], $offset[2]],
                [$offset[1], $offset[3]],
                [$offset[0], $offset[3]],
                [$offset[0], $offset[2]]
            ]]
        ];
    }
}
