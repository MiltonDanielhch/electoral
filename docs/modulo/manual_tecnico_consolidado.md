# 📘 Manual Técnico Consolidado: Sistema de Gestión Electoral (Sintonía 3026)

Este documento unifica la documentación técnica de los módulos del sistema electoral, incluyendo especificaciones de base de datos, lógica de negocio, interfaces y reportes de mejoras (Código 3026).

---

## 👥 1. Módulo de Gestión de Personas (00-people)

### Descripción General
El Módulo de Personas es el núcleo de identidad del sistema. Permite la gestión centralizada de individuos (Personas Naturales) y organizaciones (Personas Jurídicas), integrando validación dinámica, carga de archivos biométricos y una interfaz asíncrona (AJAX) para optimizar el rendimiento.

### Arquitectura de Rutas (Endpoints)
Todas las rutas están protegidas bajo los middlewares de auditoría y sistema.

| Método | Endpoint | Acción | Descripción |
| :--- | :--- | :--- | :--- |
| GET | `/admin/people` | `index` | Contenedor principal de la vista. |
| GET | `/admin/people/ajax/list` | `list` | Retorno de fragmento HTML para carga asíncrona. |
| POST | `/admin/people` | `store` | Creación y validación de registros. |
| GET | `/admin/people/{id}` | `show` | Visualización detallada de datos. |
| PUT | `/admin/people/{id}` | `update` | Actualización de datos e imágenes. |
| DELETE | `/admin/people/{id}` | `destroy` | Eliminación lógica/física del registro. |

### Lógica de Negocio (Controlador & Modelos)
El sistema implementa una **Sintonía de Identidad Dual**:
- **Personas Naturales:** Valida campos como `first_name`, `paternal_surname`, `birth_date` y `gender`.
- **Personas Jurídicas:** Valida y requiere `legal_name` y `nit`.

**Estados del Registro:**
1. **Activo:** Registro habilitado.
2. **Inactivo:** Registro suspendido.
3. **Pendiente:** Requiere revisión técnica.

### Capa de Interfaz (Frontend)
#### Vista Dinámica (Edit/Add)
Utiliza jQuery para la conmutación de campos en tiempo real:
- Si el `person_type` cambia a **Jurídica**, los campos de nombre personal se ocultan para dar prioridad a la Razón Social y el NIT.

#### Listado Asíncrono (AJAX)
La tabla principal se actualiza sin recargar la página mediante la interceptación del evento click en los enlaces de paginación de Laravel.

### Seguridad y Permisos (RBAC)
La integridad del laboratorio se mantiene mediante Laravel Policies.
- **Permissions:** `browse_people`, `read_people`, `edit_people`, `add_people`, `delete_people`.
- **Middleware:** `system` y `loggin` aseguran que solo personal autorizado y sintonizado pueda manipular los datos.

### Manejo de Archivos
- **Almacenamiento:** Carpeta `public/storage/people/`.
- **Fallback:** Si no hay imagen, se renderiza `images/default.jpg`.
- **Procesamiento:** La imagen se vincula al registro y se elimina la anterior en caso de actualización para optimizar el espacio en disco.

### 🔧 Implementación de Mejoras (Código 3026)
1. **Modelo (`Person.php`):** Añadir casting de fechas y Accessor para `full_name` inteligente.
2. **Controlador (`PersonController.php`):** Implementar limpieza de disco (`Storage::delete`) al actualizar imágenes.
3. **Frontend (`edit-add.blade.php`):** Corregir validación de campos requeridos ocultos y delegación de eventos en paginación AJAX.

---

## 🗳️ 2. Módulo de Cargos (01-cargo)

### Infraestructura de Datos (Base de Datos)
La tabla `cargos` utiliza tipos de datos optimizados para minimizar el consumo de almacenamiento.

- **Tabla:** `cargos`
- **Llave Primaria:** `id_cargo` (tipo `tinyIncrements`, soporta hasta 255 cargos).
- **Timestamps:** Desactivados.

| Campo | Tipo | Descripción |
| :--- | :--- | :--- |
| `id_cargo` | `unsigned tinyint` | Identificador único autoincremental. |
| `descripcion` | `varchar(60)` | Nombre descriptivo del cargo (Ej: Gobernador). |
| `nivel` | `enum('D', 'P', 'M')` | Jerarquía: (D)epartamental, (P)rovincial, (M)unicipal. |
| `tipo_acta` | `enum` | Comportamiento del acta: Normal o Especial. |
| `acta_unica` | `boolean` | Determina si el cargo se consolida en un solo documento. |

### Arquitectura del Backend (Laravel)
#### Modelo Eloquent (`App\Models\Cargo`)
- **Relaciones:** `candidatos()`, `actasEscrutinio()`, `resumenVotos()`.
- **Casting:** `acta_unica` como `boolean`.

#### Controlador (`CargoController`)
- **Integridad Referencial:** Prohíbe eliminar cargos con candidatos asociados.
- **Búsqueda Dinámica:** Filtra por `descripcion` y `nivel`.

### Frontend e Interfaz (Voyager)
- **Browse:** Contenedor principal con buscador AJAX.
- **List Partial:** Tabla dinámica inyectada vía JS.
- **Código Visual:** Etiquetas de colores para Nivel, Tipo Acta y Acta Única.

### 🔧 Implementación de Mejoras (Código 3026)
1. **Búsqueda Encapsulada:** Corregir lógica `OR` en `applySearch` para respetar scopes globales.
2. **Race Conditions:** Abortar peticiones AJAX previas en `list-browse-script`.
3. **UX:** Mejorar estado vacío ("No Results Found") con botón de limpieza.
4. **Casting:** Asegurar `id_cargo` como integer y `acta_unica` como boolean en el modelo.

---

## 🏛️ 3. Módulo de Organizaciones Políticas (02-organizaciones-politicas)

### Infraestructura de Datos
- **Tabla:** `organizaciones_politicas`
- **Llave Primaria:** `id_partido` (`bigInt`).
- **Timestamps:** Desactivados.

| Campo | Tipo | Descripción |
| :--- | :--- | :--- |
| `codigo_tse` | `char(3)` | Código oficial único. |
| `nombre` | `varchar(100)` | Denominación completa. |
| `sigla` | `varchar(10)` | Abreviación técnica indexada. |
| `color_hex` | `char(7)` | Identidad visual (Ej: #FF0000). |
| `logo_url` | `varchar(255)` | Ruta del archivo de imagen. |
| `estado` | `enum` | Activo o Inactivo. |

### Arquitectura del Backend
#### Modelo (`App\Models\OrganizacionPolitica`)
- **Relaciones:** `candidatos()`, `votosXPartido()`, `resumenVotos()`.
- **Configuración:** `$primaryKey = 'id_partido'`.

#### Controlador (`OrganizacionPoliticaController`)
- **Gestión de Archivos:** Limpieza automática de logos antiguos en storage.
- **Protección:** Bloqueo de eliminación si existen candidatos vinculados.

### Frontend e Interfaz
- **Browse/List:** Experiencia SPA-like.
- **Visual:** Círculo cromático dinámico basado en `color_hex`.

### 🔧 Implementación de Mejoras (Código 3026)
1. **Bug de Búsqueda:** Encapsular cláusulas `OR` en `applySearch`.
2. **AJAX:** Control de aborto de peticiones para evitar inconsistencias.
3. **UX:** Estado vacío con botón de reset.
4. **Mantenimiento Disco:** Eliminación estricta de archivos físicos al actualizar logos.
5. **Casting:** Definir `id_partido` como integer.

---

## 🗺️ 4. Módulo de Geografías (03-geografia)

### Arquitectura de Datos
Estructura de **Auto-Relación** para jerarquía territorial (DPTO > PROV > MUN).

- **Tabla:** `geografias`
- **Campos Clave:** `id_geografia`, `codigo_tse`, `parent_id`, `nivel_jerarquico`, `latitud`, `longitud`.
- **Triggers:** Cálculo automático de `nivel_jerarquico` basado en el padre.

### Capa de Backend
#### Modelo (`Geografia.php`)
- **Accessors:** `contador_recintos` (agregación en cascada), `todos_los_recintos`.
- **Scopes:** `scopeDepartamentos()`, `scopeProvincias()`, `scopeMunicipios()`.

### Capa de Presentación (SIG)
- **Leaflet.js:** Integración para captura de coordenadas y visualización.
- **UX:** Carga asíncrona de listados y validación visual de jerarquía.

### 🔧 Implementación de Mejoras (Código 3026)
1. **Optimización N+1:** Implementar caché (`Cache::remember`) para `contador_recintos`.
2. **Integridad SQL:** Agregar `onDelete('cascade')` e índices espaciales en `geografias_limites`.
3. **API Throttle:** Ajustar límites de tasa basados en usuario autenticado en lugar de IP.
4. **Geofencing:** Filtrar coordenadas fuera de los límites lógicos del Beni en `MapaController`.
5. **Validación Recursiva:** Impedir que un territorio sea su propio padre.

---

## 🗺️ 5. Motor de Resultados y Cartografía Vectorial (04-geo)

### Capa de Persistencia Geométrica (`geografias_limites`)
- **Almacenamiento:** Tabla independiente para polígonos GeoJSON.
- **Campos:** `geojson` (Geometry/JSON), `centro_latitud`, `centro_longitud`.

### Motor de Inteligencia (`ResultsController`)
- **Caché:** Estrategia de ventanas temporales (`Y-m-d-H`) para alta disponibilidad.
- **Agregación:** Procesamiento en una sola pasada de Eloquent.

### Motor de Visualización SIG (`MapaController`)
- **Coropletas:** Coloreado dinámico de zonas según el ganador.
- **Identidad Visual:** Colores políticos estandarizados (MAS: Verde, CC: Azul, FPV: Amarillo).

### API de Servicios
- `/api/mapas/geojson`: Polígonos.
- `/api/v1/acta`: Recepción de actas.
- `/api/mapas/resultados`: Datos de votos + Geometría.

### 🔧 Implementación de Mejoras (Código 3026)
1. **Optimización JSON:** Simplificación de polígonos para vistas generales.
2. **Caché de Resultados:** Reducir TTL a 60 segundos o usar invalidación por eventos.
3. **Seguridad API:** Rate limiting para endpoints públicos de resultados.
4. **Colores Dinámicos:** Vincular color directamente al modelo `OrganizacionPolitica` en lugar de hardcodear.
5. **TopoJSON:** Sugerencia de migración para reducir peso de transferencia.

---

## 🗳️ 6. Módulo de Recintos (05-recintos)

### Arquitectura del Sistema
La entidad `Recinto` es el punto de anclaje físico de las mesas.
- **Datos:** `id_recinto`, `codigo_tse`, coordenadas geográficas, relación con `Geografia`.

### Interfaces de Usuario (UX/UI)
- **Browse:** Micro-cartografía en listados (Mini-Mapa por fila).
- **Edit/Add:** Captura por clic en mapa, GPS o manual.
- **Mapa Geográfico:** Visualización de conjuntos con auto-ajuste (`fitBounds`).

### 🔧 Implementación de Mejoras (Código 3026)
1. **Modularización JS:** Crear clase global `SintoniaMap` para configuración de Leaflet.
2. **Geofencing (Backend):** Validación de coordenadas dentro del polígono del Beni en `StoreRecintoRequest`.
3. **Fix UI:** Solución al "Mapa Gris" mediante `invalidateSize()`.
4. **Clusterización:** Implementar `Leaflet.markercluster` para agrupar marcadores en mapas densos.
5. **Caché GeoJSON:** Cachear respuestas de API de mapas estáticos.

---

## 📑 7. Módulo de Mesas (06-mesas)

### Arquitectura de Datos
- **Tabla:** `mesas`
- **Campos:** `id_mesa`, `codigo_tse` (11 dígitos), `id_recinto`.
- **Estados:** Habilitada, Escrutada, Anulada, Observada.
- **Triggers:** `trg_mesas_validacion` para asegurar formato de 11 dígitos.

### Lógica de Negocio
- **Modelo:** Relaciones con `Recinto` y `ActaEscrutinio`. Scope `scopeHabilitadas`.
- **Controlador:** CRUD asíncrono con protección de eliminación si existen actas.

### Seguridad
- **Policies:** Permisos granulares (`browse`, `read`, `add`, `edit`, `delete`).
- **Rutas:** Protección por middleware de auditoría y throttling en API.

### 🔧 Reporte de Bugs y Mejoras Técnicas (Código 3026)
1. **Validación Inconsistente:** Alinear validación de `codigo_tse` en Request (regex 11 dígitos) con la DB.
2. **Campo Faltante:** Añadir `cantidad_electores` a la tabla `mesas` para cálculos de participación.
3. **Seeder:** Mejorar lógica de `MesaSeeder` para evitar duplicados lógicos o datos huérfanos.
4. **Optimización API:** Implementar `MesaResource` para controlar la carga de datos anidados y evitar JSON pesados.
5. **Expansión:** Preparar soporte para fotos de actas y dashboard de transmisión en tiempo real.

---

## ⚙️ 8. Núcleo de Reutilización: Trait `ManagesCrud` (20-trait)

### Funcionalidades Core
Trait diseñado para centralizar la lógica operativa de los controladores administrativos.
1. **Resolución de Modelos:** Instanciación dinámica.
2. **Inyección de Filtros:** Hook `applySearch` para búsquedas personalizadas.
3. **Carga de Relaciones:** Soporte para Eager Loading (`$with`).
4. **Paginación Adaptativa:** Gestión automática de respuestas parciales para AJAX.

### Capa de Interacción (JavaScript)
Script centralizado `list-browse-script.blade.php` para gestión asíncrona.
- **Debouncing:** Retraso en búsquedas.
- **Navegación Fluida:** Interceptación de clics en paginación.
- **Feedback:** Indicadores visuales de carga.
- **Funciones Clave:** `list(page)` para sincronización y `deleteItem(url, name)` para confirmaciones.
```
