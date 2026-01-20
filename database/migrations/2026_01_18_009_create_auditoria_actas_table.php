<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditoria_actas', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->foreignId('id_acta')->constrained('actas_escrutinio', 'id_acta');
            $table->string('campo_modificado', 50);
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->string('usuario', 50);
            $table->timestamp('fecha_cambio')->useCurrent();

            $table->index('id_acta');
            $table->index('fecha_cambio');
        });

        DB::unprepared("
        CREATE TRIGGER trg_auditoria_actas
        AFTER UPDATE ON actas_escrutinio
        FOR EACH ROW
        BEGIN
            IF OLD.estado != NEW.estado THEN
                INSERT INTO auditoria_actas (id_acta, campo_modificado, valor_anterior, valor_nuevo, usuario)
                VALUES (NEW.id_acta, 'estado', OLD.estado, NEW.estado, NEW.digitador);
            END IF;
            IF OLD.votos_validos != NEW.votos_validos OR OLD.total_sobres != NEW.total_sobres THEN
                INSERT INTO auditoria_actas (id_acta, campo_modificado, valor_anterior, valor_nuevo, usuario)
                VALUES (
                    NEW.id_acta,
                    'totales',
                    CONCAT(OLD.votos_validos, '/', OLD.total_sobres),
                    CONCAT(NEW.votos_validos, '/', NEW.total_sobres),
                    NEW.digitador
                );
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_actas');
        Schema::dropIfExists('auditoria_actas');
    }
};
