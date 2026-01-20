<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidato extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'candidatos';
    protected $primaryKey = 'id_candidato';

    protected $fillable = [
        'nombre_completo',
        'ci',
        'id_partido',
        'id_cargo',
        'id_geografia_postulacion',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function partido()
    {
        return $this->belongsTo(OrganizacionPolitica::class, 'id_partido', 'id_partido');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }

    public function geografiaPostulacion()
    {
        return $this->belongsTo(Geografia::class, 'id_geografia_postulacion', 'id_geografia');
    }
}
