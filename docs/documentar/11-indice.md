# Índice de Documentación

## Índice Completo

Esta documentación cubre todo el sistema panel de Voyager y sus personalizaciones.

---

## Documentos

### 00. README.md
**Descripción general del sistema**

- Información del proyecto
- Estructura del sistema
- Dependencias principales
- Características principales
- Configuración inicial
- Acceso al sistema
- Documentación detallada (referencias a otros documentos)
- Convenciones de código
- Notas importantes

---

### 01. Modelos
**Modelos de datos del sistema**

- Person (`app/Models/Person.php`)
  - Características, campos fillable, constantes
  - Accesorios, scopes, métodos
- User (`app/Models/User.php`)
  - Extiende modelo de Voyager
  - Campos adicionales, relaciones
- Modelos de Voyager (no modificados)
  - User, Role, Permission, Setting, Menu, etc.
- Tablas de la base de datos
  - Estructura de `people` y `users`
  - Índices, foreign keys
- Relaciones entre modelos
- Patrones de uso
- Notas importantes

---

### 02. Controladores
**Controladores y lógica de negocio**

- PersonController
  - index(), list(), store(), update()
  - Validaciones, transacciones DB
- UserController
  - list(), store(), update(), destroy()
  - Filtros, validaciones
- StorageController
  - store_image()
  - Múltiples tamaños de imagen, formato AVIF
- AjaxController
  - personList(), personStore()
  - Peticiones AJAX genéricas
- RoleController
  - list()
- ErrorController
  - (Pendiente de implementación)
- SolucionDigitalController
  - settings_code()
  - Integración con sistema de licencias
- Controller Base
  - custom_authorize()
  - payment_alert()
- Patrones comunes
- Notas importantes

---

### 03. Rutas
**Definición de rutas del sistema**

- Archivo de rutas (`routes/web.php`)
- Middleware aplicados
- Redirecciones (raíz, login)
- Rutas de Personas
  - listado, lista AJAX, crear, actualizar
- Rutas de Usuarios
  - lista AJAX, crear, actualizar, eliminar
- Rutas de Roles
- Rutas AJAX Genéricas
  - lista personas, crear persona
- Rutas de Utilidades
  - limpiar caché
- Rutas de Voyager (nativas)
- Middleware personalizados
- Orden de ejecución de middlewares
- Nomenclatura de rutas
- Ejemplos de uso
- Notas importantes

---

### 04. Middleware
**Middleware personalizados del sistema**

- Middleware Loggin
  - Funcionalidad, datos registrados
  - Excepciones, configuración
- Middleware System
  - Rutas siempre abiertas
  - Modo mantenimiento
  - Modo desarrollo
  - Verificación de licencia
- Registro de middlewares
- Orden de ejecución
- Middlewares de Laravel (no modificados)
- Casos de uso
- Notas importantes

---

### 05. Vistas
**Vistas personalizadas del sistema**

- Estructura de vistas
- Vistas de Administración de Personas
  - browse.blade.php (página principal)
  - list.blade.php (tabla AJAX)
- Partial Componentes
  - modal-delete.blade.php
  - modal-registerPerson.blade.php
- Vistas de Usuarios (sobrescritas de Voyager)
- Vistas de BREAD (sobrescritas)
- Convenciones de vistas
- Patrones de uso
- Notas importantes

---

### 06. Migraciones
**Estructura de base de datos**

- Migraciones personalizadas
  - Tabla People
  - Extensión de tabla Users
- Migraciones de Voyager
- Migraciones nativas de Laravel
- Relaciones entre tablas
- Diagrama de base de datos
- Ejecutar migraciones
- Seeders
- Convenciones de nomenclatura
- Notas importantes

---

### 07. Configuración
**Configuraciones del sistema**

- Configuración de Voyager
  - User, Controllers, Models, Storage
  - Database, Multilingual, Dashboard, etc.
- Configuración de Logging
  - Canales de logs, canal requests
- Configuración de Archivos
  - Disco público, almacenamiento
- Variables de entorno (.env)
- Settings de Voyager
  - Mantenimiento, desarrollo, código de sistema
- Configuración de licencias (externa)
- Configuración de imágenes
- Configuración de multipartes
- Configuración de timezone, locale
- Comandos de Artisan
- Notas importantes

---

### 08. Traits
**Traits reutilizables del sistema**

- RegistersUserEvents Trait
  - Uso del trait en modelos
  - Funcionalidad, eventos registrados
  - Evento creating, evento deleting
  - Campos de auditoría
  - Flujo de auditoría
  - Ventajas del trait
  - Ejemplos de consulta
  - Integración con soft deletes
  - Integración con middleware Loggin
- Notas importantes
- Ver también: `16-analisis-dockerfile.md` para análisis de bugs y optimizaciones del Dockerfile

---

### 09. BREAD
**Sistema BREAD de Voyager**

- Qué es BREAD
- BREAD configurados
  - People BREAD
  - Users BREAD
  - Roles BREAD
  - Permissions BREAD
  - Settings BREAD
- Permisos del sistema
  - Permisos de personas
  - Permisos de usuarios
  - Permisos de roles
  - Permisos de settings
- Crear BREAD en Voyager
- Tipos de campos BREAD
- Campos relacionales
- Vistas sobrescritas de BREAD
- Menú de navegación
- Seeders de BREAD
- Comandos de Artisan
- Notas importantes

---

### 10. Logs
**Sistema de logging del sistema**

- Canales de logs
  - Default, Stack, Single, Daily
  - Requests (personalizado)
- Middleware de Logging
  - Funcionamiento, datos registrados
  - Excepciones
- Formato de logs
- Ver logs
  - Desde terminal
  - Desde panel de Voyager Compass
- Niveles de log
- Configuración de logs
- Limpiar logs
- Logs en StorageController
- Auditoría dual (logs + traits)
- Compass de Voyager
- Notas importantes

---

### 13. Docker
**Configuración de Docker del sistema**

- Dockerfile
  - Análisis línea por línea
  - Imagen base: NGINX Unit 1.33.0 + PHP 8.2
  - Instalación de extensiones PHP
  - Configuración de OPcache y JIT
  - Instalación de Composer
  - Permisos de archivos
  - Comandos de Artisan
- Configuración de NGINX Unit
  - Archivo unit.json
  - Listeners, Routes, Applications
  - Servir archivos estáticos vs Laravel
- Comandos de Docker
  - Construir imagen
  - Ejecutar contenedor
  - Ver logs
  - Entrar al contenedor
- Variables de entorno
- Optimizaciones del Dockerfile
  - Compilación paralela
  - OPcache + JIT
  - Composer optimizado
  - Imágenes base livianas
- Requisitos del sistema
  - Hardware
  - Software
- Troubleshooting
  - Permisos
  - Conexión a BD
  - Imágenes no cargan
  - Contenedor no arranca
- Ventajas de NGINX Unit
- Comparación con alternativas
  - NGINX Unit vs Apache + mod_php
  - NGINX Unit vs Nginx + PHP-FPM
- Notas importantes

---

### 16. Análisis y Optimizaciones del Dockerfile
**Mejoras y optimizaciones del Dockerfile**

- 🐛 Problemas y Bugs Encontrados
  - Bug #1: Error en configuración de PHP (línea 13 usa > en lugar de >>)
  - Bug #2: Permisos incompletos de directorios
  - Bug #3: Archivo .env se crea en imagen (problema de seguridad)
- 🟢 Optimizaciones de Rendimiento
  - Optimización #4: Falta multi-stage build
  - Optimización #5: Falta .dockerignore
  - Optimización #6: Falta docker-compose.yml
  - Optimización #7: Falta caché de Composer
  - Optimización #8: Falta uso de BuildKit
- 🔒 Mejoras de Seguridad
  - Mejora #9: Falta escaneo de vulnerabilidades
  - Mejora #10: Falta ejecutar como usuario no-root
- 📝 Mejoras de Mantenibilidad
  - Mejora #11: Falta etiquetas de versión
  - Mejora #12: Falta documentación en Dockerfile
- 📊 Comparación de tamaños de imagen (tabla)
- 🎯 Plan de optimización del Dockerfile
- 📍 Archivos nuevos a crear (.dockerignore, docker-compose.yml)
- 🚀 Comandos para probar las optimizaciones
- 📝 Notas importantes
- 🎯 Checklist de optimizaciones de Docker

---

### 17. Plan de Ejecución para Dockerfile (NUEVO)
**Guía paso a paso para optimizar Dockerfile**

- 📅 FASE 1: Bugs Críticos (HOY - Día 1)
  - Tarea 1.1: Corregir configuración de PHP
  - Tarea 1.2: Mejorar permisos de directorios
  - Tarea 1.3: Mejorar manejo de archivo .env
- 📅 FASE 2: Optimizaciones de Rendimiento (Día 2-3)
  - Tarea 2.1: Crear archivo .dockerignore
  - Tarea 2.2: Crear docker-compose.yml
  - Tarea 2.3: Optimizar copia de archivos (caché de layers)
- 📅 FASE 3: Optimizaciones de Seguridad (Día 4)
  - Tarea 3.1: Ejecutar como usuario no-root
  - Tarea 3.2: Agregar etiquetas de versión
  - Tarea 3.3: Mejorar seguridad de contraseñas
- 📅 FASE 4: Optimizaciones de Mantenibilidad (Día 5)
  - Tarea 4.1: Agregar documentación al Dockerfile
- 📅 FASE 5: Optimizaciones Avanzadas (Semana 2)
  - Tarea 5.1: Implementar multi-stage build
  - Tarea 5.2: Implementar Docker BuildKit
  - Tarea 5.3: Implementar escaneo de vulnerabilidades
- 📊 Resumen del plan
- 🎯 Checklist de ejecución
- 🔄 Comandos de rollback
- 🚀 Comandos rápidos de verificación

---

### 14. Análisis de Bugs, Mejoras y Optimizaciones
**Análisis completo del sistema**

- 🐛 Bugs Críticos
  - Error tipográfico COALESCE (SQL syntax error)
  - Método hasRole() no existe en User de Voyager
  - Método hasPermission() no existe en User de Voyager
  - Falta importar clase Log en StorageController
  - Error tipográfico en comentario
  - Espacio extra en alert-type
- 🟡 Problemas de Seguridad
  - SQL Injection en consultas RAW (vulnerabilidad crítica)
- 🟠 Mejoras de Funcionalidad
  - Falta validación en AjaxController::personStore
  - Falta manejo de errores cuando persona no existe
  - Falta validación de campos obligatorios
- 🟢 Optimizaciones
  - No hay límite de tamaño para imágenes
  - No hay validación de formatos de datos
  - Falta caché de consultas frecuentes
- 🔴 Faltas Funcionales
  - No hay sistema de notificaciones
  - No hay API REST completa
  - No hay tests unitarios
  - No hay sistema de backups automáticos
  - No hay sistema de colas para procesos pesados
  - No hay documentación de código PHP
- 📊 Resumen de problemas (tabla)
- 🎯 Prioridad de solución (cronograma)
- 📍 Ubicación de archivos para modificación
- 🔍 Recomendaciones adicionales
- 📝 Conclusión

---

### 15. Plan de Ejecución
**Guía paso a paso para solucionar todas las tareas**

- 📅 FASE 1: Bugs Críticos (HOY - Día 1)
  - Tarea 1.1: Agregar método hasRole() al modelo User
  - Tarea 1.2: Agregar método hasPermission() al modelo User
  - Tarea 1.3: Corregir error tipográfico COALESCE en AjaxController
  - Tarea 1.4: Corregir SQL Injection en UserController
  - Tarea 1.5: Corregir SQL Injection en RoleController
  - Tarea 1.6: Corregir SQL Injection en AjaxController
  - Tarea 1.7: Importar clase Log en StorageController
  - Tarea 1.8: Corregir espacio extra en UserController
  - Tarea 1.9: Corregir error tipográfico en comentario
  - Verificación de FASE 1
- 📅 FASE 2: Mejoras de Funcionalidad (Día 2-3)
  - Tarea 2.1: Agregar validación en AjaxController::personStore
  - Tarea 2.2: Mejorar manejo de errores en UserController::store
  - Tarea 2.3: Agregar validación completa en PersonController::store
  - Tarea 2.4: Agregar validación en PersonController::update
  - Verificación de FASE 2
- 📅 FASE 3: Optimizaciones y Seguridad (Día 4-5)
  - Tarea 3.1: Mejorar seguridad de contraseñas
  - Tarea 3.2: Implementar rate limiting
  - Tarea 3.3: Agregar caché de consultas frecuentes
  - Verificación de FASE 3
- 📅 FASE 4: Faltas Funcionales (Semana 2-3)
  - Tarea 4.1: Crear sistema de backups automáticos
  - Tarea 4.2: Crear sistema de colas para imágenes
  - Tarea 4.3: Agregar PHPDoc a métodos principales
  - Verificación de FASE 4
- 📅 FASE 5: Mejoras a Largo Plazo (Mes 2-3)
  - Tarea 5.1: Implementar sistema de notificaciones
  - Tarea 5.2: Implementar API REST completa
  - Tarea 5.3: Implementar tests unitarios
  - Verificación de FASE 5
- 📊 Resumen del plan (tabla)
- 🎯 Checklist de ejecución
- 🔄 Comandos de rollback
- 📝 Notas importantes
- 🚀 Comandos rápidos de verificación

---

## Estructura del Directorio de Documentación

```
docs/documentar/
├── 00-README.md                       # Documentación general
├── 01-modelos.md                      # Modelos de datos
├── 02-controladores.md                # Controladores
├── 03-rutas.md                        # Rutas del sistema
├── 04-middleware.md                   # Middleware personalizados
├── 05-vistas.md                       # Vistas personalizadas
├── 06-migraciones.md                  # Migraciones de BD
├── 07-configuracion.md                # Configuraciones
├── 08-traits.md                       # Traits reutilizables
├── 09-bread.md                        # Sistema BREAD de Voyager
├── 10-logs.md                         # Sistema de logging
├── 11-indice.md                       # Este archivo
├── 12-diagramas.md                    # Diagramas de arquitectura
├── 13-docker.md                       # Configuración de Docker
├── 14-analisis-bugs-mejoras.md        # Análisis de bugs y mejoras
├── 15-plan-ejecucion.md               # Plan de ejecución paso a paso
└── 17-plan-dockerfile.md          # Plan de ejecución para Dockerfile (NUEVO)
```

---

## Convenciones de Documentación

### Formato Markdown
- Títulos: `# Título principal`, `## Título secundario`, `### Subtítulo`
- Código: \`\`\`php para bloques de código
- Tablas: Formato estándar de Markdown
- Listas: Guiones `-` o números `1.`
- Enlaces: `[texto](url)`

### Estructura de Cada Documento
1. **Introducción** - Descripción general
2. **Componentes** - Elementos principales
3. **Funcionalidad** - Cómo funciona
4. **Ejemplos** - Ejemplos de uso
5. **Notas Importantes** - Consideraciones especiales

---

## Referencias Cruzadas

### En Modelos (01)
- Ver: Migraciones (06) para estructura de BD
- Ver: Traits (08) para auditoría
- Ver: BREAD (09) para configuración BREAD

### En Controladores (02)
- Ver: Rutas (03) para definición de rutas
- Ver: Modelos (01) para uso de modelos
- Ver: Middleware (04) para autorización

### En Rutas (03)
- Ver: Controladores (02) para lógica
- Ver: Middleware (04) para middlewares aplicados
- Ver: BREAD (09) para rutas BREAD

### En Middleware (04)
- Ver: Logs (10) para canal requests
- Ver: Configuración (07) para settings
- Ver: Rutas (03) para aplicación

### En Vistas (05)
- Ver: Controladores (02) para datos enviados
- Ver: BREAD (09) para vistas sobrescritas

### En Migraciones (06)
- Ver: Modelos (01) para uso de tablas
- Ver: Configuración (07) para BD

### En Configuración (07)
- Ver: Logs (10) para canales de logging
- Ver: Middleware (04) para settings del sistema

### En Traits (08)
- Ver: Modelos (01) para uso en modelos
- Ver: Logs (10) para auditoría dual

### En BREAD (09)
- Ver: Modelos (01) para tablas BREAD
- Ver: Vistas (05) para personalizaciones
- Ver: Controladores (02) para rutas BREAD

### En Logs (10)
- Ver: Middleware (04) para middleware Loggin
- Ver: Configuración (07) para canales de logging
- Ver: Traits (08) para auditoría dual

### En Docker (13)
- Ver: Configuración (07) para variables de entorno
- Ver: Migraciones (06) para base de datos en Docker
- Ver: Logs (10) para ver logs de contenedor

---

## Diagrama de Referencias

```
00-README.md
    ↓
├── 01-Modelos ────┬──→ 06-Migraciones
│                 │
│                 ├──→ 08-Traits
│                 │
│                 └──→ 09-BREAD
│
├── 02-Controladores ──┬──→ 03-Rutas
│                     │
│                     ├──→ 01-Modelos
│                     │
│                     └──→ 04-Middleware
│
├── 03-Rutas ─────────┬──→ 02-Controladores
│                     │
│                     ├──→ 04-Middleware
│                     │
│                     └──→ 09-BREAD
│
├── 04-Middleware ────┬──→ 10-Logs
│                     │
│                     ├──→ 07-Configuración
│                     │
│                     └──→ 03-Rutas
│
├── 05-Vistas ────────└──→ 09-BREAD
│
├── 06-Migraciones ───→ 01-Modelos
│
├── 07-Configuración ──┬──→ 10-Logs
│                     │
│                     └──→ 04-Middleware
│
├── 08-Traits ─────────┬──→ 01-Modelos
│                     │
│                     └──→ 10-Logs
│
├── 09-BREAD ──────────┬──→ 01-Modelos
│                     │
│                     ├──→ 05-Vistas
│                     │
│                     └──→ 02-Controladores
│
└── 10-Logs ───────────┬──→ 04-Middleware
                     │
                     ├──→ 07-Configuración
                     │
                     └──→ 08-Traits

└── 13-Docker ──────────┬──→ 07-Configuración
                      │
                      └──→ 06-Migraciones

└── 14-Análisis ────────┬──→ 01-Modelos
                      │
                      ├──→ 02-Controladores
                      │
                      ├──→ 04-Middleware
                      │
                      ├──→ 03-Rutas
                      │
                      ├──→ 13-Docker
                      │
                      └──→ Todos los módulos (cross-reference)

└── 15-Plan ───────────┬──→ 14-Análisis (implementación)
                      │
                      └──→ Todos los módulos (mejoras)

└── 16-Dockerfile ────┬──→ 13-Docker
                      │
                      ├──→ 14-Análisis (bugs y mejoras)
                      │
                      └──→ 01-Modelos (dependencias)
```

---

## Cómo Usar esta Documentación

### Para Desarrolladores Nuevos
1. Leer `00-README.md` para entender el sistema
2. Leer `13-Docker.md` para configurar el entorno Docker
3. Leer `01-Modelos.md` para entender la estructura de datos
4. Leer `02-Controladores.md` para entender la lógica
5. Leer `03-Rutas.md` para entender la navegación

### Para Mantenimiento y Mejoras
1. Leer `14-analisis-bugs-mejoras.md` para identificar problemas actuales
2. Leer `16-analisis-dockerfile.md` para identificar mejoras en Docker
3. Seguir la prioridad de solución indicada
4. Revisar ubicación de archivos para modificación
5. **Leer `15-plan-ejecucion.md` para ejecutar soluciones paso a paso**
6. Implementar soluciones sugeridas siguiendo el plan
7. **Leer `16-analisis-dockerfile.md` para optimizar Dockerfile**
8. Marcar tareas completadas en el checklist
9. Documentar cambios realizados

### Para Debugging
1. Consultar `10-Logs.md` para ver cómo verificar logs
2. Consultar `04-Middleware.md` para entender el flujo de peticiones
3. Consultar `02-Controladores.md` para entender la lógica
4. Revisar `14-analisis-bugs-mejoras.md` para bugs conocidos

### Para Modificaciones
1. Consultar el documento relevante según lo que se va a modificar
2. Seguir las referencias cruzadas para entender dependencias
3. Revisar las notas importantes para evitar problemas
4. Si modifica Dockerfile, ver `13-Docker.md` para entender la configuración

### Para Debugging
1. Consultar `10-Logs.md` para ver cómo verificar logs
2. Consultar `04-Middleware.md` para entender el flujo de peticiones
3. Consultar `02-Controladores.md` para entender la lógica

### Para Auditoría
1. Consultar `10-Logs.md` para logs de peticiones
2. Consultar `08-Traits.md` para auditoría de registros
3. Consultar `04-Middleware.md` para middleware de logging

---

## Actualización de la Documentación

### Cuándo Actualizar
- Al agregar nuevos modelos
- Al modificar controladores
- Al cambiar rutas
- Al modificar middleware
- Al agregar nuevas configuraciones
- Al actualizar Voyager
- **Al solucionar bugs** (actualizar documento 14)
- **Al implementar mejoras** (actualizar documento 14)
- **Al completar tareas del plan** (actualizar documento 15)

### Formato de Actualización
- Mantener la estructura existente
- Agregar nuevos documentos si es necesario
- Actualizar referencias cruzadas
- Actualizar este índice
- **Marcar bugs solucionados en documento 14**
- **Documentar nuevas mejoras en documento 14**
- **Marcar tareas completadas en documento 15**

---

## Soporte

Para más información sobre el sistema:
- Laravel: https://laravel.com/docs
- Voyager: https://voyager.readme.io/docs
- PHP: https://www.php.net/docs.php
- NGINX Unit: https://unit.nginx.org/
- Docker: https://docs.docker.com/

---

**Última actualización:** 2026-01-18
**Documentos:** 18 archivos
**Total de líneas:** 9,300+ líneas
**Total de tamaño:** ~310 KB
