<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mesa extends Model
{
    use HasFactory, SoftDeletes;

    // Cambiamos a true para que Laravel gestione automáticamente created_at y updated_at
    public $timestamps = true;

    protected $table = 'mesas';
    protected $primaryKey = 'id_mesa';

    protected $fillable = [
        'codigo_tse',
        'id_recinto',
        'numero_mesa', // Añadido para sintonía con el seeder
        'cantidad_electores',
        'estado',
    ];

    protected $casts = [
        'estado' => 'string',
        'numero_mesa' => 'integer',
        'cantidad_electores' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el Recinto (N:1)
     */
    public function recinto()
    {
        return $this->belongsTo(Recinto::class, 'id_recinto', 'id_recinto');
    }

    /**
     * Relación con las Actas de Escrutinio (1:N)
     */
    public function actasEscrutinio()
    {
        return $this->hasMany(ActaEscrutinio::class, 'id_mesa', 'id_mesa');
    }

    /**
     * Scope para filtrar solo mesas habilitadas
     */
    public function scopeHabilitadas($query)
    {
        return $query->where('estado', 'Habilitada');
    }
}
