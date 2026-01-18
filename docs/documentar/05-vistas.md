# Vistas Personalizadas del Sistema

## Estructura de Vistas

```
resources/views/
├── administrations/          # Vistas de administración personalizadas
│   └── people/              # Vistas de personas
│       ├── browse.blade.php # Listado principal
│       └── list.blade.php    # Lista AJAX
├── partials/                # Componentes reutilizables
│   ├── modal-delete.blade.php          # Modal de confirmación eliminación
│   └── modal-registerPerson.blade.php  # Modal de registro rápido de persona
└── vendor/voyager/          # Vistas sobrescritas de Voyager
    ├── users/               # Vistas personalizadas de usuarios
    │   ├── browse.blade.php
    │   ├── edit-add.blade.php
    │   └── list.blade.php
    ├── bread/               # Vistas sobrescritas de BREAD
    └── ...                  # Otras vistas de Voyager
```

---

## Vistas de Administración de Personas

### 1. browse.blade.php

**Ubicación:** `resources/views/administrations/people/browse.blade.php`

**Propósito:** Vista principal de listado de personas con búsqueda y paginación AJAX.

**Extiende:** `@extends('voyager::master')`

**Secciones:**

#### `@section('page_title')`
```
"Viendo Datos Personales"
```

#### `@section('page_header')`
- Título: "Datos Personales" con icono `voyager-person`
- Botón "Crear" (si tiene permiso `add_people`)

#### `@section('content')`

**Elementos:**
1. **Select de paginación:** Opciones 10, 25, 50, 100 registros
2. **Input de búsqueda:** Con debounce de 2 segundos
3. **Div de resultados:** `#div-results` (carga contenido vía AJAX)

**JavaScript:**
```javascript
var countPage = 10;      // Registros por página
var timeout = null;      // Timeout para debounce

// Eventos
$('#input-search').on('keyup', function(e) {
    if(e.keyCode == 13) { // Enter
        clearTimeout(timeout);
        list();
    }
});

$('#input-search').on('input', function() {
    clearTimeout(timeout);
    timeout = setTimeout(function() {
        list(); // 2 segundos de debounce
    }, 2000);
});

$('#select-paginate').change(function(){
    countPage = $(this).val();
    list();
});

// Función AJAX
function list(page = 1){
    $('#div-results').loading({message: 'Cargando...'});
    let url = `{{ url("admin/people/ajax/list") }}`;
    let search = $('#input-search').val() ? $('#input-search').val() : '';
    
    $.ajax({
        url: `${url}?search=${search}&paginate=${countPage}&page=${page}`,
        type: 'get',
        success: function(result){
            $("#div-results").html(result);
            $('#div-results').loading('toggle');
        }
    });
}
```

**Características:**
- Búsqueda en tiempo real con debounce
- Paginación dinámica
- Icono de carga mientras carga datos
- No recarga la página (SPA-like)

---

### 2. list.blade.php

**Ubicación:** `resources/views/administrations/people/list.blade.php`

**Propósito:** Vista parcial con tabla de personas para carga vía AJAX.

**Estructura de tabla:**

| Columna | Descripción |
|---------|-------------|
| ID | ID de la persona |
| CI/Pasaporte | Documento de identidad |
| Nombre completo | Foto + nombre completo concatenado |
| Fecha nac. | Fecha de nacimiento + edad calculada |
| Telefono/Celular | Número de teléfono |
| Estado | Label (Verde: Activo, Amarillo: Inactivo) |
| Acciones | Botones Ver, Editar, Eliminar |

**Lógica de imagen:**
```php
$image = asset('images/default.jpg');
if($item->image){
    $image = $item->image
        ? asset('storage/' . $item->image)
        : asset('images/default.jpg');
}
```

**Cálculo de edad:**
```php
$now = \Carbon\Carbon::now();
$birthday = new \Carbon\Carbon($item->birth_date);
$age = $birthday->diffInYears($now);
```

**Botones de acción (según permisos):**
- `read_people` - Botón Ver (amarillo)
- `edit_people` - Botón Editar (azul)
- `delete_people` - Botón Eliminar (rojo)

**Estado vacío:**
```blade
<img src="{{ asset('images/empty.png') }}" width="120px">
<br><br>
No hay resultados
```

**Paginación:**
```blade
<p class="text-muted">Mostrando del {{$data->firstItem()}} al {{$data->lastItem()}} de {{$data->total()}} registros.</p>
{{ $data->links() }}
```

**JavaScript para paginación:**
```javascript
$('.page-link').click(function(e){
    e.preventDefault();
    let url = new URL($(this).attr('href'));
    let page = url.searchParams.get('page') || 1;
    list(page);
});
```

---

## Partial Componentes

### 1. modal-delete.blade.php

**Ubicación:** `resources/views/partials/modal-delete.blade.php`

**Propósito:** Modal de confirmación para eliminación con observación obligatoria.

**Elementos:**
- Título: "¿Estás seguro que quieres eliminar?"
- Icono: `voyager-trash` grande en rojo
- Textarea: `deleteObservation` (obligatorio)
- Checkbox: Confirmación (requerido)
- Botones: Cancelar (default), Sí eliminar (danger)

**Formulario:**
```php
<form action="#" id="delete_form" method="POST">
    {{ method_field('DELETE') }}
    {{ csrf_field() }}
    <!-- Modal content -->
</form>
```

**JavaScript de uso:**
```javascript
function deleteItem(url){
    $('#delete_form').attr('action', url);
}
```

---

### 2. modal-registerPerson.blade.php

**Ubicación:** `resources/views/partials/modal-registerPerson.blade.php`

**Propósito:** Modal para registro rápido de persona.

**Formulario:**
- `first_name` (obligatorio)
- `middle_name` (opcional)
- `paternal_surname` (obligatorio)
- `maternal_surname` (opcional)
- `ci` / `NIT` (obligatorio)
- `phone` (opcional)
- `gender` (obligatorio, select con opciones)
- `birth_date` (opcional, date picker)
- `address` (opcional, textarea)

**Acción:** `POST /admin/ajax/person/store`

**Botones:**
- Cancelar (default)
- Guardar (primary)

---

## Vistas de Usuarios (Sobrescritas de Voyager)

### 1. browse.blade.php

**Ubicación:** `resources/views/vendor/voyager/users/browse.blade.php`

**Propósito:** Página principal de gestión de usuarios.

(Similar a browse de personas, pero para usuarios)

---

### 2. list.blade.php

**Ubicación:** `resources/views/vendor/voyager/users/list.blade.php`

**Propósito:** Tabla de usuarios para carga vía AJAX.

**Columnas:**
- ID
- Nombre (foto + nombre + CI si tiene persona)
- Email
- Role (nombre del rol)
- Estado (Label verde/amarillo)
- Acciones (Ver, Editar, Eliminar)

**Lógica de imagen:**
```php
$image = asset('images/default.jpg');
if($item->person->image){
    $image = asset('storage/'.str_replace('.', '-cropped.', $item->person->image));
}
```
Nota: Usa versión `cropped` de la imagen (cuadrada 300x300).

**Condición sin persona:**
```php
@if($item->person_id)
    <!-- Muestra datos de persona -->
@else
    {{$item->name}} <!-- Muestra nombre de usuario -->
@endif
```

---

## Vistas de BREAD (Sobrescritas)

Las siguientes vistas de Voyager han sido sobrescritas en `resources/views/vendor/voyager/bread/`:

- `index.blade.php` - Listado BREAD
- `edit-add.blade.php` - Formulario crear/editar
- `read.blade.php` - Vista detalle
- `browse.blade.php` - Tabla de datos
- `order.blade.php` - Ordenamiento

Estas vistas personalizan la apariencia y funcionalidad estándar de Voyager.

---

## Convenciones de Vistas

### Nomenclatura
- `{recurso}/browse.blade.php` - Página principal
- `{recurso}/list.blade.php` - Tabla AJAX
- `vendor/voyager/{recurso}/{vista}.blade.php` - Sobrescritura de Voyager
- `partials/{componente}.blade.php` - Componentes reutilizables

### Iconos de Voyager
- `voyager-person` - Persona
- `voyager-plus` - Agregar
- `voyager-eye` - Ver
- `voyager-edit` - Editar
- `voyager-trash` - Eliminar
- `voyager-` - Otros iconos de Voyager

### Colores de Bootstrap
- `btn-success` - Verde (crear)
- `btn-warning` - Amarillo (ver)
- `btn-primary` - Azul (editar)
- `btn-danger` - Rojo (eliminar)
- `label-success` - Verde (activo)
- `label-warning` - Amarillo (inactivo)

---

## Patrones de Uso

### Incluir Partial
```blade
@include('partials.modal-delete')
```

### Verificar Permisos
```blade
@if (auth()->user()->hasPermission('add_people'))
    <a href="{{ route('voyager.people.create') }}" class="btn btn-success">
        <i class="voyager-plus"></i> <span>Crear</span>
    </a>
@endif
```

### Mostrar Imagen con Fallback
```php
$image = asset('images/default.jpg');
if($item->image){
    $image = asset('storage/' . $item->image);
}
<img src="{{ $image }}">
```

### Bucle Forelse
```blade
@forelse ($data as $item)
    <!-- Item -->
@empty
    <!-- Sin resultados -->
@endforelse
```

### Cargar Imagen Cropped
```php
$image = asset('storage/'.str_replace('.', '-cropped.', $item->person->image));
```

---

## Notas Importantes

1. **AJAX sin recarga:** Las listas se cargan vía AJAX sin recargar la página (SPA-like).

2. **Debounce de búsqueda:** La búsqueda tiene un delay de 2 segundos para reducir peticiones.

3. **Permisos en vistas:** Los botones de acción se muestran/ocultan según permisos del usuario.

4. **Imágenes múltiples:** Se usan diferentes versiones de imágenes (original, cropped, etc.).

5. **Soft deletes:** Las vistas no eliminan físicamente, usan soft deletes.

6. **Modales reutilizables:** Los modales están en `partials/` para reutilización.

7. **Sobrescritura de Voyager:** Las vistas de Voyager se sobrescriben en `vendor/voyager/`.

8. **Iconos consistentes:** Se usan los iconos de Voyager para mantener consistencia visual.

9. **Labels de estado:** Los estados se muestran con labels de colores para mejor UX.

10. **Fecha en español:** Las fechas se formatean como `d/m/Y` para el locale español.
