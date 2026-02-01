# 👥 Módulo de Gestión de Personas - Documentación Técnica (Código 3026)

## 1. Descripción General
El Módulo de Personas es el núcleo de identidad del sistema. Permite la gestión centralizada de individuos (Personas Naturales) y organizaciones (Personas Jurídicas), integrando validación dinámica, carga de archivos biométricos y una interfaz asíncrona (AJAX) para optimizar el rendimiento.

---

## 2. Arquitectura de Rutas (Endpoints)
Todas las rutas están protegidas bajo los middlewares de auditoría y sistema.

| Método | Endpoint | Acción | Descripción |
| :--- | :--- | :--- | :--- |
| GET | `/admin/people` | `index` | Contenedor principal de la vista. |
| GET | `/admin/people/ajax/list` | `list` | Retorno de fragmento HTML para carga asíncrona. |
| POST | `/admin/people` | `store` | Creación y validación de registros. |
| GET | `/admin/people/{id}` | `show` | Visualización detallada de datos. |
| PUT | `/admin/people/{id}` | `update` | Actualización de datos e imágenes. |
| DELETE | `/admin/people/{id}` | `destroy` | Eliminación lógica/física del registro. |

---

## 3. Lógica de Negocio (Controlador & Modelos)
El sistema implementa una **Sintonía de Identidad Dual**:
- **Personas Naturales:** Valida campos como `first_name`, `paternal_surname`, `birth_date` y `gender`.
- **Personas Jurídicas:** Valida y requiere `legal_name` y `nit`.

**Estados del Registro:**
1. **Activo:** Registro habilitado.
2. **Inactivo:** Registro suspendido.
3. **Pendiente:** Requiere revisión técnica.

---

## 4. Capa de Interfaz (Frontend)

### 4.1 Vista Dinámica (Edit/Add)
Utiliza jQuery para la conmutación de campos en tiempo real:
- Si el `person_type` cambia a **Jurídica**, los campos de nombre personal se ocultan para dar prioridad a la Razón Social y el NIT.

### 4.2 Listado Asíncrono (AJAX)
La tabla principal se actualiza sin recargar la página mediante la interceptación del evento click en los enlaces de paginación de Laravel:

```javascript
$('.page-link').click(function(e){
    e.preventDefault();
    list(page); // Función de recarga parcial
});
```

---

## 5. Seguridad y Permisos (RBAC)
La integridad del laboratorio se mantiene mediante Laravel Policies.
- **Permissions:** `browse_people`, `read_people`, `edit_people`, `add_people`, `delete_people`.
- **Middleware:** `system` y `loggin` aseguran que solo personal autorizado y sintonizado pueda manipular los datos.

---

## 6. Manejo de Archivos
- **Almacenamiento:** Carpeta `public/storage/people/`.
- **Fallback:** Si no hay imagen, se renderiza `images/default.jpg`.
- **Procesamiento:** La imagen se vincula al registro y se elimina la anterior en caso de actualización para optimizar el espacio en disco.

---

## 🔧 Implementación de Mejoras (Código 3026)

### 📍 Ubicación 1: El Corazón de los Datos
**Archivo:** `app/Models/Person.php`

Aquí solucionaremos el **Punto 2 (Casting de fechas)** y el **Punto 3 (Nombre Inteligente)**.
- **Busca la propiedad `$casts`:** Asegúrate de que las fechas sean tratadas como objetos y no como simples textos.
- **Añade el Accessor:** Esto garantiza que en las vistas, al llamar a `full_name`, el sistema decida automáticamente si mostrar el nombre de la empresa o de la persona.

### 📍 Ubicación 2: La Lógica de Control
**Archivo:** `app/Http/Controllers/PersonController.php`

Aquí solucionaremos el **Punto 4 (Limpieza de disco)**.
En el método `update`: Antes de procesar la nueva imagen, añade una validación para borrar la antigua:

```php
if ($request->hasFile('image') && $person->image) {
    Storage::disk('public')->delete($person->image);
}
```

### 📍 Ubicación 3: El Centro de Operaciones (Frontend)
**Archivo:** `resources/views/vendor/voyager/people/edit-add.blade.php`

Aquí solucionaremos el **Bug de los campos requeridos** y la **Paginación AJAX**.

**A. Para los campos requeridos:**
```javascript
if(type == 'Jurídica'){
    $('#natural_fields input').prop('required', false);
    $('#juridica_fields input').prop('required', true);
} else {
    $('#natural_fields input').prop('required', true);
    $('#juridica_fields input').prop('required', false);
}
```

**B. Para la Paginación (Delegación de eventos):**
```javascript
$(document).on('click', '.page-link', function(e) {
    e.preventDefault();
    // ... tu lógica de carga ...
});
```

### 🔍 Resumen de Solución Integral
- **Modelo:** Automatiza la lógica de nombres y fechas.
- **Controlador:** Gestiona el almacenamiento de forma limpia (sin archivos basura).
- **Vista:** Asegura que el formulario no se bloquee por campos ocultos y que la paginación no se "rompa" tras la primera carga.
