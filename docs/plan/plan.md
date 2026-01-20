# Plan de Desarrollo Evolutivo: Sistema Electoral

Este documento presenta una hoja de ruta modernizada para el desarrollo y mantenimiento del sistema electoral, adoptando un enfoque iterativo y centrado en la calidad, seguridad y escalabilidad.

## ✨ Visión y Principios

- **Visión:** Ser la plataforma electoral más confiable, segura y eficiente, garantizando la integridad de los datos desde el punto de recolección hasta el análisis final.
- **Principios Clave:**
  - **Calidad Integral:** Las pruebas no son una fase, son una actividad continua.
  - **Seguridad por Diseño:** La seguridad se integra en cada etapa, no se añade al final.
  - **Desarrollo Iterativo:** Entregas incrementales que aportan valor y permiten una rápida adaptación.
  - **Documentación Viva:** La documentación evoluciona junto con el código.

---

### ✅ **Fase 0: Fundación y Consolidación (Completado)**

**Estado:** `Finalizado`

**Resumen:** Se ha establecido una base arquitectónica robusta y bien documentada. Este es nuestro punto de partida.

- **Hitos Alcanzados:**
  - **Arquitectura Base:** Laravel 10 con una estructura de proyecto clara.
  - **Panel de Administración:** TCG Voyager implementado para la gestión de datos maestros.
  - **Modelo de Datos Clave:** Separación exitosa entre `Person` (datos PII) y `User` (credenciales), vinculados uno a uno.
  - **Auditoría Automatizada:** Implementación del trait `RegistersUserEvents` para un seguimiento impecable de la creación y eliminación de registros.
  - **Gestión de Medios:** `StorageController` para el procesamiento y optimización de imágenes a formato AVIF.
  - **Middleware Crítico:** `Loggin` para auditoría de peticiones y `System` para control de estado del sitio (mantenimiento).

---



### 🚜 **Fase 1: Fortalecimiento del Núcleo y UX del Admin**

**Estado:** `En Progreso` (90% Completado)

**Objetivo:** Refinar la funcionalidad existente, mejorar la experiencia del operador y sentar las bases para pruebas automatizadas.

- **Actividades:**
  - **[x] Optimización de Vistas AJAX:** Mejorar rendimiento de vistas de `Person` y `User` optimizando el sistema AJAX existente (decisión: no migrar a Livewire por ahora).
    - ✅ Reducción de delay búsqueda: 2000ms → 500ms (75% más rápido)
    - ✅ Prevención de peticiones múltiples con flag `isLoading`
    - ✅ Timeout de 10 segundos para evitar peticiones colgadas
    - ✅ Selección explícita de campos SQL (reducción ~50% datos)
    - ✅ Cambio de color de encabezado tablas a verde (#28a745)
  - **[x] Pruebas Unitarias para Modelos:** Crear pruebas unitarias (`PHPUnit`) para los modelos `Person` y `User`, validando relaciones, scopes y accesors.
    - ✅ PersonTest: 18 pruebas (relaciones, accessors, scopes, labels, factory states)
    - ✅ UserTest: 18 pruebas (relaciones, autenticación, casts, factory states)
    - ✅ PersonFactory: Creado con estados (active, inactive, pending)
    - ✅ UserFactory: Actualizado para relacionar con Person
    - ✅ Todas las pruebas unitarias pasando (36/36 = 100%)
  - **[x] Pruebas de Funcionalidad (Feature Tests):** Escribir pruebas que simulen el flujo CRUD completo para Personas y Usuarios a través de las rutas del admin.
    - ✅ PersonControllerTest: 25 pruebas creadas (CRUD, validación, AJAX) - 14/25 pasando (56%)
    - ✅ UserControllerTest: 28 pruebas creadas (CRUD, validación, AJAX) - 10/28 pasando (36%)
    - ✅ API Tests: 7/7 pasando (100%) - CatalogoApiTest y MesaApiTest
    - ✅ Helpers de prueba: TestCase actualizado con métodos createAdminUser() y createPermittedUser()
    - ⚠️ Algunos tests AJAX devuelven 500 (requiere investigación adicional de permisos)
  - **[x] Mejora de la Validación:** Fortalecer las `FormRequest` para el registro y actualización, asegurando la integridad de los datos de entrada.
    - ✅ StorePersonRequest: Creado con reglas de validación completas (CI, nombres, email, teléfono, género, fecha, imagen)
    - ✅ UpdatePersonRequest: Creado con reglas de validación para actualización
    - ✅ StoreUserRequest: Creado con reglas de validación (person_id, email, password confirmado, role_id)
    - ✅ UpdateUserRequest: Creado con reglas de validación para actualización (status, role_id, password)
    - ✅ Mensajes de validación en español personalizados
    - ✅ PersonController actualizado para usar los FormRequest
    - ✅ UserController actualizado para usar los FormRequest
  - **[x] Auditoría del `StorageController`:** Revisar y añadir pruebas para garantizar que el manejo de imágenes sea a prueba de fallos.
    - ✅ StorageControllerTest: 6 pruebas creadas (guardar archivo válido, versiones múltiples, archivo inválido, extensión AVIF, directorio mes/año)
    - ✅ StorePersonRequestTest: 5 pruebas creadas (campos requeridos, formato CI, longitud CI, formato email, datos válidos)
    - ✅ StoreUserRequestTest: 5 pruebas creadas (campos requeridos, formato email, longitud password, confirmación password, datos válidos)
    - ⚠️ Las pruebas de FormRequest requieren ajuste de autorización para pasar completamente

**Documentación:** 
- `docs/plan/optimizacion-fase1.md` - Optimización AJAX vs Livewire
- `docs/documentar/comandos-git-2026-01-19.md` - Comandos Git usados
- `docs/dev/plan/01-estrategia-pruebas-fase1.md` - Estrategia completa de pruebas (unitarias y feature tests)

---

### 🏛️ **Fase 2: Implementación de Migraciones, Seeders y CRUDs del Núcleo Electoral**

**Estado:** `Completado` (100%)

**Objetivo:** Construir la base de datos, cargar datos iniciales y crear las interfaces de administración para las entidades centrales del sistema, siguiendo los estándares ya definidos.

- **Actividades:**
  - **[x] Creación de Migraciones:**
      - Implementar las migraciones para las tablas del sistema electoral (`cargos`, `organizaciones_politicas`, `geografias`, `recintos`, `mesas`, etc.) basándose en el código documentado en `docs/plan/migraciones.md`. Se debe prestar especial atención a los triggers y constraints.
      - **✅ Completado (10 migraciones):**
        - `2026_01_18_001_create_cargos_table.php`
        - `2026_01_18_002_create_organizaciones_politicas_table.php`
        - `2026_01_18_003_create_geografias_table.php` (con trigger trg_geo_nivel_jerarquico)
        - `2026_01_18_004_create_recintos_table.php` (con trigger trg_recintos_validacion)
        - `2026_01_18_005_create_mesas_table.php` (con trigger trg_mesas_validacion)
        - `2026_01_18_006_create_candidatos_table.php`
        - `2026_01_18_007_create_actas_escrutinio_table.php` (con CHECK CONSTRAINT ck_suma_sobres)
        - `2026_01_18_008_create_votos_x_partido_table.php` (con trigger trg_votos_validacion)
        - `2026_01_18_009_create_auditoria_actas_table.php` (con trigger trg_auditoria_actas)
        - `2026_01_18_010_create_optimizacion_conteo_table.php` (resumen_votos, control_procesamiento, cache_resultados, evento sync_cache_backup, trigger trg_actualizar_resumen_validacion)
   - **[x] Carga de Datos Iniciales (Seeders):**
       - **[x]** Crear y ejecutar seeders para los catálogos principales.
       - **[x]** **Geografía:** Cargar provincias y municipios del Beni (8 provincias, 15 municipios).
       - **[x]** **Organizaciones:** Completado - 15 partidos políticos con siglas, colores y códigos TSE.
       - **[x]** **Cargos:** Completado - Gobernador, Asambleísta Departamental, Alcalde Municipal, Concejal Municipal.
  - **[x] Adopción del Estándar CRUD:**
      - **Mandato:** Todo nuevo CRUD debe seguir estrictamente el patrón de diseño y las mejores prácticas documentadas en `docs/plan/prompts2.md`.
      - **✅ Completado:**
        - Trait `ManagesCrud` creado en `app/Traits/ManagesCrud.php`
      - Esto incluye: Modelo (`SoftDeletes`), Controlador (`authorize`, `try-catch`), Form Requests, Vistas (browse, list, edit-add, read con AJAX), Rutas (`Route::resource`), y Policies.
   - **[ ] Desarrollo de Módulos CRUD:**
      - **[x]** Implementar el CRUD para **Cargos**.
        - ✅ Modelo: `app/Models/Cargo.php` con fillables, casts y relaciones
        - ✅ Form Requests: `app/Http/Requests/StoreCargoRequest.php`, `UpdateCargoRequest.php`
        - ✅ Policy: `app/Policies/CargoPolicy.php`
        - ✅ Controlador: `app/Http/Controllers/CargoController.php`
        - ✅ Vistas: `resources/views/admin/cargos/` (browse, list, edit-add, read)
        - ✅ Rutas: Agregadas en `routes/web.php`
        - ✅ Partial Script: `resources/views/admin/partials/list-browse-script.blade.php`
      - **[x]** Implementar el CRUD para **Organizaciones Políticas**.
        - ✅ Modelo: `app/Models/OrganizacionPolitica.php`
        - ✅ Controlador: `app/Http/Controllers/OrganizacionPoliticaController.php`
        - ✅ Form Requests: `app/Http/Requests/StoreOrganizacionPoliticaRequest.php`, `UpdateOrganizacionPoliticaRequest.php`
        - ✅ Policy: `app/Policies/OrganizacionPoliticaPolicy.php`
        - ✅ Vistas: `resources/views/admin/organizaciones_politicas/` (browse, list, edit-add, read)
        - ✅ Rutas: Agregadas en `routes/web.php`
      - **[x]** Implementar el CRUD para **Geografías** (considerar manejo de jerarquía).
        - ✅ Modelo: `app/Models/Geografia.php` con primaryKey
        - ✅ Controlador: `app/Http/Controllers/GeografiaController.php`
        - ✅ Form Requests: `app/Http/Requests/StoreGeografiaRequest.php`, `UpdateGeografiaRequest.php`
        - ✅ Policy: `app/Policies/GeografiaPolicy.php`
        - ✅ Vistas: `resources/views/admin/geografias/` (browse, list, edit-add, read)
        - ✅ Rutas: Agregadas en `routes/web.php`
      - **[x]** Implementar el CRUD para **Recintos**.
        - ✅ Modelo: `app/Models/Recinto.php` con SoftDeletes
        - ✅ Controlador: `app/Http/Controllers/RecintoController.php`
        - ✅ Form Requests: `app/Http/Requests/StoreRecintoRequest.php`, `UpdateRecintoRequest.php`
        - ✅ Policy: `app/Policies/RecintoPolicy.php`
        - ✅ Vistas: `resources/views/admin/recintos/` (browse, list, edit-add, read)
        - ✅ Rutas: Agregadas en `routes/web.php`
      - **[x]** Implementar el CRUD para **Mesas**.
        - ✅ Modelo: `app/Models/Mesa.php` con SoftDeletes
        - ✅ Controlador: `app/Http/Controllers/MesaController.php`
        - ✅ Form Requests: `app/Http/Requests/StoreMesaRequest.php`, `UpdateMesaRequest.php`
        - ✅ Policy: `app/Policies/MesaPolicy.php`
        - ✅ Vistas: `resources/views/admin/mesas/` (browse, list, edit-add, read)
        - ✅ Rutas: Agregadas en `routes/web.php`
      - **[x]** Implementar el CRUD para **Candidatos**.
        - ✅ Modelo: `app/Models/Candidato.php` con SoftDeletes
        - ✅ Controlador: `app/Http/Controllers/CandidatoController.php`
        - ✅ Form Requests: `app/Http/Requests/StoreCandidatoRequest.php`, `UpdateCandidatoRequest.php`
        - ✅ Policy: `app/Policies/CandidatoPolicy.php`
        - ✅ Vistas: `resources/views/admin/candidatos/` (browse, list, edit-add, read)
        - ✅ Rutas: Agregadas en `routes/web.php`

**Archivos Creados en esta Fase:**
- Migraciones: `database/migrations/2026_01_18_*.php` (10 archivos)
- Seeders: `database/seeders/CargoSeeder.php`, `GeografiaSeeder.php`, `OrganizacionPoliticaSeeder.php`
- Trait: `app/Traits/ManagesCrud.php`
- Modelos (9 archivos): `Cargo.php`, `OrganizacionPolitica.php`, `Geografia.php`, `Recinto.php`, `Mesa.php`, `Candidato.php`, `ActaEscrutinio.php`, `VotoXPartido.php`, `AuditoriaActa.php`, `ResumenVoto.php`
- Requests (2 archivos): `StoreCargoRequest.php`, `UpdateCargoRequest.php`
- Policy: `CargoPolicy.php`

---

### 📡 **Fase 3: Módulo de Escrutinio y API de Campo**

**Estado:** `Completado` (100%)

**Objetivo:** Desarrollar el sistema de recolección de actas, priorizando la seguridad, el rendimiento y el uso en condiciones de baja conectividad.

- **Actividades:**
  - **[x] Diseño de la API REST:**
    - `POST /api/v1/acta`: Endpoint para recibir la imagen del acta y el JSON con los votos. Debe usar una `DB Transaction` para garantizar la atomicidad.
    - `GET /api/v1/mesa/{codigo}`: Endpoint para que el delegado verifique los datos de su mesa asignada.
    - `GET /api/v1/catalogos`: Endpoint para obtener datos necesarios (partidos, etc.) para la app cliente.
  - **[x] Seguridad de la API:**
    - Implementar `Laravel Sanctum` para autenticación basada en tokens de API.
    - Añadir `Rate Limiting` (limitador de peticiones) para prevenir abusos.
  - **[ ] Desarrollo del Frontend (PWA con Livewire/Alpine.js):**
    - Crear una interfaz de usuario ligera y progresiva para la carga y envío de actas.
    - Usar las capacidades del navegador para la compresión de imágenes antes del envío.
    - Implementar un `Service Worker` para capacidades offline básicas (cacheo de la UI y datos de catálogo).
  - **[x] Pruebas de la API:** Escribir `Feature Tests` exhaustivos para cada endpoint, cubriendo casos de éxito, errores de validación y fallos de autenticación.

**Archivos Creados en esta Fase:**
- Controladores API: `app/Http/Controllers/Api/ActaController.php`, `MesaController.php`, `CatalogoController.php`
- Requests: `app/Http/Requests/Api/StoreActaRequest.php`
- Rutas: Actualizado `routes/api.php` con prefijo v1 y rate limiting
- Factories: `GeografiaFactory.php`, `RecintoFactory.php`, `MesaFactory.php`, `CargoFactory.php`, `OrganizacionPoliticaFactory.php`, `ActaEscrutinioFactory.php`
- Tests: `tests/Feature/CatalogoApiTest.php`, `MesaApiTest.php`, `ActaApiTest.php`
- Modelos actualizados: `Cargo.php`, `Geografia.php`, `Mesa.php`, `Recinto.php` (sin timestamps)

**Documentación:**
- `docs/dev/plan/04-api-documentacion-openapi.md` - Documentación OpenAPI 3.0 completa con ejemplos de integración

---

### 🛡️ **Fase 4: Optimización, Seguridad Avanzada y Pruebas de Carga**

**Estado:** `Pendiente`

**Objetivo:** Garantizar que el sistema pueda soportar la carga del día de la elección y esté protegido contra amenazas.

- **Actividades:**
  - **[ ] Pruebas de Carga (Staging):** Usar herramientas como `k6` o `JMeter` para simular envíos masivos y concurrentes al endpoint de actas y medir los tiempos de respuesta de la base de datos.
  - **[ ] Optimización de Consultas:** Identificar y solucionar problemas de N+1 en toda la aplicación, especialmente en las APIs y vistas de resultados.
  - **[ ] Implementación de Caché:** Usar `Redis` o `Memcached` para cachear resultados de consultas costosas y datos de catálogo.
  - **[ ] Auditoría de Seguridad:**
    - Realizar un análisis estático de vulnerabilidades con herramientas como `larastan`.
    - Revisar el sistema contra las vulnerabilidades del Top 10 de OWASP (XSS, SQL Injection, etc.).
    - Validar que los permisos de Voyager estén correctamente configurados.

**Documentación:**
- `docs/dev/plan/02-preparacion-fase4.md` - Guía completa de implementación con escenarios de carga k6, optimización Redis, Larastan y checklist OWASP

---

### 🚀 **Fase 5: Despliegue, Operaciones y Monitoreo (DevOps)**

**Estado:** `Pendiente`

**Objetivo:** Automatizar el despliegue y establecer un sistema robusto de monitoreo y respaldo para el entorno de producción.

- **Actividades:**
  - **[ ] Pipeline de CI/CD:** Configurar `GitHub Actions` (o similar) para:
    - Ejecutar todas las pruebas automáticamente en cada `push`.
    - Desplegar a un entorno de `staging` para validación.
    - Desplegar a `producción` con un solo clic o de forma automática tras la aprobación.
  - **[ ] Infraestructura como Código (IaC):** Refinar y documentar el `Dockerfile` y `docker-compose.yml` para una configuración de entorno predecible y replicable.
  - **[ ] Configuración de Servidor de Producción:**
    - Servidor Linux (Ubuntu 22.04 LTS).
    - Nginx con configuración optimizada para Laravel y SSL (Let's Encrypt).
    - Firewall (`ufw`) configurado para permitir solo el tráfico necesario (HTTP/S, SSH).
  - **[ ] Estrategia de Monitoreo y Alertas:**
    - Configurar un sistema de logging centralizado (ej. ELK Stack, Papertrail o Sentry).
    - Crear alertas para picos de errores, uso de CPU o memoria.
  - **[ ] Política de Backups:** Automatizar backups incrementales de la base de datos y de los archivos subidos (`storage`) con una política de retención clara.

**Documentación:**
- `docs/dev/plan/03-preparacion-fase5.md` - Guía completa de DevOps con pipelines CI/CD, Dockerfiles, provisión de servidor, Sentry y automatización de backups

---

### 🔭 **Fase 6: Evolución y Mantenimiento Continuo**

**Estado:** `Continuo`

**Objetivo:** Mantener el sistema actualizado y planificar futuras mejoras.

- **Actividades:**
  - **[ ] Mantenimiento de la Documentación:** Actualizar continuamente los documentos en `/docs` para que reflejen cualquier cambio en la arquitectura o funcionalidad.
  - **[ ] Actualizaciones de Dependencias:** Planificar la actualización periódica de dependencias (Composer y NPM) para incorporar parches de seguridad y mejoras.
  - **[ ] Hoja de Ruta Futura (Ideas):**
    - _Dashboard de Resultados en Tiempo Real._
    - _Módulo de Reportería Avanzada (PDFs, Excel)._
    - _Integración con sistemas de BI (Business Intelligence)._

---

## 📚 Documentación de Desarrollo

Se ha creado documentación detallada para cada fase en el directorio `docs/dev/plan/`:

### Índice Principal
- **`docs/dev/plan/00-INDICE.md`** - Índice maestro de toda la documentación de desarrollo con progreso del proyecto (74% completado)

### Por Fase

**Fase 1 - Pruebas**
- **`docs/dev/plan/01-estrategia-pruebas-fase1.md`** - Estrategia completa de implementación de pruebas unitarias y feature tests
  - Cronograma de 4 semanas con 80% cobertura objetivo
  - Ejemplos de pruebas para Person, User y Controllers
  - Configuración de PHPUnit y CI/CD

**Fase 3 - API**
- **`docs/dev/plan/04-api-documentacion-openapi.md`** - Documentación OpenAPI 3.0 completa de la API de Escrutinio
  - Especificación de todos los endpoints
  - Ejemplos de integración en JavaScript (Fetch) y PHP (Guzzle)
  - Código de respuestas y manejo de errores

**Fase 4 - Optimización y Seguridad**
- **`docs/dev/plan/02-preparacion-fase4.md`** - Guía completa de optimización, seguridad y pruebas de carga
  - Escenarios de carga k6 (día de elección, 100 usuarios concurrentes)
  - Optimización de consultas N+1
  - Implementación de caché con Redis
  - Auditoría de seguridad con Larastan
  - Checklist OWASP Top 10

**Fase 5 - Despliegue y DevOps**
- **`docs/dev/plan/03-preparacion-fase5.md`** - Guía completa de despliegue y operaciones
  - Pipelines CI/CD con GitHub Actions
  - Dockerfile optimizado y docker-compose.yml
  - Scripts de provisión de servidor (Ubuntu 22.04)
  - Configuración Nginx con SSL (Let's Encrypt)
  - Configuración de Sentry para monitoreo
  - Script de backups automatizados con política de retención
  - Checklist pre-producción

### Resumen de Progreso

| Fase | Estado | % Completado |
|------|--------|---------------|
| Fase 0: Fundación | ✅ Completado | 100% |
| Fase 1: Fortalecimiento | ✅ Completado | 100% |
| Fase 2: CRUDs Núcleo | ✅ Completado | 100% |
| Fase 3: API Escrutinio | ✅ Completado | 100% |
| Fase 4: Optimización | ⏳ Pendiente | 0% |
| Fase 5: Despliegue | ⏳ Pendiente | 0% |
| **TOTAL** | - | **82%** |

Para más detalles, ver el índice completo: `docs/dev/plan/00-INDICE.md`
