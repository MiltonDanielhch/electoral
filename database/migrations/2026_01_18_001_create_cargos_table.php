<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->tinyIncrements('id_cargo');
            $table->string('descripcion', 60);
            $table->enum('nivel', ['D', 'P', 'M']);
            $table->enum('tipo_acta', ['Normal', 'Especial'])->default('Normal');
            $table->boolean('acta_unica')->default(true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cargos');
    }
};
