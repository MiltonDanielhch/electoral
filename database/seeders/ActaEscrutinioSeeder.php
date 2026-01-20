<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActaEscrutinioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mesas = DB::table('mesas')
            ->where('estado', 'Habilitada')
            ->limit(5)
            ->get()
            ->toArray();

        $cargoGobernador = DB::table('cargos')->where('descripcion', 'Gobernador')->value('id_cargo');
        $cargoAlcalde = DB::table('cargos')->where('descripcion', 'Alcalde Municipal')->value('id_cargo');

        $partidos = DB::table('organizaciones_politicas')
            ->where('estado', 'Activo')
            ->take(5)
            ->get()
            ->keyBy('id_partido')
            ->toArray();

        foreach ($mesas as $mesa) {
            if (!$cargoGobernador) continue;

            $totalSobres = rand(200, 400);
            $totalVotantes = rand(180, $totalSobres - 10);
            $votosValidos = rand(150, $totalVotantes - 20);
            $votosBlancos = rand(10, 30);
            $votosNulos = rand(10, 30);
            $votosImpugnados = $totalSobres - ($votosValidos + $votosBlancos + $votosNulos);

            $acta = [
                'id_mesa' => $mesa['id_mesa'],
                'id_cargo' => $cargoGobernador,
                'codigo_acta' => 'ACTA-' . str_pad($mesa['id_mesa'], 6, '0', STR_PAD_LEFT),
                'foto_frontal' => null,
                'foto_reverso' => null,
                'total_sobres' => $totalSobres,
                'total_votantes' => $totalVotantes,
                'votos_validos' => $votosValidos,
                'votos_blancos' => $votosBlancos,
                'votos_nulos' => $votosNulos,
                'votos_impugnados' => max(0, $votosImpugnados),
                'digitador' => 'Digitador Prueba',
                'estado' => 'Digitada',
            ];

            $idActa = DB::table('actas_escrutinio')->insertGetId($acta);

            $votosRestantes = $votosValidos;
            $partidoKeys = array_keys($partidos);

            foreach ($partidoKeys as $index => $partidoId) {
                if ($index === count($partidoKeys) - 1) {
                    $votos = $votosRestantes;
                } else {
                    $votos = rand(0, floor($votosRestantes / (count($partidoKeys) - $index)));
                    $votosRestantes -= $votos;
                }

                DB::table('votos_x_partido')->insert([
                    'id_acta' => $idActa,
                    'id_partido' => $partidoId,
                    'votos' => $votos,
                ]);
            }

            DB::table('auditoria_actas')->insert([
                'id_acta' => $idActa,
                'accion' => 'CREACION',
                'usuario' => 'Digitador Prueba',
                'detalles' => 'Acta creada desde seeder de prueba',
                'ip_origen' => '127.0.0.1',
            ]);
        }

        DB::table('mesas')
            ->whereIn('id_mesa', array_column($mesas, 'id_mesa'))
            ->update(['estado' => 'Escrutada']);
    }
}

class ActaEscrutinioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
