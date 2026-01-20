<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoSeeder extends Seeder
{
    public function run()
    {
        $cargos = [
            [
                'descripcion' => 'Gobernador',
                'nivel' => 'D',
                'tipo_acta' => 'Normal',
                'acta_unica' => true
            ],
            [
                'descripcion' => 'Asambleísta Departamental',
                'nivel' => 'D',
                'tipo_acta' => 'Normal',
                'acta_unica' => true
            ],
            [
                'descripcion' => 'Alcalde Municipal',
                'nivel' => 'M',
                'tipo_acta' => 'Normal',
                'acta_unica' => true
            ],
            [
                'descripcion' => 'Concejal Municipal',
                'nivel' => 'M',
                'tipo_acta' => 'Normal',
                'acta_unica' => true
            ],
        ];

        DB::table('cargos')->insert($cargos);
    }
}
