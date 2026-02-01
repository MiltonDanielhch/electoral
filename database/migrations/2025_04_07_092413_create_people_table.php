<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            // 🆕 Tipo de persona (clave para CI/NIT)
            $table->enum('person_type', ['Natural', 'Jurídica'])->default('Natural');

            $table->string('tipo_doc', 10)->default('CI');
            $table->string('ci')->nullable();
            $table->string('ci_complemento', 5)->nullable();

            // 🆕 NIT para personas jurídicas
            $table->string('nit')->nullable(); // NIT sin guiones


            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('paternal_surname')->nullable();
            $table->string('maternal_surname')->nullable();

            // 🆕 Razón social (para personas jurídicas)
            $table->string('legal_name')->nullable(); // "Fundación XYZ", "Empresa ABC S.R.L."

            $table->date('birth_date')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();

            $table->text('address')->nullable();

            $table->enum('gender', ['Masculino', 'Femenino'])->nullable();
            $table->string('image')->nullable();
            $table->string('padron')->nullable()->index(); // Indexado para búsquedas rápidas

            $table->tinyInteger('status')->default(1)->comment('1=activo,0=inactivo,2=pending');

            $table->timestamps();
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
            $table->string('registerRole')->nullable();

            $table->softDeletes();
            $table->foreignId('deleteUser_id')->nullable()->constrained('users');
            $table->string('deleteRole')->nullable();
            $table->text('deleteObservation')->nullable();

            // Índice único para evitar duplicados (CI/NIT + complemento + tipo)
            $table->unique(['tipo_doc', 'ci', 'ci_complemento']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('people');
    }
};
