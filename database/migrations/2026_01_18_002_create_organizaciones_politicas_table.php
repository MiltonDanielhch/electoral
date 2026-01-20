<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organizaciones_politicas', function (Blueprint $table) {
            $table->id('id_partido');
            $table->char('codigo_tse', 3)->unique();
            $table->string('nombre', 100);
            $table->string('sigla', 10);
            $table->char('color_hex', 7)->default('#000000');
            $table->string('logo_url')->nullable();
            $table->enum('estado', ['Activo', 'Inactivo'])->default('Activo');

            $table->index('sigla');
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('organizaciones_politicas');
    }
};
