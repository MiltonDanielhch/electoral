<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $table = 'mesas';
    protected $primaryKey = 'id_mesa';

    protected $fillable = [
        'codigo_tse',
        'id_recinto',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function recinto()
    {
        return $this->belongsTo(Recinto::class, 'id_recinto', 'id_recinto');
    }

    public function actasEscrutinio()
    {
        return $this->hasMany(ActaEscrutinio::class, 'id_mesa', 'id_mesa');
    }
}
