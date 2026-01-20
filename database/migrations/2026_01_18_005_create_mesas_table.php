<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id('id_mesa');
            $table->char('codigo_tse', 11)->unique();
            $table->foreignId('id_recinto')->constrained('recintos', 'id_recinto');
            $table->enum('estado', ['Habilitada', 'Escrutada', 'Anulada', 'Observada'])->default('Habilitada');

            $table->index(['id_recinto', 'estado'], 'idx_mesa_recinto_estado');
            $table->index('codigo_tse');
            $table->index('estado');
        });

        DB::unprepared("
        CREATE TRIGGER trg_mesas_validacion
        BEFORE INSERT ON mesas
        FOR EACH ROW
        BEGIN
            IF NEW.codigo_tse NOT REGEXP '^[0-9]{11}$' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: Código TSE de mesa debe ser 11 dígitos numéricos';
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_mesas_validacion');
        Schema::dropIfExists('mesas');
    }
};
