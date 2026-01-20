# Preparación para Fase 4 - Optimización, Seguridad y Pruebas de Carga

## Fecha: 2026-01-20

## Resumen

Este documento detalla la preparación e implementación de la Fase 4 del plan de desarrollo, enfocándose en optimización de rendimiento, seguridad avanzada y pruebas de carga antes del despliegue a producción.

---

## Objetivos de la Fase 4

1. **Rendimiento:** Garantizar que el sistema soporte carga de elección
2. **Seguridad:** Validar contra amenazas y vulnerabilidades
3. **Escalabilidad:** Preparar infraestructura para picos de tráfico

---

## Parte 1: Pruebas de Carga

### Herramientas Consideradas

#### Opción 1: k6 (Recomendada ✅)

**Ventajas:**
- Escrito en Go (alto rendimiento)
- Scripting en JavaScript (fácil de aprender)
- Integración nativa con CI/CD
- Reportes detallados en tiempo real

**Instalación:**

```bash
# Windows (Chocolatey)
choco install k6

# Linux/Mac
brew install k6

# Verificar instalación
k6 version
```

#### Opción 2: JMeter (Alternativa)

**Ventajas:**
- GUI completa para configurar pruebas
- Soporta múltiples protocolos
- Comunidad grande

**Desventajas:**
- Curva de aprendizaje alta
- Consumo de recursos significativo

---

### Escenarios de Prueba de Carga

#### Escenario 1: API de Escrutinio - Alta Concurrencia

**Archivo:** `tests/load/k6/api-escrutinio.js`

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const errorRate = new Rate('errors');

export const options = {
    stages: [
        { duration: '2m', target: 10 },   // Ramp-up a 10 usuarios
        { duration: '5m', target: 10 },   // Mantener 10 usuarios
        { duration: '2m', target: 50 },   // Ramp-up a 50 usuarios
        { duration: '5m', target: 50 },   // Mantener 50 usuarios
        { duration: '2m', target: 100 },  // Ramp-up a 100 usuarios
        { duration: '10m', target: 100 }, // Pico sostenido
        { duration: '5m', target: 0 },    // Ramp-down
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // 95% de peticiones < 2s
        http_req_failed: ['rate<0.05'],    // Menos de 5% de fallos
        errors: ['rate<0.05'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const API_TOKEN = __ENV.API_TOKEN || 'your-test-token';

export default function () {
    // Prueba 1: Obtener datos de mesa
    let mesaResponse = http.get(
        `${BASE_URL}/api/v1/mesa/BEN001001001`,
        {
            headers: {
                'Authorization': `Bearer ${API_TOKEN}`,
                'Accept': 'application/json',
            },
        }
    );
    
    check(mesaResponse, {
        'mesa status 200': (r) => r.status === 200,
        'mesa tiene datos': (r) => JSON.parse(r.body).codigo !== undefined,
    }) || errorRate.add(1);
    
    sleep(Math.random() * 2 + 1); // 1-3 segundos de pausa
    
    // Prueba 2: Enviar acta (simulada)
    let actaData = {
        mesa_codigo: 'BEN001001001',
        imagen: 'data:image/jpeg;base64,/9j/4AAQSkZJRg...', // Base64 de imagen pequeña
        votos: {
            'MAS': 45,
            'CC': 32,
            'FPV': 18,
            'VOTOS_NULOS': 5,
            'VOTOS_BLANCOS': 3,
        },
    };
    
    let actaResponse = http.post(
        `${BASE_URL}/api/v1/acta`,
        JSON.stringify(actaData),
        {
            headers: {
                'Authorization': `Bearer ${API_TOKEN}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        }
    );
    
    check(actaResponse, {
        'acta status 200 o 201': (r) => r.status === 200 || r.status === 201,
        'acta procesada': (r) => JSON.parse(r.body).success === true,
    }) || errorRate.add(1);
    
    sleep(Math.random() * 3 + 2); // 2-5 segundos de pausa
}
```

**Ejecución:**

```bash
# Prueba básica
k6 run tests/load/k6/api-escrutinio.js

# Con variables de entorno
k6 run --env BASE_URL=https://staging.example.com --env API_TOKEN=secret tests/load/k6/api-escrutinio.js

# Guardar resultados
k6 run --out json=results.json tests/load/k6/api-escrutinio.js

# Generar reporte HTML
k6 run --out json=results.json tests/load/k6/api-escrutinio.js
```

---

#### Escenario 2: Panel de Administración - Uso Concurrente

**Archivo:** `tests/load/k6/admin-panel.js`

```javascript
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const errorRate = new Rate('errors');

export const options = {
    stages: [
        { duration: '1m', target: 5 },    // 5 administradores
        { duration: '3m', target: 10 },   // 10 administradores
        { duration: '3m', target: 20 },   // 20 administradores
        { duration: '5m', target: 20 },   // Mantener
        { duration: '2m', target: 0 },    // Ramp-down
    ],
    thresholds: {
        http_req_duration: ['p(95)<1500'], // 95% < 1.5s
        http_req_failed: ['rate<0.02'],    // Menos de 2% fallos
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const ADMIN_EMAIL = __ENV.ADMIN_EMAIL || 'admin@example.com';
const ADMIN_PASSWORD = __ENV.ADMIN_PASSWORD || 'password';

let authToken = '';

export function setup() {
    // Login inicial
    let loginRes = http.post(`${BASE_URL}/login`, {
        email: ADMIN_EMAIL,
        password: ADMIN_PASSWORD,
    });
    
    if (loginRes.status !== 200) {
        throw new Error('Login fallido');
    }
    
    return {
        token: loginRes.json('token') || '',
        cookies: loginRes.cookies,
    };
}

export default function (data) {
    const headers = {
        'Accept': 'text/html,application/xhtml+xml',
    };
    
    // Prueba 1: Browse Personas
    let browseRes = http.get(`${BASE_URL}/admin/people`, {
        headers,
    });
    
    check(browseRes, {
        'browse status 200': (r) => r.status === 200,
        'browse contiene tabla': (r) => r.body.includes('table'),
    }) || errorRate.add(1);
    
    sleep(1);
    
    // Prueba 2: Búsqueda AJAX
    let searchRes = http.get(`${BASE_URL}/admin/people/list?search=Juan`, {
        headers,
    });
    
    check(searchRes, {
        'search status 200': (r) => r.status === 200,
        'search es JSON': (r) => {
            try {
                JSON.parse(r.body);
                return true;
            } catch {
                return false;
            }
        },
    }) || errorRate.add(1);
    
    sleep(Math.random() * 2 + 1);
}
```

---

#### Escenario 3: Carga Masiva de Actas

**Simula el día de elección:**

```javascript
// tests/load/k6/election-day.js
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Trend } from 'k6/metrics';

const errorRate = new Rate('errors');
const responseTime = new Trend('response_time');

export const options = {
    scenarios: {
        constant_load: {
            executor: 'constant-arrival-rate',
            rate: 50, // 50 peticiones por segundo
            timeUnit: '1s',
            duration: '10m',
            preAllocatedVUs: 100,
            maxVUs: 200,
        },
    },
    thresholds: {
        http_req_duration: ['p(95)<3000', 'p(99)<5000'], // 95% < 3s, 99% < 5s
        http_req_failed: ['rate<0.01'], // < 1% fallos
        errors: ['rate<0.01'],
    },
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';
const API_TOKEN = __ENV.API_TOKEN || 'your-token';

export default function () {
    const start = new Date();
    
    // Simular envío de acta
    let actaResponse = http.post(
        `${BASE_URL}/api/v1/acta`,
        JSON.stringify(generateRandomActa()),
        {
            headers: {
                'Authorization': `Bearer ${API_TOKEN}`,
                'Content-Type': 'application/json',
            },
        }
    );
    
    const end = new Date();
    responseTime.add(end - start);
    
    check(actaResponse, {
        'acta exitosa': (r) => r.status === 200 || r.status === 201,
    }) || errorRate.add(1);
}

function generateRandomActa() {
    const mesas = ['BEN001001001', 'BEN001001002', 'BEN001002001', 'BEN001002002'];
    const partidos = ['MAS', 'CC', 'FPV', 'VOTOS_NULOS', 'VOTOS_BLANCOS'];
    
    let votos = {};
    partidos.forEach(p => {
        votos[p] = Math.floor(Math.random() * 50) + 10;
    });
    
    return {
        mesa_codigo: mesas[Math.floor(Math.random() * mesas.length)],
        imagen: 'data:image/jpeg;base64,/9j/4AAQSkZJRg...', // Imagen fija para prueba
        votos: votos,
    };
}
```

---

### Métricas a Monitorear

#### Métricas Clave de Rendimiento (KPIs)

| Métrica | Objetivo | Actual | Estado |
|---------|----------|--------|--------|
| API Response Time (P95) | < 2s | ? | ⏳ |
| API Response Time (P99) | < 5s | ? | ⏳ |
| Admin Panel Response (P95) | < 1.5s | ? | ⏳ |
| Error Rate | < 1% | ? | ⏳ |
| Throughput (req/s) | > 100 | ? | ⏳ |
| Concurrent Users | > 100 | ? | ⏳ |

#### Métricas de Base de Datos

```sql
-- Monitoreo de conexiones activas
SHOW STATUS LIKE 'Threads_connected';

-- Monitoreo de consultas lentas
SHOW VARIABLES LIKE 'long_query_time';
SHOW STATUS LIKE 'Slow_queries';

-- Monitoreo de caché de consultas
SHOW STATUS LIKE 'Qcache%';
```

---

## Parte 2: Optimización de Consultas

### Identificación de Problemas N+1

#### Herramienta: Laravel Telescope

```bash
# Instalar Telescope
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

#### Habilitar Query Logging

En `app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }
    
    public function boot(): void
    {
        if (config('app.debug')) {
            DB::listen(function (QueryExecuted $query) {
                if ($query->time > 100) { // > 100ms
                    Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                    ]);
                }
            });
        }
    }
}
```

---

### Optimización de Consultas - Ejemplos

#### Problema: N+1 en listado de personas con usuario

**Antes (Problema):**

```php
// PersonController.php - list()
public function list(Request $request)
{
    $query = Person::query();
    
    if ($request->search) {
        $query->where('nombres', 'like', "%{$request->search}%")
              ->orWhere('apellidos', 'like', "%{$request->search}%");
    }
    
    $people = $query->paginate($request->per_page ?? 10);
    
    // N+1 problem: Cada iteración hace una consulta adicional
    return response()->json($people);
}
```

**Después (Optimizado):**

```php
public function list(Request $request)
{
    $query = Person::query();
    
    if ($request->search) {
        $query->where('nombres', 'like', "%{$request->search}%")
              ->orWhere('apellidos', 'like', "%{$request->search}%");
    }
    
    // Eager loading: Cargar usuario en una sola consulta
    $query->with(['user' => function ($query) {
        $query->select('id', 'person_id', 'email', 'active');
    }]);
    
    // Seleccionar solo campos necesarios
    $people = $query->select([
        'id', 'nombres', 'apellidos', 'cedula', 'email', 'telefono'
    ])->paginate($request->per_page ?? 10);
    
    return response()->json($people);
}
```

---

#### Problema: Consulta pesada en resultados de elección

**Antes (Problema):**

```php
// ResultsController.php - index()
public function index()
{
    // Consulta lenta: múltiples joins y cálculos
    $results = DB::table('actas_escrutinio as ae')
        ->join('mesas as m', 'ae.mesa_id', '=', 'm.id')
        ->join('geografias as g', 'm.geografia_id', '=', 'g.id')
        ->join('cargos as c', 'ae.cargo_id', '=', 'c.id')
        ->leftJoin('votos_x_partido as vxp', function ($join) {
            $join->on('ae.id', '=', 'vxp.acta_id');
        })
        ->select(
            'g.nombre as geografia',
            'c.nombre as cargo',
            DB::raw('SUM(vxp.votos) as total_votos')
        )
        ->groupBy('g.id', 'c.id')
        ->get();
    
    return view('results.index', compact('results'));
}
```

**Después (Optimizado con índices y cache):**

```php
public function index()
{
    $cacheKey = 'election_results_' . now()->format('Y-m-d-H');
    
    $results = Cache::remember($cacheKey, 300, function () { // 5 minutos
        // Usar tabla de resumen precalculada
        return ResumenVoto::query()
            ->with(['geografia', 'cargo'])
            ->select(['geografia_id', 'cargo_id', 'total_votos', 'porcentaje'])
            ->orderBy('geografia_id')
            ->orderBy('cargo_id')
            ->get();
    });
    
    return view('results.index', compact('results'));
}
```

**Migración para agregar índices:**

```php
// database/migrations/2026_01_20_add_indexes_to_results.php
public function up()
{
    Schema::table('actas_escrutinio', function (Blueprint $table) {
        $table->index(['mesa_id', 'cargo_id'], 'idx_mesa_cargo');
        $table->index('procesado_at', 'idx_procesado');
    });
    
    Schema::table('votos_x_partido', function (Blueprint $table) {
        $table->index(['acta_id', 'organizacion_id'], 'idx_acta_org');
    });
    
    Schema::table('resumen_votos', function (Blueprint $table) {
        $table->index(['geografia_id', 'cargo_id'], 'idx_geo_cargo');
    });
}

public function down()
{
    Schema::table('actas_escrutinio', function (Blueprint $table) {
        $table->dropIndex('idx_mesa_cargo');
        $table->dropIndex('idx_procesado');
    });
    
    Schema::table('votos_x_partido', function (Blueprint $table) {
        $table->dropIndex('idx_acta_org');
    });
    
    Schema::table('resumen_votos', function (Blueprint $table) {
        $table->dropIndex('idx_geo_cargo');
    });
}
```

---

### Optimización con Caché

#### Configuración de Redis

**Instalación:**

```bash
# composer
composer require predis/predis

# .env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Estrategias de Caché:**

1. **Caché de Catálogos (Larga duración)**

```php
// CatalogoController.php
public function index()
{
    return Cache::rememberForever('catalogos:all', function () {
        return [
            'organizaciones' => OrganizacionPolitica::all(),
            'cargos' => Cargo::all(),
            'geografias' => Geografia::where('nivel', 'municipio')->get(),
        ];
    });
}
```

2. **Caché de Resultados (Corta duración)**

```php
// ResultadosController.php
public function show($cargoId)
{
    $results = Cache::remember("results:cargo:{$cargoId}", 60, function () use ($cargoId) {
        return $this->calculateResults($cargoId);
    });
    
    return response()->json($results);
}
```

3. **Invalidación de Caché**

```php
// ActaController.php
public function store(StoreActaRequest $request)
{
    DB::transaction(function () use ($request) {
        // Procesar acta
        $acta = ActaEscrutinio::create($request->validated());
        
        // Invalidar caché de resultados
        Cache::forget("results:cargo:{$acta->cargo_id}");
        Cache::forget('election_results_' . now()->format('Y-m-d-H'));
    });
    
    return response()->json(['success' => true]);
}
```

---

## Parte 3: Auditoría de Seguridad

### Análisis Estático con Larastan

#### Instalación

```bash
composer require --dev larastan/larastan
```

#### Configuración

**Archivo:** `phpstan.neon`

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app
    level: 5
    ignoreErrors:
        - '#Unsafe usage of new static#'
```

#### Ejecución

```bash
# Análisis completo
./vendor/bin/phpstan analyse

# Generar reporte
./vendor/bin/phpstan analyse --error-format=table --report=summary

# Ver solo errores críticos
./vendor/bin/phpstan analyse --level=5
```

---

### Validación contra OWASP Top 10

#### Checklist de Seguridad

| Vulnerabilidad | Estado | Acción |
|----------------|--------|--------|
| 1. Broken Access Control | ⏳ Pendiente | Revisar Policies |
| 2. Cryptographic Failures | ⏳ Pendiente | Validar HTTPS |
| 3. Injection | ✅ OK | Usar Eloquent ORM |
| 4. Insecure Design | ⏳ Pendiente | Revisar arquitectura |
| 5. Security Misconfiguration | ⏳ Pendiente | Revisar config |
| 6. Vulnerable Components | ⏳ Pendiente | Audit dependencias |
| 7. Auth Failures | ⏳ Pendiente | Revisar Sanctum |
| 8. Data Integrity | ⏳ Pendiente | Validar checksum |
| 9. Logging Failures | ⏳ Pendiente | Revisar Loggin middleware |
| 10. SSRF | ✅ OK | No hay peticiones externas |

---

### Auditoría de Permisos Voyager

#### Revisión de Roles y Permisos

**Comando para listar roles:**

```bash
php artisan tinker
>>> \TCG\Voyager\Models\Role::all()->pluck('name')
```

**Comando para listar permisos:**

```bash
php artisan tinker
>>> \TCG\Voyager\Models\Permission::with('roles')->get()
```

**Validación:**

```php
// Tests/Feature/VoyagerPermissionsTest.php
public function test_admin_role_has_all_permissions()
{
    $admin = Role::where('name', 'admin')->first();
    
    $this->assertGreaterThan(
        50,
        $admin->permissions->count(),
        'Admin debe tener suficientes permisos'
    );
}

public function test_unauthorized_user_cannot_access_people()
{
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->get(route('voyager.people.browse'));
    
    $response->assertStatus(403);
}
```

---

## Cronograma de Implementación

### Semana 1: Pruebas de Carga
- [ ] Instalar y configurar k6
- [ ] Crear escenario API escrutinio
- [ ] Crear escenario panel admin
- [ ] Ejecutar pruebas y recopilar datos
- [ ] Identificar cuellos de botella

### Semana 2: Optimización
- [ ] Corregir problemas N+1 identificados
- [ ] Implementar índices de base de datos
- [ ] Configurar Redis para caché
- [ ] Implementar estrategias de caché
- [ ] Validar mejoras con k6

### Semana 3: Seguridad
- [ ] Configurar Larastan
- [ ] Ejecutar análisis estático
- [ ] Completar checklist OWASP
- [ ] Revisar permisos Voyager
- [ ] Auditar dependencias

### Semana 4: Integración y Documentación
- [ ] Ejecutar suite completa de pruebas
- [ ] Documentar resultados
- [ ] Crear guía de tuning
- [ ] Preparar métricas para monitoreo

---

## Referencias

- k6 Documentation: https://k6.io/docs/
- Laravel Telescope: https://laravel.com/docs/10.x/telescope
- Larastan: https://github.com/larastan/larastan
- OWASP Top 10: https://owasp.org/www-project-top-ten/
- Plan general: `docs/plan/plan.md`

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-20  
**Responsable:** Equipo de Desarrollo
