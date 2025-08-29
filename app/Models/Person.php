<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\RegistersUserEvents;
use Attribute;

class Person extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'ci',
        'first_name',
        'middle_name',
        'paternal_surname',
        'maternal_surname',
        'birth_date',
        'email',
        'phone',
        'address',
        'gender',
        'image',
        'status',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_PENDING = 2;

    public static function getStatusLabel($status)
    {
        return match ($status) {
            self::STATUS_ACTIVE => 'Activo',
            self::STATUS_INACTIVE => 'Inactivo',
            self::STATUS_PENDING => 'Pendiente',
            default => 'Desconocido',
        };
    }
    /* -----------------------------------------------------------------
     |  Accessors & Mutators
     | -----------------------------------------------------------------*/
   public function getFullNameAttribute()
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->paternal_surname,
            $this->maternal_surname,
        ])->filter()->join(' '));
    }
    /* -----------------------------------------------------------------
     |  Scopes
     | -----------------------------------------------------------------*/
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
