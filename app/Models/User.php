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
     * Checks if User has a Role.
     *
     * @param string|array $name The role to check.
     */
    public function hasRole($name)
    {
        if (is_null($this->role)) {
            return false;
        }

        $roles = $this->role->pluck('name')->toArray();

        foreach ((is_array($name) ? $name : [$name]) as $role) {
            if (in_array($role, $roles)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission($name)
    {
        // The admin role has all permissions.
        if ($this->hasRole('admin')) {
            return true;
        }

        if (is_null($this->role)) {
            return false;
        }

        $permissions = $this->role->permissions->pluck('key')->toArray();

        foreach ((is_array($name) ? $name : [$name]) as $permission) {
            if (in_array($permission, $permissions)) {
                return true;
            }
        }

        return false;
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
