<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Geografia extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'geografias';
    protected $primaryKey = 'id_geografia';

    protected $fillable = [
        'codigo_tse',
        'nombre',
        'tipo',
        'parent_id',
        'nivel_jerarquico',
    ];

    protected $casts = [
        'nivel_jerarquico' => 'integer',
    ];

    public function parent()
    {
        return $this->belongsTo(Geografia::class, 'parent_id', 'id_geografia');
    }

    public function children()
    {
        return $this->hasMany(Geografia::class, 'parent_id', 'id_geografia');
    }

    public function recintos()
    {
        return $this->hasMany(Recinto::class, 'id_geografia', 'id_geografia');
    }

    public function candidatos()
    {
        return $this->hasMany(Candidato::class, 'id_geografia_postulacion', 'id_geografia');
    }

    public function resumenVotos()
    {
        return $this->hasMany(ResumenVoto::class, 'id_geografia', 'id_geografia');
    }

    public function scopeDepartamentos($query)
    {
        return $query->where('tipo', 'Departamento');
    }

    public function scopeProvincias($query)
    {
        return $query->where('tipo', 'Provincia');
    }

    public function scopeMunicipios($query)
    {
        return $query->where('tipo', 'Municipio');
    }
}
