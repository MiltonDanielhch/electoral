# Diagramas del Sistema

## Diagrama de Arquitectura General

```
┌─────────────────────────────────────────────────────────────────┐
│                        USUARIO                                   │
│  (Navegador Web)                                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ HTTP Request
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                    MIDDLEWARE STACK                              │
├─────────────────────────────────────────────────────────────────┤
│  1. Authenticate       → Verifica si usuario está autenticado    │
│  2. Loggin             → Registra petición en logs               │
│  3. System             → Verifica mantenimiento, desarrollo,     │
│                         licencias                                 │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ Si todo OK
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                      ROUTER                                      │
│  (routes/web.php)                                                │
│  - Rutas de Personas (/admin/people/*)                          │
│  - Rutas de Usuarios (/admin/users/*)                            │
│  - Rutas de Roles (/admin/roles/*)                               │
│  - Rutas AJAX (/admin/ajax/*)                                    │
│  - Rutas de Voyager (nativas)                                   │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ Route matched
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                   CONTROLLERS                                    │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────────┐  ┌──────────────────┐                      │
│  │ PersonController │  │ UserController   │                      │
│  │ - index()        │  │ - list()         │                      │
│  │ - list()         │  │ - store()        │                      │
│  │ - store()        │  │ - update()       │                      │
│  │ - update()       │  │ - destroy()      │                      │
│  └──────────────────┘  └──────────────────┘                      │
│                                                               │
│  ┌──────────────────┐  ┌──────────────────┐                      │
│  │ StorageController│  │ AjaxController    │                      │
│  │ - store_image()  │  │ - personList()   │                      │
│  │                  │  │ - personStore()  │                      │
│  └──────────────────┘  └──────────────────┘                      │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ Business Logic
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                       MODELS                                      │
├─────────────────────────────────────────────────────────────────┤
│  ┌──────────────────┐  ┌──────────────────┐                      │
│  │ Person          │  │ User             │                      │
│  │ - RegistersUser │  │ - RegistersUser  │                      │
│  │   Events Trait  │  │   Events Trait   │                      │
│  │ - SoftDeletes   │  │ - SoftDeletes    │                      │
│  │ - HasFactory    │  │ - HasApiTokens   │                      │
│  └──────────────────┘  └──────────────────┘                      │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         │ Eloquent ORM
                         ↓
┌─────────────────────────────────────────────────────────────────┐
│                   DATABASE                                       │
├─────────────────────────────────────────────────────────────────┤
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ people          │  │ users           │  │ roles           │ │
│  ├─────────────────┤  ├─────────────────┤  ├─────────────────┤ │
│  │ id              │──│ person_id       │──│ role_id         │ │
│  │ first_name      │  │ name            │  │ display_name    │ │
│  │ email           │  │ email           │  │                 │ │
│  │ ...             │  │ ...             │  │                 │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
│                                                               │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│  │ permissions    │  │ settings        │  │ menus           │ │
│  │                 │  │                 │  │                 │ │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
```

---

## Diagrama de Flujo de Petición HTTP

```
Usuario → Navegador → HTTP Request
                        ↓
                ┌───────────────┐
                │ Authenticate  │ → No autenticado → /admin/login
                └───────┬───────┘
                        │ Sí
                        ↓
                ┌───────────────┐
                │   Loggin      │ → Registrar en logs (canal requests)
                └───────┬───────┘
                        │
                        ↓
                ┌───────────────┐
                │    System     │
                ├───────────────┤
                │ Mantenimiento │ → Sí → Solo admins → 503 para otros
                │    ON?        │
                ├───────────────┤
                │ Desarrollo?   │ → Sí → Solo admins → 503 para otros
                ├───────────────┤
                │ Licencia OK?  │ → No → Bloquear POST/PUT/DELETE
                └───────┬───────┘
                        │ OK
                        ↓
                ┌───────────────┐
                │    Router     │ → Encontrar ruta
                └───────┬───────┘
                        │
                        ↓
                ┌───────────────┐
                │  Controller   │ → Ejecutar lógica
                └───────┬───────┘
                        │
                        ↓
                ┌───────────────┐
                │    Model      │ → Operaciones DB
                └───────┬───────┘
                        │
                        ↓
                ┌───────────────┐
                │   Database    │ → Persistencia
                └───────┬───────┘
                        │
                        ↓
                ┌───────────────┐
                │    View       │ → Generar HTML
                └───────┬───────┘
                        │
                        ↓
              HTTP Response → Navegador → Usuario
```

---

## Diagrama de Auditoría (RegistersUserEvents Trait)

```
┌─────────────────────────────────────────────────────────────────┐
│ CREAR REGISTRO                                                  │
└─────────────────────────────────────────────────────────────────┘

Usuario Autenticado (ID: 5, Rol: 'admin')
        │
        │ Person::create([...])
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Evento 'creating' se dispara                                   │
│                                                                  │
│ Trait RegistersUserEvents:                                      │
│   - Auth::check() = true                                        │
│   - $model->registerUser_id = 5                                 │
│   - $model->registerRole = 'admin'                              │
│                                                                  │
│ Registro se inserta con datos de auditoría                      │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
Registro en BD:
  - id: 100
  - first_name: "Juan"
  - registerUser_id: 5 ← Auditoría
  - registerRole: "admin" ← Auditoría
  - created_at: 2026-01-18 15:30:00

┌─────────────────────────────────────────────────────────────────┐
│ ELIMINAR REGISTRO (Soft Delete)                                 │
└─────────────────────────────────────────────────────────────────┘

Usuario Autenticado (ID: 3, Rol: 'editor')
        │
        │ Formulario: deleteObservation = "Duplicado de registro"
        │
        │ $person->delete()
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Evento 'deleting' se dispara                                    │
│                                                                  │
│ Trait RegistersUserEvents:                                      │
│   - Auth::check() = true                                        │
│   - $model->deleteUser_id = 3                                   │
│   - $model->deleteRole = 'editor'                               │
│   - $model->deleteObservation = "Duplicado de registro"         │
│   - $model->save()                                              │
│                                                                  │
│ Soft delete se ejecuta                                          │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
Registro en BD (con soft delete):
  - id: 100
  - first_name: "Juan"
  - deleted_at: 2026-01-18 15:35:00 ← Soft delete
  - deleteUser_id: 3 ← Auditoría
  - deleteRole: "editor" ← Auditoría
  - deleteObservation: "Duplicado de registro" ← Auditoría
```

---

## Diagrama de Gestión de Imágenes (StorageController)

```
┌─────────────────────────────────────────────────────────────────┐
│ SUBIDA DE IMAGEN                                                 │
└─────────────────────────────────────────────────────────────────┘

Usuario selecciona imagen (ej: "foto.jpg", 5MB)
        │
        │ POST /admin/people
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ PersonController@store()                                        │
│   - $storage = new StorageController()                          │
│   - $imagePath = $storage->store_image($request->image, 'people')│
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ StorageController@store_image()                                  │
│                                                                  │
│ 1. Validar archivo                                               │
│    - Es imagen válida?                                           │
│    - Formato: jpeg, jpg, png, bmp, webp                         │
│                                                                  │
│ 2. Crear directorio                                              │
│    - people/January2026/                                        │
│                                                                  │
│ 3. Generar nombre aleatorio                                      │
│    - X7aB9cD2e3fG4hI5jK6lM7nO8pQ9rS0tUday18am                    │
│                                                                  │
│ 4. Cargar imagen original                                        │
│    - Image::make($file->getRealPath())->orientate()             │
│                                                                  │
│ 5. Generar versiones                                             │
│    ├── Original (1200px)        → nombre.avif                   │
│    ├── Banner (900px)           → nombre-banner.avif            │
│    ├── Medium (600px)           → nombre-medium.avif            │
│    ├── Small (256px)            → nombre-small.avif             │
│    └── Cropped (300x300)        → nombre-cropped.avif           │
│                                                                  │
│ 6. Convertir a AVIF (calidad 80%)                                │
│    - Storage::put($path, $image->encode('avif', 80))           │
│                                                                  │
│ 7. Guardar en almacenamiento público                             │
│    - storage/app/public/people/January2026/                     │
│                                                                  │
│ 8. Retornar ruta original                                       │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
Archivos creados:
  - people/January2026/X7aB9...O8p.avif (1200px, 150KB)
  - people/January2026/X7aB9...O8p-banner.avif (900px, 80KB)
  - people/January2026/X7aB9...O8p-medium.avif (600px, 50KB)
  - people/January2026/X7aB9...O8p-small.avif (256px, 20KB)
  - people/January2026/X7aB9...O8p-cropped.avif (300x300, 15KB)

URLs públicas:
  - /storage/people/January2026/X7aB9...O8p.avif
  - /storage/people/January2026/X7aB9...O8p-banner.avif
  - /storage/people/January2026/X7aB9...O8p-medium.avif
  - /storage/people/January2026/X7aB9...O8p-small.avif
  - /storage/people/January2026/X7aB9...O8p-cropped.avif
```

---

## Diagrama de Relaciones Entre Modelos

```
┌─────────────────┐         ┌─────────────────┐
│     People      │         │     Users       │
├─────────────────┤         ├─────────────────┤
│ id (PK)         │◄────────│ person_id (FK)  │
│ person_type     │    1:1  │ id (PK)         │
│ ci              │         │ name            │
│ first_name      │         │ email           │
│ ...             │         │ password        │
│ registerUser_id │─┐       │ role_id (FK)    │
│ deleteUser_id   │ │       │ status          │
│ deleted_at      │ │       │ registerUser_id │
└─────────────────┘ │       │ deleteUser_id   │
         │          │       │ deleted_at      │
         │          │       └─────────┬───────┘
         │          │                 │
         │  ┌───────┴─────────────────┴───────┐
         │  │              │                   │
         │  │         ┌────┴────┐              │
         │  │         │         │              │
         │  │         ↓         ↓              │
         │  │   ┌─────────┐  ┌─────────┐      │
         │  │   │ Roles   │  │ Voyager │      │
         │  │   │         │  │ Models  │      │
         │  │   │ id (PK) │  │         │      │
         │  └───│ name    │  │ User    │      │
         │      │ display │  │ Role    │      │
         │      │         │  │ Permission     │
         │      └─────────┘  │ Setting  │      │
         │                   │ Menu    │      │
         │                   └─────────┘      │
         │                                      │
         └───────┬──────────────────────────────┘
                 │
        (Auto-relación para auditoría)
         registerUser_id → users.id
         deleteUser_id → users.id
```

---

## Diagrama de Flujo de AJAX (Listado de Personas)

```
Usuario ingresa a /admin/people
        │
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Vista: browse.blade.php                                          │
│   - Muestra input de búsqueda                                     │
│   - Muestra select de paginación                                 │
│   - Carga lista inicial vacía (#div-results)                      │
│   - Ejecuta: list() en document.ready                            │
└─────────────────────────────────────────────────────────────────┘
        │
        │ document.ready → list()
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ JavaScript: list(page = 1)                                       │
│   - Muestra loading                                              │
│   - Hace petición AJAX a /admin/people/ajax/list                 │
│   - Parámetros: search='', paginate=10, page=1                   │
└─────────────────────────────────────────────────────────────────┘
        │
        │ AJAX GET /admin/people/ajax/list?search=&paginate=10&page=1
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ PersonController@list()                                          │
│   - Recibe parámetros                                           │
│   - Ejecuta consulta Eloquent:                                   │
│       Person::query()                                            │
│           ->selectRaw("CONCAT(...) as full_name")               │
│           ->whereNull('deleted_at')                              │
│           ->orderByDesc('id')                                    │
│           ->paginate(10)                                         │
│   - Retorna vista: list.blade.php                                │
└─────────────────────────────────────────────────────────────────┘
        │
        │ HTML de tabla
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Vista: list.blade.php                                            │
│   - Muestra tabla con personas                                   │
│   - Muestra paginación (links)                                   │
│   - Incluye JavaScript para paginación                           │
└─────────────────────────────────────────────────────────────────┘
        │
        │ Inserta HTML en #div-results
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Usuario ve lista de personas                                    │
│   - Puede hacer clic en botón de página                          │
│   - Puede escribir en búsqueda (debounce 2s)                     │
└─────────────────────────────────────────────────────────────────┘
        │
        │ Usuario escribe "Juan"
        │
        │ setTimeout(2s) → list()
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ JavaScript: list(page = 1)                                       │
│   - Hace petición AJAX: search='Juan', paginate=10, page=1      │
└─────────────────────────────────────────────────────────────────┘
        │
        │ (Se repite el proceso con filtro de búsqueda)
        ↓
PersonController@list() → Filtra por "Juan" → Retorna resultados

┌─────────────────────────────────────────────────────────────────┐
│ Usuario ve resultados filtrados                                  │
│   - Mantiene página sin recargar (AJAX)                          │
└─────────────────────────────────────────────────────────────────┘
```

---

## Diagrama de Verificación de Licencias

```
┌─────────────────────────────────────────────────────────────────┐
│ Petición HTTP al sistema                                         │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Middleware: System                                               │
│   ─────────────────────────────────────────────────────────────  │
│   1. Verificar si ruta está abierta                             │
│      - /admin/login → Abrir                                     │
│      - /admin/logout → Abrir                                    │
│      - /admin/voyager-assets* → Abrir                           │
│      - / → Abrir                                                 │
│                                                                  │
│   2. Verificar mantenimiento                                     │
│      - setting('configuracion.maintenance') === '1'             │
│      - Sí → Solo admins                                         │
│      - No → Continuar                                            │
│                                                                  │
│   3. Verificar desarrollo                                        │
│      - setting('system.development')                             │
│      - Sí → Solo admins                                         │
│      - No → Continuar                                            │
│                                                                  │
│   4. Verificar licencia                                          │
│      - $controller = new SolucionDigitalController()            │
│      - $data = $controller->settings_code()                      │
│      - $payment = new Controller()                              │
│      - $status = $payment->payment_alert()                      │
│                                                                  │
│      Si $status === 'finalizado':                               │
│        - Bloquear métodos: POST, PUT, PATCH, DELETE             │
│        - Permitir rutas: login, logout, settings                │
│        - Redirigir con mensaje de error                          │
│                                                                  │
│   5. Continuar a controlador                                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## Diagrama de Sistema de Logs

```
┌─────────────────────────────────────────────────────────────────┐
│ Petición HTTP al sistema                                         │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Middleware: Loggin                                               │
│   ─────────────────────────────────────────────────────────────  │
│   1. Verificar URL no es /admin/compass                          │
│      - Sí es compass → No registrar                              │
│      - No es compass → Continuar                                 │
│                                                                  │
│   2. Intentar registrar usuario                                  │
│      if(!Auth::user()->hasRole('admin'))                         │
│      {                                                           │
│          - user_id: Auth::user()->id                             │
│          - role: Auth::user()->role->name                       │
│          - name: Auth::user()->name                              │
│          - email: Auth::user()->email                            │
│          - ip: request()->ip()                                   │
│          - url: request()->url()                                 │
│          - method: request()->method()                           │
│          - input: request()->except(['password', '_token'])     │
│                                                                  │
│          Log::channel('requests')->info('Petición...', $data)   │
│      }                                                           │
│                                                                  │
│   3. Si hay error, registrar solo datos básicos                   │
│      - ip, url, method, input                                    │
│                                                                  │
│   4. Continuar                                                    │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Log guardado en archivo                                           │
│   ─────────────────────────────────────────────────────────────  │
│   Archivo: storage/logs/requests-2026-01-18.log                 │
│   Formato:                                                        │
│     [2026-01-18 15:30:45] local.INFO: Petición... {...}         │
│                                                                  │
│   Rotación diaria:                                               │
│     - requests-2026-01-18.log                                    │
│     - requests-2026-01-19.log                                    │
│     - requests-2026-01-20.log                                    │
│     - ...                                                        │
│                                                                  │
│   Retención: 30 días                                             │
└─────────────────────────────────────────────────────────────────┘
        │
        ↓
┌─────────────────────────────────────────────────────────────────┐
│ Visualizar logs                                                   │
│   ─────────────────────────────────────────────────────────────  │
│   1. Desde terminal:                                              │
│      tail -f storage/logs/requests-2026-01-18.log                │
│                                                                  │
│   2. Desde Voyager Compass:                                      │
│      /admin/compass                                              │
│      - Seleccionar canal: requests                              │
│      - Filtrar por nivel, fecha, texto                           │
│                                                                  │
│   3. Buscar en logs:                                             │
│      grep "Petición" storage/logs/requests-*.log                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## Notas Sobre los Diagramas

1. **Arquitectura Capas:** El sistema sigue una arquitectura de capas típica MVC con middleware adicionales.

2. **Auditoría:** El trait RegistersUserEvents asegura que se registre quién crea/elimina registros.

3. **Imágenes:** StorageController genera múltiples versiones de imágenes automáticamente.

4. **AJAX:** Los listados usan AJAX para cargar datos sin recargar la página.

5. **Licencias:** El sistema verifica el estado de licencia en cada petición vía middleware System.

6. **Logs:** El middleware Loggin registra todas las peticiones HTTP en el canal requests.

7. **Soft Deletes:** Las eliminaciones son lógicas (soft deletes), no físicas.

8. **Relaciones:** Users y People tienen una relación uno a uno opcional.

9. **Permisos:** Los permisos de Voyager controlan el acceso a diferentes acciones BREAD.

10. **Vistas:** Las vistas personalizadas sobrescriben las de Voyager para mayor flexibilidad.
