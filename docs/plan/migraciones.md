# 🗄️ Migraciones de Base de Datos - Sistema Electoral

Este documento contiene el código fuente completo de las migraciones para el sistema de escrutinio.
Las migraciones están ordenadas cronológicamente para respetar las dependencias de claves foráneas e incluyen Triggers y Eventos para la lógica de negocio en base de datos.

## 📋 Índice de Migraciones

| Orden | Archivo | Descripción |
| :--- | :--- | :--- |
| 001 | [Cargos](#1-tabla-cargos) | Catálogo de cargos electivos. |
| 002 | [Organizaciones](#2-tabla-organizaciones-políticas) | Partidos y agrupaciones políticas. |
| 003 | [Geografías](#3-tabla-geografías) | Jerarquía territorial con Trigger de nivel. |
| 004 | [Recintos](#4-tabla-recintos) | Lugares de votación con validación. |
| 005 | [Mesas](#5-tabla-mesas) | Mesas de sufragio. |
| 006 | [Candidatos](#6-tabla-candidatos) | Postulantes por cargo y territorio. |
| 007 | [Actas](#7-tabla-actas-de-escrutinio) | Cabecera de actas y validación de totales. |
| 008 | [Votos](#8-tabla-votos-por-partido) | Detalle de votos con Trigger de consistencia. |
| 009 | [Auditoría](#9-tabla-auditoría-de-actas) | Historial de cambios automático. |
| 010 | [Optimización](#10-tabla-optimización-y-conteo) | Tablas de resumen, caché en memoria y eventos. |

---

## 1. Tabla Cargos
**Archivo:** `2026_01_18_001_create_cargos_table.php`

```php
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
```

## 2. Tabla Organizaciones Políticas
**Archivo:** `2026_01_18_002_create_organizaciones_politicas_table.php`

```php
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
```

## 3. Tabla Geografías
**Archivo:** `2026_01_18_003_create_geografias_table.php`

Incluye trigger `trg_geo_nivel_jerarquico` para calcular automáticamente el nivel (1=Dpto, 2=Prov, etc.).

```php
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
```

## 4. Tabla Recintos
**Archivo:** `2026_01_18_004_create_recintos_table.php`

Incluye validación para asegurar que los recintos pertenezcan a un Municipio.

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('recintos', function (Blueprint $table) {
            $table->id('id_recinto');
            $table->char('codigo_tse', 3)->unique();
            $table->foreignId('id_geografia')->constrained('geografias', 'id_geografia');
            $table->string('nombre', 150);
            $table->string('direccion', 255)->nullable();

            $table->index('id_geografia');
            $table->index('codigo_tse');
        });

        DB::unprepared("
        CREATE TRIGGER trg_recintos_validacion
        BEFORE INSERT ON recintos
        FOR EACH ROW
        BEGIN
            DECLARE geo_tipo VARCHAR(20);
            SELECT tipo INTO geo_tipo FROM geografias WHERE id_geografia = NEW.id_geografia;
            IF geo_tipo != 'Municipio' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: Los recintos solo pueden asociarse a municipios';
            END IF;
            IF NEW.codigo_tse NOT REGEXP '^[0-9]{3}$' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: Código TSE de recinto debe ser 3 dígitos numéricos';
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_recintos_validacion');
        Schema::dropIfExists('recintos');
    }
};
```

## 5. Tabla Mesas
**Archivo:** `2026_01_18_005_create_mesas_table.php`

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mesas', function (Blueprint $table) {
            $table->id('id_mesa');
            $table->char('codigo_tse', 11)->unique();
            $table->foreignId('id_recinto')->constrained('recintos', 'id_recinto');
            $table->enum('estado', ['Habilitada', 'Escrutada', 'Anulada', 'Observada'])->default('Habilitada');

            $table->index(['id_recinto', 'estado'], 'idx_mesa_recinto_estado');
            $table->index('codigo_tse');
            $table->index('estado');
        });

        DB::unprepared("
        CREATE TRIGGER trg_mesas_validacion
        BEFORE INSERT ON mesas
        FOR EACH ROW
        BEGIN
            IF NEW.codigo_tse NOT REGEXP '^[0-9]{11}$' THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: Código TSE de mesa debe ser 11 dígitos numéricos';
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_mesas_validacion');
        Schema::dropIfExists('mesas');
    }
};
```

## 6. Tabla Candidatos
**Archivo:** `2026_01_18_006_create_candidatos_table.php`

```php
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
```

## 7. Tabla Actas de Escrutinio
**Archivo:** `2026_01_18_007_create_actas_escrutinio_table.php`

Incluye `CHECK CONSTRAINT` para asegurar integridad matemática (validos + blancos + nulos = total).

```php
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
        DB::statement("ALTER TABLE actas_escrutinio DROP CHECK IF EXISTS ck_suma_sobres");
        Schema::dropIfExists('actas_escrutinio');
    }
};
```

## 8. Tabla Votos por Partido
**Archivo:** `2026_01_18_008_create_votos_x_partido_table.php`

Incluye trigger para evitar que la suma de votos por partido supere los votos válidos del acta.

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('votos_x_partido', function (Blueprint $table) {
            $table->foreignId('id_acta')->constrained('actas_escrutinio', 'id_acta')->onDelete('cascade');
            $table->foreignId('id_partido')->constrained('organizaciones_politicas', 'id_partido');
            $table->integer('votos')->unsigned();
            $table->primary(['id_acta', 'id_partido']);
            $table->index('id_partido');
            $table->index('votos');
        });

        DB::unprepared("
        CREATE TRIGGER trg_votos_validacion
        BEFORE INSERT ON votos_x_partido
        FOR EACH ROW
        BEGIN
            DECLARE total_validos INT;
            DECLARE suma_actual INT;
            SELECT votos_validos INTO total_validos FROM actas_escrutinio WHERE id_acta = NEW.id_acta;
            SELECT COALESCE(SUM(votos), 0) INTO suma_actual FROM votos_x_partido WHERE id_acta = NEW.id_acta;
            IF (suma_actual + NEW.votos) > total_validos THEN
                SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Error: La suma de votos por partido excede los votos válidos del acta';
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_votos_validacion');
        Schema::dropIfExists('votos_x_partido');
    }
};
```

## 9. Tabla Auditoría de Actas
**Archivo:** `2026_01_18_009_create_auditoria_actas_table.php`

Registra automáticamente cualquier cambio en el estado o totales de un acta.

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auditoria_actas', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->foreignId('id_acta')->constrained('actas_escrutinio', 'id_acta');
            $table->string('campo_modificado', 50);
            $table->text('valor_anterior')->nullable();
            $table->text('valor_nuevo')->nullable();
            $table->string('usuario', 50);
            $table->timestamp('fecha_cambio')->useCurrent();

            $table->index('id_acta');
            $table->index('fecha_cambio');
        });

        DB::unprepared("
        CREATE TRIGGER trg_auditoria_actas
        AFTER UPDATE ON actas_escrutinio
        FOR EACH ROW
        BEGIN
            IF OLD.estado != NEW.estado THEN
                INSERT INTO auditoria_actas (id_acta, campo_modificado, valor_anterior, valor_nuevo, usuario)
                VALUES (NEW.id_acta, 'estado', OLD.estado, NEW.estado, NEW.digitador);
            END IF;
            IF OLD.votos_validos != NEW.votos_validos OR OLD.total_sobres != NEW.total_sobres THEN
                INSERT INTO auditoria_actas (id_acta, campo_modificado, valor_anterior, valor_nuevo, usuario)
                VALUES (
                    NEW.id_acta,
                    'totales',
                    CONCAT(OLD.votos_validos, '/', OLD.total_sobres),
                    CONCAT(NEW.votos_validos, '/', NEW.total_sobres),
                    NEW.digitador
                );
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_actas');
        Schema::dropIfExists('auditoria_actas');
    }
};
```

## 10. Tabla Optimización y Conteo
**Archivo:** `2026_01_18_010_create_optimizacion_conteo_table.php`

Crea tablas de resumen, tablas en memoria para caché y eventos de sincronización.

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('resumen_votos', function (Blueprint $table) {
            $table->unsignedTinyInteger('id_cargo');
            $table->foreignId('id_geografia')->constrained('geografias', 'id_geografia')->onDelete('cascade');
            $table->foreignId('id_partido')->constrained('organizaciones_politicas', 'id_partido')->onDelete('cascade');
            $table->integer('total_votos')->unsigned()->default(0);
            $table->integer('total_mesas_escrutadas')->unsigned()->default(0);
            $table->decimal('porcentaje_votos', 5, 2)->default(0.00);
            $table->timestamp('ultima_actualizacion')->useCurrent();

            $table->primary(['id_cargo', 'id_geografia', 'id_partido'], 'pk_resumen');
            $table->index(['id_geografia', 'id_cargo'], 'idx_resumen_territorio');
            $table->index(['id_cargo', 'ultima_actualizacion'], 'idx_actualizacion_rapida');
            $table->index('porcentaje_votos');
        });

        Schema::create('control_procesamiento', function (Blueprint $table) {
            $table->id('id_control');
            $table->string('tabla_destino', 50)->unique();
            $table->bigInteger('ultimo_id_procesado')->default(0);
            $table->dateTime('fecha_ultima_actualizacion')->nullable();
            $table->enum('estado', ['Activo', 'Pausado', 'Error'])->default('Activo');
            $table->integer('total_registros_procesados')->default(0);
            $table->timestamp('ultima_ejecucion_exitosa')->nullable();
            $table->text('ultimo_error')->nullable();
            $table->index('estado');
        });

        DB::table('control_procesamiento')->insert([
            'tabla_destino' => 'resumen_votos',
            'ultimo_id_procesado' => 0,
            'estado' => 'Activo',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::unprepared("
        CREATE TABLE cache_resultados (
            cache_key VARCHAR(100) PRIMARY KEY,
            cache_data JSON NOT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_cache_expires (expires_at)
        ) ENGINE=MEMORY;

        CREATE TABLE cache_resultados_backup (
            cache_key VARCHAR(100) PRIMARY KEY,
            cache_data JSON NOT NULL,
            last_sync DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_backup_sync (last_sync)
        ) ENGINE=InnoDB;

        CREATE EVENT IF NOT EXISTS sync_cache_backup
        ON SCHEDULE EVERY 30 SECOND
        DO
        INSERT INTO cache_resultados_backup (cache_key, cache_data)
        SELECT cache_key, cache_data FROM cache_resultados
        ON DUPLICATE KEY UPDATE
            cache_data = VALUES(cache_data),
            last_sync = CURRENT_TIMESTAMP;
        ");

        DB::unprepared("
        CREATE TRIGGER trg_actualizar_resumen_validacion
        AFTER UPDATE ON actas_escrutinio
        FOR EACH ROW
        BEGIN
            DECLARE v_id_geografia INT;
            DECLARE v_id_cargo TINYINT;
            IF OLD.estado != 'Validada' AND NEW.estado = 'Validada' THEN
                SELECT r.id_geografia, NEW.id_cargo INTO v_id_geografia, v_id_cargo
                FROM mesas m JOIN recintos r ON m.id_recinto = r.id_recinto
                WHERE m.id_mesa = NEW.id_mesa;
                UPDATE control_procesamiento
                SET ultimo_id_procesado = NEW.id_acta,
                    total_registros_procesados = total_registros_procesados + 1,
                    fecha_ultima_actualizacion = NOW()
                WHERE tabla_destino = 'resumen_votos';
                INSERT INTO cache_resultados (cache_key, cache_data, expires_at)
                VALUES (
                    CONCAT('worker_signal_', v_id_cargo, '_', v_id_geografia),
                    JSON_OBJECT('acta_id', NEW.id_acta, 'cargo_id', v_id_cargo, 'geografia_id', v_id_geografia, 'timestamp', UNIX_TIMESTAMP()),
                    DATE_ADD(NOW(), INTERVAL 5 MINUTE)
                )
                ON DUPLICATE KEY UPDATE
                    cache_data = VALUES(cache_data),
                    expires_at = VALUES(expires_at);
            END IF;
        END");
    }

    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_actualizar_resumen_validacion');
        DB::unprepared('DROP EVENT IF EXISTS sync_cache_backup');
        DB::unprepared('DROP TABLE IF EXISTS cache_resultados, cache_resultados_backup');
        Schema::dropIfExists('control_procesamiento');
        Schema::dropIfExists('resumen_votos');
    }
};
```
