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

---
Entonces, el flujo de trabajo es claro:

Realizaremos las tareas de la Fase 1 (Mejoras al panel) en tu rama panel.
Cuando empecemos la Fase 2 (Sistema Electoral), trabajaremos sobre la rama actual de este proyecto.
Dado que este es el caso, y como la rama actual es para lo electoral, ¿quieres que omitamos la Fase 1 por ahora y empecemos directamente con la Fase 2: Implementación de Migraciones, Seeders y CRUDs del Núcleo Electoral?

---


### 🚜 **Fase 1: Fortalecimiento del Núcleo y UX del Admin**

**Estado:** `Pendiente`

**Objetivo:** Refinar la funcionalidad existente, mejorar la experiencia del operador y sentar las bases para pruebas automatizadas.

- **Actividades:**
  - **[ ] Refactorización de Vistas BREAD:** Convertir las vistas de `Person` y `User` a componentes Livewire para una experiencia más dinámica y reactiva, eliminando la dependencia de AJAX manual.
  - **[ ] Pruebas Unitarias para Modelos:** Crear pruebas unitarias (`Pest` o `PHPUnit`) para los modelos `Person` y `User`, validando relaciones, scopes y accesors.
  - **[ ] Pruebas de Funcionalidad (Feature Tests):** Escribir pruebas que simulen el flujo CRUD completo para Personas y Usuarios a través de las rutas del admin.
  - **[ ] Mejora de la Validación:** Fortalecer las `FormRequest` para el registro y actualización, asegurando la integridad de los datos de entrada.
  - **[ ] Auditoría del `StorageController`:** Revisar y añadir pruebas para garantizar que el manejo de imágenes sea a prueba de fallos (ej. tipos de archivo inválidos, errores de escritura).

---

### 🏛️ **Fase 2: Implementación de Migraciones, Seeders y CRUDs del Núcleo Electoral**

**Estado:** `Pendiente`

**Objetivo:** Construir la base de datos, cargar datos iniciales y crear las interfaces de administración para las entidades centrales del sistema, siguiendo los estándares ya definidos.

- **Actividades:**
  - **[ ] Creación de Migraciones:**
      - Implementar las migraciones para las tablas del sistema electoral (`cargos`, `organizaciones_politicas`, `geografias`, `recintos`, `mesas`, etc.) basándose en el código documentado en `docs/plan/migraciones.md`. Se debe prestar especial atención a los triggers y constraints.
  - **[ ] Carga de Datos Iniciales (Seeders):**
      - **[ ]** Crear y ejecutar seeders para los catálogos principales.
      - **[ ]** **Geografía:** Cargar provincias y municipios del Beni.
      - **[ ]** **Organizaciones:** Cargar logos, siglas y colores de los partidos.
      - **[ ]** **Cargos:** Definir Gobernador, Asambleístas, etc.
  - **[ ] Adopción del Estándar CRUD:**
      - **Mandato:** Todo nuevo CRUD debe seguir estrictamente el patrón de diseño y las mejores prácticas documentadas en `docs/plan/prompt.md`.
      - Esto incluye: Modelo (`SoftDeletes`), Controlador (`authorize`, `try-catch`), Form Requests, Vistas (browse, list, edit-add, read con AJAX), Rutas (`Route::resource`), y Policies.
  - **[ ] Desarrollo de Módulos CRUD:**
      - **[ ]** Implementar el CRUD para **Cargos**.
      - **[ ]** Implementar el CRUD para **Organizaciones Políticas**.
      - **[ ]** Implementar el CRUD para **Geografías** (considerar manejo de jerarquía).
      - **[ ]** Implementar el CRUD para **Recintos**.
      - **[ ]** Implementar el CRUD para **Mesas**.
      - **[ ]** Implementar el CRUD para **Candidatos**.

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
