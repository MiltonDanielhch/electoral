<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        $role = Role::firstOrNew(['name' => 'admin']);
        if (!$role->exists) {
            $role->fill([
                'display_name' => __('voyager::seeders.roles.admin'),
            ])->save();
        }

        $role = Role::firstOrNew(['name' => 'user']);
        if (!$role->exists) {
            $role->fill([
                'display_name' => __('voyager::seeders.roles.user'),
            ])->save();
        }

        $role = Role::firstOrNew(['name' => 'tecnico']);
        if (!$role->exists) {
            $role->fill([
                'display_name' => 'Técnico',
            ])->save();
        }



        // $role = Role::firstOrNew(['name' => 'almacen_admin']);
        // if (!$role->exists) {
        //     $role->fill([
        //         'display_name' => __('Almacen - Administrador de todos los Almacenes'),
        //     ])->save();
        // }
        
        // $role = Role::firstOrNew(['name' => 'almacen_subadmin']);
        // if (!$role->exists) {
        //     $role->fill([
        //         'display_name' => __('Almacen - Responsable de almacen "Report"'),
        //     ])->save();
        // }
    }
}
