# Traits del Sistema

## RegistersUserEvents Trait

**Archivo:** `app/Traits/RegistersUserEvents.php`

**Propósito:** Trait para registrar automáticamente eventos de creación y eliminación en modelos, guardando información de auditoría del usuario autenticado.

---

## Uso del Trait

### En Modelos

El trait se usa en los modelos principales del sistema:

```php
// app/Models/Person.php
class Person extends Model
{
    use HasFactory, RegistersUserEvents, SoftDeletes;
    // ...
}

// app/Models/User.php
class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, RegistersUserEvents;
    // ...
}
```

---

## Funcionalidad del Trait

### 1. Boot Method

El trait registra eventos automáticamente en el método `bootRegistersUserEvents()`:

```php
protected static function bootRegistersUserEvents()
{
    // Evento creating
    static::creating(function ($model) {
        if (Auth::check()) {
            $user = Auth::user();
            $model->registerUser_id = $user->id;
            $model->registerRole = $user->role->name;
        }
    });

    // Evento deleting
    static::deleting(function ($model) {
        if (Auth::check()) {
            $user = Auth::user();
            $model->deleteUser_id = $user->id;
            $model->deleteRole = $user->role->name;
            $model->deleteObservation = request()->input('deleteObservation');
            $model->save();
        }
    });
}
```

---

## Eventos Registrados

### Evento `creating`

**Cuándo se dispara:** Justo antes de insertar un nuevo registro en la base de datos.

**Funcionalidad:**
- Verifica si hay un usuario autenticado (`Auth::check()`)
- Si hay usuario, registra:
  - `registerUser_id` = ID del usuario autenticado
  - `registerRole` = Nombre del rol del usuario autenticado

**Ejemplo:**
```php
// Al crear una persona
$person = Person::create([
    'first_name' => 'Juan',
    'email' => 'juan@example.com',
]);

// El trait agrega automáticamente:
// $person->registerUser_id = 1 (ID del usuario actual)
// $person->registerRole = 'admin' (rol del usuario actual)
```

---

### Evento `deleting`

**Cuándo se dispara:** Justo antes de realizar un soft delete de un registro.

**Funcionalidad:**
- Verifica si hay un usuario autenticado
- Si hay usuario, registra:
  - `deleteUser_id` = ID del usuario autenticado
  - `deleteRole` = Nombre del rol del usuario autenticado
  - `deleteObservation` = Texto de observación del formulario de eliminación
- Guarda el registro con estos datos antes de eliminarlo

**Ejemplo:**
```php
// Al eliminar una persona
$person = Person::find(1);
$person->delete(); // Soft delete

// El trait agrega automáticamente:
// $person->deleteUser_id = 1 (ID del usuario actual)
// $person->deleteRole = 'admin' (rol del usuario actual)
// $person->deleteObservation = 'Motivo de eliminación...' (desde formulario)
```

---

## Campos de Auditoría

El trait espera que los modelos tengan los siguientes campos:

### Campos de Registro (Creating)
```php
$fillable = [
    'registerUser_id',  // FK → users.id
    'registerRole',     // string (nombre del rol)
    // ... otros campos
];
```

### Campos de Eliminación (Deleting)
```php
$fillable = [
    'deleted_at',        // timestamp (soft delete)
    'deleteUser_id',     // FK → users.id
    'deleteRole',        // string (nombre del rol)
    'deleteObservation', // text (motivo de eliminación)
    // ... otros campos
];
```

---

## Flujo de Auditoría

### Crear un Registro

```
1. Usuario autenticado (ID: 5, Rol: 'editor')
2. Se crea persona:
   Person::create([...])
3. Evento 'creating' se dispara
4. Trait registra:
   - registerUser_id = 5
   - registerRole = 'editor'
5. Registro se inserta con datos de auditoría
```

### Eliminar un Registro

```
1. Usuario autenticado (ID: 3, Rol: 'admin')
2. Se envía formulario de eliminación con observación:
   deleteObservation = "Duplicado de registro"
3. Se ejecuta:
   $person->delete()
4. Evento 'deleting' se dispara
5. Trait registra:
   - deleteUser_id = 3
   - deleteRole = 'admin'
   - deleteObservation = "Duplicado de registro"
6. Registro se guarda con datos de auditoría
7. Soft delete se ejecuta (deleted_at se establece)
```

---

## Ventajas del Trait

### 1. Auditoría Automática
No es necesario registrar manualmente quién crea o elimina registros.

### 2. Rastreo Completo
- **Creación:** Quién y con qué rol creó el registro
- **Eliminación:** Quién, con qué rol y por qué motivo se eliminó

### 3. Consistencia
Todos los modelos que usan el trait tienen el mismo comportamiento.

### 4. Transparencia
Se mantiene un historial completo de todas las operaciones.

### 5. Reutilizable
Puede agregarse a cualquier modelo que necesite auditoría.

---

## Ejemplos de Consulta

### Quién creó un registro
```php
$person = Person::find(1);
$creator = User::find($person->registerUser_id);
$creatorRole = $person->registerRole; // 'admin'
echo "Creado por: {$creator->name} ({$creatorRole})";
```

### Quién eliminó un registro
```php
$person = Person::withTrashed()->find(1);
if ($person->deleted_at) {
    $deleter = User::find($person->deleteUser_id);
    $deleterRole = $person->deleteRole;
    $observation = $person->deleteObservation;
    echo "Eliminado por: {$deleter->name} ({$deleterRole})";
    echo "Motivo: {$observation}";
}
```

### Historial de eliminaciones
```php
$deletedPeople = Person::onlyTrashed()
    ->where('deleteUser_id', Auth::id())
    ->get();
```

---

## Integración con Soft Deletes

El trait trabaja perfectamente con soft deletes:

```php
// Modelo
class Person extends Model
{
    use SoftDeletes, RegistersUserEvents;
    // ...
}

// Flujo:
// 1. Se llama $person->delete()
// 2. Evento 'deleting' se dispara
// 3. Trait registra deleteUser_id, deleteRole, deleteObservation
// 4. Trait guarda el registro
// 5. Soft delete se ejecuta (deleted_at = now())
// 6. Registro permanece en BD pero marcado como eliminado
```

---

## Integración con Middleware Loggin

El trait complementa el middleware `Loggin`:

- **Middleware Loggin:** Registra cada petición HTTP
- **Trait RegistersUserEvents:** Registra quién creó/eliminó registros específicos

Ambos proporcionan una auditoría completa del sistema.

---

## Notas Importantes

1. **Autenticación Requerida:** El trait solo registra datos si hay un usuario autenticado (`Auth::check()`).

2. **Modelos con Soft Deletes:** El trait espera que los modelos usen `SoftDeletes` para funcionar correctamente con el evento `deleting`.

3. **Campos Opcionales:** Los campos de auditoría deben ser nullable para permitir registros sin usuario (ej: seeders, imports).

4. **Observación Obligatoria:** El `deleteObservation` se obtiene del request, debe venir del formulario (ver `modal-delete.blade.php`).

5. **Nombre del Rol:** Se guarda el nombre del rol (`$user->role->name`), no el ID, para más legibilidad.

6. **Auto-relación en Users:** En la tabla `users`, tanto `registerUser_id` como `deleteUser_id` son FKs a la misma tabla (auto-relación).

7. **No Requiere Configuración:** El trait se activa automáticamente al hacer `use RegistersUserEvents`.

8. **Consistente con BREAD:** Los campos de auditoría pueden agregarse a BREAD de Voyager para visualizarlos.

9. **Logs vs Trait:** Los logs del middleware registran peticiones, el trait registra datos de registro.

10. **Reutilizable:** Puede agregarse a cualquier modelo nuevo que necesite auditoría sin modificar el trait.

---

## Extensión del Trait

Si se necesita agregar más campos de auditoría, se puede extender el trait:

```php
// Opcional: Agregar más datos
static::creating(function ($model) {
    if (Auth::check()) {
        $user = Auth::user();
        $model->registerUser_id = $user->id;
        $model->registerRole = $user->role->name;
        $model->registerIp = request()->ip(); // Nuevo campo
        $model->registerUserAgent = request()->userAgent(); // Nuevo campo
    }
});

static::deleting(function ($model) {
    if (Auth::check()) {
        $user = Auth::user();
        $model->deleteUser_id = $user->id;
        $model->deleteRole = $user->role->name;
        $model->deleteObservation = request()->input('deleteObservation');
        $model->deleteIp = request()->ip(); // Nuevo campo
        $model->deleteUserAgent = request()->userAgent(); // Nuevo campo
        $model->save();
    }
});
```
