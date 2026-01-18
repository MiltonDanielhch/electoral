# Controladores del Sistema

## Controladores Personalizados

### 1. PersonController (`app/Http/Controllers/PersonController.php`)

Controlador para gestión completa de personas.

**Constructor:**
```php
public function __construct()
{
    $this->middleware('auth');
}
```

**Métodos:**

#### `index()`
- **Ruta:** GET `/admin/people`
- **Permiso requerido:** `browse_people`
- **Descripción:** Muestra vista principal de listado de personas
- **Vista:** `administrations.people.browse`

#### `list()`
- **Ruta:** GET `/admin/people/ajax/list`
- **Descripción:** Retorna lista paginada de personas (para AJAX)
- **Parámetros:**
  - `search`: Texto de búsqueda (opcional)
  - `paginate`: Cantidad de registros por página (default: 10)
- **Búsqueda:** Busca en ID, CI, teléfono y nombres
- **Vista:** `administrations.people.list`

#### `store(Request $request)`
- **Ruta:** POST `/admin/people`
- **Permiso requerido:** `add_people`
- **Descripción:** Crea una nueva persona
- **Validación:** Imagen debe ser imagen válida (jpeg, jpg, png, bmp, webp)
- **Proceso:**
  1. Valida imagen
  2. Inicia transacción DB
  3. Almacena imagen en múltiples formatos
  4. Crea registro de persona
  5. Commit o rollback según resultado

#### `update(Request $request, $id)`
- **Ruta:** PUT `/admin/people/{id}`
- **Permiso requerido:** `edit_people`
- **Descripción:** Actualiza persona existente
- **Validación:** Imagen (si se proporciona)
- **Proceso:**
  1. Busca persona por ID
  2. Actualiza campos
  3. Si hay nueva imagen, la reemplaza
  4. Guarda cambios

---

### 2. UserController (`app/Http/Controllers/UserController.php`)

Controlador para gestión de usuarios.

**Métodos:**

#### `list()`
- **Ruta:** GET `/admin/users/ajax/list`
- **Descripción:** Lista usuarios con paginación
- **Filtros:**
  - Solo muestra usuarios no-admin al usuario no-admin
  - Busca por ID, nombre, email
- **Relaciones:** Carga relación `person`
- **Vista:** `vendor.voyager.users.list`

#### `store(Request $request)`
- **Ruta:** POST `/admin/users/store`
- **Descripción:** Crea nuevo usuario
- **Validaciones:**
  - Email único en sistema
  - Persona debe existir y estar activa
- **Proceso:**
  1. Verifica si email existe
  2. Busca persona activa
  3. Inicia transacción
  4. Crea usuario con hash de password
  5. Commit o rollback

#### `update(Request $request, $id)`
- **Ruta:** PUT `/admin/users/{id}`
- **Descripción:** Actualiza usuario existente
- **Campos actualizables:**
  - `status`: Activo/Inactivo
  - `role_id`: Rol del usuario
  - `password`: Nueva contraseña (si se proporciona)

#### `destroy(Request $request, $id)`
- **Ruta:** DELETE `/admin/users/{id}/deleted`
- **Descripción:** Elimina usuario (soft delete)
- **Proceso:**
  1. Busca usuario activo por ID
  2. Ejecuta soft delete
  3. Commit o rollback

---

### 3. StorageController (`app/Http/Controllers/StorageController.php`)

Controlador para gestión de almacenamiento de imágenes.

**Métodos:**

#### `store_image($file, $folder, $size = 1200)`
- **Descripción:** Almacena imagen en múltiples formatos y tamaños
- **Parámetros:**
  - `$file`: Archivo de imagen
  - `$folder`: Carpeta de destino
  - `$size`: Tamaño principal (default: 1200)
- **Proceso:**
  1. Valida archivo
  2. Crea directorio con formato: `{folder}/{FY}/`
  3. Genera nombre aleatorio
  4. Carga imagen original
  5. Genera versiones:
     - Original (tamaño personalizable)
     - Banner (900px ancho)
     - Medium (600px ancho)
     - Small (256px ancho)
     - Cropped (300x300px cuadrado)
  6. Convierte a formato AVIF (80% calidad)
  7. Retorna ruta de imagen original

**Formatos generados:**
```
{nombre}.{ext}
{nombre}-banner.{ext}
{nombre}-medium.{ext}
{nombre}-small.{ext}
{nombre}-cropped.{ext}
```

**Uso:**
```php
$storage = new StorageController();
$imagePath = $storage->store_image($request->image, 'people');
```

---

### 4. AjaxController (`app/Http/Controllers/AjaxController.php`)

Controlador para peticiones AJAX genéricas.

**Métodos:**

#### `personList()`
- **Ruta:** GET `/admin/ajax/personList`
- **Parámetro:** `q` (query de búsqueda)
- **Descripción:** Busca personas para selectores/autocompletados
- **Búsqueda:** CI, teléfono, nombres y combinaciones
- **Retorna:** JSON con lista de personas

#### `personStore(Request $request)`
- **Ruta:** POST `/admin/ajax/person/store`
- **Descripción:** Crea persona rápida vía AJAX
- **Retorna:** JSON con persona creada o error

---

### 5. RoleController (`app/Http/Controllers/RoleController.php`)

Controlador para gestión de roles.

**Métodos:**

#### `list()`
- **Ruta:** GET `/admin/roles/ajax/list`
- **Descripción:** Lista roles para selectores
- **Retorna:** JSON con lista de roles

---

### 6. ErrorController (`app/Http/Controllers/ErrorController.php`)

Controlador para manejo de errores personalizados.

**Métodos:**
- (Aún no implementados)

---

### 7. SolucionDigitalController (`app/Http/Controllers/SolucionDigitalController.php`)

Controlador para integración con sistema de licencias.

**Métodos:**

#### `settings_code()`
- **Descripción:** Obtiene configuración de licencia
- **Proceso:**
  1. Conecta a base de datos externa `solucionDigital`
  2. Busca registro en tabla `web_systems`
  3. Filtra por código del sistema
- **Retorna:** Objeto con datos de licencia o null

---

## Controller Base (`app/Http/Controllers/Controller.php`)

Controlador base con funcionalidades compartidas.

**Métodos:**

#### `custom_authorize($permission)`
- **Descripción:** Verifica permiso personalizado
- **Uso:**
```php
$this->custom_authorize('browse_people');
// Aborta con 403 si no tiene permiso
```

#### `payment_alert()`
- **Descripción:** Verifica estado de licencia de pago
- **Retorna:**
  - `'finalizado'`: Licencia vencida
  - `0-3`: Días restantes (si <= 3)
  - `'vigente'`: Sistema activo
  - `null`: Sin configuración o demo
- **Lógica:**
  - Si es tipo "Demo", no hay restricción
  - Compara fecha de vencimiento con fecha actual
  - Retorna días restantes si faltan 3 o menos

---

## Patrones Comunes

### Validación de Imágenes
```php
$request->validate([
    'image' => 'image|mimes:jpeg,jpg,png,bmp,webp'
]);
```

### Transacciones de Base de Datos
```php
DB::beginTransaction();
try {
    // Operaciones
    DB::commit();
    return redirect()->with(['message' => 'Éxito', 'alert-type' => 'success']);
} catch (\Throwable $th) {
    DB::rollback();
    return redirect()->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
}
```

### Verificación de Permisos
```php
if (auth()->user()->hasPermission('browse_people')) {
    // Permitir acción
}
```

### Búsqueda con Filtros
```php
$data = Model::query()
    ->when($search, function ($q) use ($search) {
        $q->where('campo', 'like', "%{$search}%");
    })
    ->paginate($paginate);
```

---

## Rutas de Controladores

Ver `03-rutas.md` para detalle completo de rutas.

---

## Notas Importantes

1. **Transacciones:** Todos los controladores que modifican datos usan transacciones para garantizar integridad.

2. **Validación:** Las validaciones se realizan en el controlador (no en el modelo).

3. **Auditoría:** Los modelos registran automáticamente quién crea/elimina gracias al trait `RegistersUserEvents`.

4. **Imágenes:** StorageController maneja todo el proceso de almacenamiento y conversión de imágenes.

5. **Permisos:** Se usan permisos BREAD de Voyager para autorización.

6. **AJAX:** Las rutas AJAX retornan JSON o vistas parciales para actualizaciones dinámicas.

7. **Soft Deletes:** Las eliminaciones son lógicas (soft deletes), no físicas.

8. **Sistema de Licencias:** Controller base incluye lógica para verificar estado de licencia externo.
