# Índice de Documentación de Desarrollo

## Fecha: 2026-01-20

## Descripción

Este índice organiza toda la documentación técnica y de desarrollo del Sistema Electoral, proporcionando acceso rápido a recursos por fase, tema y tipo de documento.

---

## Estructura General de Documentación

```
docs/
├── plan/                           # Planes y especificaciones principales
│   ├── plan.md                     # Plan de desarrollo evolutivo (Fases 0-6)
│   ├── migraciones.md              # Especificaciones de migraciones DB
│   ├── prompts2.md                 # Estándares CRUD
│   ├── prompt.md                   # Prompts de desarrollo
│   ├── bd.md                       # Esquema de base de datos
│   ├── diagrama-er.md              # Diagrama Entidad-Relación
│   └── optimizacion-fase1.md       # Optimización AJAX vs Livewire
│
├── documentar/                     # Documentación del sistema existente
│   ├── 00-README.md                # Introducción general
│   ├── 01-modelos.md               # Modelos de datos
│   ├── 02-controladores.md         # Controladores
│   ├── 03-rutas.md                 # Rutas
│   ├── 04-middleware.md            # Middleware
│   ├── 05-vistas.md                # Vistas
│   ├── 06-migraciones.md           # Migraciones
│   ├── 07-configuracion.md         # Configuración
│   ├── 08-traits.md                # Traits
│   ├── 09-bread.md                 # Sistema BREAD
│   ├── 10-logs.md                  # Logs
│   ├── 11-indice.md                # Índice original
│   ├── 12-diagramas.md             # Diagramas
│   ├── 13-docker.md                # Docker
│   ├── 14-analisis-bugs-mejoras.md # Análisis
│   ├── 15-plan-ejecucion.md        # Plan de ejecución
│   ├── 16-resumen-ejecutivo.md     # Resumen ejecutivo (v1.3.0)
│   ├── 17-historial-cambios.md     # Historial versiones
│   ├── comandos-git-2026-01-19.md  # Tutorial Git
│   └── registro-actividades-2026-01-20.md  # Registro diario
│
└── dev/                            # Documentación de desarrollo activo
    └── plan/                       # Planes de desarrollo detallados
        ├── 00-INDICE.md            # Este archivo
        ├── 01-estrategia-pruebas-fase1.md    # Estrategia de pruebas
        ├── 02-preparacion-fase4.md            # Optimización y seguridad
        ├── 03-preparacion-fase5.md            # Despliegue y DevOps
        └── 04-api-documentacion-openapi.md    # Documentación API
```

---

## Documentación por Fase

### Fase 0: Fundación y Consolidación ✅ COMPLETADA

**Estado:** Finalizado

**Documentación:**
- `docs/plan/plan.md` - Resumen de arquitectura base
- `docs/documentar/01-modelos.md` - Modelos Person/User
- `docs/documentar/08-traits.md` - Trait RegistersUserEvents
- `docs/documentar/02-controladores.md` - StorageController

**Componentes Implementados:**
- Laravel 10 + TCG Voyager
- Modelos Person/User (separados)
- Trait RegistersUserEvents (auditoría)
- StorageController (AVIF)
- Middleware Loggin y System

---

### Fase 1: Fortalecimiento del Núcleo y UX del Admin 🔄 EN PROGRESO

**Estado:** Parcialmente completado (Optimización AJAX ✅, Pruebas pendientes)

**Documentación:**
- `docs/dev/plan/01-estrategia-pruebas-fase1.md` - Estrategia completa de pruebas
- `docs/plan/optimizacion-fase1.md` - Optimización AJAX (completado)
- `docs/documentar/comandos-git-2026-01-19.md` - Comandos Git usados

**Componentes Implementados:**
- ✅ Optimización AJAX (75% más rápido)
- ✅ Prevención de peticiones múltiples
- ✅ Timeout 10s
- ✅ Selección de campos SQL
- ✅ Color verde en tablas

**Pendientes:**
- ⏳ Pruebas unitarias para Person y User
- ⏳ Feature tests para CRUD completo
- ⏳ Mejora de FormRequest
- ⏳ Auditoría de StorageController

**Archivos de Pruebas a Crear:**
- `tests/Unit/PersonTest.php`
- `tests/Unit/UserTest.php`
- `tests/Feature/PersonControllerTest.php`
- `tests/Feature/UserControllerTest.php`
- `tests/Unit/Requests/StorePersonRequestTest.php`

---

### Fase 2: Migraciones, Seeders y CRUDs del Núcleo Electoral ✅ COMPLETADA

**Estado:** Finalizado (100%)

**Documentación:**
- `docs/plan/migraciones.md` - Especificaciones de migraciones con triggers
- `docs/plan/bd.md` - Esquema de base de datos
- `docs/plan/prompts2.md` - Estándares CRUD

**Componentes Implementados:**
- ✅ 10 migraciones con triggers y constraints
- ✅ 3 seeders (Cargos, Geografía, Organizaciones)
- ✅ Trait ManagesCrud
- ✅ CRUDs completos: Cargos, Organizaciones, Geografías, Recintos, Mesas, Candidatos

**Archivos Creados:**
- Migraciones: `database/migrations/2026_01_18_*.php`
- Seeders: `database/seeders/` (3 archivos)
- Trait: `app/Traits/ManagesCrud.php`
- Modelos: `app/Models/` (9 archivos)
- Controladores: `app/Http/Controllers/` (6 archivos)
- Vistas: `resources/views/admin/` (24 archivos)
- Requests: `app/Http/Requests/` (12 archivos)
- Policies: `app/Policies/` (6 archivos)

---

### Fase 3: Módulo de Escrutinio y API de Campo ✅ COMPLETADA

**Estado:** Finalizado (100%)

**Documentación:**
- `docs/dev/plan/04-api-documentacion-openapi.md` - Documentación OpenAPI completa
- `docs/plan/plan.md` - Sección de Fase 3

**Componentes Implementados:**
- ✅ API REST `/api/v1/*`
- ✅ Laravel Sanctum (autenticación)
- ✅ Rate limiting (60 req/min)
- ✅ Feature tests para API
- ✅ Factories para testing

**Endpoints API:**
- `GET /api/v1/mesa/{codigo}` - Obtener datos de mesa
- `GET /api/v1/catalogos` - Obtener catálogos
- `POST /api/v1/acta` - Enviar acta de escrutinio
- `GET /api/v1/acta/{mesa_codigo}` - Verificar estado de acta

**Archivos Creados:**
- Controladores API: `app/Http/Controllers/Api/` (3 archivos)
- Requests: `app/Http/Requests/Api/StoreActaRequest.php`
- Rutas: `routes/api.php`
- Factories: `database/factories/` (6 archivos)
- Tests: `tests/Feature/` (3 archivos)

---

### Fase 4: Optimización, Seguridad Avanzada y Pruebas de Carga ⏳ PENDIENTE

**Estado:** Pendiente

**Documentación:**
- `docs/dev/plan/02-preparacion-fase4.md` - Guía completa de implementación

**Componentes a Implementar:**
- ⏳ Pruebas de carga con k6
- ⏳ Optimización de consultas N+1
- ⏳ Implementación de caché con Redis
- ⏳ Auditoría de seguridad (Larastan, OWASP)
- ⏳ Optimización de índices de base de datos

**Archivos a Crear:**
- `tests/load/k6/api-escrutinio.js` - Escenario API
- `tests/load/k6/admin-panel.js` - Escenario Admin
- `tests/load/k6/election-day.js` - Escenario Día de Elección
- `phpstan.neon` - Configuración Larastan
- Migraciones de índices

---

### Fase 5: Despliegue, Operaciones y Monitoreo ⏳ PENDIENTE

**Estado:** Pendiente

**Documentación:**
- `docs/dev/plan/03-preparacion-fase5.md` - Guía completa de DevOps

**Componentes a Implementar:**
- ⏳ Pipeline CI/CD con GitHub Actions
- ⏳ Infraestructura como Código (Docker)
- ⏳ Configuración servidor de producción
- ⏳ Monitoreo y alertas (Sentry)
- ⏳ Estrategia de backups automatizados

**Archivos a Crear:**
- `.github/workflows/deploy.yml` - Pipeline producción
- `.github/workflows/deploy-staging.yml` - Pipeline staging
- `Dockerfile` optimizado
- `docker-compose.yml`
- `scripts/provision-server.sh`
- `scripts/backup.sh`
- `docker/nginx/conf.d/electoral.conf`

---

### Fase 6: Evolución y Mantenimiento Continuo ♾️ EN CURSO

**Estado:** Continuo

**Documentación:**
- `docs/plan/plan.md` - Sección de Fase 6
- `docs/documentar/registro-actividades-2026-01-20.md` - Registro diario

**Actividades:**
- ♾️ Mantenimiento de documentación
- ♾️ Actualizaciones de dependencias
- ♾️ Hoja de ruta futura

---

## Documentación por Tema

### Arquitectura y Diseño

| Archivo | Descripción | Fase |
|---------|-------------|------|
| `docs/plan/plan.md` | Plan de desarrollo evolutivo | Todas |
| `docs/plan/bd.md` | Esquema de base de datos | Fase 2 |
| `docs/plan/diagrama-er.md` | Diagrama Entidad-Relación | Fase 2 |
| `docs/plan/migraciones.md` | Migraciones con triggers | Fase 2 |
| `docs/plan/prompts2.md` | Estándares CRUD | Fase 2 |

### Desarrollo y Código

| Archivo | Descripción | Fase |
|---------|-------------|------|
| `docs/documentar/01-modelos.md` | Modelos de datos | Fase 0 |
| `docs/documentar/02-controladores.md` | Controladores | Fase 0 |
| `docs/documentar/03-rutas.md` | Rutas | Fase 0 |
| `docs/documentar/04-middleware.md` | Middleware | Fase 0 |
| `docs/documentar/05-vistas.md` | Vistas | Fase 0 |
| `docs/documentar/08-traits.md` | Traits | Fase 0 |

### Pruebas y Calidad

| Archivo | Descripción | Fase |
|---------|-------------|------|
| `docs/dev/plan/01-estrategia-pruebas-fase1.md` | Estrategia de pruebas | Fase 1 |
| `docs/dev/plan/02-preparacion-fase4.md` | Pruebas de carga | Fase 4 |
| `docs/plan/optimizacion-fase1.md` | Optimización AJAX | Fase 1 |

### API

| Archivo | Descripción | Fase |
|---------|-------------|------|
| `docs/dev/plan/04-api-documentacion-openapi.md` | Documentación OpenAPI | Fase 3 |

### DevOps y Despliegue

| Archivo | Descripción | Fase |
|---------|-------------|------|
| `docs/dev/plan/03-preparacion-fase5.md` | Despliegue y DevOps | Fase 5 |
| `docs/documentar/13-docker.md` | Docker | Fase 0 |
| `docs/documentar/07-configuracion.md` | Configuración | Fase 0 |

### Seguridad

| Archivo | Descripción | Fase |
|---------|-------------|------|
| `docs/dev/plan/02-preparacion-fase4.md` | Auditoría de seguridad | Fase 4 |
| `docs/documentar/07-configuracion.md` | Configuración | Fase 0 |

---

## Progreso General del Proyecto

| Fase | Estado | Completado | Total | % |
|------|--------|------------|-------|---|
| Fase 0: Fundación | ✅ Completado | 6 | 6 | 100% |
| Fase 1: Fortalecimiento | 🔄 En Progreso | 5 | 9 | 56% |
| Fase 2: CRUDs Núcleo | ✅ Completado | 100% | 100% | 100% |
| Fase 3: API Escrutinio | ✅ Completado | 100% | 100% | 100% |
| Fase 4: Optimización | ⏳ Pendiente | 0 | 20 | 0% |
| Fase 5: Despliegue | ⏳ Pendiente | 0 | 15 | 0% |
| **TOTAL** | - | 111 | 150 | **74%** |

---

## Próximos Pasos Prioritarios

### Esta Semana
1. ✅ Completar documentación de desarrollo (hecho)
2. ⏳ Implementar pruebas unitarias (Fase 1)
3. ⏳ Implementar feature tests (Fase 1)

### Próximas 2 Semanas
4. ⏳ Comenzar pruebas de carga (Fase 4)
5. ⏳ Optimizar consultas N+1 (Fase 4)
6. ⏳ Configurar Redis para caché (Fase 4)

### Próximo Mes
7. ⏳ Configurar CI/CD (Fase 5)
8. ⏳ Desplegar a staging (Fase 5)
9. ⏳ Configurar monitoreo (Fase 5)

---

## Referencias Rápidas

### Comandos Comunes

```bash
# Ejecutar pruebas
php artisan test

# Ejecutar pruebas con cobertura
php artisan test --coverage

# Ejecutar migraciones
php artisan migrate --seed

# Optimizar caché
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpiar caché
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Revisar logs
tail -f storage/logs/laravel.log
```

### Archivos de Configuración Clave

| Archivo | Propósito |
|---------|-----------|
| `.env` | Variables de entorno |
| `config/database.php` | Configuración BD |
| `config/cache.php` | Configuración caché |
| `config/sanctum.php` | Configuración API |
| `routes/web.php` | Rutas web |
| `routes/api.php` | Rutas API |

---

## Contribuciones y Actualizaciones

### Cómo Agregar Documentación

1. Crear el archivo en la ubicación apropiada
2. Agregar entrada a este índice
3. Actualizar el estado de la fase correspondiente
4. Registrar en `docs/documentar/registro-actividades-YYYY-MM-DD.md`

### Convenciones de Nombres

- Nombres de archivos: `kebab-case.md`
- Fechas: `YYYY-MM-DD`
- Versiones: `vX.Y.Z`
- IDs: `01-`, `02-`, etc. para orden

---

## Soporte

**Documentación Principal:** `docs/plan/plan.md`  
**Registro de Actividades:** `docs/documentar/registro-actividades-2026-01-20.md`  
**Resumen Ejecutivo:** `docs/documentar/16-resumen-ejecutivo.md`  
**Historial de Versiones:** `docs/documentar/17-historial-cambios.md`

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-20  
**Última Actualización:** 2026-01-20  
**Responsable:** Equipo de Desarrollo
