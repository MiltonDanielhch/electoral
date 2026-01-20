<?php

namespace App\Policies;

use App\Models\Candidato;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CandidatoPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('browse_candidatos');
    }

    public function view(User $user, Candidato $candidato)
    {
        return $user->hasPermission('read_candidatos');
    }

    public function create(User $user)
    {
        return $user->hasPermission('add_candidatos');
    }

    public function update(User $user, Candidato $candidato)
    {
        return $user->hasPermission('edit_candidatos');
    }

    public function delete(User $user, Candidato $candidato)
    {
        return $user->hasPermission('delete_candidatos');
    }
}
