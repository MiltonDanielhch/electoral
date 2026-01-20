<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('actas_escrutinio', function (Blueprint $table) {
            $table->id('id_acta');
            $table->foreignId('id_mesa')->constrained('mesas', 'id_mesa');
            $table->unsignedTinyInteger('id_cargo');
            $table->foreign('id_cargo')->references('id_cargo')->on('cargos')->onDelete('cascade');
            $table->string('codigo_acta', 30)->unique();
            $table->string('foto_frontal', 255)->nullable();
            $table->string('foto_reverso', 255)->nullable();
            $table->integer('total_sobres')->unsigned();
            $table->integer('total_votantes')->unsigned();
            $table->integer('votos_validos')->unsigned();
            $table->integer('votos_blancos')->unsigned();
            $table->integer('votos_nulos')->unsigned();
            $table->integer('votos_impugnados')->unsigned()->default(0);
            $table->string('digitador', 30);
            $table->enum('estado', ['Pendiente', 'Digitada', 'Observada', 'Validada', 'Cerrada'])->default('Pendiente');
            $table->timestamps();

            $table->unique(['id_mesa', 'id_cargo'], 'uk_mesa_cargo');
            $table->index(['id_cargo', 'estado'], 'idx_acta_cargo_estado');
            $table->index('estado');
            $table->index('created_at');
            $table->engine = 'InnoDB';
        });

        DB::statement("ALTER TABLE actas_escrutinio ADD CONSTRAINT ck_suma_sobres CHECK (votos_validos + votos_blancos + votos_nulos + votos_impugnados = total_sobres)");
    }

    public function down()
    {
        try {
            DB::statement("ALTER TABLE actas_escrutinio DROP CONSTRAINT IF EXISTS ck_suma_sobres");
        } catch (\Exception $e) {
            DB::statement("ALTER TABLE actas_escrutinio DROP CHECK IF EXISTS ck_suma_sobres");
        }
        Schema::dropIfExists('actas_escrutinio');
    }
};
