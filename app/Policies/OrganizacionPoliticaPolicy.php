<?php

namespace App\Policies;

use App\Models\OrganizacionPolitica;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganizacionPoliticaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_organizaciones_politicas');
    }

    public function view(User $user, OrganizacionPolitica $organizacion)
    {
        return $user->hasPermission('read_organizaciones_politicas');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_organizaciones_politicas');
    }

    public function update(User $user, OrganizacionPolitica $organizacion)
    {
        return $user->hasPermission('edit_organizaciones_politicas');
    }

    public function delete(User $user, OrganizacionPolitica $organizacion)
    {
        return $user->hasPermission('delete_organizaciones_politicas');
    }
}
