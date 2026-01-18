# Plan de Ejecución - Solución de Bugs y Mejoras

## Cronograma General

Este documento es una guía paso a paso para ejecutar todas las tareas identificadas en el análisis del sistema.

---

## 📋 Índice del Plan

### FASE 1: Bugs Críticos (HOY - Día 1)
### FASE 2: Mejoras de Funcionalidad (Día 2-3)
### FASE 3: Optimizaciones y Seguridad (Día 4-5)
### FASE 4: Faltas Funcionales (Semana 2-3)
### FASE 5: Mejoras a Largo Plazo (Mes 2-3)

---

## 📅 FASE 1: Bugs Críticos (HOY - Día 1)

**Tiempo estimado:** 2-3 horas
**Prioridad:** 🔴 CRÍTICA - El sistema no funciona sin esto

### Tarea 1.1: Agregar Método hasRole() al Modelo User

**Archivo:** `app/Models/User.php`

**Paso 1:** Abrir el archivo
```bash
code app/Models/User.php
# o tu editor favorito
```

**Paso 2:** Agregar el método hasRole() después de la línea 42 (después de la función person())

**Código a agregar:**
```php
/**
 * Verifica si el usuario tiene un rol específico
 *
 * @param string|array $role Nombre del rol o array de roles
 * @return bool True si tiene el rol, false en caso contrario
 */
public function hasRole($role)
{
    if (!$this->role) {
        return false;
    }

    if (is_array($role)) {
        return in_array($this->role->name, $role);
    }

    return $this->role->name === $role;
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar que no haya errores de sintaxis
```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->hasRole('admin');
// Debe retornar true o false sin errores
>>> exit
```

**Paso 5:** Probar que el sistema funciona
```bash
# Iniciar el servidor
php artisan serve

# Navegar a http://localhost:8000/admin
# Debería funcionar sin errores
```

---

### Tarea 1.2: Agregar Método hasPermission() al Modelo User

**Archivo:** `app/Models/User.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Agregar el método hasPermission() después del método hasRole()

**Código a agregar:**
```php
/**
 * Verifica si el usuario tiene un permiso específico
 *
 * @param string $permission Llave del permiso
 * @return bool True si tiene el permiso, false en caso contrario
 */
public function hasPermission($permission)
{
    if (!$this->role) {
        return false;
    }

    // Los admins tienen todos los permisos
    if ($this->role->name === 'admin') {
        return true;
    }

    // Verificar si el rol tiene el permiso
    return $this->role->permissions->contains('key', $permission);
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar que funcione
```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->hasPermission('browse_users');
// Debe retornar true o false sin errores
>>> exit
```

---

### Tarea 1.3: Corregir Error Tipográfico COALESCE en AjaxController

**Archivo:** `app/Http/Controllers/AjaxController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Buscar la línea 24

**Línea 24 - ANTES:**
```php
$subQ->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, '')) like ?", ["%$q%"])
```

**Línea 24 - DESPUÉS:**
```php
$subQ->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, '')) like ?", ["%$q%"])
```

**Paso 3:** Buscar la línea 25

**Línea 25 - ANTES:**
```php
->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$q%"])
```

**Línea 25 - DESPUÉS:**
```php
->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$q%"])
```

**Paso 4:** Buscar la línea 26

**Línea 26 - ANTES:**
```php
->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$q%"]);
```

**Línea 26 - DESPUÉS:**
```php
->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$q%"]);
```

**Paso 5:** Guardar el archivo

**Paso 6:** Verificar sintaxis
```bash
php -l app/Http/Controllers/AjaxController.php
# Debe mostrar: No syntax errors detected
```

---

### Tarea 1.4: Corregir SQL Injection en UserController

**Archivo:** `app/Http/Controllers/UserController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método list() completo (líneas 28-47)

**Código nuevo:**
```php
public function list()
{
    // $this->custom_authorize('browse_users');
    $rol_id = Auth::user()->role->id;

    $search = request('search') ?? null;
    $paginate = request('paginate') ?? 10;

    $data = User::with(['person'])
        ->where(function($query) use ($search){
            if ($search) {
                if (is_numeric($search)) {
                    $query->where('id', $search);
                } else {
                    $query->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                }
            }
        })
        ->when($rol_id != 1, function ($query) {
            return $query->where('role_id', '!=', 1);
        })
        ->orderBy('id', 'DESC')
        ->paginate($paginate);

    return view('vendor.voyager.users.list', compact('data'));
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar sintaxis
```bash
php -l app/Http/Controllers/UserController.php
```

---

### Tarea 1.5: Corregir SQL Injection en RoleController

**Archivo:** `app/Http/Controllers/RoleController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método list() completo (líneas 22-39)

**Código nuevo:**
```php
public function list()
{
    $search = request('search') ?? null;
    $paginate = request('paginate') ?? 10;

    $rol_id = Auth::user()->role->id;

    $data = Role::where(function($query) use ($search){
                    if ($search) {
                        if (is_numeric($search)) {
                            $query->where('id', $search);
                        } else {
                            $query->where('name', 'like', "%{$search}%")
                                   ->orWhere('display_name', 'like', "%{$search}%");
                        }
                    }
                })
                ->when($rol_id != 1, function ($query) {
                    return $query->where('id', '!=', 1);
                })
                ->orderBy('id', 'DESC')
                ->paginate($paginate);

    return view('vendor.voyager.roles.list', compact('data'));
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar sintaxis
```bash
php -l app/Http/Controllers/RoleController.php
```

---

### Tarea 1.6: Corregir SQL Injection en AjaxController

**Archivo:** `app/Http/Controllers/AjaxController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método personList() completo (líneas 15-31)

**Código nuevo:**
```php
public function personList()
{
    $q = request('q');

    $query = Person::where('deleted_at', null);

    if ($q) {
        $query->where('ci', 'like', "%{$q}%")
              ->orWhere('phone', 'like', "%{$q}%")
              ->orWhere('first_name', 'like', "%{$q}%")
              ->orWhere('middle_name', 'like', "%{$q}%")
              ->orWhere('paternal_surname', 'like', "%{$q}%")
              ->orWhere('maternal_surname', 'like', "%{$q}%")
              ->orWhere(function ($subQ) use ($q) {
                  $fullName = "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''))";
                  $subQ->whereRaw("{$fullName} like ?", ["%{$q}%"]);
              })
              ->orWhere(function ($subQ) use ($q) {
                  $fullName = "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, ''))";
                  $subQ->whereRaw("{$fullName} like ?", ["%{$q}%"]);
              })
              ->orWhere(function ($subQ) use ($q) {
                  $fullName = "CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, ''))";
                  $subQ->whereRaw("{$fullName} like ?", ["%{$q}%"]);
              });
    }

    $data = $query->get();

    return response()->json($data);
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar sintaxis
```bash
php -l app/Http/Controllers/AjaxController.php
```

---

### Tarea 1.7: Importar Clase Log en StorageController

**Archivo:** `app/Http/Controllers/StorageController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Agregar el import al inicio (después de la línea 8)

**Código ANTES:**
```php
use Illuminate\Http\Request;

use Illuminate\Support\Str;
```

**Código DESPUÉS:**
```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;  // ← AGREGAR ESTA LÍNEA

use Illuminate\Support\Str;
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar sintaxis
```bash
php -l app/Http/Controllers/StorageController.php
```

---

### Tarea 1.8: Corregir Espacio Extra en UserController

**Archivo:** `app/Http/Controllers/UserController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Buscar la línea 55

**Línea 55 - ANTES:**
```php
return redirect()->route('voyager.users.index')->with(['message' => 'El correo ya existe.', 'alert-type' => 'warning    ']);
```

**Línea 55 - DESPUÉS:**
```php
return redirect()->route('voyager.users.index')->with(['message' => 'El correo ya existe.', 'alert-type' => 'warning']);
```

**Paso 3:** Guardar el archivo

---

### Tarea 1.9: Corregir Error Tipográfico en Comentario

**Archivo:** `app/Http/Controllers/UserController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Buscar la línea 24

**Línea 24 - ANTES:**
```php
// return view('vendor.voyager.users.broswse');
```

**Línea 24 - DESPUÉS:**
```php
// return view('vendor.voyager.users.browse');
```

**Paso 3:** Guardar el archivo

---

### Verificación de FASE 1

**Paso 1:** Limpiar caché
```bash
php artisan optimize:clear
```

**Paso 2:** Verificar sintaxis de todos los controladores
```bash
find app/Http/Controllers -name "*.php" -exec php -l {} \;
# Todos deben mostrar "No syntax errors detected"
```

**Paso 3:** Verificar sintaxis de los modelos
```bash
find app/Models -name "*.php" -exec php -l {} \;
# Todos deben mostrar "No syntax errors detected"
```

**Paso 4:** Ejecutar tests si existen
```bash
php artisan test
```

**Paso 5:** Probar el sistema
```bash
php artisan serve
```

**Verificar:**
- ✅ Se puede acceder a `/admin`
- ✅ Se puede ver el listado de personas
- ✅ Se puede buscar personas
- ✅ Se puede ver el listado de usuarios
- ✅ No hay errores 500
- ✅ Los logs funcionan correctamente

---

## 📅 FASE 2: Mejoras de Funcionalidad (Día 2-3)

**Tiempo estimado:** 4-6 horas
**Prioridad:** 🟡 ALTA - Previene datos inválidos y errores

### Tarea 2.1: Agregar Validación en AjaxController::personStore

**Archivo:** `app/Http/Controllers/AjaxController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método personStore() completo

**Código nuevo:**
```php
public function personStore(Request $request)
{
    // Validar datos
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'paternal_surname' => 'required|string|max:255',
        'ci' => 'required|string|unique:people',
        'email' => 'nullable|email|unique:people',
        'phone' => 'nullable|string|max:20',
        'gender' => 'nullable|in:Masculino,Femenino',
        'birth_date' => 'nullable|date|before:today',
    ]);

    DB::beginTransaction();
    try {
        $person = Person::create($validated);
        DB::commit();
        return response()->json(['person' => $person], 201);
    } catch (\Throwable $th) {
        DB::rollback();
        return response()->json(['error' => $th->getMessage()], 500);
    }
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar
```bash
php -l app/Http/Controllers/AjaxController.php
```

---

### Tarea 2.2: Mejorar Manejo de Errores en UserController::store

**Archivo:** `app/Http/Controllers/UserController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método store() completo

**Código nuevo:**
```php
public function store(Request $request)
{
    // Validar datos
    $validated = $request->validate([
        'person_id' => 'required|exists:people,id,deleted_at,NULL,status,1',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8',
        'role_id' => 'required|exists:roles,id',
    ]);

    DB::beginTransaction();
    try {
        // Obtener persona
        $person = Person::where('deleted_at', null)
                        ->where('status', 1)
                        ->where('id', $validated['person_id'])
                        ->first();

        if (!$person) {
            throw new \Exception('La persona seleccionada no existe o no está activa.');
        }

        // Crear usuario
        User::create([
            'person_id' => $validated['person_id'],
            'name' => $person->first_name,
            'role_id' => $validated['role_id'],
            'email' => $validated['email'],
            'avatar' => 'users/default.png',
            'password' => bcrypt($validated['password']),
        ]);

        DB::commit();
        return redirect()->route('voyager.users.index')->with([
            'message' => 'Registrado exitosamente.',
            'alert-type' => 'success'
        ]);

    } catch (\Throwable $th) {
        DB::rollback();
        return redirect()->route('voyager.users.index')->with([
            'message' => $th->getMessage(),
            'alert-type' => 'error'
        ]);
    }
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar
```bash
php -l app/Http/Controllers/UserController.php
```

---

### Tarea 2.3: Agregar Validación Completa en PersonController::store

**Archivo:** `app/Http/Controllers/PersonController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método store() completo

**Código nuevo:**
```php
public function store(Request $request)
{
    $this->custom_authorize('add_people');

    // Validar datos
    $validated = $request->validate([
        'ci' => 'required|string|unique:people|regex:/^[0-9]{7,10}$/',
        'first_name' => 'required|string|max:255',
        'paternal_surname' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'maternal_surname' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:people|max:255',
        'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
        'gender' => 'nullable|in:Masculino,Femenino',
        'birth_date' => 'nullable|date|before:today',
        'address' => 'nullable|string|max:1000',
        'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240', // Max 10MB
    ]);

    DB::beginTransaction();
    try {
        $storageController = new StorageController();

        $imagePath = $request->image
            ? $storageController->store_image($request->image, 'people')
            : null;

        Person::create([
            'ci' => $validated['ci'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'paternal_surname' => $validated['paternal_surname'],
            'maternal_surname' => $validated['maternal_surname'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'image' => $imagePath,
        ]);

        DB::commit();
        return redirect()->route('voyager.people.index')->with([
            'message' => 'Registrado exitosamente',
            'alert-type' => 'success'
        ]);
    } catch (\Throwable $th) {
        DB::rollback();
        return redirect()->route('voyager.people.index')->with([
            'message' => $th->getMessage(),
            'alert-type' => 'error'
        ]);
    }
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar
```bash
php -l app/Http/Controllers/PersonController.php
```

---

### Tarea 2.4: Agregar Validación en PersonController::update

**Archivo:** `app/Http/Controllers/PersonController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Reemplazar el método update() completo

**Código nuevo:**
```php
public function update(Request $request, $id)
{
    $this->custom_authorize('edit_people');

    // Validar datos
    $validated = $request->validate([
        'ci' => 'required|string|regex:/^[0-9]{7,10}$/|unique:people,ci,' . $id,
        'first_name' => 'required|string|max:255',
        'paternal_surname' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'maternal_surname' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:people,email,' . $id . ',id|max:255',
        'phone' => 'nullable|string|regex:/^[0-9+\s\-]{7,20}$/|max:20',
        'gender' => 'nullable|in:Masculino,Femenino',
        'birth_date' => 'nullable|date|before:today',
        'address' => 'nullable|string|max:1000',
        'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:10240',
    ]);

    DB::beginTransaction();
    try {
        $storageController = new StorageController();
        $person = Person::find($id);

        if (!$person) {
            throw new \Exception('Persona no encontrada.');
        }

        $person->ci = $validated['ci'];
        $person->birth_date = $validated['birth_date'];
        $person->gender = $validated['gender'];
        $person->first_name = $validated['first_name'];
        $person->middle_name = $validated['middle_name'];
        $person->paternal_surname = $validated['paternal_surname'];
        $person->maternal_surname = $validated['maternal_surname'];
        $person->email = $validated['email'];
        $person->phone = $validated['phone'];
        $person->address = $validated['address'];
        $person->status = $request->has('status') ? 1 : 0;

        if ($request->image) {
            $person->image = $storageController->store_image($request->image, 'people');
        }

        $person->save();

        DB::commit();
        return redirect()->route('voyager.people.index')->with([
            'message' => 'Actualizada exitosamente',
            'alert-type' => 'success'
        ]);
    } catch (\Throwable $th) {
        DB::rollback();
        return redirect()->route('voyager.people.index')->with([
            'message' => $th->getMessage(),
            'alert-type' => 'error'
        ]);
    }
}
```

**Paso 3:** Guardar el archivo

**Paso 4:** Verificar
```bash
php -l app/Http/Controllers/PersonController.php
```

---

### Verificación de FASE 2

**Paso 1:** Limpiar caché
```bash
php artisan optimize:clear
```

**Paso 2:** Verificar sintaxis
```bash
find app/Http/Controllers -name "*.php" -exec php -l {} \;
```

**Paso 3:** Probar validaciones
- ✅ Intentar crear persona sin datos obligatorios
- ✅ Intentar crear persona con email duplicado
- ✅ Intentar crear persona con CI inválido
- ✅ Intentar crear usuario sin persona válida
- ✅ Intentar crear usuario con email duplicado

**Paso 4:** Verificar que las validaciones funcionen correctamente

---

## 📅 FASE 3: Optimizaciones y Seguridad (Día 4-5)

**Tiempo estimado:** 3-4 horas
**Prioridad:** 🟢 MEDIA - Mejora rendimiento y seguridad

### Tarea 3.1: Mejorar Seguridad de Contraseñas

**Archivo:** `app/Http/Controllers/UserController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Actualizar validación de password en store()
```php
// En el array de validación
'password' => 'required|string|min:8|confirmed',
```

**Paso 3:** Agregar campo password_confirmation en la vista de creación de usuario

---

### Tarea 3.2: Implementar Rate Limiting

**Archivo:** `app/Http/Kernel.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Agregar rate limiting al grupo admin
```php
protected $middlewareGroups = [
    'web' => [
        // ... otros middlewares ...
        \Illuminate\Routing\Middleware\ThrottleRequests::class.':60,1',
    ],
];
```

---

### Tarea 3.3: Agregar Caché de Consultas Frecuentes

**Archivo:** `app/Http/Controllers/RoleController.php`

**Paso 1:** Abrir el archivo

**Paso 2:** Modificar el método list() para usar caché
```php
public function list()
{
    $search = request('search') ?? null;
    $paginate = request('paginate') ?? 10;
    $rol_id = Auth::user()->role->id;

    $cacheKey = "roles_list_{$rol_id}_{$search}_{$paginate}_request('page', 1)}";

    $data = Cache::remember($cacheKey, 300, function() use ($search, $paginate, $rol_id) {
        return Role::where(function($query) use ($search){
                    if ($search) {
                        if (is_numeric($search)) {
                            $query->where('id', $search);
                        } else {
                            $query->where('name', 'like', "%{$search}%")
                                   ->orWhere('display_name', 'like', "%{$search}%");
                        }
                    }
                })
                ->when($rol_id != 1, function ($query) {
                    return $query->where('id', '!=', 1);
                })
                ->orderBy('id', 'DESC')
                ->paginate($paginate);
    });

    return view('vendor.voyager.roles.list', compact('data'));
}
```

---

### Verificación de FASE 3

**Paso 1:** Limpiar caché
```bash
php artisan optimize:clear
php artisan cache:clear
```

**Paso 2:** Verificar que el caché funcione
```bash
php artisan tinker
>>> Cache::put('test', 'value', 60);
>>> Cache::get('test');
>>> exit
```

**Paso 3:** Probar rate limiting
```bash
# Hacer más de 60 peticiones en 1 minuto
# Debería ver error 429 Too Many Requests
```

---

## 📅 FASE 4: Faltas Funcionales (Semana 2-3)

**Tiempo estimado:** 10-15 horas
**Prioridad:** 🟡 MEDIA - Mejoras importantes pero no críticas

### Tarea 4.1: Crear Sistema de Backups Automáticos

**Paso 1:** Crear script de backup
```bash
nano scripts/database-backup.sh
```

**Contenido del script:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/electoral"
mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -u root -p electoral > $BACKUP_DIR/electoral_$DATE.sql

# Backup de archivos
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz storage/app/public/

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete

echo "Backup completado: $DATE"
```

**Paso 2:** Dar permisos de ejecución
```bash
chmod +x scripts/database-backup.sh
```

**Paso 3:** Agregar al crontab
```bash
crontab -e
# Agregar esta línea para backup diario a las 2 AM
0 2 * * * /path/to/scripts/database-backup.sh >> /var/backups/backup.log 2>&1
```

**Paso 4:** Probar el backup
```bash
./scripts/database-backup.sh
ls -lh /var/backups/electoral/
```

---

### Tarea 4.2: Crear Sistema de Colas para Imágenes

**Paso 1:** Crear Job para procesamiento de imágenes
```bash
php artisan make:job ProcessImage
```

**Paso 2:** Editar el Job
**Archivo:** `app/Jobs/ProcessImage.php`

**Código:**
```php
<?php

namespace App\Jobs;

use App\Http\Controllers\StorageController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $file;
    private $folder;

    public function __construct($file, $folder)
    {
        $this->file = $file;
        $this->folder = $folder;
    }

    public function handle()
    {
        $storageController = new StorageController();
        return $storageController->store_image($this->file, $this->folder);
    }
}
```

**Paso 3:** Configurar cola en `.env`
```bash
QUEUE_CONNECTION=database
```

**Paso 4:** Crear tabla de jobs
```bash
php artisan queue:table
php artisan migrate
```

**Paso 5:** Usar el Job en el controlador
```php
// En PersonController::store
ProcessImage::dispatch($request->image, 'people');
```

**Paso 6:** Iniciar el worker de colas
```bash
php artisan queue:work
```

---

### Tarea 4.3: Agregar PHPDoc a Métodos Principales

**Archivos:** Todos los controladores y modelos

**Paso 1:** Agregar documentación a cada método público

**Ejemplo para PersonController:**
```php
/**
 * Muestra el listado principal de personas
 *
 * @return \Illuminate\View\View Vista de listado de personas
 * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si no tiene permiso browse_people
 */
public function index()
{
    $this->custom_authorize('browse_people');
    return view('administrations.people.browse');
}

/**
 * Retorna lista paginada de personas para AJAX
 *
 * @param \Illuminate\Http\Request $request Petición HTTP
 * @return \Illuminate\View\View Vista parcial con tabla de personas
 */
public function list(Request $request)
{
    // ...
}

/**
 * Crea una nueva persona en el sistema
 *
 * @param \Illuminate\Http\Request $request Datos de la persona a crear
 * @return \Illuminate\Http\RedirectResponse Redirección con mensaje de éxito o error
 * @throws \Throwable Si ocurre un error al crear la persona
 */
public function store(Request $request)
{
    // ...
}
```

---

### Verificación de FASE 4

**Paso 1:** Verificar que el backup funcione
```bash
ls -lh /var/backups/electoral/
```

**Paso 2:** Verificar que las colas funcionen
```bash
php artisan queue:work --once
```

**Paso 3:** Verificar que la documentación PHPDoc sea correcta
```bash
phpdoc -d app/Http/Controllers -t docs/phpdoc
```

---

## 📅 FASE 5: Mejoras a Largo Plazo (Mes 2-3)

**Tiempo estimado:** 20-30 horas
**Prioridad:** 🟢 BAJA - Mejoras significativas pero no urgentes

### Tarea 5.1: Implementar Sistema de Notificaciones

**Paso 1:** Crear notificaciones
```bash
php artisan make:notification UserCreated
php artisan make:notification UserDeleted
php artisan make:notification UserRoleChanged
```

**Paso 2:** Implementar cada notificación
**Archivo:** `app/Notifications/UserCreated.php`

**Código:**
```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserCreated extends Notification
{
    use Queueable;

    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new \Illuminate\Notifications\Messages\MailMessage)
                    ->line('Un nuevo usuario ha sido creado.')
                    ->line("Nombre: {$this->user->name}")
                    ->line("Email: {$this->user->email}")
                    ->action('Ver usuario', url('/admin/users/' . $this->user->id))
                    ->line('Gracias por usar nuestro sistema!');
    }

    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ];
    }
}
```

**Paso 3:** Enviar notificación al crear usuario
```php
// En UserController::store
$user = User::create([...]);
$user->notify(new UserCreated($user));
```

---

### Tarea 5.2: Implementar API REST Completa

**Paso 1:** Crear rutas API
**Archivo:** `routes/api.php`

**Código:**
```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

Route::middleware('auth:sanctum')->group(function () {
    // Rutas de People
    Route::apiResource('people', PersonController::class);
    Route::get('people/search', [PersonController::class, 'search']);
    Route::get('people/{id}/activate', [PersonController::class, 'activate']);
    Route::get('people/{id}/deactivate', [PersonController::class, 'deactivate']);

    // Rutas de Users
    Route::apiResource('users', UserController::class);
    Route::get('users/me', [UserController::class, 'me']);
    Route::put('users/{id}/status', [UserController::class, 'updateStatus']);
    Route::put('users/{id}/password', [UserController::class, 'updatePassword']);

    // Rutas de Roles
    Route::apiResource('roles', RoleController::class);
});
```

**Paso 2:** Crear API Resources
```bash
php artisan make:resource PersonResource
php artisan make:resource UserResource
php artisan make:resource RoleResource
```

**Paso 3:** Implementar resources
**Archivo:** `app/Http/Resources/PersonResource.php`

**Código:**
```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ci' => $this->ci,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'paternal_surname' => $this->paternal_surname,
            'maternal_surname' => $this->maternal_surname,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'gender' => $this->gender,
            'birth_date' => $this->birth_date,
            'image' => $this->image,
            'status' => $this->status,
            'full_name' => $this->full_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

**Paso 4:** Usar resources en controladores
```php
use App\Http\Resources\PersonResource;

public function index()
{
    $people = Person::all();
    return PersonResource::collection($people);
}

public function show($id)
{
    $person = Person::find($id);
    return new PersonResource($person);
}
```

---

### Tarea 5.3: Implementar Tests Unitarios

**Paso 1:** Crear tests
```bash
php artisan make:test PersonTest
php artisan make:test UserTest
php artisan make:test RoleTest
```

**Paso 2:** Implementar tests
**Archivo:** `tests/Feature/PersonTest.php`

**Código:**
```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_list_people()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->get('/admin/people/ajax/list');

        $response->assertStatus(200);
    }

    public function test_user_can_create_person()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
                         ->post('/admin/people', [
                             'ci' => '12345678',
                             'first_name' => 'Juan',
                             'paternal_surname' => 'Pérez',
                         ]);

        $response->assertRedirect(route('voyager.people.index'));
        $this->assertDatabaseHas('people', ['ci' => '12345678']);
    }

    public function test_user_can_update_person()
    {
        $user = User::factory()->create();
        $person = Person::factory()->create();

        $response = $this->actingAs($user)
                         ->put("/admin/people/{$person->id}", [
                             'ci' => '87654321',
                             'first_name' => 'Pedro',
                             'paternal_surname' => 'García',
                         ]);

        $response->assertRedirect(route('voyager.people.index'));
        $this->assertDatabaseHas('people', ['ci' => '87654321']);
    }

    public function test_user_can_delete_person()
    {
        $user = User::factory()->create();
        $person = Person::factory()->create();

        $response = $this->actingAs($user)
                         ->delete("/admin/people/{$person->id}");

        $response->assertRedirect(route('voyager.people.index'));
        $this->assertSoftDeleted('people', ['id' => $person->id]);
    }

    public function test_unauthorized_user_cannot_create_person()
    {
        $user = User::factory()->create(['role_id' => 2]); // No admin
        $response = $this->actingAs($user)
                         ->post('/admin/people', [
                             'ci' => '12345678',
                             'first_name' => 'Juan',
                             'paternal_surname' => 'Pérez',
                         ]);

        $response->assertForbidden();
    }
}
```

**Paso 3:** Ejecutar tests
```bash
php artisan test --filter PersonTest
```

---

### Verificación de FASE 5

**Paso 1:** Verificar notificaciones
```bash
php artisan tinker
>>> $user = App\Models\User::first();
>>> $user->notify(new \App\Notifications\UserCreated($user));
>>> exit
```

**Paso 2:** Verificar API
```bash
# Crear token de acceso
php artisan tinker
>>> $user = App\Models\User::first();
>>> $token = $user->createToken('test-token')->plainTextToken;
>>> exit

# Probar API
curl -H "Authorization: Bearer $token" http://localhost:8000/api/people
```

**Paso 3:** Ejecutar tests
```bash
php artisan test
```

---

## 📊 Resumen del Plan

| Fase | Tareas | Tiempo Estimado | Prioridad | Status |
|------|--------|-----------------|-----------|--------|
| FASE 1 | Bugs Críticos | 2-3 horas | 🔴 CRÍTICA | ✅ COMPLETADA |
| FASE 2 | Mejoras Funcionalidad | 4-6 horas | 🟡 ALTA | ✅ COMPLETADA |
| FASE 3 | Optimizaciones y Seguridad | 3-4 horas | 🟢 MEDIA | ✅ COMPLETADA |
| FASE 4 | Faltas Funcionales | 10-15 horas | 🟢 MEDIA | ⬜ Pendiente |
| FASE 5 | Mejoras Largo Plazo | 20-30 horas | 🟢 BAJA | ⬜ Pendiente |

---

## 🎯 Checklist de Ejecución

### FASE 1 - Bugs Críticos
- [x] Tarea 1.1: Agregar método hasRole()
- [x] Tarea 1.2: Agregar método hasPermission()
- [x] Tarea 1.3: Corregir COALESCE en AjaxController
- [x] Tarea 1.4: Corregir SQL Injection en UserController
- [x] Tarea 1.5: Corregir SQL Injection en RoleController
- [x] Tarea 1.6: Corregir SQL Injection en AjaxController
- [x] Tarea 1.7: Importar Log en StorageController
- [x] Tarea 1.8: Corregir espacio extra en UserController
- [x] Tarea 1.9: Corregir error tipográfico en comentario
- [x] Verificación de FASE 1

### FASE 2 - Mejoras de Funcionalidad
- [x] Tarea 2.1: Validación en AjaxController::personStore
- [x] Tarea 2.2: Manejo de errores en UserController::store
- [x] Tarea 2.3: Validación en PersonController::store
- [x] Tarea 2.4: Validación en PersonController::update
- [x] Verificación de FASE 2

### FASE 3 - Optimizaciones y Seguridad
- [x] Tarea 3.1: Mejorar seguridad de contraseñas
- [x] Tarea 3.2: Implementar rate limiting
- [x] Tarea 3.3: Agregar caché de consultas
- [x] Verificación de FASE 3

### FASE 4 - Faltas Funcionales
- [ ] Tarea 4.1: Sistema de backups automáticos
- [ ] Tarea 4.2: Sistema de colas para imágenes
- [ ] Tarea 4.3: Agregar PHPDoc a métodos
- [ ] Verificación de FASE 4

### FASE 5 - Mejoras Largo Plazo
- [ ] Tarea 5.1: Sistema de notificaciones
- [ ] Tarea 5.2: API REST completa
- [ ] Tarea 5.3: Tests unitarios
- [ ] Verificación de FASE 5

---

## 🔄 Comandos de Rollback

Si algo sale mal, puedes revertir cambios usando Git:

```bash
# Ver cambios
git status
git diff

# Revertir cambios específicos
git checkout -- app/Models/User.php
git checkout -- app/Http/Controllers/UserController.php

# Revertir todos los cambios (cuidado!)
git checkout .

# Crear nuevo commit con cambios
git add .
git commit -m "Fase 1 completada: Bugs críticos solucionados"
```

---

## 📝 Notas Importantes

1. **Siempre hacer backup antes de modificar archivos**
2. **Probar cada cambio en un entorno de desarrollo**
3. **Verificar sintaxis PHP después de cada modificación**
4. **Limpiar caché después de modificar controladores**
5. **Ejecutar tests después de cada fase**
6. **Documentar cambios realizados**
7. **Usar Git para versionar cambios**

---

## 🚀 Comandos Rápidos de Verificación

```bash
# Verificar sintaxis PHP
find app -name "*.php" -exec php -l {} \;

# Limpiar caché
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Verificar rutas
php artisan route:list

# Verificar configuración
php artisan config:show

# Ejecutar tests
php artisan test

# Verificar migraciones
php artisan migrate:status

# Verificar colas
php artisan queue:failed
```

---

**Última actualización:** 2026-01-18
**Versión del plan:** 1.1.0
**Autor:** AI Assistant
**Estado:** FASE 1, 2 y 3 COMPLETADAS
