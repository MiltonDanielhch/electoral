<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function createAdminUser(): \App\Models\User
    {
        $role = \TCG\Voyager\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator']
        );

        $user = \App\Models\User::factory()->create(['role_id' => $role->id]);

        $browsePermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'browse_admin'],
            ['table_name' => 'admin', 'keyDescription' => 'Browse Admin']
        );

        $addPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'add_admin'],
            ['table_name' => 'admin', 'keyDescription' => 'Add Admin']
        );

        $editPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'edit_admin'],
            ['table_name' => 'admin', 'keyDescription' => 'Edit Admin']
        );

        $deletePermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'delete_admin'],
            ['table_name' => 'admin', 'keyDescription' => 'Delete Admin']
        );

        $browseUsersPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'browse_users'],
            ['table_name' => 'users', 'keyDescription' => 'Browse Users']
        );

        $addUsersPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'add_users'],
            ['table_name' => 'users', 'keyDescription' => 'Add Users']
        );

        $editUsersPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'edit_users'],
            ['table_name' => 'users', 'keyDescription' => 'Edit Users']
        );

        $deleteUsersPermission = \TCG\Voyager\Models\Permission::firstOrCreate(
            ['key' => 'delete_users'],
            ['table_name' => 'users', 'keyDescription' => 'Delete Users']
        );

        $role->permissions()->sync([
            $browsePermission->id, $addPermission->id, $editPermission->id, $deletePermission->id,
            $browseUsersPermission->id, $addUsersPermission->id, $editUsersPermission->id, $deleteUsersPermission->id
        ]);

        return $user->load('role.permissions');
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
                ['table_name' => $perm, 'keyDescription' => ucfirst($perm)]
            );
            $permissionIds[] = $permission->id;
        }

        $role->permissions()->sync($permissionIds);

        return $user->load('role.permissions');
    }
}

