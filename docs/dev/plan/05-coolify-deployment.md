# Guía de Despliegue en Coolify

**Fecha:** 2026-01-23  
**Estado:** Fase 5 - Despliegue, Operaciones y Monitoreo

## Resumen

Este documento detalla cómo desplegar el Sistema Electoral en Coolify, una plataforma de despliegue open-source que simplifica el manejo de contenedores Docker.

---

## Requisitos Previos

1. **Coolify instalado** - Servidor con Coolify (VPS mínimo 2CPU/4GB RAM)
2. **GitHub Repository** - El código debe estar en GitHub con acceso webhooks
3. **Docker Hub** (opcional) - Para almacenar imágenes Docker personalizadas
4. **Dominio** - Dominio configurado para apuntar al servidor de Coolify

---

## 1. Configuración del Proyecto en Coolify

### 1.1 Crear Nuevo Proyecto

1. Acceder a tu panel de Coolify
2. Hacer clic en **"New Project"**
3. Seleccionar **"From Git Repository"**
4. Conectar tu cuenta de GitHub
5. Seleccionar el repositorio `electoral`
6. Elegir la rama `main` (o `develop` para staging)

### 1.2 Configurar Dockerfile

Coolify detectará automáticamente el `Dockerfile` en la raíz del proyecto.

**Dockerfile existente:** `Dockerfile` (basado en NGINX Unit + PHP 8.2)

### 1.3 Configurar Variables de Entorno

En Coolify, ir a **Environment Variables** y configurar:

#### Variables de Aplicación

```bash
APP_NAME=Electoral
APP_ENV=production
APP_KEY=base64:GENERAR_CON_PHP_ARTISAN_KEY:GENERATE
APP_DEBUG=false
APP_URL=https://electoral.tu-dominio.com

LOG_CHANNEL=stack
LOG_LEVEL=warning
```

#### Base de Datos

Coolify puede manejar bases de datos MySQL/PostgreSQL integradas o externas.

**Opción A: Usar base de datos de Coolify**

1. En el proyecto, agregar un servicio **MySQL** o **PostgreSQL**
2. Coolify proporcionará las credenciales automáticamente

```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=electoral
DB_USERNAME=electoral
DB_PASSWORD=contraseña_segura_generada_por_coolify
```

**Opción B: Base de datos externa**

```bash
DB_CONNECTION=mysql
DB_HOST=tu-db-host.com
DB_PORT=3306
DB_DATABASE=electoral
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

#### Caché y Colas

```bash
BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
```

#### Redis

```bash
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
```

#### Correo (Opcional)

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.tu-proveedor.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@dominio.com
MAIL_PASSWORD=tu_contraseña
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@electoral.tu-dominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### Seguridad

```bash
SESSION_SECURE_COOKIE=true
```

---

## 2. Configuración de Servicios Adicionales

### 2.1 Servicio Redis

1. En el proyecto de Coolify, agregar servicio **Redis**
2. Coolify lo configurará automáticamente en el mismo network Docker
3. La variable `REDIS_HOST=redis` apuntará al servicio Redis

### 2.2 Servicio de Base de Datos

1. Agregar servicio **MySQL** (8.0) o **PostgreSQL** (14+)
2. Configurar las credenciales en las variables de entorno
3. Activar persistent storage para la base de datos

### 2.3 Servicio de Queue Worker (Opcional)

Para procesar jobs en segundo plano:

**Dockerfile para queue worker:**

```dockerfile
FROM electoral-app:latest

CMD ["php", "artisan", "queue:work", "--sleep=3", "--tries=3", "--timeout=90"]
```

En Coolify:
1. Crear otro servicio usando el mismo Dockerfile
2. Sobrescribir el comando con el de queue worker
3. Escalar según sea necesario (1-3 workers)
4. Configurar restart policy `unless-stopped`

### 2.4 Servicio de Scheduler (Opcional)

Para ejecutar comandos programados:

```dockerfile
FROM electoral-app:latest

CMD ["php", "artisan", "schedule:work"]
```

En Coolify:
1. Crear otro servicio
2. Sobrescribir el comando con el de scheduler
3. Solo 1 instancia del scheduler

---

## 3. Configuración de Dominio y SSL

### 3.1 Configurar Dominio

1. En el proyecto de Coolify, ir a **Domains**
2. Agregar el dominio: `electoral.tu-dominio.com`
3. Coolify generará automáticamente un certificado SSL usando Let's Encrypt

### 3.2 DNS Configuration

Apuntar el dominio al servidor de Coolify:

```
A    electoral    123.45.67.89
```

### 3.3 Health Check

Coolify configurará automáticamente un health check al endpoint `/api/health` (si existe) o a la ruta `/`.

Crear endpoint de health:

```php
Route::get('/api/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

---

## 4. Despliegue Inicial

### 4.1 Ejecutar Migraciones

Antes del primer despliegue, es necesario ejecutar las migraciones y seeders.

**Opción A: Desde Coolify (SSH Console)**

1. En el proyecto, ir a **Console**
2. Ejecutar comandos:
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

**Opción B: Deployment Hook**

Agregar un hook en Coolify (Deployment Hooks):

```bash
php artisan migrate --force --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4.2 Permiso de Storage

Asegurar que el directorio `storage` sea writable.

En Coolify, configurar el volume mapping para `storage`:

```
/storage -> /var/www/example/storage
```

---

## 5. Configuración de Backups en Coolify

Coolify tiene integración nativa con backups para bases de datos y volúmenes.

### 5.1 Backups de Base de Datos

1. Ir al servicio de base de datos en Coolify
2. Activar **Backups**
3. Configurar retención: 7-30 días
4. Configurar frecuencia: Diaria o cada 6 horas

### 5.2 Backups de Storage (Archivos)

1. Ir al servicio principal de la aplicación
2. Activar **Volume Backups**
3. Configurar retención: 7-30 días
4. Configurar frecuencia: Diaria

### 5.3 Backups Automáticos

Coolify permite automatizar los backups:

- **DB Backup:** Cada 6 horas, retención 7 días
- **Volume Backup:** Diario, retención 30 días
- **Off-site:** Configurar integración con S3 compatible (MinIO, Wasabi, AWS S3)

---

## 6. Integración con GitHub Actions

El workflow `.github/workflows/ci-cd.yml` ejecutará pruebas automáticamente en cada push/PR.

Coolify se puede integrar con GitHub Actions para despliegues automáticos:

### 6.1 Webhook Trigger

1. En Coolify, ir al proyecto
2. Copiar el **Webhook URL**
3. En GitHub Actions workflow, agregar:

```yaml
- name: Trigger Coolify Deployment
  run: curl -X POST ${{ secrets.COOLIFY_WEBHOOK_URL }}
  if: github.ref == 'refs/heads/main'
```

### 6.2 Despliegue Automático

Coolify tiene opción de **Auto Deploy**:
- Activar en el proyecto de Coolify
- Cada push a la rama seleccionada activará un nuevo despliegue

---

## 7. Monitoreo y Logs

### 7.1 Logs en Coolify

1. Ir al proyecto en Coolify
2. Click en **Logs**
3. Ver logs en tiempo real de todos los servicios

### 7.2 Integración con Sentry

Para monitoreo avanzado de errores:

1. Instalar Sentry en Laravel:
   ```bash
   composer require sentry/sentry-laravel
   php artisan sentry:publish
   php artisan sentry:install
   ```

2. Configurar variable de entorno en Coolify:
   ```bash
   SENTRY_LARAVEL_DSN=https://xxx@o0.ingest.sentry.io/xxx
   SENTRY_TRACES_SAMPLE_RATE=0.1
   SENTRY_PROFILES_SAMPLE_RATE=0.1
   ```

3. Configurar `app/Exceptions/Handler.php`:
   ```php
   public function register(): void
   {
       $this->reportable(function (Throwable $e) {
           \Sentry\Laravel\Integration::captureUnhandledException($e);
       });
   }
   ```

---

## 8. Configuración de Seguridad

### 8.1 Firewall en Coolify

Coolify maneja el firewall automáticamente:
- Solo puertos necesarios abiertos (80, 443, 22 SSH)
- Rate limiting configurado en Nginx

### 8.2 Configuración de Nginx

Coolify configura automáticamente Nginx con SSL y headers de seguridad:

- HSTS habilitado
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block

### 8.3 Variables Sensibles

Nunca incluir secrets en el repositorio:
- Todas las credenciales deben ir en **Environment Variables** de Coolify
- Usar `APP_KEY` generado únicamente con `php artisan key:generate`
- Rotar secretos periódicamente

---

## 9. Escalabilidad

### 9.1 Escalar Servicios

En Coolify, ir al servicio y ajustar **Replicas**:

- **App:** 2-4 réplicas (según tráfico)
- **Queue Worker:** 2-5 workers (según carga de jobs)
- **Scheduler:** 1 réplica (siempre)
- **Redis:** 1 réplica (puede usar modo cluster con Redis Sentinel)

### 9.2 Load Balancing

Coolify maneja automáticamente el load balancing entre réplicas.

---

## 10. Checklist Pre-Producción

- [ ] **Repositorio conectado** a Coolify desde GitHub
- [ ] **Variables de entorno** configuradas correctamente
- [ ] **Base de datos** servicio creado y persistent volume activado
- [ ] **Redis** servicio configurado
- [ ] **Migraciones ejecutadas** en primer despliegue
- [ ] **Storage link** creado (`php artisan storage:link`)
- [ ] **Cachés configurados** (config, route, view cache)
- [ ] **Dominio configurado** y apuntando al servidor
- [ ] **SSL activado** (Let's Encrypt auto-renovable)
- [ ] **Backups activados** para DB y volumes
- [ ] **Sentry configurado** para monitoreo de errores
- [ ] **Health check** configurado
- [ ] **Queue workers** activados (si necesario)
- [ ] **Scheduler** activado (si necesario)
- [ ] **GitHub Actions** probando en cada push
- [ ] **Auto deploy** desactivado inicialmente (activar después de validar)

---

## 11. Resolución de Problemas Comunes

### Error 500 después del despliegue

1. Verificar logs en Coolify
2. Verificar que las migraciones se ejecutaron correctamente
3. Verificar permisos de storage
4. Verificar que el cache se borró: `php artisan cache:clear`

### Base de datos no conecta

1. Verificar que el servicio de DB está corriendo
2. Verificar las variables de entorno DB_HOST, DB_PORT
3. Verificar que están en el mismo network Docker

### SSL no se genera

1. Verificar que el dominio está apuntando correctamente al servidor
2. Verificar que los puertos 80 y 443 están abiertos
3. Esperar hasta 5 minutos para la generación inicial de Let's Encrypt

---

## Referencias

- [Coolify Documentation](https://coolify.io/docs)
- [Laravel Deployment](https://laravel.com/docs/10.x/deployment)
- [Plan General](../plan/plan.md)

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-23
