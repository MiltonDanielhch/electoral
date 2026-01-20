<?php

namespace Tests;

trait WithPermissions
{
    protected function createAdminUser(): \App\Models\User
    {
        $role = \TCG\Voyager\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

        $browsePermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'browse_admin'],
            ['table_name' => 'admin', 'display_name' => 'Browse Admin']
        );

        $addPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'add_admin'],
            ['table_name' => 'admin', 'display_name' => 'Add Admin']
        );

        $editPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'edit_admin'],
            ['table_name' => 'admin', 'display_name' => 'Edit Admin']
        );

        $deletePermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'delete_admin'],
            ['table_name' => 'admin', 'display_name' => 'Delete Admin']
        );

        $role->permissions()->sync([$browsePermission->id, $addPermission->id, $editPermission->id, $deletePermission->id]);

        return $user;
    }

    protected function createPermittedUser(array $permissions): \App\Models\User
    {
        $role = \TCG\Voyager\Models\Role::firstOrCreate(
            ['name' => 'test_role_' . uniqid()],
            ['display_name' => 'Test Role']
        );

        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

        $permissionIds = [];
        foreach ($permissions as $perm) {
            $permission = \TCG\Voyager\Models\Permission::firstOrCreate(
                ['key' => $perm],
                ['table_name' => $perm, 'display_name' => ucfirst($perm)]
            );
            $permissionIds[] = $permission->id;
        }

        $role->permissions()->sync($permissionIds);

        return $user;
    }
}
