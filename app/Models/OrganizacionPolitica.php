<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganizacionPolitica extends Model
{
    use HasFactory;

    protected $table = 'organizaciones_politicas';
    protected $primaryKey = 'id_partido';

    protected $fillable = [
        'codigo_tse',
        'nombre',
        'sigla',
        'color_hex',
        'logo_url',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
    ];

    public function candidatos()
    {
        return $this->hasMany(Candidato::class, 'id_partido', 'id_partido');
    }

    public function votosXPartido()
    {
        return $this->hasMany(VotoXPartido::class, 'id_partido', 'id_partido');
    }

    public function resumenVotos()
    {
        return $this->hasMany(ResumenVoto::class, 'id_partido', 'id_partido');
    }
}
