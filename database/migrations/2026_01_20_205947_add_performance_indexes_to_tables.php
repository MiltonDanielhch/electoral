<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('actas_escrutinio', function (Blueprint $table) {
            if (!Schema::hasIndex('actas_escrutinio', 'idx_acta_mesa_cargo')) {
                $table->index(['id_mesa', 'id_cargo'], 'idx_acta_mesa_cargo');
            }
            if (!Schema::hasIndex('actas_escrutinio', 'idx_acta_estado')) {
                $table->index('estado', 'idx_acta_estado');
            }
            if (!Schema::hasIndex('actas_escrutinio', 'idx_acta_created')) {
                $table->index('created_at', 'idx_acta_created');
            }
        });

        Schema::table('votos_x_partido', function (Blueprint $table) {
            if (!Schema::hasIndex('votos_x_partido', 'idx_voto_acta_partido')) {
                $table->index(['id_acta', 'id_partido'], 'idx_voto_acta_partido');
            }
        });

        Schema::table('mesas', function (Blueprint $table) {
            if (!Schema::hasIndex('mesas', 'idx_mesa_codigo_tse')) {
                $table->index('codigo_tse', 'idx_mesa_codigo_tse');
            }
        });

        Schema::table('recintos', function (Blueprint $table) {
            if (!Schema::hasIndex('recintos', 'idx_recinto_geografia')) {
                $table->index('id_geografia', 'idx_recinto_geografia');
            }
        });

        Schema::table('geografias', function (Blueprint $table) {
            if (!Schema::hasIndex('geografias', 'idx_geo_tipo_nivel')) {
                $table->index(['tipo', 'nivel_jerarquico'], 'idx_geo_tipo_nivel');
            }
        });

        Schema::table('resumen_votos', function (Blueprint $table) {
            if (!Schema::hasIndex('resumen_votos', 'idx_resumen_fecha')) {
                $table->index('ultima_actualizacion', 'idx_resumen_fecha');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasIndex('users', 'idx_users_person_status')) {
                $table->index(['person_id', 'status'], 'idx_users_person_status');
            }
        });

        Schema::table('people', function (Blueprint $table) {
            if (!Schema::hasIndex('people', 'idx_people_ci')) {
                $table->index('ci', 'idx_people_ci');
            }
        });

        Schema::table('candidatos', function (Blueprint $table) {
            if (!Schema::hasIndex('candidatos', 'idx_candidato_cargo_partido')) {
                $table->index(['id_cargo', 'id_partido'], 'idx_candidato_cargo_partido');
            }
        });

        Schema::table('organizaciones_politicas', function (Blueprint $table) {
            if (!Schema::hasIndex('organizaciones_politicas', 'idx_org_estado')) {
                $table->index('estado', 'idx_org_estado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('actas_escrutinio', function (Blueprint $table) {
            $table->dropIndex('idx_acta_mesa_cargo');
            $table->dropIndex('idx_acta_estado');
            $table->dropIndex('idx_acta_created');
        });

        Schema::table('votos_x_partido', function (Blueprint $table) {
            $table->dropIndex('idx_voto_acta_partido');
        });

        Schema::table('mesas', function (Blueprint $table) {
            $table->dropIndex('idx_mesa_recinto_estado');
            $table->dropIndex('idx_mesa_codigo_tse');
        });

        Schema::table('recintos', function (Blueprint $table) {
            $table->dropIndex('idx_recinto_geografia');
        });

        Schema::table('geografias', function (Blueprint $table) {
            if (Schema::hasColumn('geografias', 'nivel_jerarquico')) {
                $table->dropIndex('idx_geo_tipo_nivel');
            }
        });

        Schema::table('resumen_votos', function (Blueprint $table) {
            $table->dropIndex('idx_resumen_fecha');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_person_status');
        });

        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex('idx_people_ci');
        });

        Schema::table('candidatos', function (Blueprint $table) {
            $table->dropIndex('idx_candidato_cargo_partido');
        });

        Schema::table('organizaciones_politicas', function (Blueprint $table) {
            $table->dropIndex('idx_org_estado');
        });
    }
};
