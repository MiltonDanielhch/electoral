<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Optimización de índices para geofencing y búsquedas espaciales
     */
    public function up(): void
    {
        // Índices optimizados para recintos - consultas de geofencing
        Schema::table('recintos', function (Blueprint $table) {
            // Índice compuesto para consultas de geofencing (lat, lon, geografia)
            if (!Schema::hasIndex('recintos', 'idx_recintos_geo_spatial')) {
                $table->index(['latitud', 'longitud', 'id_geografia'], 'idx_recintos_geo_spatial');
            }

            // Índice para búsquedas por municipio con coordenadas
            if (!Schema::hasIndex('recintos', 'idx_recintos_municipio_coords')) {
                $table->index(['id_geografia', 'latitud', 'longitud'], 'idx_recintos_municipio_coords');
            }

            // Índice para búsquedas de recintos eliminados (soft deletes)
            if (!Schema::hasIndex('recintos', 'idx_recintos_deleted_at')) {
                $table->index('deleted_at', 'idx_recintos_deleted_at');
            }
        });

        // Índice FULLTEXT para búsquedas de texto en recintos
        Schema::table('recintos', function (Blueprint $table) {
            if (!Schema::hasIndex('recintos', 'idx_recintos_fulltext')) {
                $table->fullText(['nombre', 'direccion'], 'idx_recintos_fulltext');
            }
        });

        // Índice FULLTEXT para búsquedas de texto en geografías
        Schema::table('geografias', function (Blueprint $table) {
            if (!Schema::hasIndex('geografias', 'idx_geo_fulltext')) {
                $table->fullText('nombre', 'idx_geo_fulltext');
            }
        });

        // Índice para búsquedas por código TSE en recintos
        Schema::table('recintos', function (Blueprint $table) {
            if (!Schema::hasIndex('recintos', 'idx_recintos_codigo_busqueda')) {
                $table->index('codigo_tse', 'idx_recintos_codigo_busqueda');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recintos', function (Blueprint $table) {
            $table->dropIndex('idx_recintos_geo_spatial');
            $table->dropIndex('idx_recintos_municipio_coords');
            $table->dropIndex('idx_recintos_deleted_at');
            $table->dropIndex('idx_recintos_fulltext');
            $table->dropIndex('idx_recintos_codigo_busqueda');
        });

        Schema::table('geografias', function (Blueprint $table) {
            $table->dropIndex('idx_geo_fulltext');
        });
    }
};
