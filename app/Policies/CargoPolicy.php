<?php

namespace App\Policies;

use App\Models\Cargo;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CargoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_cargos');
    }

    public function view(User $user, Cargo $cargo)
    {
        return $user->hasPermission('read_cargos');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_cargos');
    }

    public function update(User $user, Cargo $cargo)
    {
        return $user->hasPermission('edit_cargos');
    }

    public function delete(User $user, Cargo $cargo)
    {
        return $user->hasPermission('delete_cargos');
    }
}
