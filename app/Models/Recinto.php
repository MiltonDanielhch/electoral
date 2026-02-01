<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recinto extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $table = 'recintos';
    protected $primaryKey = 'id_recinto';

    protected $fillable = [
        'codigo_tse',
        'id_geografia',
        'nombre',
        'direccion',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['lat_lon'];

    public function getLatLonAttribute(): ?array
    {
        if (!$this->latitud || !$this->longitud) {
            return null;
        }

        if ($this->latitud == 0 && $this->longitud == 0) {
            return null;
        }

        return [
            'lat' => (float)$this->latitud,
            'lon' => (float)$this->longitud,
        ];
    }

    public function geografia()
    {
        return $this->belongsTo(Geografia::class, 'id_geografia', 'id_geografia');
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'id_recinto', 'id_recinto');
    }

    /**
     * Scope para filtrar recintos con coordenadas válidas dentro del Beni
     * Sintonía: Geofencing - Validación de Territorio
     */
    public function scopeConCoordenadasValidas($query)
    {
        return $query
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->where('latitud', '!=', 0)
            ->where('longitud', '!=', 0)
            // Sintonía: Rango válido para el Departamento del Beni
            ->whereBetween('latitud', [-16.5, -10.0])
            ->whereBetween('longitud', [-68.0, -60.0]);
    }

    /**
     * Scope para recintos que tienen coordenadas (no nulas ni 0,0)
     */
    public function scopeGeolocalizados($query)
    {
        return $query
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->where('latitud', '!=', 0)
            ->where('longitud', '!=', 0);
    }
}
