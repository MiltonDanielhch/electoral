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
        'tipo_doc',
        'ci',
        'ci_complemento',
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

        $fullNameRaw = "TRIM(CONCAT(
            COALESCE(first_name, ''), ' ',
            COALESCE(middle_name, ''), ' ',
            COALESCE(paternal_surname, ''), ' ',
            COALESCE(maternal_surname, '')
        ))";

        return $query->where(function ($sub) use ($term, $fullNameRaw) {
            if (is_numeric($term)) {
                $sub->where('id', $term)
                    ->orWhere('ci', 'like', "%{$term}%")
                    ->orWhere('padron', 'like', "%{$term}%");
            } else {
                $sub->where('email', 'like', "%{$term}%")
                    ->orWhereRaw("{$fullNameRaw} like ?", ["%{$term}%"]);
            }
        });
    }

    /**
     * Busca posibles duplicados basado en CI o combinación de nombres/apellidos similares
     */
    public function scopePotentialDuplicates($query, $ci = null, $firstName = null, $paternalSurname = null, $maternalSurname = null, $excludeId = null)
    {
        return $query->where(function ($q) use ($ci, $firstName, $paternalSurname, $maternalSurname) {
            // Buscar por CI exacto
            if ($ci) {
                $q->orWhere('ci', $ci);
            }
            
            // Buscar por combinación similar de nombres y apellidos
            if ($firstName && $paternalSurname) {
                $q->orWhere(function ($sub) use ($firstName, $paternalSurname, $maternalSurname) {
                    $sub->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($firstName) . '%'])
                        ->whereRaw('LOWER(paternal_surname) LIKE ?', ['%' . strtolower($paternalSurname) . '%']);
                    
                    if ($maternalSurname) {
                        $sub->whereRaw('LOWER(maternal_surname) LIKE ?', ['%' . strtolower($maternalSurname) . '%']);
                    }
                });
            }
        })
        ->when($excludeId, function ($q) use ($excludeId) {
            $q->where('id', '!=', $excludeId);
        })
        ->whereNull('deleted_at')
        ->limit(5);
    }

    /* -----------------------------------------------------------------
     |  Validación de CI Boliviano (Algoritmo Módulo 11)
     | -----------------------------------------------------------------*/
    
    /**
     * Valida un Carnet de Identidad boliviano usando el algoritmo de módulo 11
     * 
     * @param string $ci Número de CI (sin complemento)
     * @param string $complemento Complemento opcional (ej: 1A, 2B)
     * @return bool
     */
    public static function validateBolivianCI(string $ci, ?string $complemento = null): bool
    {
        // Limpiar el CI de cualquier carácter no numérico (excepto para validación del número base)
        $ciClean = preg_replace('/[^0-9]/', '', $ci);
        
        // El CI debe tener entre 6 y 10 dígitos
        if (strlen($ciClean) < 6 || strlen($ciClean) > 10) {
            return false;
        }
        
        // Si tiene complemento, la validación es más flexible
        if ($complemento) {
            // Con complemento, aceptamos el CI si tiene formato básico válido
            return strlen($ciClean) >= 6;
        }
        
        // Algoritmo de módulo 11 para CI sin complemento
        return self::verifyModulo11($ciClean);
    }
    
    /**
     * Verificación usando algoritmo de módulo 11
     */
    private static function verifyModulo11(string $ci): bool
    {
        $length = strlen($ci);
        if ($length < 6) return false;
        
        // Tomar los últimos dígitos para validación
        $baseNumber = substr($ci, 0, $length - 1);
        $checkDigit = substr($ci, -1);
        
        if (!is_numeric($checkDigit)) return false;
        
        $sum = 0;
        $multipliers = [2, 3, 4, 5, 6, 7, 8, 9];
        $multiplierIndex = 0;
        
        // Recorrer de derecha a izquierda
        for ($i = strlen($baseNumber) - 1; $i >= 0; $i--) {
            $digit = intval($baseNumber[$i]);
            $sum += $digit * $multipliers[$multiplierIndex];
            $multiplierIndex = ($multiplierIndex + 1) % count($multipliers);
        }
        
        $remainder = $sum % 11;
        $calculatedCheckDigit = 11 - $remainder;
        
        if ($calculatedCheckDigit == 11) $calculatedCheckDigit = 0;
        if ($calculatedCheckDigit == 10) $calculatedCheckDigit = 1;
        
        return $calculatedCheckDigit == intval($checkDigit);
    }
    
    /**
     * Accessor para mostrar CI formateado
     */
    public function getCiFormattedAttribute(): string
    {
        $ci = $this->ci;
        $comp = $this->ci_complemento;
        
        if (!$ci) return 'Sin CI';
        if ($comp) {
            return $ci . '-' . $comp;
        }
        return $ci;
    }
    
    /**
     * Accessor para calcular edad
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->birth_date) return null;
        return $this->birth_date->age;
    }
}
