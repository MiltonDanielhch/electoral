# Modelos del Sistema

## Modelos Personalizados

### 1. Person (`app/Models/Person.php`)

Modelo principal para gestionar personas (naturales y jurídicas).

**Características:**
- Hereda de `Illuminate\Database\Eloquent\Model`
- Usa `SoftDeletes` para eliminación lógica
- Usa trait `RegistersUserEvents` para auditoría automática

**Campos Fillable:**
```php
'ci',                     // Carnet de identidad
'first_name',             // Primer nombre
'middle_name',            // Segundo nombre
'paternal_surname',       // Apellido paterno
'maternal_surname',       // Apellido materno
'birth_date',             // Fecha de nacimiento
'email',                  // Email
'phone',                  // Teléfono
'address',                // Dirección
'gender',                 // Género
'image',                  // URL de imagen
'status',                 // Estado (1=Activo, 0=Inactivo, 2=Pendiente)
'registerUser_id',        // ID del usuario que registró
'registerRole',           // Rol del usuario que registró
'deleted_at',             // Fecha de eliminación lógica
'deleteUser_id',          // ID del usuario que eliminó
'deleteRole',             // Rol del usuario que eliminó
'deleteObservation'       // Observación de eliminación
```

**Constantes de Estado:**
```php
STATUS_ACTIVE = 1     // Activo
STATUS_INACTIVE = 0   // Inactivo
STATUS_PENDING = 2    // Pendiente
```

**Accesorios:**
- `full_name`: Retorna el nombre completo concatenando todos los campos de nombre

**Scopes:**
- `active()`: Filtra registros con status = 1

**Métodos:**
```php
public static function getStatusLabel($status)
// Retorna etiqueta legible del estado ('Activo', 'Inactivo', 'Pendiente')
```

---

### 2. User (`app/Models/User.php`)

Modelo de usuarios que extiende el modelo de Voyager.

**Características:**
- Extiende de `\TCG\Voyager\Models\User`
- Usa `HasApiTokens`, `SoftDeletes`, `Notifiable`, `RegistersUserEvents`

**Campos Fillable:**
```php
'person_id',              // ID de la persona relacionada
'name',                   // Nombre de usuario
'role_id',                // ID del rol
'email',                  // Email
'password',               // Contraseña (hash)
'status',                 // Estado del usuario
'registerUser_id',        // Auditoría: usuario que registró
'registerRole',           // Auditoría: rol del usuario que registró
'deleted_at',             // Soft delete
'deleteUser_id',          // Auditoría: usuario que eliminó
'deleteRole',             // Auditoría: rol del usuario que eliminó
'deleteObservation'       // Auditoría: observación de eliminación
```

**Relaciones:**
```php
public function person()
// Pertenece a un Person (uno a uno)
```

---

## Modelos de Voyager (No Modificados)

### Modelos del Sistema de Usuarios

- `\TCG\Voyager\Models\User` - Modelo base de usuarios
- `\TCG\Voyager\Models\Role` - Modelo de roles
- `\TCG\Voyager\Models\Permission` - Modelo de permisos
- `\TCG\Voyager\Models\Setting` - Modelo de configuraciones del sistema
- `\TCG\Voyager\Models\Menu` - Modelo de menús
- `\TCG\Voyager\Models\MenuItem` - Ítems de menú
- `\TCG\Voyager\Models\DataType` - Tipos de datos (BREAD)
- `\TCG\Voyager\Models\DataRow` - Filas de datos (campos BREAD)
- `\TCG\Voyager\Models\Post` - Modelo de posts (blog)
- `\TCG\Voyager\Models\Page` - Modelo de páginas
- `\TCG\Voyager\Models\Category` - Modelo de categorías

---

## Tablas de la Base de Datos

### Tabla `people`

Almacena información de personas.

**Campos principales:**
- `id` (PK, auto-increment)
- `person_type` (enum: 'Natural', 'Jurídica')
- `tipo_doc` (string, default: 'CI')
- `ci` (string, nullable)
- `ci_complemento` (string, nullable)
- `nit` (string, nullable) - Para personas jurídicas
- `first_name`, `middle_name`, `paternal_surname`, `maternal_surname`
- `legal_name` (string, nullable) - Razón social para jurídicas
- `birth_date` (date, nullable)
- `email` (string, unique, nullable)
- `phone` (string, nullable)
- `address` (text, nullable)
- `gender` (enum: 'Masculino', 'Femenino', nullable)
- `image` (string, nullable)
- `status` (tinyInt, default: 1)
- `estado_persona` (enum: 'Activo', 'Inactivo', 'Fallecido')
- `registerUser_id` (FK → users)
- `registerRole` (string, nullable)
- `created_at`, `updated_at`
- `deleted_at` (soft delete)
- `deleteUser_id` (FK → users)
- `deleteRole` (string, nullable)
- `deleteObservation` (text, nullable)

**Índices:**
- PK: `id`
- Unique: `tipo_doc`, `ci`, `ci_complemento`
- FK: `registerUser_id`, `deleteUser_id`

### Tabla `users` (Extendida)

Almacena usuarios del sistema.

**Campos adicionales a Voyager:**
- `person_id` (FK → people, nullable)
- `status` (smallInt, default: 1)
- `registerUser_id` (FK → users, nullable)
- `registerRole` (string, nullable)
- `deleted_at` (soft delete)
- `deleteUser_id` (FK → users, nullable)
- `deleteRole` (string, nullable)
- `deleteObservation` (text, nullable)

**Relaciones:**
- FK: `person_id` → `people.id`
- FK: `registerUser_id` → `users.id`
- FK: `deleteUser_id` → `users.id`

---

## Relaciones Entre Modelos

```
Person (1) ←→ (1) User
   ↓                   ↓
  Usuarios         Roles/Permisos
   ↓                   ↓
Auditoría (registro/eliminación)
```

---

## Patrones de Uso

### Crear una Persona
```php
$person = Person::create([
    'ci' => '123456789',
    'first_name' => 'Juan',
    'paternal_surname' => 'Pérez',
    'email' => 'juan@example.com',
    // Otros campos...
]);
```

### Crear un Usuario
```php
$user = User::create([
    'person_id' => $person->id,
    'name' => 'jperez',
    'email' => 'juan@example.com',
    'password' => bcrypt('password'),
    'role_id' => 2, // Ejemplo: rol editor
]);
```

### Buscar Personas Activas
```php
$activePeople = Person::active()->get();
```

### Obtener Persona con su Usuario
```php
$personWithUser = Person::with('user')->find(1);
```

### Eliminar Persona (Soft Delete)
```php
$person = Person::find(1);
$person->delete(); // Registra automáticamente usuario y rol
```

---

## Notas Importantes

1. **Auditoría Automática:** El trait `RegistersUserEvents` registra automáticamente el usuario y rol que crea o elimina registros.

2. **Soft Deletes:** Todos los modelos importantes usan soft deletes para mantener integridad de datos.

3. **Imagen en Multiple Formatos:** Las imágenes se almacenan en formato AVIF con múltiples versiones generadas automáticamente.

4. **Personas Jurídicas:** El sistema soporta personas naturales y jurídicas con campos específicos para cada tipo.

5. **Estados:** Se usan diferentes estados según el contexto (status numérico vs enum).
