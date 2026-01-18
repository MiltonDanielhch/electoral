# Sistema de Logging

## Canales de Logs

El sistema utiliza múltiples canales de logging para diferentes propósitos.

---

## Canales Configurados

### 1. Canal Default

**Archivo:** `config/logging.php`

```php
'default' => env('LOG_CHANNEL', 'stack'),
```

Usa el canal `stack` por defecto.

---

### 2. Canal Stack

```php
'stack' => [
    'driver' => 'stack',
    'channels' => ['single'],
    'ignore_exceptions' => false,
],
```

Combina múltiples canales (en este caso, solo `single`).

---

### 3. Canal Single

```php
'single' => [
    'driver' => 'single',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
],
```

Guarda todo en un solo archivo `laravel.log`.

---

### 4. Canal Daily

```php
'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => 14,  // Mantiene logs por 14 días
],
```

Crea un archivo por día: `laravel-YYYY-MM-DD.log`

---

### 5. Canal Requests (Personalizado)

**Canal principal para logs de peticiones HTTP del sistema.**

```php
'requests' => [
    'driver' => 'daily',
    'path' => storage_path('logs/requests.log'),
    'level' => 'info',
    'days' => 30,  // Mantiene logs por 30 días
]
```

**Usado por:** Middleware `Loggin`

**Archivos generados:**
- `storage/logs/requests-2026-01-18.log`
- `storage/logs/requests-2026-01-19.log`
- etc.

---

## Middleware de Logging

**Archivo:** `app/Http/Middleware/Loggin.php`

**Propósito:** Registrar todas las peticiones HTTP al sistema para auditoría.

### Funcionamiento

```php
public function handle(Request $request, Closure $next)
{
    // Excluir compass para evitar bucles
    if (!str_contains(request()->url(), 'admin/compass') ) {
        try {
            if(!Auth::user()->hasRole('admin'))
            {
                $data = [
                    'user_id' => Auth::user()->id,
                    'role' => Auth::user()->role->name,
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'ip' => request()->ip(),
                    'url' => request()->url(),
                    'method' => request()->method(),
                    'input' => request()->except(['password', '_token', '_method']),
                ];
                Log::channel('requests')->info('Petición HTTP al sistema.', $data);
            }
        } catch (\Throwable $th) {
            $data = [
                'ip' => request()->ip(),
                'url' => request()->url(),
                'method' => request()->method(),
                'input' => request()->except(['password', '_token', '_method']),
            ];
            Log::channel('requests')->info('Petición HTTP al sistema.', $data);
        }
    }

    return $next($request);
}
```

### Datos Registrados

**Cuando usuario autenticado y NO es admin:**
- `user_id` - ID del usuario
- `role` - Nombre del rol
- `name` - Nombre del usuario
- `email` - Email del usuario
- `ip` - Dirección IP
- `url` - URL de la petición
- `method` - Método HTTP
- `input` - Parámetros (sin password ni tokens)

**Cuando hay error o no hay usuario:**
- `ip` - Dirección IP
- `url` - URL de la petición
- `method` - Método HTTP
- `input` - Parámetros (sin password ni tokens)

### Excepciones

No se registran peticiones a `/admin/compass` para evitar bucles infinitos.

---

## Formato de Logs

### Formato Estándar de Laravel

```
[YYYY-MM-DD HH:MM:SS] local.INFO: Mensaje del log {"data":"json"}
```

### Ejemplo de Log de Petición

```
[2026-01-18 15:30:45] local.INFO: Petición HTTP al sistema. {
    "user_id": 1,
    "role": "admin",
    "name": "Administrador",
    "email": "admin@example.com",
    "ip": "192.168.1.100",
    "url": "http://localhost/admin/people",
    "method": "GET",
    "input": {
        "search": "Juan",
        "paginate": "10"
    }
}
```

### Ejemplo de Log de Error

```
[2026-01-18 15:35:22] local.ERROR: Error al guardar la imagen: The image file is invalid. {
    "file": "avatar.jpg",
    "folder": "people",
    "trace": "#0 ..."
}
```

---

## Ver Logs

### Desde Terminal

#### Ver último log
```bash
tail -f storage/logs/laravel.log
```

#### Ver log de peticiones
```bash
tail -f storage/logs/requests-2026-01-18.log
```

#### Buscar en logs
```bash
grep "Petición HTTP" storage/logs/requests-2026-01-18.log
```

#### Ver logs de errores
```bash
grep "ERROR" storage/logs/laravel.log
```

### Desde Panel de Voyager

**Voyager Compass:** `/admin/compass`

Permite visualizar logs del sistema:
- Seleccionar canal (`requests`, `daily`, etc.)
- Filtrar por nivel (INFO, WARNING, ERROR, etc.)
- Buscar por texto
- Ver detalles de cada entrada

**Nota:** Las peticiones a `/admin/compass` no se registran en logs para evitar bucles.

---

## Niveles de Log

Laravel usa los siguientes niveles de severidad:

| Nivel | Descripción | Uso en Sistema |
|-------|-------------|----------------|
| `DEBUG` | Información detallada de depuración | Debugging |
| `INFO` | Información general | Peticiones HTTP (middleware Loggin) |
| `NOTICE` | Eventos normales pero significativos | - |
| `WARNING` | Advertencias | - |
| `ERROR` | Errores de runtime | Errores de almacenamiento de imágenes |
| `CRITICAL` | Errores críticos | - |
| `ALERT` | Acción inmediata requerida | - |
| `EMERGENCY` | Sistema no usable | - |

---

## Configuración de Logs

### Cambiar nivel de log

En `.env`:
```bash
LOG_LEVEL=info
```

Valores posibles: `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency`

### Cambiar canal por defecto

En `.env`:
```bash
LOG_CHANNEL=daily
```

### Configurar días de retención

En `config/logging.php`:
```php
'requests' => [
    'driver' => 'daily',
    'days' => 90,  // Mantener por 90 días
]
```

---

## Limpiar Logs

### Limpiar caché de logs
```bash
php artisan log:clear
```

### Borrar archivos de logs manualmente
```bash
rm storage/logs/*.log
```

### Borrar logs antiguos
```bash
find storage/logs -name "*.log" -mtime +30 -delete
```

---

## Logs en StorageController

**Archivo:** `app/Http/Controllers/StorageController.php`

### Log de Errores de Imagen

```php
} catch (\Throwable $th) {
    \Log::error('Error al guardar la imagen: ' . $th->getMessage(), [
        'file' => $file ? $file->getClientOriginalName() : 'null',
        'folder' => $folder,
        'trace' => $th->getTraceAsString()
    ]);
    return null;
}
```

**Nivel:** `ERROR`
**Datos registrados:**
- Mensaje de error
- Nombre del archivo
- Carpeta de destino
- Stack trace completo

---

## Logs en StorageController (comentado)

Hay código comentado para logs más detallados:

```php
// Comentado en línea 21
\Log::info('Almacenando imagen', [
    'filename' => $filename,
    'path' => $path,
    'size' => $file->getSize()
]);
```

---

## Auditoría Dual

El sistema tiene dos niveles de auditoría:

### 1. Logs de Peticiones (Middleware Loggin)
- Registra TODAS las peticiones HTTP
- Nivel de aplicación (requests)
- Almacenados en: `storage/logs/requests-YYYY-MM-DD.log`

### 2. Auditoría de Registros (Trait RegistersUserEvents)
- Registra QUIÉN creó/eliminó registros
- Nivel de base de datos (modelos)
- Almacenados en: Tablas `people`, `users`

**Ejemplo:**
```
Log: "Usuario Admin (ID:1) hizo POST a /admin/people"
Model: Person.registerUser_id = 1, Person.registerRole = 'admin'
```

Ambos proporcionan una auditoría completa del sistema.

---

## Compass de Voyager

**URL:** `/admin/compass`

**Funcionalidades:**
- Visualizar logs en tiempo real
- Filtrar por canal
- Filtrar por nivel de log
- Buscar por texto
- Ver detalles de cada entrada
- Descargar logs

**Canales disponibles:**
- `single` - Log general
- `daily` - Logs diarios
- `requests` - Logs de peticiones

---

## Notas Importantes

1. **Canal requests:** Este es el canal principal para auditoría de peticiones HTTP.

2. **Exclusión de Compass:** Las peticiones a `/admin/compass` no se registran para evitar bucles infinitos.

3. **Sensibilidad:** Los passwords y tokens (_token, _method) se excluyen de los logs por seguridad.

4. **Logs vs Traits:** Los logs registran peticiones, los traits registran datos de registro (auditoría de BD).

5. **Rotación diaria:** El canal `requests` crea un archivo por día para mejor organización.

6. **Retención:** Los logs de peticiones se mantienen por 30 días por defecto.

7. **Logs de errores:** Los errores de StorageController se registran con nivel `ERROR`.

8. **No log de admins:** Las peticiones de usuarios con rol 'admin' no se registran (solo otros roles).

9. **Try-Catch:** El middleware Loggin tiene try-catch para evitar que fallos rompan la aplicación.

10. **Visualización:** Se puede visualizar logs desde terminal (`tail -f`) o desde Voyager Compass.
