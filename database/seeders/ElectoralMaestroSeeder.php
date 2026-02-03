<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ElectoralMaestroSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CargoSeeder::class,
            GeografiaSeeder::class,
            OrganizacionPoliticaSeeder::class,
            RecintoSeeder::class,
            MesaSeeder::class,
            CandidatoSeeder::class,
        ]);
    }
}
