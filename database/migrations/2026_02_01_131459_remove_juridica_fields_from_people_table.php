<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina campos de Persona Jurídica - Sistema ahora solo para ciudadanos humanos
     * "1 Humano = 1 Voto" - Elimina riesgo de votos fantasma por empresas
     */
    public function up(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Eliminar campos de persona jurídica
            $table->dropColumn(['person_type', 'nit', 'legal_name']);
            
            // Eliminar el índice único anterior que incluía tipo_doc
            $table->dropUnique(['tipo_doc', 'ci', 'ci_complemento']);
            
            // Crear nuevo índice único solo para CI
            $table->unique(['ci', 'ci_complemento'], 'people_ci_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            // Restaurar campos de persona jurídica
            $table->enum('person_type', ['Natural', 'Jurídica'])->default('Natural')->after('id');
            $table->string('nit')->nullable()->after('ci_complemento');
            $table->string('legal_name')->nullable()->after('maternal_surname');
            
            // Restaurar índice anterior
            $table->dropUnique('people_ci_unique');
            $table->unique(['tipo_doc', 'ci', 'ci_complemento']);
        });
    }
};
