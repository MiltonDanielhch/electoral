<?php

namespace App\Policies;

use App\Models\Geografia;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GeografiaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_geografias');
    }

    public function view(User $user, Geografia $geografia)
    {
        return $user->hasPermission('read_geografias');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_geografias');
    }

    public function update(User $user, Geografia $geografia)
    {
        return $user->hasPermission('edit_geografias');
    }

    public function delete(User $user, Geografia $geografia)
    {
        return $user->hasPermission('delete_geografias');
    }
}
