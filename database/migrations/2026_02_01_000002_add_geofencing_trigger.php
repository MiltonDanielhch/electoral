<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Trigger de validación geofencing a nivel de base de datos
     * Segunda capa de protección (PHP/JS ya validan)
     */
    public function up(): void
    {
        // Trigger para validar coordenadas antes de INSERT
        DB::unprepared("
            CREATE TRIGGER trg_validar_geofencing_insert
            BEFORE INSERT ON recintos
            FOR EACH ROW
            BEGIN
                IF NEW.latitud IS NOT NULL AND NEW.longitud IS NOT NULL THEN
                    IF NEW.latitud < -16.5 OR NEW.latitud > -10.0 OR
                       NEW.longitud < -68.0 OR NEW.longitud > -60.0 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Coordenadas fuera del Departamento del Beni (Lat: -16.5 a -10.0, Lon: -68.0 a -60.0)';
                    END IF;
                END IF;
            END
        ");

        // Trigger para validar coordenadas antes de UPDATE
        DB::unprepared("
            CREATE TRIGGER trg_validar_geofencing_update
            BEFORE UPDATE ON recintos
            FOR EACH ROW
            BEGIN
                IF NEW.latitud IS NOT NULL AND NEW.longitud IS NOT NULL THEN
                    IF NEW.latitud < -16.5 OR NEW.latitud > -10.0 OR
                       NEW.longitud < -68.0 OR NEW.longitud > -60.0 THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Coordenadas fuera del Departamento del Beni (Lat: -16.5 a -10.0, Lon: -68.0 a -60.0)';
                    END IF;
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_validar_geofencing_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_validar_geofencing_update');
    }
};
