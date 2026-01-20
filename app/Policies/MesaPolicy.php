<?php

namespace App\Policies;

use App\Models\Mesa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MesaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_mesas');
    }

    public function view(User $user, Mesa $mesa)
    {
        return $user->hasPermission('read_mesas');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_mesas');
    }

    public function update(User $user, Mesa $mesa)
    {
        return $user->hasPermission('edit_mesas');
    }

    public function delete(User $user, Mesa $mesa)
    {
        return $user->hasPermission('delete_mesas');
    }
}
