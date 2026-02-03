<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(VoyagerDatabaseSeeder::class);
        $this->call(UsersTableSeeder::class);
        // $this->call(CargoSeeder::class);
        // $this->call(GeografiaSeeder::class);
        // $this->call(OrganizacionPoliticaSeeder::class);
        // $this->call(RecintoSeeder::class);
        // $this->call(MesaSeeder::class);
        // $this->call(CandidatoSeeder::class);

        $this->call(ElectoralMaestroSeeder::class);

        // Opcional: Descomentar la siguiente línea para crear actas de ejemplo
        // $this->call(ActaEscrutinioSeeder::class);

        $this->call(ElectoralMenuSeeder::class);
        $this->call(GeografiaLimiteSeeder::class);
        // $this->call(MenuItemsTableSeeder::class);
        // $this->call(DataTypesTableSeeder::class);
        // $this->call(DataRowsTableSeeder::class);

        // $this->call(SettingsTableSeeder::class);

        // $this->call(RolesTableSeeder::class);
        // $this->call(PermissionsTableSeeder::class);
        // $this->call(PermissionRoleTableSeeder::class);
    }
}
