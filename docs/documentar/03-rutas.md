# Rutas del Sistema

## Archivo de Rutas

**Archivo principal:** `routes/web.php`

## Middleware Aplicados

El sistema usa un grupo de rutas con middleware personalizados:

```php
Route::prefix('admin')->middleware(['loggin', 'system'])->group(function () {
    // Rutas del sistema
});
```

**Middlewares:**
- `loggin`: Registra todas las peticiones HTTP en logs
- `system`: Controla acceso, mantenimiento y licencias
- `auth`: Verifica autenticación (en controladores)

---

## Redirecciones

```php
// Redirección raíz
Route::redirect('login', 'admin/login')->name('login');
Route::redirect('/', 'admin');
```

---

## Rutas de Personas

### Listado Principal
```php
GET /admin/people
Name: voyager.people.index
Controller: PersonController@index
Permiso: browse_people
Vista: administrations.people.browse
```

### Lista AJAX
```php
GET /admin/people/ajax/list
Name: voyager.people.ajax.list
Controller: PersonController@list
Parámetros:
  - search: texto de búsqueda
  - paginate: registros por página (default: 10)
  - page: número de página
Vista: administrations.people.list
```

### Crear Persona
```php
POST /admin/people
Name: voyager.people.store
Controller: PersonController@store
Permiso: add_people
Validación: Imagen válida
```

### Actualizar Persona
```php
PUT /admin/people/{id}
Name: voyager.people.update
Controller: PersonController@update
Permiso: edit_people
Validación: Imagen válida (si se envía)
```

---

## Rutas de Usuarios

### Lista AJAX de Usuarios
```php
GET /admin/users/ajax/list
Name: voyager.users.ajax.list
Controller: UserController@list
Parámetros:
  - search: texto de búsqueda
  - paginate: registros por página (default: 10)
  - page: número de página
Filtros:
  - Si no es admin, no ve otros admins
  - Busca en ID, nombre, email
Vista: vendor.voyager.users.list
```

### Crear Usuario
```php
POST /admin/users/store
Name: voyager.users.store
Controller: UserController@store
Validaciones:
  - Email único
  - Persona debe existir y estar activa
```

### Actualizar Usuario
```php
PUT /admin/users/{id}
Name: voyager.users.update
Controller: UserController@update
Campos actualizables:
  - status: 0 o 1
  - role_id: ID del rol
  - password: nueva contraseña
```

### Eliminar Usuario
```php
DELETE /admin/users/{id}/deleted
Name: voyager.users.destroy
Controller: UserController@destroy
Tipo: Soft delete
```

---

## Rutas de Roles

### Lista AJAX de Roles
```php
GET /admin/roles/ajax/list
Name: voyager.roles.ajax.list
Controller: RoleController@list
Retorna: JSON con roles
```

---

## Rutas AJAX Genéricas

### Lista de Personas (Autocompletado)
```php
GET /admin/ajax/personList
Controller: AjaxController@personList
Parámetro: q (query de búsqueda)
Retorna: JSON con personas
Uso: Para selectores/autocompletados
```

### Crear Persona Rápida
```php
POST /admin/ajax/person/store
Controller: AjaxController@personStore
Body: Campos de persona
Retorna: JSON con persona creada o error
```

---

## Rutas de Utilidades

### Limpiar Caché
```php
GET /admin/clear-cache
Name: clear.cache
Acción: Ejecuta `php artisan optimize:clear`
Redirect: /admin/profile
Flash: success message
```

---

## Rutas de Voyager

Todas las rutas nativas de Voyager están incluidas:

```php
Voyager::routes();
```

Estas incluyen:
- Login/logout
- Dashboard
- BREAD (browse, read, edit, add, delete)
- Menús
- Roles
- Permisos
- Configuraciones
- Media
- Compass (logs)
- etc.

---

## Middleware Personalizados

### Middleware Loggin

**Archivo:** `app/Http/Middleware/Loggin.php`

**Función:**
1. Registra todas las peticiones HTTP en logs
2. Excluye logs cuando el usuario accede a `/admin/compass`
3. Si el usuario NO es admin, registra:
   - user_id
   - role
   - name
   - email
   - ip
   - url
   - method
   - input (excluye password, _token, _method)
4. Si hay error o no hay usuario, registra solo ip, url, method, input

**Canal de logs:** `requests`
**Configuración:** `config/logging.php`

### Middleware System

**Archivo:** `app/Http/Middleware/System.php`

**Función:**

1. **Rutas siempre abiertas:**
   - `/admin/login`
   - `/admin/logout`
   - `/admin/password/*`
   - `/admin/voyager-assets*`
   - `/`

2. **Modo mantenimiento:**
   - Si `setting('configuracion.maintenance') === '1'`
   - Solo permite acceso a roles: admin, Administrador
   - Retorna error 503 para otros usuarios

3. **Modo desarrollo:**
   - Si `setting('system.development')`
   - Solo permite acceso a admins
   - Retorna error 503 para otros usuarios

4. **Verificación de licencia:**
   - Obtiene datos de licencia vía `SolucionDigitalController`
   - Si la licencia está finalizada:
     - Bloquea métodos POST, PUT, PATCH, DELETE
     - Permite acceso solo a: login, logout, settings
     - Redirige con mensaje de error

5. **Continuar:**
   - Si todo está bien, permite acceso normal

---

## Orden de Ejecución de Middlewares

1. `auth` - Verifica si usuario está autenticado
2. `loggin` - Registra la petición en logs
3. `system` - Verifica mantenimiento, desarrollo y licencia
4. Controlador - Ejecuta la lógica del controlador

---

## Nomenclatura de Rutas

### Prefijos
- `/admin` - Rutas administrativas
- `/admin/ajax` - Rutas AJAX
- `/admin/people` - Rutas de personas
- `/admin/users` - Rutas de usuarios
- `/admin/roles` - Rutas de roles

### Nombres de Rutas
- `voyager.{recurso}.{accion}` - Rutas estándar
- `voyager.{recurso}.ajax.{accion}` - Rutas AJAX
- `clear.cache` - Ruta de utilidad

---

## Ejemplos de Uso

### En Vistas (Blade)
```php
<a href="{{ route('voyager.people.index') }}">Ver Personas</a>
<form action="{{ route('voyager.people.store') }}" method="POST">
    {{ csrf_field() }}
</form>
```

### En JavaScript
```javascript
let url = "{{ url('admin/people/ajax/list') }}";
$.ajax({
    url: url,
    method: 'GET',
    data: { search: 'Juan', paginate: 10 }
});
```

### En Controladores
```php
return redirect()->route('voyager.people.index')
    ->with(['message' => 'Éxito', 'alert-type' => 'success']);
```

---

## Notas Importantes

1. **Autenticación:** Todas las rutas requieren autenticación (excepto login).

2. **Permisos:** Muchas rutas verifican permisos BREAD (`browse_people`, `add_people`, etc.).

3. **Logs:** Se registran TODAS las peticiones (excepto compass) para auditoría.

4. **Licencia:** El sistema puede bloquear acciones si la licencia está vencida.

5. **Mantenimiento:** Se puede activar modo mantenimiento desde settings.

6. **Desarrollo:** Se puede activar modo desarrollo para solo admins.

7. **AJAX:** Las rutas AJAX retornan vistas parciales o JSON para actualizaciones dinámicas sin recargar página.

8. **Voyager:** Se preservan todas las rutas nativas de Voyager para compatibilidad.

9. **Redirecciones:** La raíz `/` y `/login` redirigen a `/admin` y `/admin/login` respectivamente.

10. **Soft Deletes:** Las rutas de eliminación ejecutan soft deletes, no borrado físico.
