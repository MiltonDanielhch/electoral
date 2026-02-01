<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geografias_limites', function (Blueprint $table) {
            $table->id('id_limite');
            $table->foreignId('id_geografia')->constrained('geografias', 'id_geografia');
            $table->json('geojson')->comment('GeoJSON del polígono');
            $table->decimal('centro_latitud', 10, 8)->nullable();
            $table->decimal('centro_longitud', 11, 8)->nullable();
            $table->decimal('area_km2', 12, 4)->nullable();
            $table->timestamps();

            $table->index('id_geografia');
        });
    }

    public function down()
    {
        Schema::dropIfExists('geografias_limites');
    }
};
