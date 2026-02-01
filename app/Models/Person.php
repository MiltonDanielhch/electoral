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
        'person_type',
        'tipo_doc',
        'ci',
        'ci_complemento',
        'nit',
        'first_name',
        'middle_name',
        'paternal_surname',
        'maternal_surname',
        'legal_name',
        'birth_date',
        'email',
        'phone',
        'address',
        'gender',
        'image',
        'padron',
        'status',

        'registerUser_id',
        'registerRole',
        'deleted_at',
        'deleteUser_id',
        'deleteRole',
        'deleteObservation',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'status' => 'integer',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;
    const STATUS_PENDING = 2;

    public function user()
    {
        return $this->hasOne(User::class, 'person_id');
    }

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
        if ($this->person_type === 'Jurídica') {
            return $this->legal_name;
        }

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

    public function scopeSearch($query, $term)
    {
        if (!$term) {
            return $query;
        }

        $fullNameRaw = "CASE
            WHEN person_type = 'Jurídica' THEN legal_name
            ELSE TRIM(CONCAT(
                COALESCE(first_name, ''), ' ',
                COALESCE(middle_name, ''), ' ',
                COALESCE(paternal_surname, ''), ' ',
                COALESCE(maternal_surname, '')
            ))
        END";

        return $query->where(function ($sub) use ($term, $fullNameRaw) {
            if (is_numeric($term)) {
                $sub->where('id', $term)
                    ->orWhere('ci', 'like', "%{$term}%")
                    ->orWhere('nit', 'like', "%{$term}%")
                    ->orWhere('padron', 'like', "%{$term}%");
            } else {
                $sub->where('email', 'like', "%{$term}%")
                    ->orWhere('legal_name', 'like', "%{$term}%")
                    ->orWhereRaw("{$fullNameRaw} like ?", ["%{$term}%"]);
            }
        });
    }
}
