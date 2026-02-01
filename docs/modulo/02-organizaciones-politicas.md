# 🏛️ Módulo de Organizaciones Políticas - Documentación Técnica

Este documento detalla la estructura, lógica y componentes del módulo de **Organizaciones Políticas** dentro del sistema electoral. Este módulo gestiona las entidades legales, su identidad visual y su vinculación con el proceso de escrutinio.

---

## 1. Infraestructura de Datos (Base de Datos)

La tabla `organizaciones_politicas` utiliza tipos de datos optimizados para el manejo de recursos multimedia y búsqueda rápida por siglas oficiales.

- **Tabla:** `organizaciones_politicas`
- **Llave Primaria:** `id_partido` (tipo `bigInt`, autoincremental).
- **Timestamps:** Desactivados (Auditoría gestionada por middleware).

| Campo | Tipo | Descripción |
| :--- | :--- | :--- |
| `id_partido` | `bigInt` | Identificador único primario. |
| `codigo_tse` | `char(3)` | Código oficial único asignado por el Tribunal Electoral. |
| `nombre` | `varchar(100)` | Denominación completa de la organización. |
| `sigla` | `varchar(10)` | Abreviación técnica indexada para búsquedas. |
| `color_hex` | `char(7)` | Código hexadecimal de identidad visual (Ej: #FF0000). |
| `logo_url` | `varchar(255)` | Ruta del archivo de imagen en el storage público. |
| `estado` | `enum` | Estado operativo: Activo o Inactivo. |

---

## 2. Arquitectura del Backend (Laravel)

### 🧬 Modelo Eloquent (`App\Models\OrganizacionPolitica`)
El modelo actúa como el núcleo de verdad para la identidad de los partidos:
- **Relaciones:**
  - `candidatos()`: Relación 1:N (Una organización postula múltiples candidatos).
  - `votosXPartido()`: Relación con el detalle de votos por acta.
  - `resumenVotos()`: Relación con la tabla de consolidación de resultados.
- **Configuración:** Define `$primaryKey = 'id_partido'` para romper la convención estándar y alinearse con la base de datos optimizada.

### 🕹️ Controlador (`OrganizacionPoliticaController`)
Gestiona el ciclo de vida mediante el Trait `ManagesCrud`.
- **Gestión de Archivos:** Implementa lógica de limpieza de disco; al actualizar o eliminar un logo, el sistema borra el archivo anterior del storage para evitar saturación.
- **Protección de Integridad:** Bloquea la eliminación de organizaciones si existen candidatos vinculados.

### 🛡️ Validación y Seguridad
- **FormRequests:** `StoreOrganizacionPoliticaRequest` y `UpdateOrganizacionPoliticaRequest`.
- **Lógica de Unicidad:** El Update utiliza `Rule::unique()->ignore()` para permitir ediciones sin colisionar con el propio registro.
- **Policies:** Acceso restringido según roles; solo el `admin_electoral` puede ejecutar el borrado físico de organizaciones.

---

## 3. Definición de Rutas (Endpoints)

Las rutas están protegidas por una doble capa de middleware para auditoría de cambios y control de estado del sistema.

- **Middleware:** `loggin` (Auth/Auditoría), `system` (Logs de transacciones).
- **Prefijo:** `admin/organizaciones_politicas`

| Método | URI | Nombre de Ruta | Acción |
| :--- | :--- | :--- | :--- |
| GET | `/` | `admin.organizaciones_politicas.index` | Vista principal (Shell). |
| GET | `/ajax/list` | `admin.organizaciones_politicas.ajax.list` | Listado dinámico (AJAX). |
| POST | `/` | `admin.organizaciones_politicas.store` | Guardar registro y Logo. |
| GET | `/{id}/edit` | `admin.organizaciones_politicas.edit` | Formulario polimórfico. |
| PUT | `/{id}` | `admin.organizaciones_politicas.update` | Actualizar registro y Storage. |
| DELETE | `/{id}` | `admin.organizaciones_politicas.destroy` | Eliminación controlada. |

---

## 4. Frontend e Interfaz (Voyager)

El módulo implementa una experiencia SPA-like para agilizar la gestión de datos masivos.

- **Browse (`browse.blade.php`):** Contenedor asíncrono con buscador global.
- **List Partial (`list.blade.php`):** Tabla inyectada vía JS. Renderiza dinámicamente el color del partido y el estado.
- **Formulario (`edit-add.blade.php`):** Incluye previsualización en tiempo real del color hexadecimal y carga de archivos binarios.

### 🎨 Código Visual (Labels)
- **Identidad:** Círculo cromático dinámico basado en `color_hex`.
- **Estado Activo:** Verde (`label-success`).
- **Estado Inactivo:** Rojo (`label-danger`).

---

## 5. Especificaciones de Ingeniería Aplicadas

### Patrones de Diseño
- **Template Method:** Implementado a través de `ManagesCrud` para estandarizar el comportamiento de los controladores.
- **Polymorphism (UI):** La vista `edit-add` muta sus métodos (POST/PUT) y títulos según la existencia del modelo, reduciendo la redundancia de código.

### Análisis de Mejoras (Sintonía 3026)
- **Búsqueda Encapsulada:** Se ha corregido la lógica de filtrado para agrupar cláusulas `OR`, asegurando que el filtro por nombre o sigla no ignore los estados de seguridad de la consulta.
- **Control de Race Conditions:** El script de carga AJAX ahora aborta peticiones previas si el usuario realiza búsquedas rápidas consecutivas, evitando inconsistencias en el renderizado de la lista.
- **Optimización de UX:** El listado incluye un estado vacío (`@empty`) con un botón de "Limpiar búsqueda" que resetea el estado del componente sin recargar la página.

---

## 🛠️ Consideraciones de Mantenimiento

### Gestión de Memoria y Rendimiento
- **Eager Loading:** Se recomienda el uso de `with()` en consultas masivas para evitar el problema de $N+1$ al recuperar relaciones de votos en el Dashboard.
- **Storage Link:** Es imperativo que el comando `php artisan storage:link` esté ejecutado en producción para la correcta visualización de los logos institucionales.

### Escalabilidad
- **Identificadores:** El uso de `bigInt` para `id_partido` garantiza que el sistema pueda escalar a niveles nacionales o regionales sin riesgo de desbordamiento de enteros, soportando hasta $9.22 \times 10^{18}$ registros.

> **Código 3026:** Sintonía de Documentación Finalizada.

---

## 🔧 Implementación de Mejoras (Código 3026)

### ✅ 1. Corrección del Bug de Búsqueda (Cláusulas Agrupadas) - COMPLETADO
**Ubicación:** `app/Http/Controllers/OrganizacionPoliticaController.php`

**Estado:** ✅ Implementado el 2026-02-01

El buscador anterior usaba `orWhere` de forma plana, lo que podía romper filtros globales de seguridad. Ahora está encapsulado en una función anónima para asegurar la lógica: `WHERE (condiciones de seguridad) AND (nombre LIKE OR sigla LIKE)`.

```php
protected function applySearch(Builder $query, string $search): Builder
{
    return $query->where(function ($q) use ($search) {
        $q->where('nombre', 'like', "%$search%")
          ->orWhere('sigla', 'like', "%$search%")
          ->orWhere('codigo_tse', 'like', "%$search%");
    });
}
```

### ✅ 2. Evitar "Race Conditions" en la carga AJAX - COMPLETADO
**Ubicación:** `resources/views/admin/partials/list-browse-script.blade.php`

**Estado:** ✅ Implementado el 2026-02-01

El script ahora incluye control de aborto de peticiones previas cuando el usuario realiza búsquedas rápidas consecutivas, evitando inconsistencias en el renderizado.

```javascript
let currentRequest = null;

function list(page = 1) {
    if (currentRequest) {
        currentRequest.abort();
    }
    
    currentRequest = $.ajax({
        url: `${listUrl}?${urlParams.toString()}`,
        type: 'GET',
        success: response => {
            $('#list-container').html(response);
            currentRequest = null;
        },
        error: (xhr) => {
            if (xhr.statusText !== 'abort') {
                console.error('Error al cargar la lista:', xhr);
            }
        }
    });
}
```

### ✅ 3. Mejora de UX: Estado Vacío y Reset - COMPLETADO
**Ubicación:** `resources/views/admin/organizaciones_politicas/list.blade.php`

**Estado:** ✅ Implementado el 2026-02-01

El listado ahora incluye un estado vacío mejorado con icono visual y botón de "Limpiar filtros" que resetea el estado del componente sin recargar la página.

```blade
@empty
    <tr>
        <td colspan="7" class="text-center" style="padding: 40px;">
            <div class="text-muted">
                <i class="voyager-search" style="font-size: 50px; margin-bottom: 10px;"></i>
                <p>No se encontraron organizaciones políticas con esos criterios.</p>
                <button class="btn btn-sm btn-info" onclick="$('#search').val('').trigger('input')">
                    <i class="voyager-refresh"></i> Limpiar filtros
                </button>
            </div>
        </td>
    </tr>
@endforelse
```

### ✅ 4. Seguridad de Archivos (Mantenimiento de Disco) - COMPLETADO
**Ubicación:** `app/Http/Controllers/OrganizacionPoliticaController.php` (Método `update`)

**Estado:** ✅ Implementado el 2026-02-01

La eliminación del logo viejo ahora verifica estrictamente que el archivo exista en el disco antes de intentar borrarlo, evitando errores y acumulación de archivos basura.

```php
if ($request->hasFile('logo_url')) {
    // Eliminar físico si existe la ruta y el archivo en disco
    if ($organizacion->logo_url && Storage::disk('public')->exists($organizacion->logo_url)) {
        Storage::disk('public')->delete($organizacion->logo_url);
    }
    $data['logo_url'] = $request->file('logo_url')->store('organizaciones/logos', 'public');
}
```

### ✅ 5. Optimización del Modelo (Casting) - COMPLETADO
**Ubicación:** `app/Models/OrganizacionPolitica.php`

**Estado:** ✅ Implementado el 2026-02-01

Se añadieron casts al modelo para asegurar que los IDs siempre se traten como enteros y no como strings al salir de la base de datos, mejorando la consistencia de tipos.

```php
protected $casts = [
    'id_partido' => 'integer',
    'estado'     => 'string',
];
```

### 📊 Resumen de Mantenimiento Aplicado

| Acción | Impacto | Nivel de Prioridad | Estado |
| :--- | :--- | :--- | :--- |
| **Agrupar Where** | Seguridad de Datos | Alta | ✅ Completado |
| **Abortar AJAX** | Rendimiento Frontend | Media | ✅ Completado |
| **Limpieza Storage** | Ahorro de Disco | Alta | ✅ Completado |
| **Botón Limpiar** | Usabilidad (UX) | Baja | ✅ Completado |
| **Casting de IDs** | Integridad de Datos | Media | ✅ Completado |

---

## 🔮 Mejoras Futuras Sugeridas (Backlog)

Basado en el análisis del código, se identificaron las siguientes mejoras adicionales para futuras iteraciones:

### 6. Sistema de Caché para Logos
**Impacto:** Rendimiento en carga de imágenes  
**Descripción:** Implementar caché de thumbnails para los logos usando Intervention Image para optimizar tiempos de carga en el frontend.

### 7. Soft Deletes para Organizaciones
**Impacto:** Recuperación de datos  
**Descripción:** Añadir `use SoftDeletes` al modelo para permitir recuperar organizaciones eliminadas accidentalmente sin perder el historial de votos asociados.

### 8. Validación de Imagen en Frontend
**Impacto:** UX/Validación temprana  
**Descripción:** Añadir validación JavaScript del tamaño y formato de imagen antes de enviar al servidor, reduciendo peticiones innecesarias.

### 9. API Rate Limiting
**Impacto:** Seguridad/Performance  
**Descripción:** Implementar rate limiting en el endpoint de listado AJAX para prevenir ataques de fuerza bruta o scraping.

### 10. Lazy Loading para Imágenes
**Impacto:** Performance Frontend  
**Descripción:** Implementar `loading="lazy"` en las etiquetas `<img>` de los logos para mejorar el tiempo de carga inicial de la página.

---

> **Última actualización:** 2026-02-01 - Sintonía de Documentación Finalizada con todas las mejoras implementadas.
