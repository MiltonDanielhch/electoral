<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CandidatoSeeder extends Seeder
{
    public function run(): void
    {
        $partidos = DB::table('organizaciones_politicas')->pluck('id_partido', 'sigla')->toArray();
        $cargos = DB::table('cargos')->pluck('id_cargo', 'descripcion')->toArray();
        $geografiaBeni = DB::table('geografias')->where('nombre', 'Beni')->value('id_geografia');

        $candidatosGobernador = [
            ['nombre_completo' => 'Alexander Martin Castillo Soria', 'ci' => '12345678', 'sigla' => 'MAS-IPSP', 'cargo' => 'Gobernador'],
            ['nombre_completo' => 'Germán Medina', 'ci' => '23456789', 'sigla' => 'CC', 'cargo' => 'Gobernador'],
            ['nombre_completo' => 'Felipe González', 'ci' => '34567890', 'sigla' => 'MTS', 'cargo' => 'Gobernador'],
        ];

        $candidatosAlcalde = [
            ['nombre_completo' => 'Luis Castillo', 'ci' => '45678901', 'sigla' => 'MAS-IPSP', 'cargo' => 'Alcalde Municipal'],
            ['nombre_completo' => 'Rosa Flores', 'ci' => '56789012', 'sigla' => 'CC', 'cargo' => 'Alcalde Municipal'],
            ['nombre_completo' => 'Carlos Ramírez', 'ci' => '67890123', 'sigla' => 'MTS', 'cargo' => 'Alcalde Municipal'],
        ];

        $candidatosConcejal = [
            ['nombre_completo' => 'Pedro García', 'ci' => '78901234', 'sigla' => 'MAS-IPSP', 'cargo' => 'Concejal Municipal', 'geografia' => 'Trinidad'],
            ['nombre_completo' => 'José Rodríguez', 'ci' => '90123456', 'sigla' => 'CC', 'cargo' => 'Concejal Municipal', 'geografia' => 'Trinidad'],
            ['nombre_completo' => 'Carlos Fernández', 'ci' => '12345678', 'sigla' => 'MTS', 'cargo' => 'Concejal Municipal', 'geografia' => 'Trinidad'],
        ];

        $todosCandidatos = array_merge($candidatosGobernador, $candidatosAlcalde, $candidatosConcejal);

        foreach ($todosCandidatos as $candidato) {
            $partidoId = $partidos[$candidato['sigla']] ?? null;
            $cargoId = $cargos[$candidato['cargo']] ?? null;

            $geografia = isset($candidato['geografia'])
                ? DB::table('geografias')->where('nombre', $candidato['geografia'])->value('id_geografia')
                : $geografiaBeni;

            if ($partidoId && $cargoId && $geografia) {
                $existe = DB::table('candidatos')
                    ->where('id_partido', $partidoId)
                    ->where('id_cargo', $cargoId)
                    ->where('id_geografia_postulacion', $geografia)
                    ->exists();

                $existeCI = DB::table('candidatos')
                    ->where('ci', $candidato['ci'])
                    ->exists();

                if (!$existe && !$existeCI) {
                    DB::table('candidatos')->insert([
                        'nombre_completo' => $candidato['nombre_completo'],
                        'ci' => $candidato['ci'],
                        'id_partido' => $partidoId,
                        'id_cargo' => $cargoId,
                        'id_geografia_postulacion' => $geografia,
                        'estado' => 'Postulado',
                    ]);
                }
            }
        }
    }
}
