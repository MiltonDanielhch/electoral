<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GeografiaSeeder extends Seeder
{
    public function run()
    {
        $beniDepartamento = DB::table('geografias')->insertGetId([
            'codigo_tse' => '800000000',
            'nombre' => 'Beni',
            'tipo' => 'Departamento',
            'parent_id' => null,
            'nivel_jerarquico' => 1
        ]);

        $provincias = [
            ['nombre' => 'Cercado', 'codigo' => '801000000'],
            ['nombre' => 'Vaca Díez', 'codigo' => '802000000'],
            ['nombre' => 'José Ballivián', 'codigo' => '803000000'],
            ['nombre' => 'Yacuma', 'codigo' => '804000000'],
            ['nombre' => 'Moxos', 'codigo' => '805000000'],
            ['nombre' => 'Marbán', 'codigo' => '806000000'],
            ['nombre' => 'Mamoré', 'codigo' => '807000000'],
            ['nombre' => 'Iténez', 'codigo' => '808000000'],
        ];

        $provinciaIds = [];
        foreach ($provincias as $provincia) {
            $provinciaIds[$provincia['nombre']] = DB::table('geografias')->insertGetId([
                'codigo_tse' => $provincia['codigo'],
                'nombre' => $provincia['nombre'],
                'tipo' => 'Provincia',
                'parent_id' => $beniDepartamento,
                'nivel_jerarquico' => 2
            ]);
        }

        $municipios = [
            ['nombre' => 'Trinidad', 'provincia' => 'Cercado', 'codigo' => '801010000'],
            ['nombre' => 'San Javier', 'provincia' => 'Cercado', 'codigo' => '801020000'],
            ['nombre' => 'San Andrés', 'provincia' => 'Cercado', 'codigo' => '801030000'],
            ['nombre' => 'San Ignacio de Moxos', 'provincia' => 'Moxos', 'codigo' => '805010000'],
            ['nombre' => 'Santa Ana del Yacuma', 'provincia' => 'Yacuma', 'codigo' => '804010000'],
            ['nombre' => 'Reyes', 'provincia' => 'José Ballivián', 'codigo' => '803010000'],
            ['nombre' => 'Rurrenabaque', 'provincia' => 'José Ballivián', 'codigo' => '803020000'],
            ['nombre' => 'San Borja', 'provincia' => 'José Ballivián', 'codigo' => '803030000'],
            ['nombre' => 'Loreto', 'provincia' => 'Marbán', 'codigo' => '806010000'],
            ['nombre' => 'Magdalena', 'provincia' => 'Iténez', 'codigo' => '808010000'],
            ['nombre' => 'Baures', 'provincia' => 'Iténez', 'codigo' => '808020000'],
            ['nombre' => 'Huacaraje', 'provincia' => 'Iténez', 'codigo' => '808030000'],
            ['nombre' => 'San Joaquín', 'provincia' => 'Mamoré', 'codigo' => '807010000'],
            ['nombre' => 'San Ramón', 'provincia' => 'Mamoré', 'codigo' => '807020000'],
            ['nombre' => 'Puerto Siles', 'provincia' => 'Vaca Díez', 'codigo' => '802010000'],
        ];

        foreach ($municipios as $municipio) {
            if (isset($provinciaIds[$municipio['provincia']])) {
                DB::table('geografias')->insert([
                    'codigo_tse' => $municipio['codigo'],
                    'nombre' => $municipio['nombre'],
                    'tipo' => 'Municipio',
                    'parent_id' => $provinciaIds[$municipio['provincia']],
                    'nivel_jerarquico' => 3
                ]);
            }
        }
    }
}
