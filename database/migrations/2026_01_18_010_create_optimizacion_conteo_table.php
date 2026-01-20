<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resumen_votos', function (Blueprint $table) {
            $table->unsignedTinyInteger('id_cargo');
            $table->foreignId('id_geografia')->constrained('geografias', 'id_geografia')->onDelete('cascade');
            $table->foreignId('id_partido')->constrained('organizaciones_politicas', 'id_partido')->onDelete('cascade');
            $table->integer('total_votos')->unsigned()->default(0);
            $table->integer('total_mesas_escrutadas')->unsigned()->default(0);
            $table->decimal('porcentaje_votos', 5, 2)->default(0.00);
            $table->timestamp('ultima_actualizacion')->useCurrent();

            $table->primary(['id_cargo', 'id_geografia', 'id_partido'], 'pk_resumen');
            $table->index(['id_geografia', 'id_cargo'], 'idx_resumen_territorio');
            $table->index(['id_cargo', 'ultima_actualizacion'], 'idx_actualizacion_rapida');
            $table->index('porcentaje_votos');
        });

        Schema::create('control_procesamiento', function (Blueprint $table) {
            $table->id('id_control');
            $table->string('tabla_destino', 50)->unique();
            $table->bigInteger('ultimo_id_procesado')->default(0);
            $table->dateTime('fecha_ultima_actualizacion')->nullable();
            $table->enum('estado', ['Activo', 'Pausado', 'Error'])->default('Activo');
            $table->integer('total_registros_procesados')->default(0);
            $table->timestamp('ultima_ejecucion_exitosa')->nullable();
            $table->text('ultimo_error')->nullable();
            $table->index('estado');
            $table->timestamps();
        });

        DB::table('control_procesamiento')->insert([
            'tabla_destino' => 'resumen_votos',
            'ultimo_id_procesado' => 0,
            'estado' => 'Activo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::unprepared("
        CREATE TABLE cache_resultados (
            cache_key VARCHAR(100) PRIMARY KEY,
            cache_data JSON NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cache_expires (expires_at)
        ) ENGINE=InnoDB;

        CREATE TABLE cache_resultados_backup (
            cache_key VARCHAR(100) PRIMARY KEY,
            cache_data JSON NOT NULL,
            last_sync DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_backup_sync (last_sync)
        ) ENGINE=InnoDB;

        CREATE EVENT IF NOT EXISTS sync_cache_backup
        ON SCHEDULE EVERY 30 SECOND
        DO
        INSERT INTO cache_resultados_backup (cache_key, cache_data)
        SELECT cache_key, cache_data FROM cache_resultados
        ON DUPLICATE KEY UPDATE
            cache_data = VALUES(cache_data),
            last_sync = CURRENT_TIMESTAMP;
        ");

        DB::unprepared("
        CREATE TRIGGER trg_actualizar_resumen_validacion
        AFTER UPDATE ON actas_escrutinio
        FOR EACH ROW
        BEGIN
            DECLARE v_id_geografia INT;
            DECLARE v_id_cargo TINYINT;
            IF OLD.estado != 'Validada' AND NEW.estado = 'Validada' THEN
                SELECT r.id_geografia, NEW.id_cargo INTO v_id_geografia, v_id_cargo
                FROM mesas m JOIN recintos r ON m.id_recinto = r.id_recinto
                WHERE m.id_mesa = NEW.id_mesa;
                UPDATE control_procesamiento
                SET ultimo_id_procesado = NEW.id_acta,
                    total_registros_procesados = total_registros_procesados + 1,
                    fecha_ultima_actualizacion = NOW()
                WHERE tabla_destino = 'resumen_votos';
                INSERT INTO cache_resultados (cache_key, cache_data, expires_at)
                VALUES (
                    CONCAT('worker_signal_', v_id_cargo, '_', v_id_geografia),
                    JSON_OBJECT('acta_id', NEW.id_acta, 'cargo_id', v_id_cargo, 'geografia_id', v_id_geografia, 'timestamp', UNIX_TIMESTAMP()),
                    DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                )
                ON DUPLICATE KEY UPDATE
                    cache_data = VALUES(cache_data),
                    expires_at = VALUES(expires_at);
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_resumen_validacion');
        DB::unprepared('DROP EVENT IF EXISTS sync_cache_backup');
        DB::unprepared('DROP TABLE IF EXISTS cache_resultados, cache_resultados_backup');
        Schema::dropIfExists('control_procesamiento');
        Schema::dropIfExists('resumen_votos');
    }
};
