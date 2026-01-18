# BREAD de Voyager

## Qué es BREAD

BREAD (Browse, Read, Edit, Add, Delete) es el sistema de CRUD de Voyager que permite generar interfaces administrativas para cualquier tabla de la base de datos.

---

## BREAD Configurados en el Sistema

### 1. People BREAD

**Tabla:** `people`

**Acciones BREAD:**
- **Browse:** Listado de personas (personalizado)
- **Read:** Ver detalle de persona
- **Edit:** Editar persona existente
- **Add:** Crear nueva persona
- **Delete:** Eliminar persona (soft delete)

**Permisos:**
- `browse_people` - Ver listado
- `read_people` - Ver detalle
- `edit_people` - Editar
- `add_people` - Crear
- `delete_people` - Eliminar

**Vistas Personalizadas:**
- `administrations.people.browse` - Listado principal
- `administrations.people.list` - Tabla AJAX

**Rutas:**
```php
GET /admin/people              # Browse (personalizado)
GET /admin/people/{id}         # Read
GET /admin/people/create       # Add
POST /admin/people             # Store
GET /admin/people/{id}/edit    # Edit
PUT /admin/people/{id}         # Update
DELETE /admin/people/{id}      # Delete
```

**Campos Principales:**
- `person_type` - Enum (Natural/Jurídica)
- `tipo_doc` - String
- `ci` - String
- `first_name` - String
- `middle_name` - String
- `paternal_surname` - String
- `maternal_surname` - String
- `legal_name` - String
- `birth_date` - Date
- `email` - Email
- `phone` - String
- `address` - Text
- `gender` - Enum
- `image` - Image
- `status` - Enum

---

### 2. Users BREAD

**Tabla:** `users` (extendida)

**Acciones BREAD:**
- **Browse:** Listado de usuarios (personalizado)
- **Read:** Ver detalle de usuario
- **Edit:** Editar usuario existente
- **Add:** Crear nuevo usuario
- **Delete:** Eliminar usuario (soft delete)

**Permisos:**
- `browse_users` - Ver listado
- `read_users` - Ver detalle
- `edit_users` - Editar
- `add_users` - Crear
- `delete_users` - Eliminar

**Vistas Personalizadas:**
- `vendor.voyager.users.browse` - Listado principal
- `vendor.voyager.users.list` - Tabla AJAX
- `vendor.voyager.users.edit-add` - Formulario

**Rutas:**
```php
GET /admin/users              # Browse (personalizado)
GET /admin/users/{id}         # Read
GET /admin/users/create       # Add
POST /admin/users             # Store
GET /admin/users/{id}/edit    # Edit
PUT /admin/users/{id}         # Update
DELETE /admin/users/{id}      # Delete
```

**Campos Principales:**
- `person_id` - Relation (People)
- `name` - String
- `email` - Email
- `password` - Password
- `role_id` - Relation (Roles)
- `avatar` - Image
- `status` - Enum

---

### 3. Roles BREAD

**Tabla:** `roles` (Voyager nativo)

**Acciones BREAD:**
- Browse, Read, Edit, Add, Delete (estándar de Voyager)

**Permisos:**
- `browse_roles` - Ver listado
- `read_roles` - Ver detalle
- `edit_roles` - Editar
- `add_roles` - Crear
- `delete_roles` - Eliminar

**Vistas:**
- Estándar de Voyager (no modificadas)

---

### 4. Permissions BREAD

**Tabla:** `permissions` (Voyager nativo)

**Acciones BREAD:**
- Browse, Read, Edit, Add, Delete (estándar de Voyager)

**Permisos:**
- `browse_permissions` - Ver listado
- `read_permissions` - Ver detalle
- `edit_permissions` - Editar
- `add_permissions` - Crear
- `delete_permissions` - Eliminar

**Vistas:**
- Estándar de Voyager (no modificadas)

---

### 5. Settings BREAD

**Tabla:** `settings` (Voyager nativo)

**Acciones:**
- Browse, Read, Edit (Add/Delete no aplicables en settings)

**Permisos:**
- `browse_settings` - Ver configuraciones
- `read_settings` - Ver configuración
- `edit_settings` - Editar configuraciones

**Settings del Sistema:**
- `configuracion.maintenance` - Modo mantenimiento
- `system.development` - Modo desarrollo
- `system.code-system` - Código de sistema (licencia)

---

## Permisos del Sistema

### Permisos de Personas

| Permiso | Descripción | Verificación |
|---------|-------------|--------------|
| `browse_people` | Ver listado de personas | `hasPermission('browse_people')` |
| `read_people` | Ver detalle de persona | `hasPermission('read_people')` |
| `edit_people` | Editar persona | `hasPermission('edit_people')` |
| `add_people` | Crear persona | `hasPermission('add_people')` |
| `delete_people` | Eliminar persona | `hasPermission('delete_people')` |

### Permisos de Usuarios

| Permiso | Descripción | Verificación |
|---------|-------------|--------------|
| `browse_users` | Ver listado de usuarios | `hasPermission('browse_users')` |
| `read_users` | Ver detalle de usuario | `hasPermission('read_users')` |
| `edit_users` | Editar usuario | `hasPermission('edit_users')` |
| `add_users` | Crear usuario | `hasPermission('add_users')` |
| `delete_users` | Eliminar usuario | `hasPermission('delete_users')` |

### Permisos de Roles

| Permiso | Descripción |
|---------|-------------|
| `browse_roles` | Ver listado de roles |
| `read_roles` | Ver detalle de rol |
| `edit_roles` | Editar rol |
| `add_roles` | Crear rol |
| `delete_roles` | Eliminar rol |

### Permisos de Settings

| Permiso | Descripción |
|---------|-------------|
| `browse_settings` | Ver configuraciones |
| `read_settings` | Ver configuración |
| `edit_settings` | Editar configuraciones |

---

## Crear BREAD en Voyager

### Desde el Panel de Administrativo

1. Ir a `/admin/bread`
2. Seleccionar tabla
3. Configurar campos
4. Definir permisos
5. Guardar

### Por Consola (Seeder)

```bash
php artisan db:seed --class=DataTypesTableSeeder
php artisan db:seed --class=DataRowsTableSeeder
```

---

## Tipos de Campos BREAD

Voyager soporta múltiples tipos de campos:

### Tipos Comunes

| Tipo | Descripción | Ejemplo en Sistema |
|------|-------------|-------------------|
| `text` | Campo de texto simple | `first_name` |
| `textarea` | Área de texto grande | `address` |
| `number` | Campo numérico | `ci` |
| `checkbox` | Casilla de verificación | `status` |
| `radio_btn` | Botones de radio | `gender` |
| `select_dropdown` | Lista desplegable | `gender` |
| `date` | Selector de fecha | `birth_date` |
| `time` | Selector de hora | - |
| `timestamp` | Fecha y hora | `created_at` |
| `image` | Subida de imagen | `image`, `avatar` |
| `file` | Subida de archivo | - |
| `rich_text_box` | Editor WYSIWYG | - |
| `code_editor` | Editor de código | - |
| `coordinates` | Coordenadas GPS | - |
| `relationship` | Relación con otra tabla | `person_id`, `role_id` |

---

## Campos Relacionales (Relationship)

### Person → Users
```php
// En BREAD de users
'type' => 'relationship',
'field' => 'person_id',
'controller' => '',
'relation_type' => 'belongsTo',
'model' => 'App\\Models\\Person',
'table' => 'people',
'type_select' => 'select',
'column' => 'first_name', // Campo a mostrar
```

### Users → Roles
```php
// En BREAD de users
'type' => 'relationship',
'field' => 'role_id',
'controller' => '',
'relation_type' => 'belongsTo',
'model' => 'TCG\\Voyager\\Models\\Role',
'table' => 'roles',
'type_select' => 'select',
'column' => 'display_name', // Campo a mostrar
```

---

## Vistas Sobrescritas de BREAD

Las siguientes vistas de BREAD han sido sobrescritas:

**Ubicación:** `resources/views/vendor/voyager/bread/`

1. **index.blade.php** - Listado BREAD
2. **edit-add.blade.php** - Formulario crear/editar
3. **read.blade.php** - Vista detalle
4. **browse.blade.php** - Tabla de datos
5. **order.blade.php** - Ordenamiento

---

## Menú de Navegación

El menú de navegación se configura en:
- Panel de Voyager: `/admin/menus`
- Tabla: `menus` y `menu_items`

**Ítems del sistema:**
- Dashboard
- Datos Personales (People)
- Usuarios
- Roles
- Permisos
- Configuraciones
- Menús
- Media
- Compass

---

## Seeders de BREAD

### DataTypesTableSeeder
Crea los tipos de datos BREAD:
- people
- users
- roles
- permissions
- settings
- etc.

### DataRowsTableSeeder
Crea las filas de datos (campos) para cada BREAD.

---

## Comandos de Artisan

### Listar BREAD
```bash
php artisan bread:list
```

### Exportar BREAD a Seeders
```bash
php artisan bread:export people
```

### Crear BREAD desde consola
```bash
php artisan bread:create people
```

---

## Notas Importantes

1. **Vistas Personalizadas:** Los BREAD de People y Users usan vistas personalizadas en lugar de las estándar de Voyager.

2. **Permisos:** Cada acción BREAD tiene un permiso asociado que se verifica en controladores y vistas.

3. **Soft Deletes:** Las acciones de "Delete" en BREAD ejecutan soft deletes, no eliminación física.

4. **Relaciones:** Los campos relacionales muestran selects con datos de tablas relacionadas.

5. **Validaciones:** Las validaciones de BREAD se configuran en Voyager y se ejecutan automáticamente.

6. **Imágenes:** Los campos de imagen usan el uploader de Voyager que integra con StorageController.

7. **Status Fields:** Los campos de status usan checkboxes o selects según el tipo de dato.

8. **Auditoría:** Los campos de auditoría (`registerUser_id`, `deleteUser_id`, etc.) se registran automáticamente por el trait.

9. **AJAX:** Los listados de People y Users usan AJAX para carga dinámica sin recargar la página.

10. **BREAD Tools:** Las herramientas de BREAD de Voyager permiten crear/eliminar campos sin necesidad de migraciones manuales.
