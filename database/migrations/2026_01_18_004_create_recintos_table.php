<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recintos', function (Blueprint $table) {
            $table->id('id_recinto');
            $table->char('codigo_tse', 3)->unique();
            $table->foreignId('id_geografia')->constrained('geografias', 'id_geografia');
            $table->string('nombre', 150);
            $table->string('direccion', 255)->nullable();
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['latitud', 'longitud'], 'idx_recintos_coords');
            $table->index('id_geografia');
            $table->index('codigo_tse');
        });

        DB::unprepared("
        CREATE TRIGGER trg_recintos_validacion
        BEFORE INSERT ON recintos
        FOR EACH ROW
        BEGIN
            DECLARE geo_tipo VARCHAR(20);
            SELECT tipo INTO geo_tipo FROM geografias WHERE id_geografia = NEW.id_geografia;
            IF geo_tipo != 'Municipio' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: Los recintos solo pueden asociarse a municipios';
            END IF;
            IF NEW.codigo_tse NOT REGEXP '^[0-9]{3}$' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: Código TSE de recinto debe ser 3 dígitos numéricos';
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_recintos_validacion');
        Schema::dropIfExists('recintos');
    }
};
