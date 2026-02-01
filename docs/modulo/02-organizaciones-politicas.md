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

### 1. Corrección del Bug de Búsqueda (Cláusulas Agrupadas)
**Ubicación:** `app/Http/Controllers/OrganizacionPoliticaController.php`

El buscador actual usa `orWhere` de forma plana, lo que puede romper filtros globales de seguridad. Debemos encapsularlos en una función anónima.

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
**Por qué:** Esto asegura que la lógica sea: `WHERE (condiciones de seguridad) AND (nombre LIKE OR sigla LIKE)`.

### 2. Evitar "Race Conditions" en la carga AJAX
**Ubicación:** `resources/views/admin/partials/list-browse-script.blade.php` (o donde residan tus scripts globales de lista).

Si el usuario escribe rápido, las peticiones se amontonan. Añade un controlador de aborto:

```javascript
let currentRequest = null; // Variable global al inicio del script

function list(page = 1) {
    // ... lógica de parámetros ...

    // ABORTAR petición previa si existe
    if (currentRequest) {
        currentRequest.abort();
    }

    // Guardar la nueva petición
    currentRequest = $.ajax({
        url: `${listUrl}?${urlParams.toString()}`,
        type: 'GET',
        success: response => {
            $('#list-container').html(response);
            currentRequest = null; 
        },
        error: (xhr) => {
            if (xhr.statusText !== 'abort') {
                console.error('Error en la carga');
            }
        }
    });
}
```

### 3. Mejora de UX: Estado Vacío y Reset
**Ubicación:** `resources/views/admin/organizaciones_politicas/list.blade.php`

Modifica el bloque `@empty` para dar una salida al usuario cuando no hay resultados:

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

### 4. Seguridad de Archivos (Mantenimiento de Disco)
**Ubicación:** `app/Http/Controllers/OrganizacionPoliticaController.php` (Método `update`)

Asegúrate de que la eliminación del logo viejo sea estricta para evitar "archivos basura" en el servidor.

```php
if ($request->hasFile('logo_url')) {
    // Eliminar físico si existe la ruta y el archivo en disco
    if ($organizacion->logo_url && Storage::disk('public')->exists($organizacion->logo_url)) {
        Storage::disk('public')->delete($organizacion->logo_url);
    }
    $data['logo_url'] = $request->file('logo_url')->store('organizaciones/logos', 'public');
}
```

### 5. Optimización del Modelo (Casting)
**Ubicación:** `app/Models/OrganizacionPolitica.php`

Añade los casts para asegurar que los IDs siempre se traten como enteros y no como strings al salir de la base de datos:

```php
protected $casts = [
    'id_partido' => 'integer',
    'estado'     => 'string',
];
```

### 📊 Resumen de Mantenimiento Aplicado

| Acción | Impacto | Nivel de Prioridad |
| :--- | :--- | :--- |
| **Agrupar Where** | Seguridad de Datos | Alta |
| **Abortar AJAX** | Rendimiento Frontend | Media |
| **Limpieza Storage** | Ahorro de Disco | Alta |
| **Botón Limpiar** | Usabilidad (UX) | Baja |
