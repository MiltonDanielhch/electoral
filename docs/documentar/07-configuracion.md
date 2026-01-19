# Configuraciones del Sistema

## Archivos de Configuración

El sistema usa varios archivos de configuración de Laravel y Voyager.

---

## Configuración de Voyager

**Archivo:** `config/voyager.php`

### Secciones Principales

#### 1. User Config
```php
'user' => [
    'add_default_role_on_register' => true,  // Asignar rol al registrar
    'default_role' => 'user',                 // Rol por defecto
    'default_avatar' => 'users/default.png',   // Avatar por defecto
    'redirect' => '/admin',                   // Redirección tras login
]
```

#### 2. Controllers Config
```php
'controllers' => [
    'namespace' => 'TCG\\Voyager\\Http\\Controllers',
]
```
Namespace de controladores de Voyager.

#### 3. Models Config
```php
'models' => [
   // 'namespace' => 'App\\Models\\',
]
```
Namespace de modelos para BREAD (comentado, usa default de Laravel).

#### 4. Storage Config
```php
'storage' => [
    'disk' => 'public',  // Disco de almacenamiento
]
```

#### 5. Media Manager
```php
'hidden_files' => false,  // Mostrar archivos ocultos
```

#### 6. Database Config
```php
'database' => [
    'tables' => [
        'hidden' => [
            'migrations',
            'data_rows',
            'data_types',
            'menu_items',
            'password_resets',
            'permission_role',
            'personal_access_tokens',
            'settings',
        ],
    ],
    'autoload_migrations' => true,  // Cargar migraciones automáticamente
]
```
Tablas ocultas en Voyager y autoload de migraciones.

#### 7. Multilingual Config
```php
'multilingual' => [
    'enabled' => false,    // Multilenguaje desactivado
    'default' => 'en',
    'locales' => ['en'],  // Solo inglés
]
```

#### 8. Dashboard Config
```php
'dashboard' => [
    'widgets' => [],  // Widgets del dashboard
]
```

#### 9. Primary BREAD Display
```php
'primary_bread_display' => 'id',  // Campo principal en BREAD
```

---

## Configuración de Logging

**Archivo:** `config/logging.php`

### Canales de Logs

#### Default Channel
```php
'default' => env('LOG_CHANNEL', 'stack'),
```

#### Stack Channel
```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single'],
    'ignore_exceptions' => false,
],
```

#### Single Channel
```php
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
],
```

#### Daily Channel
```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,  // Mantener logs 14 días
],
```

#### Requests Channel (Personalizado)
El sistema usa un canal personalizado para logs de peticiones HTTP:

```php
'requests' => [
    'driver' => 'daily',
    'path' => storage_path('logs/requests.log'),
    'level' => 'info',
    'days' => 30,  // Mantener logs 30 días
]
```

**Uso:** El middleware `Loggin` registra en este canal:
```php
Log::channel('requests')->info('Petición HTTP al sistema.', $data);
```

---

## Configuración de Archivos

**Archivo:** `config/filesystems.php`

### Disco Público
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
]
```

**Enlace simbólico:**
```bash
php artisan storage:link
```

**Uso en sistema:**
- Imágenes de personas se almacenan en: `storage/app/public/people/{FY}/`
- URLs públicas: `http://domain.com/storage/people/{FY}/{imagen}.avif`

---

## Variables de Entorno (.env)

### Configuración Básica
```bash
APP_NAME="Panel Administrativo"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Base de Datos Principal
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=app
DB_USERNAME=root
DB_PASSWORD=
```

### Sistema de Archivos
```bash
FILESYSTEM_DISK=public
```

### Otros
```bash
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

---

## Settings de Voyager

Los settings de Voyager se almacenan en la tabla `settings` y se acceden con:

```php
setting('clave.de.setting');
```

### Settings del Sistema

#### Mantenimiento
```php
setting('configuracion.maintenance')
// '1' = activado, '0' = desactivado
```

#### Desarrollo
```php
setting('system.development')
// true = activado, false/null = desactivado
```

#### Código de Sistema (Licencia)
```php
setting('system.code-system')
// Código único del sistema
```

---

## Configuración de Intervención de Imágenes

**Archivo:** `config/image.php` (si existe, o usa configuración por defecto)

El sistema usa `intervention/image` para procesar imágenes:

```php
// Uso en StorageController
use Intervention\Image\ImageManagerStatic as Image;

$image = Image::make($file->getRealPath())->orientate();
$image->resize($width, null, function ($constraint) {
    $constraint->aspectRatio();
});
```

**Formatos soportados:**
- Input: jpeg, jpg, png, bmp, webp
- Output: avif (con calidad 80%)

---

## Configuración de Multipartes (Formularios)

**Archivo:** `php.ini` (necesario ajustar para subida de imágenes)

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
memory_limit = 256M
```

---

## Configuración de Timezone

**Archivo:** `config/app.php`

```php
'timezone' => 'America/La_Paz',
```

---

## Configuración de Locale

**Archivo:** `config/app.php`

```php
'locale' => 'es',
'fallback_locale' => 'en',
'available_locales' => ['es', 'en'],
```

---

## Configuración de CORS (si es necesario)

**Archivo:** `config/cors.php`

```php
'paths' => ['api/*', 'admin/ajax/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],
'allowed_headers' => ['*'],
```

---

## Comandos de Artisan para Configuración

### Limpiar caché de configuración
```bash
php artisan config:clear
```

### Recargar configuración
```bash
php artisan config:cache
```

### Limpiar toda la caché
```bash
php artisan optimize:clear
```

### Enlace simbólico de almacenamiento
```bash
php artisan storage:link
```

### Ver configuración actual
```bash
php artisan config:show
```

---

## Notas Importantes

1. **Settings vs Config:** Los settings de Voyager se almacenan en BD (`settings` table), mientras que las configs de Laravel están en archivos.

2. **Caché de Config:** En producción, se debe usar `php artisan config:cache` para mejor rendimiento.

3. **Logs Separados:** Los logs de peticiones HTTP se almacenan en un canal separado (`requests`) para auditoría.

4. **Almacenamiento Público:** Las imágenes se sirven a través del enlace simbólico `public/storage`.

5. **Imágenes AVIF:** El sistema convierte todas las imágenes a AVIF para optimización de tamaño.

6. **Multilenguaje:** El multilenguaje de Voyager está desactivado por defecto.

7. **Soft Deletes:** Configurado en nivel de modelo, no en config.

8. **Middleware de Sistema:** El middleware `System` usa settings para controlar mantenimiento y desarrollo.

9. **Logs de Compass:** Se excluyen logs de `/admin/compass` para evitar bucles infinitos.
