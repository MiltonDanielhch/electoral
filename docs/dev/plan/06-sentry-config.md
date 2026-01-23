# Configuración de Sentry para Monitoreo de Errores

**Fecha:** 2026-01-23  
**Estado:** Fase 5 - Despliegue, Operaciones y Monitoreo

## Resumen

Sentry es una plataforma de monitoreo de errores y performance que permite detectar, diagnosticar y resolver problemas en la aplicación en tiempo real.

---

## 1. Instalación

### 1.1 Requisitos previos

- Cuenta en Sentry (https://sentry.io)
- Proyecto creado en Sentry con Laravel seleccionado
- DSN (Data Source Name) del proyecto

### 1.2 Instalar paquete via Composer

```bash
composer require sentry/sentry-laravel
```

### 1.3 Publicar configuración

```bash
php artisan vendor:publish --provider="Sentry\Laravel\ServiceProvider"
```

Esto creará el archivo `config/sentry.php`.

### 1.4 Instalar integración

```bash
php artisan sentry:install
```

Este comando:
- Solicita el DSN de Sentry
- Actualiza el archivo `.env`
- Configura el reporte automático de excepciones

---

## 2. Configuración

### 2.1 Variables de Entorno

Agregar al `.env`:

```bash
# Sentry DSN (obtenido de tu proyecto en Sentry.io)
SENTRY_LARAVEL_DSN=https://xxxxx@o0.ingest.sentry.io/xxxxx

# Sampling para Performance Monitoring
SENTRY_TRACES_SAMPLE_RATE=0.1

# Sampling para Profiling
SENTRY_PROFILES_SAMPLE_RATE=0.1

# Environments
SENTRY_ENVIRONMENT=production

# Release tracking (opcional)
SENTRY_RELEASE=v1.0.0
```

**Valores recomendados de sampling:**

| Tipo | Ambiente | Valor |
|------|----------|-------|
| Traces | Desarrollo | 1.0 (100%) |
| Traces | Staging | 0.5 (50%) |
| Traces | Producción | 0.1 (10%) |
| Profiles | Desarrollo | 1.0 (100%) |
| Profiles | Staging | 0.5 (50%) |
| Profiles | Producción | 0.1 (10%) |

### 2.2 Configuración en Coolify

Agregar las siguientes variables de entorno en Coolify:

```bash
SENTRY_LARAVEL_DSN=https://xxxxx@o0.ingest.sentry.io/xxxxx
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1
SENTRY_ENVIRONMENT=production
```

---

## 3. Integración con Laravel

### 3.1 Configurar Exception Handler

Editar `app/Exceptions/Handler.php`:

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Sentry\Laravel\Integration;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            \Sentry\Laravel\Integration::captureUnhandledException($e);
        });
    }

    public function report(Throwable $e): void
    {
        if ($this->shouldReport($e) && app()->bound('sentry')) {
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }
}
```

### 3.2 Configurar Boot Service Providers

Editar `config/sentry.php` o usar el archivo publicado:

```php
<?php

return [
    'dsn' => env('SENTRY_LARAVEL_DSN'),
    'traces_sample_rate' => (float) env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
    'profiles_sample_rate' => (float) env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),
    'environment' => env('SENTRY_ENVIRONMENT', app()->environment()),
    'release' => env('SENTRY_RELEASE', trim(exec('git log --pretty="%h" -n1 HEAD'))),
    'before_send' => function (\Sentry\Event $event) {
        if (app()->environment('local', 'testing')) {
            return null;
        }
        return $event;
    },
];
```

---

## 4. Uso Avanzado

### 4.1 Enviar Mensajes Personalizados

```php
\Sentry\captureMessage('Usuario inició sesión', \Sentry\Severity::info());
```

### 4.2 Agregar Contexto de Usuario

```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    $scope->setUser([
        'id' => auth()->id(),
        'email' => auth()->user()->email,
        'role' => auth()->user()?->role->name,
    ]);
});
```

### 4.3 Agregar Tags

```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    $scope->setTag('module', 'escrutinio');
    $scope->setTag('action', 'carga_acta');
});
```

### 4.4 Agregar Extra Data

```php
\Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
    $scope->setExtra('mesa_id', $mesa->id);
    $scope->setExtra('recinto_id', $mesa->recinto->id);
});
```

### 4.5 Capturar Excepciones Específicas

```php
try {
    $acta = ActaEscrutinio::findOrFail($id);
} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    \Sentry\captureException($e);
    return response()->json(['error' => 'Acta no encontrada'], 404);
}
```

### 4.6 Performance Monitoring

Para rastrear el rendimiento de transacciones:

```php
$transaction = \Sentry\startTransaction([
    'name' => 'proceso_escrutinio',
    'op' => 'escrutinio',
]);

try {
    $span1 = $transaction->startChild([
        'op' => 'database',
        'description' => 'guardar_acta',
    ]);

    $acta = ActaEscrutinio::create($data);

    $span1->finish();

    $span2 = $transaction->startChild([
        'op' => 'cache',
        'description' => 'actualizar_resultados',
    ]);

    Cache::forget('resultados');

    $span2->finish();

} finally {
    $transaction->finish();
}
```

---

## 5. Configuración de Alertas

### 5.1 Alertas en Sentry

1. En Sentry, ir a **Settings > Alerts**
2. Crear nueva alerta:
   - **Error Rate:** Aumentar por X% en Y minutos
   - **Issue Created:** Se crea un nuevo issue
   - **Performance:** Transacciones lentas (>3s)

### 5.2 Notificaciones

Configurar notificaciones a:
- Slack
- Email
- SMS
- PagerDuty (opcional)

---

## 6. Integración con API de Escrutinio

Añadir monitoreo específico al controlador de actas:

```php
use Sentry\State\Scope;

class ActaController extends Controller
{
    public function store(StoreActaRequest $request)
    {
        $transaction = \Sentry\startTransaction([
            'name' => 'acta_upload',
            'op' => 'escrutinio.store',
        ]);

        try {
            \Sentry\configureScope(function (Scope $scope) use ($request) {
                $scope->setTag('mesa_codigo', $request->mesa_codigo);
                $scope->setUser([
                    'id' => $request->user()->id,
                ]);
            });

            DB::beginTransaction();

            $acta = ActaEscrutinio::create([
                'mesa_codigo' => $request->mesa_codigo,
                'recinto_id' => $request->recinto_id,
                'cargo_id' => $request->cargo_id,
                'imagen_url' => $request->imagen_url,
                'usuario_id' => $request->user()->id,
            ]);

            foreach ($request->votos as $voto) {
                VotoXPartido::create([
                    'acta_escrutinio_id' => $acta->id,
                    'organizacion_politica_id' => $voto['organizacion_id'],
                    'votos' => $voto['votos'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'acta_id' => $acta->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            throw $e;
        } finally {
            $transaction->finish();
        }
    }
}
```

---

## 7. Filtros y Exclusiones

### 7.1 Excluir Errores No Críticos

En `config/sentry.php`:

```php
'ignore_exceptions' => [
    \Illuminate\Auth\AuthenticationException::class,
    \Illuminate\Validation\ValidationException::class,
    \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException::class,
],
```

### 7.2 Filtros de Request

Excluir endpoints específicos de monitoreo:

```php
'trace_propagation_targets' => [
    'api/v1/*',
],
```

---

## 8. Release Tracking

Automatizar tracking de releases con GitHub Actions:

En `.github/workflows/ci-cd.yml`:

```yaml
- name: Create Sentry Release
  run: |
    curl -sL https://sentry.io/api/0/organizations/${SENTRY_ORG}/releases/ \
      -H "Authorization: Bearer ${{ secrets.SENTRY_AUTH_TOKEN }}" \
      -d version=${{ github.sha }} \
      -d projects[]=electoral

    npx @sentry/wizard@latest -i github -p @sentry/node -r latest
  env:
    SENTRY_AUTH_TOKEN: ${{ secrets.SENTRY_AUTH_TOKEN }}
    SENTRY_ORG: ${{ secrets.SENTRY_ORG }}
```

---

## 9. Dashboard en Sentry

### 9.1 Métricas Clave

Configurar dashboard con:
- **Error Rate:** Tasa de errores por endpoint
- **Top Errors:** Errores más frecuentes
- **Performance:** Transacciones más lentas
- **User Impact:** Usuarios afectados por errores

### 9.2 Custom Dashboards

Crear dashboard específico para el Sistema Electoral:
- Actas por minuto
- Tasa de éxito de carga de actas
- Errores por tipo (validación, base de datos, autenticación)
- Tiempos de respuesta por endpoint

---

## 10. Pruebas Locales

### 10.1 Verificar Integración

```bash
php artisan sentry:test
```

Este comando envía un evento de prueba a Sentry.

### 10.2 Simular Error

```php
Route::get('/sentry-test', function () {
    throw new \Exception('Test error for Sentry');
});
```

---

## 11. Checklist de Implementación

- [ ] Paquete `sentry/sentry-laravel` instalado
- [ ] Configuración publicada (`config/sentry.php`)
- [ ] Variables de entorno configuradas (`.env` y Coolify)
- [ ] Exception Handler actualizado
- [ ] Excepciones no críticas excluidas
- [ ] Sampling rates configuradas apropiadamente
- [ ] Alertas configuradas (Slack/Email)
- [ ] Dashboard personalizado creado
- [ ] Test de integración ejecutado (`php artisan sentry:test`)
- [ ] Release tracking configurado (opcional)

---

## Referencias

- [Sentry Laravel Docs](https://docs.sentry.io/platforms/php/guides/laravel/)
- [Sentry Performance Monitoring](https://docs.sentry.io/platforms/php/guides/laravel/performance/)
- [Guía de Despliegue en Coolify](05-coolify-deployment.md)

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Media  
**Fecha de Creación:** 2026-01-23
