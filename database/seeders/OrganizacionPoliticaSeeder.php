<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrganizacionPolitica;

class OrganizacionPoliticaSeeder extends Seeder
{
    public function run()
    {
        $partidos = [
            [
                'codigo_tse' => 'MAS',
                'nombre' => 'Movimiento al Socialismo - Instrumento Político por la Soberanía de los Pueblos',
                'sigla' => 'MAS-IPSP',
                'color_hex' => '#005a9c',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'CC',
                'nombre' => 'Comunidad Ciudadana',
                'sigla' => 'CC',
                'color_hex' => '#ff6b00',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'MTS',
                'nombre' => 'Movimiento Tercer Sistema',
                'sigla' => 'MTS',
                'color_hex' => '#8b0000',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'PDC',
                'nombre' => 'Partido Demócrata Cristiano',
                'sigla' => 'PDC',
                'color_hex' => '#ff0000',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'FPV',
                'nombre' => 'Frente Para la Victoria',
                'sigla' => 'FPV',
                'color_hex' => '#1a5276',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'MNR',
                'nombre' => 'Movimiento Nacionalista Revolucionario',
                'sigla' => 'MNR',
                'color_hex' => '#c0392b',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'ADN',
                'nombre' => 'Acción Democrática Nacionalista',
                'sigla' => 'ADN',
                'color_hex' => '#1e3799',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'MIR',
                'nombre' => 'Movimiento de la Izquierda Revolucionaria',
                'sigla' => 'MIR',
                'color_hex' => '#2ecc71',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'MOP',
                'nombre' => 'Movimiento de la Oposición Popular',
                'sigla' => 'MOP',
                'color_hex' => '#9b59b6',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'UN',
                'nombre' => 'Unidad Nacional',
                'sigla' => 'UN',
                'color_hex' => '#f39c12',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'UCS',
                'nombre' => 'Unidad Cívica Solidaridad',
                'sigla' => 'UCS',
                'color_hex' => '#00cec9',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'NAR',
                'nombre' => 'Nueva Alternativa Revolucionaria',
                'sigla' => 'NAR',
                'color_hex' => '#6c5ce7',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'PAN',
                'nombre' => 'Partido Acción Nacional',
                'sigla' => 'PAN-BOL',
                'color_hex' => '#d63031',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'AS',
                'nombre' => 'Alianza Social',
                'sigla' => 'AS',
                'color_hex' => '#e84393',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
            [
                'codigo_tse' => 'PSP',
                'nombre' => 'Partido Socialista Pro Bolivia',
                'sigla' => 'PSP',
                'color_hex' => '#fdcb6e',
                'logo_url' => null,
                'estado' => 'Activo',
            ],
        ];

        foreach ($partidos as $partido) {
            OrganizacionPolitica::create($partido);
        }
    }
}
