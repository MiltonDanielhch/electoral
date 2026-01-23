# Estrategia de Backups en Coolify

**Fecha:** 2026-01-23  
**Estado:** Fase 5 - Despliegue, Operaciones y Monitoreo

## Resumen

Este documento describe la estrategia completa de backups para el Sistema Electoral desplegado en Coolify, cubriendo base de datos, archivos de almacenamiento y código.

---

## 1. Tipos de Backups

### 1.1 Base de Datos (MySQL)

**Frecuencia:** Cada 6 horas (4 veces al día)  
**Retención:** 30 días

**Contenido:**
- Tablas completas del esquema electoral
- Triggers y procedimientos almacenados
- Índices y constraints

### 1.2 Storage / Archivos

**Frecuencia:** Diario (1 vez al día)  
**Retención:** 30 días

**Contenido:**
- Imágenes de actas (`storage/app/public/actas`)
- Archivos de fotos de personas (`storage/app/public/personas`)
- Logs (`storage/logs`)

### 1.3 Código (Opcional)

**Frecuencia:** Semanal  
**Retención:** 12 semanas

**Contenido:**
- Código fuente del proyecto (en GitHub)
- Configuraciones del proyecto

---

## 2. Configuración de Backups en Coolify

### 2.1 Backups de Base de Datos

Coolify ofrece backups nativos para bases de datos MySQL/PostgreSQL.

**Pasos:**

1. Ir al proyecto en Coolify
2. Navegar al servicio **MySQL**
3. Ir a la sección **Backups**
4. Configurar:

| Opción | Valor |
|--------|-------|
| Frecuencia | Cada 6 horas |
| Retención | 30 días |
| Tipo de backup | Completo |
| Compresión | Habilitado |

### 2.2 Backups de Volúmenes

Coolify permite hacer backups de volúmenes Docker.

**Pasos:**

1. Ir al proyecto en Coolify
2. Navegar al servicio principal de la aplicación
3. Ir a la sección **Volumes**
4. Configurar backup para el volumen `/storage`:

| Opción | Valor |
|--------|-------|
| Ruta | /storage |
| Frecuencia | Diario |
| Retención | 30 días |

### 2.3 Backups con S3 Compatibles

Coolify permite enviar backups a servicios S3 compatibles:

**Proveedores compatibles:**
- AWS S3
- DigitalOcean Spaces
- Wasabi
- MinIO (self-hosted)
- Backblaze B2

**Configuración:**

1. Ir a **Settings > Storage** en Coolify
2. Agregar configuración S3:

```bash
S3_ENDPOINT=https://s3.us-east-1.amazonaws.com
S3_ACCESS_KEY_ID=AKIAIOSFODNN7EXAMPLE
S3_SECRET_ACCESS_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY
S3_BUCKET=electoral-backups
S3_REGION=us-east-1
```

3. Seleccionar el bucket en la configuración de backups

---

## 3. Política de Retención

### 3.1 Base de Datos

| Período | Frecuencia | Cantidad | Retención |
|--------|------------|----------|-----------|
| Últimas 24 horas | Cada 6 horas | 4 | 24 horas |
| Últimos 7 días | Diario | 7 | 7 días |
| Últimos 30 días | Diario | 23 | 23 días |
| Total | - | 34 | 30 días |

### 3.2 Storage

| Período | Frecuencia | Cantidad | Retención |
|--------|------------|----------|-----------|
| Últimos 30 días | Diario | 30 | 30 días |

---

## 4. Scripts de Backup Personalizados (Opcional)

Si se necesita un control más granular de los backups, se pueden crear scripts personalizados.

### 4.1 Script de Backup de Base de Datos

**Archivo:** `scripts/backup-db.sh`

```bash
#!/bin/bash

# Script de backup de base de datos para Coolify
# Ejecutar como cron job en el host del servidor

set -e

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/electoral"
DB_HOST="${DB_HOST:-mysql}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_DATABASE:-electoral}"
DB_USER="${DB_USERNAME:-electoral}"
DB_PASS="${DB_PASSWORD}"

# Crear directorio de backup
mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -h $DB_HOST -P $DB_PORT -u $DB_USER -p$DB_PASS \
    --single-transaction \
    --routines \
    --triggers \
    --quick \
    $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Limpiar backups antiguos (retención de 30 días)
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete

echo "Backup de DB completado: db_$DATE.sql.gz"
```

### 4.2 Script de Backup de Storage

**Archivo:** `scripts/backup-storage.sh`

```bash
#!/bin/bash

# Script de backup de storage para Coolify

set -e

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/electoral"
STORAGE_DIR="/var/lib/docker/volumes/electoral_storage/_data"

# Crear directorio de backup
mkdir -p $BACKUP_DIR

# Backup de storage
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz \
    -C $STORAGE_DIR .

# Limpiar backups antiguos (retención de 30 días)
find $BACKUP_DIR -name "storage_*.tar.gz" -mtime +30 -delete

echo "Backup de storage completado: storage_$DATE.tar.gz"
```

### 4.3 Configurar Cron Jobs

Agregar al crontab del servidor:

```bash
# Backup de DB cada 6 horas
0 */6 * * * /path/to/scripts/backup-db.sh >> /var/log/electoral-backup.log 2>&1

# Backup de storage diario a las 2 AM
0 2 * * * /path/to/scripts/backup-storage.sh >> /var/log/electoral-backup.log 2>&1
```

---

## 5. Procedimiento de Restauración

### 5.1 Restaurar Base de Datos desde Coolify

**Pasos:**

1. Ir al servicio **MySQL** en Coolify
2. Ir a la sección **Backups**
3. Seleccionar el backup deseado
4. Click en **Restore**
5. Confirmar la restauración

**⚠️ ADVERTENCIA:** La restauración sobrescribirá la base de datos actual.

### 5.2 Restaurar Base de Datos Manualmente

```bash
# Copiar el backup al servidor
scp db_20260123_020000.sql.gz user@server:/tmp/

# Extraer el backup
gunzip /tmp/db_20260123_020000.sql.gz

# Restaurar la base de datos
mysql -h mysql -u electoral -p electoral < /tmp/db_20260123_020000.sql
```

### 5.3 Restaurar Storage desde Coolify

**Pasos:**

1. Ir al servicio principal en Coolify
2. Ir a la sección **Volumes**
3. Seleccionar el backup deseado
4. Click en **Restore**

### 5.4 Restaurar Storage Manualmente

```bash
# Copiar el backup al servidor
scp storage_20260123_020000.tar.gz user@server:/tmp/

# Extraer el backup
tar -xzf /tmp/storage_20260123_020000.tar.gz -C /var/lib/docker/volumes/electoral_storage/_data/

# Reiniciar el contenedor
docker restart electoral-app
```

---

## 6. Backups de Emergencia para Día de Elección

### 6.1 Estrategia de Día de Elección

Durante el día de elección, aumentar la frecuencia de backups:

| Tipo | Frecuencia Normal | Día de Elección |
|------|-------------------|-----------------|
| Base de Datos | Cada 6 horas | Cada 15 minutos |
| Storage | Diario | Cada hora |

### 6.2 Script de Backup Incremental (Día de Elección)

```bash
#!/bin/bash

# Script de backup incremental para día de elección
# Solo guarda cambios desde el último backup

set -e

DATE=$(date +%Y%m%d_%H%M%S)
LAST_BACKUP=$(ls -t /var/backups/electoral/inc_*.sql.gz 2>/dev/null | head -1)

# Backup con binlog (MySQL)
mysqldump -h mysql -u electoral -p electoral \
    --single-transaction \
    --flush-logs \
    --master-data=2 \
    --delete-master-logs | gzip > /var/backups/electoral/inc_$DATE.sql.gz

echo "Backup incremental completado: inc_$DATE.sql.gz"
```

---

## 7. Verificación de Backups

### 7.1 Verificación Automática

Agregar un cron job para verificar que los backups se están creando:

```bash
# Verificar backups cada día a las 6 AM
0 6 * * * /path/to/scripts/verify-backups.sh
```

**Archivo:** `scripts/verify-backups.sh`

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/electoral"
ALERT_EMAIL="admin@electoral.com"

# Verificar backup de DB reciente
LATEST_DB=$(ls -t $BACKUP_DIR/db_*.sql.gz 2>/dev/null | head -1)
DB_AGE=$(( ($(date +%s) - $(stat -c %Y "$LATEST_DB")) / 3600 ))

if [ $DB_AGE -gt 8 ]; then
    echo "ALERTA: Backup de DB más reciente tiene $DB_AGE horas" | \
        mail -s "Backup Alert - Electoral" $ALERT_EMAIL
fi

# Verificar backup de storage reciente
LATEST_STORAGE=$(ls -t $BACKUP_DIR/storage_*.tar.gz 2>/dev/null | head -1)
STORAGE_AGE=$(( ($(date +%s) - $(stat -c %Y "$LATEST_STORAGE")) / 3600 ))

if [ $STORAGE_AGE -gt 26 ]; then
    echo "ALERTA: Backup de Storage más reciente tiene $STORAGE_AGE horas" | \
        mail -s "Backup Alert - Electoral" $ALERT_EMAIL
fi
```

### 7.2 Verificación Manual

```bash
# Listar backups de DB
ls -lh /var/backups/electoral/db_*.sql.gz

# Listar backups de storage
ls -lh /var/backups/electoral/storage_*.tar.gz

# Verificar tamaño de backup más reciente
du -sh /var/backups/electoral/db_*.sql.gz | tail -1
```

---

## 8. Checklist de Configuración

### 8.1 Coolify

- [ ] Servicio de MySQL con backups activados
- [ ] Frecuencia de backups configurada (6 horas)
- [ ] Retención de backups configurada (30 días)
- [ ] Volumen de storage con backups activados
- [ ] Backups enviados a S3 compatible (opcional)
- [ ] Notificaciones de backup configuradas

### 8.2 Scripts Personalizados (si se usan)

- [ ] Scripts de backup creados y probados
- [ ] Permisos de ejecución configurados (`chmod +x`)
- [ ] Cron jobs configurados
- [ ] Script de verificación configurado
- [ ] Logs de backups monitoreados

### 8.3 Procedimiento de Restauración

- [ ] Procedimiento documentado
- [ ] Restauración de DB probada
- [ ] Restauración de storage probada
- [ ] Personal capacitado en procedimientos de emergencia

---

## 9. Monitoreo de Backups

### 9.1 Métricas a Monitorear

- **Tiempo del último backup:** No exceder la frecuencia configurada
- **Tamaño de backup:** Alerta si cambia drásticamente
- **Tiempo de ejecución:** Alerta si excede 30 minutos
- **Espacio disponible:** Mantener mínimo 20% de espacio libre

### 9.2 Integración con Sentry

Enviar alertas a Sentry si un backup falla:

```php
use Sentry\State\Scope;

Route::get('/api/backups/check', function () {
    $latestDbBackup = DB::table('backups')
        ->where('type', 'database')
        ->latest()
        ->first();

    if (!$latestDbBackup || $latestDbBackup->created_at->diffInHours() > 8) {
        \Sentry\configureScope(function (Scope $scope) {
            $scope->setTag('backup_type', 'database');
            $scope->setLevel(\Sentry\Severity::error());
        });
        \Sentry\captureMessage('Backup de base de datos no reciente');
    }

    return response()->json(['status' => 'ok']);
});
```

---

## Referencias

- [Coolify Backup Documentation](https://coolify.io/docs/backups)
- [MySQL Backup Best Practices](https://dev.mysql.com/doc/refman/8.0/en/backup-methods.html)
- [AWS S3 Backup Strategy](https://docs.aws.amazon.com/AmazonS3/latest/userguide/Versioning.html)
- [Guía de Despliegue en Coolify](05-coolify-deployment.md)

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-23
