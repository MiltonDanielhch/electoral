# Middleware del Sistema

## Middleware Personalizados

El sistema tiene dos middlewares personalizados principales que se aplican a todas las rutas administrativas.

---

## 1. Middleware Loggin

**Archivo:** `app/Http/Middleware/Loggin.php`

**Propósito:** Registrar todas las peticiones HTTP del sistema para auditoría.

### Funcionalidad

```php
public function handle(Request $request, Closure $next)
{
    // 1. Verificar modo desarrollo (comentado)
    // 2. Verificar estado de usuario (comentado)
    
    // 3. Evitar registrar logs de compass
    if (!str_contains(request()->url(), 'admin/compass')) {
        try {
            // Si usuario NO es admin, registrar datos completos
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
            // Si hay error, registrar solo datos básicos
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
- `method` - Método HTTP (GET, POST, PUT, DELETE)
- `input` - Parámetros (excepto password, _token, _method)

**Cuando hay error o no hay usuario:**
- `ip` - Dirección IP
- `url` - URL de la petición
- `method` - Método HTTP
- `input` - Parámetros (excepto password, _token, _method)

### Excepciones

No se registran logs cuando la URL contiene:
- `/admin/compass`

Esto evita bucles infinitos cuando se visualizan los logs en Compass.

### Configuración

**Canal de logs:** `requests`

Para configurar el canal, ver `config/logging.php`:

```php
'channels' => [
    'requests' => [
        'driver' => 'daily',
        'path' => storage_path('logs/requests.log'),
        'level' => 'info',
        'days' => 30,
    ],
]
```

---

## 2. Middleware System

**Archivo:** `app/Http/Middleware/System.php`

**Propósito:** Controlar acceso al sistema según mantenimiento, modo desarrollo y estado de licencia.

### Funcionalidad

```php
public function handle(Request $request, Closure $next)
{
    // 1. Rutas críticas siempre abiertas
    $open = [
        'admin/login',
        'admin/logout',
        'admin/password/*',
        'admin/voyager-assets*',
        '/',
    ];
    if ($request->is($open)) {
        return $next($request);
    }

    // 2. Modo mantenimiento
    if (setting('configuracion.maintenance') === '1') {
        if (auth()->check() && auth()->user()->hasRole(['admin', 'Administrador'])) {
            return $next($request);
        }
        return response()->view('errors.503', [], 503);
    }

    // 3. Modo desarrollo
    if (Auth::user()) {
        if (setting('system.development') && !auth()->user()->hasRole('admin')) {
           return response()->view('errors.503', [], 503);
        }
    }

    // 4. Lógica de licencia
    $controller = new SolucionDigitalController();
    $data = $controller->settings_code();

    if ($data) {
        $payment = new Controller();
        if ($payment->payment_alert() === 'finalizado') {
            $blockedMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
            $allowedRoutes  = ['admin/login', 'admin/logout', 'admin/settings'];

            if (
                in_array($request->method(), $blockedMethods) &&
                !in_array($request->path(), $allowedRoutes)
            ) {
                return redirect()->back()
                    ->withInput()
                    ->with([
                        'message' => 'Para continuar con el servicio sin interrupciones, contacte al administrador.',
                        'alert-type' => 'error'
                    ]);
            }
        }
    }

    // 5. Si todo está bien, continuar
    return $next($request);
}
```

### 1. Rutas Siempre Abiertas

Las siguientes rutas pasan sin restricciones:
- `/admin/login`
- `/admin/logout`
- `/admin/password/*`
- `/admin/voyager-assets*`
- `/`

### 2. Modo Mantenimiento

**Setting:** `configuracion.maintenance`

**Lógica:**
- Si el setting es `'1'`, el sistema está en mantenimiento
- Solo usuarios con roles `admin` o `Administrador` pueden acceder
- Otros usuarios reciben error 503 (Service Unavailable)

**Activar mantenimiento:**
```php
// Desde la BD o panel de Voyager
setting(['configuracion.maintenance' => '1']);
```

### 3. Modo Desarrollo

**Setting:** `system.development`

**Lógica:**
- Si el setting está activo, solo admins pueden acceder
- Otros usuarios reciben error 503

**Activar desarrollo:**
```php
// Desde la BD o panel de Voyager
setting(['system.development' => true]);
```

### 4. Verificación de Licencia

**Integración:** `SolucionDigitalController` → `Controller@payment_alert()`

**Lógica:**
1. Obtiene datos de licencia de BD externa
2. Verifica estado de pago
3. Si la licencia está **finalizada**:
   - Bloquea métodos: `POST`, `PUT`, `PATCH`, `DELETE`
   - Permite rutas: `login`, `logout`, `settings`
   - Redirige con mensaje de error
4. Si la licencia está **vigente**: Permite acceso normal

**Mensaje de error:**
```
"Para continuar con el servicio sin interrupciones, contacte al administrador."
```

### Estados de Licencia

Ver `02-controladores.md` → `Controller@payment_alert()`:
- `'finalizado'` - Licencia vencida
- `0-3` - Días restantes (si <= 3)
- `'vigente'` - Sistema activo
- `null` - Sin configuración o demo

---

## Registro de Middlewares

Los middlewares se registran en `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        \App\Http\Middleware\EncryptCookies::class,
        \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
        \Illuminate\Session\Middleware\StartSession::class,
        // ... otros middlewares
    ],
];

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'loggin' => \App\Http\Middleware\Loggin::class,
    'system' => \App\Http\Middleware\System::class,
    // ... otros middlewares
];
```

---

## Orden de Ejecución

Para una petición típica:

1. **Auth** (`\App\Http\Middleware\Authenticate::class`)
   - Verifica si usuario está autenticado
   - Redirige a login si no lo está

2. **Loggin** (`\App\Http\Middleware\Loggin::class`)
   - Registra la petición en logs
   - No modifica la petición

3. **System** (`\App\Http\Middleware\System::class`)
   - Verifica mantenimiento
   - Verifica modo desarrollo
   - Verifica licencia
   - Puede bloquear la petición

4. **Controlador**
   - Ejecuta la lógica del controlador

---

## Middlewares de Laravel (No Modificados)

El sistema también usa los middlewares nativos de Laravel:

- `EncryptCookies` - Encripta cookies
- `AddQueuedCookiesToResponse` - Añade cookies
- `StartSession` - Inicia sesión
- `Authenticate` - Verifica autenticación
- `VerifyCsrfToken` - Verifica tokens CSRF
- `TrimStrings` - Elimina espacios en blanco
- `TrustProxies` - Confía en proxies
- `PreventRequestsDuringMaintenance` - Bloquea peticiones en mantenimiento nativo

---

## Casos de Uso

### 1. Activar Mantenimiento

```php
// Desde la BD o panel de Voyager
setting(['configuracion.maintenance' => '1']);
```

Resultado:
- Solo admins pueden acceder
- Otros usuarios ven error 503

### 2. Activar Modo Desarrollo

```php
// Desde la BD o panel de Voyager
setting(['system.development' => true]);
```

Resultado:
- Solo admins pueden acceder
- Útil para pruebas sin afectar usuarios

### 3. Licencia Vencida

Si `Controller@payment_alert()` retorna `'finalizado'`:
- Se bloquean peticiones POST, PUT, PATCH, DELETE
- Solo GET permitido en rutas excepto login, logout, settings
- Se muestra mensaje de contacto con administrador

### 4. Ver Logs de Auditoría

```bash
tail -f storage/logs/requests-2026-01-18.log
```

Formato del log:
```
[2026-01-18 15:30:45] local.INFO: Petición HTTP al sistema. {"user_id":1,"role":"admin","name":"Admin","email":"admin@example.com","ip":"192.168.1.1","url":"http://localhost/admin/people","method":"GET","input":[]}
```

---

## Notas Importantes

1. **Orden importa:** Los middlewares se ejecutan en orden inverso al registrado.

2. **Excepciones:** El middleware Loggin tiene bloques try-catch para evitar que fallos rompan la aplicación.

3. **Logs de Compass:** Se excluyen logs de `/admin/compass` para evitar bucles infinitos.

4. **Soft Deletes:** Los middlewares no impiden soft deletes, solo bloquean peticiones cuando la licencia está vencida.

5. **Rutas Abiertas:** Login, logout y assets siempre pasan sin restricciones.

6. **Mantenimiento vs Desarrollo:** Son dos modos distintos con diferentes niveles de restricción.

7. **Licencia Externa:** La verificación de licencia se hace contra una BD externa vía `SolucionDigitalController`.

8. **Admin Role:** Los roles permitidos en mantenimiento son `'admin'` y `'Administrador'` (ambos).

9. **IP Logging:** Se registra la IP de cada petición para auditoría completa.

10. **Input Filtering:** En los logs se excluyen datos sensibles (password, tokens).
