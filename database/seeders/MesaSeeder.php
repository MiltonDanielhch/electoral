<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MesaSeeder extends Seeder
{
    public function run(): void
    {
        $recintos = DB::table('recintos')->pluck('id_recinto', 'nombre')->toArray();

        $mesasPorRecinto = [
            'Unidad Educativa 6 de Junio' => [
                '00100100001', '00100100002', '00100100003', '00100100004', '00100100005',
                '00100100006', '00100100007', '00100100008', '00100100009', '00100100010',
            ],
            'Unidad Educativa Bolivia' => [
                '00100200001', '00100200002', '00100200003', '00100200004', '00100200005',
            ],
            'Colegio Nacional San Ignacio' => [
                '00100300001', '00100300002', '00100300003', '00100300004',
            ],
            'Unidad Educativa Petrolera' => [
                '00100400001', '00100400002', '00100400003',
            ],
            'Centro Cultural René Moreno' => [
                '00100500001', '00100500002', '00100500003', '00100500004',
            ],
            'Unidad Educativa San Ignacio' => [
                '00100600001', '00100600002', '00100600003',
            ],
            'Colegio Técnico Humanístico' => [
                '00100700001', '00100700002',
            ],
            'Unidad Educativa General Ballivián' => [
                '00100800001', '00100800002', '00100800003', '00100800004', '00100800005',
            ],
            'Colegio Sagrado Corazón' => [
                '00100900001', '00100900002', '00100900003',
            ],
            'Unidad Educativa San Javier' => [
                '00101000001', '00101000002', '00101000003',
            ],
            'Centro Educativo Franciscano' => [
                '00101100001', '00101100002',
            ],
        ];

        $estados = ['Habilitada', 'Escrutada', 'Anulada', 'Observada'];

        foreach ($mesasPorRecinto as $recintoNombre => $mesas) {
            $recintoId = $recintos[$recintoNombre] ?? null;

            if (!$recintoId) {
                continue;
            }

            foreach ($mesas as $codigoMesa) {
                DB::table('mesas')->insert([
                    'codigo_tse' => $codigoMesa,
                    'id_recinto' => $recintoId,
                    'estado' => $estados[array_rand($estados)],
                ]);
            }
        }
    }
}
