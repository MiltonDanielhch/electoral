<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResumenVoto extends Model
{
    use HasFactory;

    protected $table = 'resumen_votos';
    protected $primaryKey = ['id_cargo', 'id_geografia', 'id_partido'];
    public $incrementing = false;

    protected $fillable = [
        'id_cargo',
        'id_geografia',
        'id_partido',
        'total_votos',
        'total_mesas_escrutadas',
        'porcentaje_votos',
        'ultima_actualizacion',
    ];

    protected $casts = [
        'total_votos' => 'integer',
        'total_mesas_escrutadas' => 'integer',
        'porcentaje_votos' => 'decimal:2',
        'ultima_actualizacion' => 'datetime',
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }

    public function geografia()
    {
        return $this->belongsTo(Geografia::class, 'id_geografia', 'id_geografia');
    }

    public function partido()
    {
        return $this->belongsTo(OrganizacionPolitica::class, 'id_partido', 'id_partido');
    }
}
