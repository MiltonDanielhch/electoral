<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditoriaActa extends Model
{
    use HasFactory;

    protected $table = 'auditoria_actas';
    protected $primaryKey = 'id_auditoria';

    protected $fillable = [
        'id_acta',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'usuario',
        'fecha_cambio',
    ];

    protected $casts = [
        'fecha_cambio' => 'datetime',
    ];

    public function actaEscrutinio()
    {
        return $this->belongsTo(ActaEscrutinio::class, 'id_acta', 'id_acta');
    }
}
