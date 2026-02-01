<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        // 1. Limpiar tabla intermedia primero para evitar lentitud o bloqueos en sync
        \DB::table('permission_role')->delete();

        // --- ROL ADMIN ---
        $this->command->info('Asignando permisos a Admin...');

        // $role = Role::where('name', 'admin')->first();
        $role = Role::where('name', 'admin')->orWhere('id', 1)->first();

        if (!$role) {
            $role = Role::create(['name' => 'admin', 'display_name' => 'Administrador']);
        }

        $permissions = Permission::all();
        $role->permissions()->sync($permissions->pluck('id')->all());

        // --- ROL ADMINISTRADOR ---
        $role = Role::where('name', 'administrador')->first();
        if ($role) {
            $permissions = Permission::whereRaw('table_name = "admin" or
                                                `key` = "add_egressdonor" or


                                                table_name = "people" or
                                                table_name = "roles" or
                                                table_name = "users" or
                                                table_name = "settings" or
                                                table_name = "cargos" or
                                                `key` = "browse_people" or
                                                `key` = "read_people" or
                                                `key` = "add_people" or
                                                `key` = "edit_people" or
                                                `key` = "delete_people" or
                                                table_name = "organizaciones_politicas" or
                                                table_name = "geografias" or
                                                table_name = "recintos" or
                                                table_name = "mesas" or
                                                table_name = "candidatos" or


                                                `key` = "browse_clear-cache"')->get();
            $role->permissions()->sync($permissions->pluck('id')->all());
        }

        // --- ROL TÉCNICO ---
        $this->command->info('Asignando permisos a Técnico...');
        $role = Role::where('name', 'tecnico')->first();

        if (!$role) {
            $role = Role::create([
                'name' => 'tecnico',
                'display_name' => 'Técnico Electoral',
            ]);
        }

        $permissionKeys = [
            'browse_admin', // Importante para acceder al panel
            'browse_cargos',
            'read_cargos',
            'add_cargos',
            'edit_cargos',
            'delete_cargos',
            'browse_organizaciones_politicas',
            'read_organizaciones_politicas',
            'add_organizaciones_politicas',
            'edit_organizaciones_politicas',
            'delete_organizaciones_politicas',
            'browse_geografias',
            'read_geografias',
            'add_geografias',
            'edit_geografias',
            'delete_geografias',
            'browse_recintos',
            'read_recintos',
            'add_recintos',
            'edit_recintos',
            'delete_recintos',
            'browse_mesas',
            'read_mesas',
            'add_mesas',
            'edit_mesas',
            'delete_mesas',
            'browse_candidatos',
            'read_candidatos',
            'add_candidatos',
            'edit_candidatos',
            'delete_candidatos',

            // Personas
            'browse_people',
            'read_people',
            'add_people',
            'edit_people',
            'delete_people',

            'browse_clear-cache',
        ];

        $permissions = Permission::whereIn('key', $permissionKeys)->get();
        $role->permissions()->sync($permissions->pluck('id')->all());
    }
}
