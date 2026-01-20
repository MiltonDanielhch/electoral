<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActaEscrutinio extends Model
{
    use HasFactory;

    protected $table = 'actas_escrutinio';
    protected $primaryKey = 'id_acta';

    protected $fillable = [
        'id_mesa',
        'id_cargo',
        'codigo_acta',
        'foto_frontal',
        'foto_reverso',
        'total_sobres',
        'total_votantes',
        'votos_validos',
        'votos_blancos',
        'votos_nulos',
        'votos_impugnados',
        'digitador',
        'estado',
    ];

    protected $casts = [
        'total_sobres' => 'integer',
        'total_votantes' => 'integer',
        'votos_validos' => 'integer',
        'votos_blancos' => 'integer',
        'votos_nulos' => 'integer',
        'votos_impugnados' => 'integer',
        'estado' => 'string',
    ];

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'id_mesa', 'id_mesa');
    }

    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }

    public function votosXPartido()
    {
        return $this->hasMany(VotoXPartido::class, 'id_acta', 'id_acta');
    }

    public function auditorias()
    {
        return $this->hasMany(AuditoriaActa::class, 'id_acta', 'id_acta');
    }
}
