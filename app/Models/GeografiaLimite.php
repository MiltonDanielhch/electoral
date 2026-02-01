<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeografiaLimite extends Model
{
    use HasFactory;

    protected $table = 'geografias_limites';
    protected $primaryKey = 'id_limite';

    protected $fillable = [
        'id_geografia',
        'geojson',
        'centro_latitud',
        'centro_longitud',
        'area_km2',
    ];

    protected $casts = [
        'geojson' => 'array',
        'centro_latitud' => 'decimal:8',
        'centro_longitud' => 'decimal:8',
        'area_km2' => 'decimal:4',
    ];

    public function geografia()
    {
        return $this->belongsTo(Geografia::class, 'id_geografia', 'id_geografia');
    }

    public function scopeConGeojson($query)
    {
        return $query->whereNotNull('geojson');
    }

    public function scopePorTipo($query, string $tipo)
    {
        return $query->whereHas('geografia', function ($q) use ($tipo) {
            $q->where('tipo', $tipo);
        });
    }
}
