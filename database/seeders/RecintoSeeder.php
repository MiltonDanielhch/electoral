<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecintoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $municipios = DB::table('geografias')->where('tipo', 'Municipio')->pluck('id_geografia', 'nombre')->toArray();

        $recintosTrinidad = [
            [
                'nombre' => 'Unidad Educativa 6 de Junio',
                'codigo_tse' => '001',
                'direccion' => 'Calle Sucre esquina Ballivián',
            ],
            [
                'nombre' => 'Unidad Educativa Bolivia',
                'codigo_tse' => '002',
                'direccion' => 'Av. Principal s/n',
            ],
            [
                'nombre' => 'Colegio Nacional San Ignacio',
                'codigo_tse' => '003',
                'direccion' => 'Calle 6 de Agosto',
            ],
            [
                'nombre' => 'Unidad Educativa Petrolera',
                'codigo_tse' => '004',
                'direccion' => 'Av. Petrolera',
            ],
            [
                'nombre' => 'Centro Cultural René Moreno',
                'codigo_tse' => '005',
                'direccion' => 'Plaza Principal',
            ],
        ];

        $recintosSanIgnacio = [
            [
                'nombre' => 'Unidad Educativa San Ignacio',
                'codigo_tse' => '006',
                'direccion' => 'Plaza Principal',
            ],
            [
                'nombre' => 'Colegio Técnico Humanístico',
                'codigo_tse' => '007',
                'direccion' => 'Calle Principal',
            ],
        ];

        $recintosRurrenabaque = [
            [
                'nombre' => 'Unidad Educativa General Ballivián',
                'codigo_tse' => '008',
                'direccion' => 'Av. Principal',
            ],
            [
                'nombre' => 'Colegio Sagrado Corazón',
                'codigo_tse' => '009',
                'direccion' => 'Calle 16 de Julio',
            ],
        ];

        $recintosSanJavier = [
            [
                'nombre' => 'Unidad Educativa San Javier',
                'codigo_tse' => '010',
                'direccion' => 'Plaza de Armas',
            ],
            [
                'nombre' => 'Centro Educativo Franciscano',
                'codigo_tse' => '011',
                'direccion' => 'Calle Misiones',
            ],
        ];

        $recintosReyes = [
            [
                'nombre' => 'Unidad Educativa Reyes',
                'codigo_tse' => '012',
                'direccion' => 'Calle Principal',
            ],
            [
                'nombre' => 'Centro Cultural Municipal',
                'codigo_tse' => '013',
                'direccion' => 'Plaza de Reyes',
            ],
        ];

        $recintosSantaAna = [
            [
                'nombre' => 'Unidad Educativa Santa Ana',
                'codigo_tse' => '014',
                'direccion' => 'Calle Principal',
            ],
            [
                'nombre' => 'Colegio Yacuma',
                'codigo_tse' => '015',
                'direccion' => 'Av. 9 de Febrero',
            ],
        ];

        $recintosLoreto = [
            [
                'nombre' => 'Unidad Educativa Loreto',
                'codigo_tse' => '016',
                'direccion' => 'Plaza Principal',
            ],
            [
                'nombre' => 'Centro Educativo Marbán',
                'codigo_tse' => '017',
                'direccion' => 'Calle Central',
            ],
        ];

        $recintosMagdalena = [
            [
                'nombre' => 'Unidad Educativa Magdalena',
                'codigo_tse' => '018',
                'direccion' => 'Calle Principal',
            ],
            [
                'nombre' => 'Centro Cultural Iténez',
                'codigo_tse' => '019',
                'direccion' => 'Av. Iténez',
            ],
        ];

        $recintosBaures = [
            [
                'nombre' => 'Unidad Educativa Baures',
                'codigo_tse' => '020',
                'direccion' => 'Calle Principal',
            ],
            [
                'nombre' => 'Colegio Etnoeducativo',
                'codigo_tse' => '021',
                'direccion' => 'Plaza Baures',
            ],
        ];

        $recintosSanJoaquin = [
            [
                'nombre' => 'Unidad Educativa San Joaquín',
                'codigo_tse' => '022',
                'direccion' => 'Calle Principal',
            ],
            [
                'nombre' => 'Centro Cultural Mamoré',
                'codigo_tse' => '023',
                'direccion' => 'Av. San Joaquín',
            ],
        ];

        $recintosSanRamon = [
            [
                'nombre' => 'Unidad Educativa San Ramón',
                'codigo_tse' => '024',
                'direccion' => 'Plaza Principal',
            ],
            [
                'nombre' => 'Colegio San Ramón',
                'codigo_tse' => '025',
                'direccion' => 'Calle 6 de Agosto',
            ],
        ];

        $recintosPuertoSiles = [
            [
                'nombre' => 'Unidad Educativa Puerto Siles',
                'codigo_tse' => '026',
                'direccion' => 'Calle Principal',
            ],
            [
                'nombre' => 'Centro Cultural Vaca Díez',
                'codigo_tse' => '027',
                'direccion' => 'Plaza Municipal',
            ],
        ];

        $todosRecintos = array_merge(
            $recintosTrinidad,
            $recintosSanIgnacio,
            $recintosRurrenabaque,
            $recintosSanJavier,
            $recintosReyes,
            $recintosSantaAna,
            $recintosLoreto,
            $recintosMagdalena,
            $recintosBaures,
            $recintosSanJoaquin,
            $recintosSanRamon,
            $recintosPuertoSiles
        );

        foreach ($todosRecintos as $recinto) {
            $municipioNombre = match (true) {
                in_array($recinto['codigo_tse'], ['001', '002', '003', '004', '005']) => 'Trinidad',
                in_array($recinto['codigo_tse'], ['006', '007']) => 'San Ignacio de Moxos',
                in_array($recinto['codigo_tse'], ['008', '009']) => 'Rurrenabaque',
                in_array($recinto['codigo_tse'], ['010', '011']) => 'San Javier',
                in_array($recinto['codigo_tse'], ['012', '013']) => 'Reyes',
                in_array($recinto['codigo_tse'], ['014', '015']) => 'Santa Ana del Yacuma',
                in_array($recinto['codigo_tse'], ['016', '017']) => 'Loreto',
                in_array($recinto['codigo_tse'], ['018', '019']) => 'Magdalena',
                in_array($recinto['codigo_tse'], ['020', '021']) => 'Baures',
                in_array($recinto['codigo_tse'], ['022', '023']) => 'San Joaquín',
                in_array($recinto['codigo_tse'], ['024', '025']) => 'San Ramón',
                in_array($recinto['codigo_tse'], ['026', '027']) => 'Puerto Siles',
                default => 'Trinidad',
            };

            $municipioId = $municipios[$municipioNombre] ?? $municipios['Trinidad'] ?? null;

            if ($municipioId) {
                DB::table('recintos')->insert([
                    'codigo_tse' => $recinto['codigo_tse'],
                    'id_geografia' => $municipioId,
                    'nombre' => $recinto['nombre'],
                    'direccion' => $recinto['direccion'],
                ]);
            }
        }
    }
}
