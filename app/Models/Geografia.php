<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Geografia extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'geografias';
    protected $primaryKey = 'id_geografia';

    protected $fillable = [
        'codigo_tse',
        'nombre',
        'tipo',
        'parent_id',
        'nivel_jerarquico',
        'latitud',
        'longitud',
    ];

    protected $casts = [
        'nivel_jerarquico' => 'integer',
        'latitud' => 'float',
        'longitud' => 'float',
    ];

    // App\Models\Geografia.php

    /**
     * Atributo para obtener el conteo de recintos en cascada
     */
    public function getContadorRecintosAttribute()
    {
        // Caso 1: Si es Municipio, cuenta sus recintos directos
        if ($this->tipo == 'Municipio') {
            return DB::table('recintos')->where('id_geografia', $this->id_geografia)->count();
        }

        // Caso 2: Si es Provincia, cuenta recintos de sus municipios hijos
        if ($this->tipo == 'Provincia') {
            $municipiosIds = DB::table('geografias')
                ->where('parent_id', $this->id_geografia)
                ->pluck('id_geografia');

            return DB::table('recintos')->whereIn('id_geografia', $municipiosIds)->count();
        }

        // Caso 3: Si es Departamento, cuenta recintos de todos los municipios de todas sus provincias
        if ($this->tipo == 'Departamento') {
            return DB::table('recintos')
                ->whereIn('id_geografia', function($query) {
                    $query->select('id_geografia')
                        ->from('geografias')
                        ->whereIn('parent_id', function($subQuery) {
                            $subQuery->select('id_geografia')
                                ->from('geografias')
                                ->where('parent_id', $this->id_geografia);
                        });
                })->count();
        }

        return 0;
    }


    public function getContadorCandidatosAttribute()
    {
        // Caso 1: Municipio
        if ($this->tipo == 'Municipio') {
            return DB::table('candidatos')
                ->where('id_geografia_postulacion', $this->id_geografia) // Sintonizado
                ->count();
        }

        // Caso 2: Provincia (Suma candidatos de sus municipios hijos)
        if ($this->tipo == 'Provincia') {
            $municipiosIds = DB::table('geografias')
                ->where('parent_id', $this->id_geografia)
                ->pluck('id_geografia');

            return DB::table('candidatos')
                ->whereIn('id_geografia_postulacion', $municipiosIds) // Sintonizado
                ->count();
        }

        // Caso 3: Departamento (Suma total de candidatos en todo el Beni)
        if ($this->tipo == 'Departamento') {
            // Obtenemos los IDs de las provincias
            $provinciasIds = DB::table('geografias')
                ->where('parent_id', $this->id_geografia)
                ->pluck('id_geografia');

            // Obtenemos los IDs de los municipios
            $municipiosIds = DB::table('geografias')
                ->whereIn('parent_id', $provinciasIds)
                ->pluck('id_geografia');

            // Contamos candidatos usando la columna correcta
            return DB::table('candidatos')
                ->whereIn('id_geografia_postulacion', $municipiosIds) // Sintonizado
                ->count();
        }

        return 0;
    }

    /**
     * Obtiene todos los recintos en cascada (objetos)
     */
    public function getTodosLosRecintosAttribute()
    {
        // Si es Municipio, los suyos directos
        if ($this->tipo == 'Municipio') {
            return \App\Models\Recinto::where('id_geografia', $this->id_geografia)->get();
        }

        // Si es Provincia, los de sus municipios
        if ($this->tipo == 'Provincia') {
            $municipiosIds = \DB::table('geografias')
                ->where('parent_id', $this->id_geografia)
                ->pluck('id_geografia');

            return \App\Models\Recinto::whereIn('id_geografia', $municipiosIds)->get();
        }

        // Si es Departamento, todos los del Beni
        if ($this->tipo == 'Departamento') {
            return \App\Models\Recinto::whereIn('id_geografia', function($query) {
                $query->select('id_geografia')
                    ->from('geografias')
                    ->whereIn('parent_id', function($subQuery) {
                        $subQuery->select('id_geografia')
                            ->from('geografias')
                            ->where('parent_id', $this->id_geografia);
                    });
            })->get();
        }

        return collect(); // Devuelve colección vacía si no hay nada
    }

    public function parent()
    {
        return $this->belongsTo(Geografia::class, 'parent_id', 'id_geografia');
    }

    public function children()
    {
        return $this->hasMany(Geografia::class, 'parent_id', 'id_geografia');
    }

    public function recintos()
    {
        return $this->hasMany(Recinto::class, 'id_geografia', 'id_geografia');
    }

    public function candidatos()
    {
        return $this->hasMany(Candidato::class, 'id_geografia_postulacion', 'id_geografia');
    }

    public function resumenVotos()
    {
        return $this->hasMany(ResumenVoto::class, 'id_geografia', 'id_geografia');
    }

    public function limite()
    {
        return $this->hasOne(GeografiaLimite::class, 'id_geografia', 'id_geografia');
    }

    public function scopeDepartamentos($query)
    {
        return $query->where('tipo', 'Departamento');
    }

    public function scopeProvincias($query)
    {
        return $query->where('tipo', 'Provincia');
    }

    public function scopeMunicipios($query)
    {
        return $query->where('tipo', 'Municipio');
    }
}
