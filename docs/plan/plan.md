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

**Estado:** `Finalizado` o saltar esta fase y ve a la fase 2

**Objetivo:** Refinar la funcionalidad existente, mejorar la experiencia del operador y sentar las bases para pruebas automatizadas.

- **Actividades:**
  - **[ ] Refactorización de Vistas BREAD:** Convertir las vistas de `Person` y `User` a componentes Livewire para una experiencia más dinámica y reactiva, eliminando la dependencia de AJAX manual.
  - **[ ] Pruebas Unitarias para Modelos:** Crear pruebas unitarias (`Pest` o `PHPUnit`) para los modelos `Person` y `User`, validando relaciones, scopes y accesors.
  - **[ ] Pruebas de Funcionalidad (Feature Tests):** Escribir pruebas que simulen el flujo CRUD completo para Personas y Usuarios a través de las rutas del admin.
  - **[ ] Mejora de la Validación:** Fortalecer las `FormRequest` para el registro y actualización, asegurando la integridad de los datos de entrada.
  - **[ ] Auditoría del `StorageController`:** Revisar y añadir pruebas para garantizar que el manejo de imágenes sea a prueba de fallos (ej. tipos de archivo inválidos, errores de escritura).

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
      - **[ ]** **Organizaciones:** Pendiente - Cargar logos, siglas y colores de los partidos.
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
- Seeders: `database/seeders/CargoSeeder.php`, `GeografiaSeeder.php`
- Trait: `app/Traits/ManagesCrud.php`
- Modelos (9 archivos): `Cargo.php`, `OrganizacionPolitica.php`, `Geografia.php`, `Recinto.php`, `Mesa.php`, `Candidato.php`, `ActaEscrutinio.php`, `VotoXPartido.php`, `AuditoriaActa.php`, `ResumenVoto.php`
- Requests (2 archivos): `StoreCargoRequest.php`, `UpdateCargoRequest.php`
- Policy: `CargoPolicy.php`

---

### 📡 **Fase 3: Módulo de Escrutinio y API de Campo**

**Estado:** `Pendiente`

**Objetivo:** Desarrollar el sistema de recolección de actas, priorizando la seguridad, el rendimiento y el uso en condiciones de baja conectividad.

- **Actividades:**
  - **[ ] Diseño de la API REST:**
    - `POST /api/v1/acta`: Endpoint para recibir la imagen del acta y el JSON con los votos. Debe usar una `DB Transaction` para garantizar la atomicidad.
    - `GET /api/v1/mesa/{codigo}`: Endpoint para que el delegado verifique los datos de su mesa asignada.
    - `GET /api/v1/catalogos`: Endpoint para obtener datos necesarios (partidos, etc.) para la app cliente.
  - **[ ] Seguridad de la API:**
    - Implementar `Laravel Sanctum` para autenticación basada en tokens de API.
    - Añadir `Rate Limiting` (limitador de peticiones) para prevenir abusos.
  - **[ ] Desarrollo del Frontend (PWA con Livewire/Alpine.js):**
    - Crear una interfaz de usuario ligera y progresiva para la carga y envío de actas.
    - Usar las capacidades del navegador para la compresión de imágenes antes del envío.
    - Implementar un `Service Worker` para capacidades offline básicas (cacheo de la UI y datos de catálogo).
  - **[ ] Pruebas de la API:** Escribir `Feature Tests` exhaustivos para cada endpoint, cubriendo casos de éxito, errores de validación y fallos de autenticación.

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
