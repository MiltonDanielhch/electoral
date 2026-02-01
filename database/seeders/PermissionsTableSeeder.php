<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('permissions')->delete();

        Permission::firstOrCreate([
            'key'        => 'browse_admin',
            'keyDescription'=>'vista de acceso al sistema',
            'table_name' => 'admin',
            'tableDescription'=>'Panel del Sistema'
        ]);

        $keys = [
            // 'browse_admin',
            'browse_bread',
            'browse_database',
            'browse_media',
            'browse_compass',
            'browse_clear-cache',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => null,
            ]);
        }

        Permission::generateFor('menus');

        Permission::generateFor('roles');
        Permission::generateFor('permissions');
        Permission::generateFor('settings');

        Permission::generateFor('users');

        Permission::generateFor('posts');
        Permission::generateFor('categories');
        Permission::generateFor('pages');

        $permissions = [
            // Cargos
            ['key' => 'browse_cargos', 'table_name' => 'cargos'],
            ['key' => 'read_cargos', 'table_name' => 'cargos'],
            ['key' => 'add_cargos', 'table_name' => 'cargos'],
            ['key' => 'edit_cargos', 'table_name' => 'cargos'],
            ['key' => 'delete_cargos', 'table_name' => 'cargos'],

            // Organizaciones Políticas
            ['key' => 'browse_organizaciones_politicas', 'table_name' => 'organizaciones_politicas'],
            ['key' => 'read_organizaciones_politicas', 'table_name' => 'organizaciones_politicas'],
            ['key' => 'add_organizaciones_politicas', 'table_name' => 'organizaciones_politicas'],
            ['key' => 'edit_organizaciones_politicas', 'table_name' => 'organizaciones_politicas'],
            ['key' => 'delete_organizaciones_politicas', 'table_name' => 'organizaciones_politicas'],

            // Geografías
            ['key' => 'browse_geografias', 'table_name' => 'geografias'],
            ['key' => 'read_geografias', 'table_name' => 'geografias'],
            ['key' => 'add_geografias', 'table_name' => 'geografias'],
            ['key' => 'edit_geografias', 'table_name' => 'geografias'],
            ['key' => 'delete_geografias', 'table_name' => 'geografias'],

            // Recintos
            ['key' => 'browse_recintos', 'table_name' => 'recintos'],
            ['key' => 'read_recintos', 'table_name' => 'recintos'],
            ['key' => 'add_recintos', 'table_name' => 'recintos'],
            ['key' => 'edit_recintos', 'table_name' => 'recintos'],
            ['key' => 'delete_recintos', 'table_name' => 'recintos'],

            // Mesas
            ['key' => 'browse_mesas', 'table_name' => 'mesas'],
            ['key' => 'read_mesas', 'table_name' => 'mesas'],
            ['key' => 'add_mesas', 'table_name' => 'mesas'],
            ['key' => 'edit_mesas', 'table_name' => 'mesas'],
            ['key' => 'delete_mesas', 'table_name' => 'mesas'],

            // Candidatos
            ['key' => 'browse_candidatos', 'table_name' => 'candidatos'],
            ['key' => 'read_candidatos', 'table_name' => 'candidatos'],
            ['key' => 'add_candidatos', 'table_name' => 'candidatos'],
            ['key' => 'edit_candidatos', 'table_name' => 'candidatos'],
            ['key' => 'delete_candidatos', 'table_name' => 'candidatos'],

            // Personas
            ['key' => 'browse_people', 'table_name' => 'people'],
            ['key' => 'read_people', 'table_name' => 'people'],
            ['key' => 'add_people', 'table_name' => 'people'],
            ['key' => 'edit_people', 'table_name' => 'people'],
            ['key' => 'delete_people', 'table_name' => 'people'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['key' => $permission['key']],
                $permission
            );
        }

        // // Administracion
        // $permissions = [
        //     'browse_people' => 'Ver lista de personas',
        //     'read_people' => 'Ver detalles de una persona',
        //     'edit_people' => 'Editar información de personas',
        //     'add_people' => 'Agregar nuevas personas',
        //     'delete_people' => 'Eliminar personas',
        // ];

        // foreach ($permissions as $key => $description) {
        //     Permission::firstOrCreate([
        //         'key'        => $key,
        //         'keyDescription'=> $description,
        //         'table_name' => 'people',
        //         'tableDescription'=>'Personas'
        //     ]);
        // }







    }
}
