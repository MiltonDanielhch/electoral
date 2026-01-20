<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('votos_x_partido', function (Blueprint $table) {
            $table->foreignId('id_acta')->constrained('actas_escrutinio', 'id_acta')->onDelete('cascade');
            $table->foreignId('id_partido')->constrained('organizaciones_politicas', 'id_partido');
            $table->integer('votos')->unsigned();
            $table->primary(['id_acta', 'id_partido']);
            $table->index('id_partido');
            $table->index('votos');
        });

        DB::unprepared("
        CREATE TRIGGER trg_votos_validacion
        BEFORE INSERT ON votos_x_partido
        FOR EACH ROW
        BEGIN
            DECLARE total_validos INT;
            DECLARE suma_actual INT;
            SELECT votos_validos INTO total_validos FROM actas_escrutinio WHERE id_acta = NEW.id_acta;
            SELECT COALESCE(SUM(votos), 0) INTO suma_actual FROM votos_x_partido WHERE id_acta = NEW.id_acta;
            IF (suma_actual + NEW.votos) > total_validos THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: La suma de votos por partido excede los votos válidos del acta';
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_votos_validacion');
        Schema::dropIfExists('votos_x_partido');
    }
};
