# 🗳️ Módulo de Cargos - Documentación Técnica

Este documento detalla la estructura, lógica y componentes del módulo de **Cargos Electivos** dentro del sistema electoral. Este módulo define la jerarquía de los puestos en disputa y las reglas para el tratamiento de sus actas.

---

## 1. Infraestructura de Datos (Base de Datos)

La tabla `cargos` utiliza tipos de datos optimizados para minimizar el consumo de almacenamiento y maximizar la velocidad de indexación.

- **Tabla:** `cargos`
- **Llave Primaria:** `id_cargo` (tipo `tinyIncrements`, soporta hasta 255 cargos).
- **Timestamps:** Desactivados (Tabla de configuración estática).

| Campo | Tipo | Descripción |
| :--- | :--- | :--- |
| `id_cargo` | `unsigned tinyint` | Identificador único autoincremental. |
| `descripcion` | `varchar(60)` | Nombre descriptivo del cargo (Ej: Gobernador). |
| `nivel` | `enum('D', 'P', 'M')` | Jerarquía: (D)epartamental, (P)rovincial, (M)unicipal. |
| `tipo_acta` | `enum` | Comportamiento del acta: Normal o Especial. |
| `acta_unica` | `boolean` | Determina si el cargo se consolida en un solo documento. |



---

## 2. Arquitectura del Backend (Laravel)

### 🧬 Modelo Eloquent (`App\Models\Cargo`)
El modelo centraliza la lógica de datos y las relaciones con el resto del ecosistema electoral:
- **Relaciones:**
  - `candidatos()`: Relación 1:N (Un cargo agrupa múltiples candidatos).
  - `actasEscrutinio()`: Relación con los documentos físicos de votación.
  - `resumenVotos()`: Relación con la tabla de consolidación de resultados finales.
- **Casting:** El campo `acta_unica` se maneja como un tipo lógico `boolean` nativo en PHP.

### 🕹️ Controlador (`CargoController`)
Gestiona el ciclo de vida del recurso mediante un CRUD asíncrono.
- **Integridad Referencial:** El sistema prohíbe la eliminación de cargos que tengan candidatos asociados para evitar orfandad de datos.
- **Búsqueda Dinámica:** Permite filtrar registros por `descripcion` y `nivel`.

### 🛡️ Validación y Seguridad
- **FormRequests:** Se utilizan `StoreCargoRequest` y `UpdateCargoRequest` para asegurar que los datos cumplen con los tipos de la DB.
- **Policies:** Acceso controlado mediante el sistema de permisos RBAC:
  - `browse_cargos`, `read_cargos`, `add_cargos`, `edit_cargos`, `delete_cargos`.

---

## 3. Definición de Rutas (Endpoints)

Las rutas están protegidas por una doble capa de middleware para auditoría y control del sistema.

- **Middleware:** `loggin` (Auditoría), `system` (Estado del sistema).
- **Prefijo:** `admin/cargos`

| Método | URI | Nombre de Ruta | Acción |
| :--- | :--- | :--- | :--- |
| GET | `/` | `admin.cargos.index` | Vista principal. |
| GET | `/ajax/list` | `admin.cargos.ajax.list` | Carga asíncrona (AJAX). |
| POST | `/` | `admin.cargos.store` | Guardar registro. |
| GET | `/{id}/edit` | `admin.cargos.edit` | Formulario de edición. |
| PUT | `/{id}` | `admin.cargos.update` | Actualizar registro. |
| DELETE | `/{id}` | `admin.cargos.destroy` | Eliminar registro. |

---

## 4. Frontend e Interfaz (Voyager)

El módulo utiliza vistas **Blade** personalizadas que extienden del maestro de Voyager, con una arquitectura orientada a la fluidez.

- **Browse (`browse.blade.php`):** Contenedor principal con buscador AJAX y lógica de modales.
- **List Partial (`list.blade.php`):** Tabla dinámica inyectada vía JavaScript. Incluye paginación y estadísticas de registros.
- **Formulario (`edit-add.blade.php`):** Interfaz polimórfica que adapta campos y métodos según se trate de creación o edición.

### 🎨 Código Visual (Labels)
- **Nivel (D, P, M):** Siempre Celeste (`label-info`).
- **Tipo Acta:** Azul (`Normal`) o Naranja (`Especial`).
- **Acta Única:** Verde (`Sí`) o Rojo (`No`).

---

## 5. Datos Maestros (Seeders)
Para inicializar el laboratorio electoral, se incluye el `CargoSeeder` con los siguientes valores base:
1. **Gobernador** (Nivel D)
2. **Asambleísta Departamental** (Nivel D)
3. **Alcalde Municipal** (Nivel M)
4. **Concejal Municipal** (Nivel M)

---

## 🛠️ Especificaciones de Ingeniería Aplicadas

### Patrones de Diseño
- **Trait-Based CRUD:** El uso de `ManagesCrud` permite una escalabilidad lineal, facilitando la creación de nuevos módulos administrativos en minutos.
- **Polymorphic Forms:** El formulario `edit-add` actúa como un componente inteligente que detecta el estado del modelo para mutar su comportamiento (POST/PUT).

### Integridad y Auditoría
- **Restricción de Cascada Inversa:** El sistema implementa un bloqueo lógico en el método `destroy`. Se garantiza la persistencia histórica prohibiendo la eliminación de cargos que ya tengan registros electorales (Candidatos) vinculados.
- **Auditoría Transversal:** Mediante el middleware `loggin`, cada interacción con el módulo de cargos queda registrada para trazabilidad forense.

### Optimización de Frontend
- **Arquitectura desacoplada:** El uso de `admin.partials.list-browse-script` separa la lógica de presentación de la lógica de obtención de datos, permitiendo actualizaciones de la tabla sin pérdida de estado en los filtros de búsqueda.


## 🛠️ Consideraciones de Mantenimiento

### Optimización de Consultas
- **Encapsulamiento de Cláusulas:** Las búsquedas utilizan grupos de parámetros para asegurar que los filtros `OR` no interfieran con la seguridad global de la consulta (E.g., soft deletes o estados activos).
- **Control de Concurrencia (AJAX):** El sistema implementa la cancelación de peticiones pendientes para evitar inconsistencias visuales cuando el usuario navega rápidamente entre páginas o realiza búsquedas consecutivas.

### Escalabilidad de Datos
- **Límite de Tipos de Cargo:** La arquitectura utiliza `tinyIncrements` (1 byte), optimizando el almacenamiento para hasta 255 tipos de cargos. Para implementaciones de gran escala, se recomienda migrar a `smallIncrements` (2 bytes / 65,535 registros).

---

## 🔧 Implementación de Mejoras (Código 3026)

### 1. Corregir el Bug del "Or" (Búsqueda Encapsulada)
**Ubicación:** `app/Http/Controllers/CargoController.php`

Busca el método `applySearch` y reemplázalo por este:

```php
protected function applySearch($query, $search)
{
    return $query->where(function ($q) use ($search) {
        $q->where('descripcion', 'like', "%$search%")
          ->orWhere('nivel', 'like', "%$search%");
    });
}
```
**Por qué:** Esto asegura que si el sistema añade filtros de seguridad (como ver solo cargos de una gestión específica), el `OR` no ignore esas reglas.

### 2. Evitar "Race Conditions" en AJAX (Abortar peticiones)
**Ubicación:** `resources/views/admin/partials/list-browse-script.blade.php`

Modifica el inicio del script y la función `list`:

```javascript
let currentRequest = null; // <-- Añadir esta línea al inicio del script

function list(page = 1) {
    const search = $('#search').val()?.trim() || '';

    let urlParams = new URLSearchParams({
        search: search,
        paginate: countPage,
        page: page
    });

    // --- NUEVO: Abortar petición previa si existe ---
    if (currentRequest) {
        currentRequest.abort();
    }

    $('#list-container').html(`
        <div class="text-center" style="padding: 40px">
            <i class="voyager-refresh voyager-2x voyager-spin"></i><br>Cargando...
        </div>
    `);

    // --- Guardar la petición en la variable ---
    currentRequest = $.ajax({
        url: `${listUrl}?${urlParams.toString()}`,
        type: 'GET',
        success: response => {
            $('#list-container').html(response);
            const event = new CustomEvent('list-loaded');
            document.dispatchEvent(event);
            currentRequest = null; // Liberar al terminar
        },
        error: (xhr) => {
            if (xhr.statusText !== 'abort') { // No mostrar error si fue cancelada a propósito
                console.error('Error al cargar la lista:', xhr);
                $('#list-container').html(`<div class="alert alert-danger text-center">Error al cargar los datos.</div>`);
            }
        }
    });
}
```

### 3. Mejora de UX: "No Results Found" con botón de limpiar
**Ubicación:** `resources/views/admin/cargos/list.blade.php`

Busca el bloque `@empty` al final de la tabla y cámbialo por esto:

```blade
@forelse ($items as $cargo)
    {{-- ... tu código de filas ... --}}
@empty
    <tr>
        <td colspan="6" class="text-center" style="padding: 30px;">
            <div class="text-muted">
                <i class="voyager-search" style="font-size: 40px;"></i>
                <p>No se encontraron cargos con los criterios de búsqueda.</p>
                <button class="btn btn-sm btn-info" onclick="$('#search').val('').trigger('input')">
                    Limpiar búsqueda
                </button>
            </div>
        </td>
    </tr>
@endforelse
```

### 4. Ajuste de Casting en el Modelo
**Ubicación:** `app/Models/Cargo.php`

Asegúrate de tener esto para que Laravel entienda siempre el tipo de dato:

```php
protected $casts = [
    'acta_unica' => 'boolean',
    'id_cargo' => 'integer',
];
```
