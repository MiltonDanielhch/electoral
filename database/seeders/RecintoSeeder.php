<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecintoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtenemos los municipios con sus IDs para la relación
        $municipios = DB::table('geografias')
            ->where('tipo', 'Municipio')
            ->pluck('id_geografia', 'nombre')
            ->toArray();

        // Estructura de datos con coordenadas para que el mapa funcione de entrada
        $datosRecintos = [
            'Trinidad' => [
                ['nombre' => 'U.E. 6 de Junio', 'tse' => '001', 'dir' => 'Calle Sucre esq. Ballivián', 'lat' => -14.8308, 'lng' => -64.9041],
                ['nombre' => 'U.E. Bolivia', 'tse' => '002', 'dir' => 'Av. Principal s/n', 'lat' => -14.8350, 'lng' => -64.9000],
                ['nombre' => 'Col. Nac. San Ignacio', 'tse' => '003', 'dir' => 'Calle 6 de Agosto', 'lat' => -14.8280, 'lng' => -64.9060],
                ['nombre' => 'U.E. Petrolera', 'tse' => '004', 'dir' => 'Av. Petrolera', 'lat' => -14.8410, 'lng' => -64.9100],
                ['nombre' => 'Centro Cultural René Moreno', 'tse' => '005', 'dir' => 'Plaza Principal', 'lat' => -14.8333, 'lng' => -64.9022],
            ],
            'San Ignacio de Moxos' => [
                ['nombre' => 'U.E. San Ignacio', 'tse' => '006', 'dir' => 'Plaza Principal', 'lat' => -14.9961, 'lng' => -65.6403],
                ['nombre' => 'Colegio Técnico Humanístico', 'tse' => '007', 'dir' => 'Calle Principal', 'lat' => -14.9980, 'lng' => -65.6420],
            ],
            'Rurrenabaque' => [
                ['nombre' => 'U.E. General Ballivián', 'tse' => '008', 'dir' => 'Av. Principal', 'lat' => -14.4414, 'lng' => -67.5278],
                ['nombre' => 'Colegio Sagrado Corazón', 'tse' => '009', 'dir' => 'Calle 16 de Julio', 'lat' => -14.4430, 'lng' => -67.5300],
            ],
            'Santa Ana del Yacuma' => [
                ['nombre' => 'U.E. Santa Ana', 'tse' => '014', 'dir' => 'Calle Principal', 'lat' => -13.7444, 'lng' => -65.4264],
                ['nombre' => 'Colegio Yacuma', 'tse' => '015', 'dir' => 'Av. 9 de Febrero', 'lat' => -13.7460, 'lng' => -65.4280],
            ],
            // Puedes seguir agregando el resto de municipios aquí...
        ];

        foreach ($datosRecintos as $municipioNombre => $recintos) {
            // Buscamos el ID del municipio en el array que plockeamos al inicio
            $municipioId = $municipios[$municipioNombre] ?? null;

            if ($municipioId) {
                foreach ($recintos as $r) {
                    DB::table('recintos')->updateOrInsert(
                        ['codigo_tse' => $r['tse']], // Si el código existe, lo actualiza (evita duplicados)
                        [
                            'id_geografia' => $municipioId,
                            'nombre'       => $r['nombre'],
                            'direccion'    => $r['dir'],
                            'latitud'      => $r['lat'] ?? null,
                            'longitud'     => $r['lng'] ?? null,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]
                    );
                }
            }
        }

        $this->command->info('completada: Recintos del Beni cargados con éxito.');
    }
}
