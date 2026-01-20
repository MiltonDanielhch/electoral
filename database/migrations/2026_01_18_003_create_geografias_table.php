<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geografias', function (Blueprint $table) {
            $table->id('id_geografia');
            $table->char('codigo_tse', 9)->unique();
            $table->string('nombre', 100);
            $table->enum('tipo', ['Departamento', 'Provincia', 'Municipio', 'Cantón', 'Localidad']);
            $table->foreignId('parent_id')->nullable()->constrained('geografias', 'id_geografia')->onDelete('set null');
            $table->tinyInteger('nivel_jerarquico')->default(0);

            $table->index(['tipo', 'parent_id'], 'idx_geo_tipo_parent');
            $table->index('nivel_jerarquico');
            $table->index('codigo_tse');
        });

        DB::unprepared("
        CREATE TRIGGER trg_geo_nivel_jerarquico
        BEFORE INSERT ON geografias
        FOR EACH ROW
        BEGIN
            IF NEW.parent_id IS NULL THEN
                SET NEW.nivel_jerarquico = 1;
            ELSE
                SELECT nivel_jerarquico + 1 INTO @nivel_padre
                FROM geografias WHERE id_geografia = NEW.parent_id;
                SET NEW.nivel_jerarquico = COALESCE(@nivel_padre, 2);
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_geo_nivel_jerarquico');
        Schema::dropIfExists('geografias');
    }
};
