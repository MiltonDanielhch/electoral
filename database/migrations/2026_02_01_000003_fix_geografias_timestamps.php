<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Arreglar timestamps null en registros existentes de geografías
     * Esto evita errores al usar el caché que depende de updated_at
     */
    public function up(): void
    {
        $now = Carbon::now();

        // Actualizar registros que tienen timestamps null
        DB::table('geografias')
            ->whereNull('created_at')
            ->orWhereNull('updated_at')
            ->update([
                'created_at' => $now,
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No revertir - los timestamps deben mantenerse
    }
};
