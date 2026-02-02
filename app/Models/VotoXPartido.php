<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotoXPartido extends Model
{
    use HasFactory;

    protected $table = 'votos_x_partido';
    protected $primaryKey = 'id_acta';
    public $incrementing = false;

    protected $fillable = [
        'id_acta',
        'id_partido',
        'votos',
    ];

    public $timestamps = false;

    protected $casts = [
        'votos' => 'integer',
    ];

    public function actaEscrutinio()
    {
        return $this->belongsTo(ActaEscrutinio::class, 'id_acta', 'id_acta');
    }

    public function partido()
    {
        return $this->belongsTo(OrganizacionPolitica::class, 'id_partido', 'id_partido');
    }
}
