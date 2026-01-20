<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'cargos';
    protected $primaryKey = 'id_cargo';
    protected $keyType = 'int';

    protected $fillable = [
        'descripcion',
        'nivel',
        'tipo_acta',
        'acta_unica',
    ];

    protected $casts = [
        'acta_unica' => 'boolean',
    ];

    public function candidatos()
    {
        return $this->hasMany(Candidato::class, 'id_cargo', 'id_cargo');
    }

    public function actasEscrutinio()
    {
        return $this->hasMany(ActaEscrutinio::class, 'id_cargo', 'id_cargo');
    }

    public function resumenVotos()
    {
        return $this->hasMany(ResumenVoto::class, 'id_cargo', 'id_cargo');
    }
}
