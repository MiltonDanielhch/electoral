<?php

namespace App\Policies;

use App\Models\Recinto;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecintoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_recintos');
    }

    public function view(User $user, Recinto $recinto)
    {
        return $user->hasPermission('read_recintos');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_recintos');
    }

    public function update(User $user, Recinto $recinto)
    {
        return $user->hasPermission('edit_recintos');
    }

    public function delete(User $user, Recinto $recinto)
    {
        return $user->hasPermission('delete_recintos');
    }
}
