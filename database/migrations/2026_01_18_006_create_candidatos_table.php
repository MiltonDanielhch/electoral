<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('candidatos', function (Blueprint $table) {
            $table->id('id_candidato');
            $table->string('nombre_completo', 150);
            $table->string('ci', 20)->unique();
            $table->foreignId('id_partido')->constrained('organizaciones_politicas', 'id_partido');
            $table->unsignedTinyInteger('id_cargo');
            $table->foreign('id_cargo')->references('id_cargo')->on('cargos')->onDelete('cascade');
            $table->foreignId('id_geografia_postulacion')->constrained('geografias', 'id_geografia');
            $table->enum('estado', ['Postulado', 'Retirado', 'Electo'])->default('Postulado');

            $table->unique(['id_cargo', 'id_geografia_postulacion', 'id_partido'], 'uk_candidato_unico');
            $table->index('id_partido');
            $table->index('id_geografia_postulacion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('candidatos');
    }
};
