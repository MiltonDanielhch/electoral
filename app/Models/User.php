<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Traits\RegistersUserEvents;


class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, RegistersUserEvents;

    protected $dates = ['deleted_at'];


    protected $fillable = [
        'person_id',
        'name',
        'role_id',
        'email',
        'password',
        'status',
        
        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class, 'person_id');
    }

    /**
     * Verifica si el usuario tiene un rol específico
     *
     * @param string|array $role Nombre del rol o array de roles
     * @return bool True si tiene el rol, false en caso contrario
     */
    public function hasRole($role)
    {
        if (!$this->role) {
            return false;
        }

        if (is_array($role)) {
            return in_array($this->role->name, $role);
        }

        return $this->role->name === $role;
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     *
     * @param string $permission Llave del permiso
     * @return bool True si tiene el permiso, false en caso contrario
     */
    public function hasPermission($permission)
    {
        if (!$this->role) {
            return false;
        }

        // Los admins tienen todos los permisos
        if ($this->role->name === 'admin') {
            return true;
        }

        // Verificar si el rol tiene el permiso
        return $this->role->permissions->contains('key', $permission);
    }



    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
