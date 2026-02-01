# 👥 Módulo de Gestión de Personas - Documentación Técnica (Código 3026)

## 1. Descripción General
El Módulo de Personas es el núcleo de identidad del sistema. Gestiona exclusivamente **Personas Naturales** (ciudadanos/humanos) para el proceso de votación, eliminando completamente el soporte para Personas Jurídicas que podrían comprometer la integridad del sistema electoral (1 Humano = 1 Voto).

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

### ✅ Eliminación de Persona Jurídica (Implementado 2026-02-01)
**Motivo:** Un sistema de votos debe ser "1 Humano = 1 Registro". Las empresas aumentan la superficie de ataque para inyectar "votos fantasma".

**Cambios realizados:**
- Eliminados campos: `person_type`, `legal_name`, `nit`
- Simplificado el accessor `getFullNameAttribute()`
- Actualizado el scope `search()` para búsqueda solo de personas naturales
- Formulario simplificado sin selector de tipo

**Campos de Persona Natural:**
- `first_name`, `middle_name` - Nombres
- `paternal_surname`, `maternal_surname` - Apellidos
- `tipo_doc`, `ci`, `ci_complemento` - Documento de identidad
- `birth_date` - Fecha de nacimiento (con casting automático)
- `gender` - Género
- `padron` - Registro electoral
- `email`, `phone`, `address` - Contacto
- `image` - Foto biométrica
- `status` - Estado (Activo/Inactivo/Pendiente)

---

## 4. Capa de Interfaz (Frontend)

### 4.1 Vista de Formulario (Edit/Add)
**Estado:** ✅ Simplificada y optimizada (2026-02-01)

- Eliminado selector de "Tipo de Persona"
- Campos de persona natural siempre visibles
- Validación HTML5 con atributos `required`
- Botón para eliminar imagen actual
- Mejoras UX con iconos y mensajes de ayuda

### 4.2 Listado Asíncrono (AJAX)
**Estado:** ✅ Paginación corregida con delegación de eventos (2026-02-01)

La paginación ahora usa delegación de eventos para funcionar correctamente con contenido recargado dinámicamente:

```javascript
$(document).off('click', '.page-link').on('click', '.page-link', function(e){
    e.preventDefault();
    let link = $(this).attr('href');
    if(link){
        let url = new URL(link);
        let page = url.searchParams.get('page') || 1;
        list(page);
    }
});
```

**Mejoras adicionales:**
- Estado vacío mejorado con icono y botón "Limpiar filtros"
- Delegación de eventos para botón eliminar
- Corrección de comillas en atributos onclick

---

## 5. Seguridad y Permisos (RBAC)
La integridad se mantiene mediante Laravel Policies.
- **Permissions:** `browse_people`, `read_people`, `edit_people`, `add_people`, `delete_people`.
- **Middleware:** `system` y `loggin` aseguran auditoría completa.

---

## 6. Manejo de Archivos
- **Almacenamiento:** Carpeta `public/storage/people/`.
- **Fallback:** Si no hay imagen, se renderiza `images/default.jpg`.
- **Limpieza:** La imagen anterior se elimina automáticamente al actualizar.

---

## 🔧 Implementación de Mejoras (Código 3026) - COMPLETADO

### ✅ 1. Eliminación de Persona Jurídica
**Ubicación:** `app/Models/Person.php`, `resources/views/admin/people/edit-add.blade.php`

**Estado:** ✅ Implementado el 2026-02-01

Se eliminó completamente el soporte para Personas Jurídicas del sistema, garantizando que solo ciudadanos humanos puedan ser registrados para votación.

**Cambios en el modelo:**
```php
// Eliminados del $fillable:
// 'person_type', 'legal_name', 'nit'

// Accessor simplificado:
public function getFullNameAttribute()
{
    return trim(collect([
        $this->first_name,
        $this->middle_name,
        $this->paternal_surname,
        $this->maternal_surname,
    ])->filter()->join(' '));
}

// Scope search simplificado:
$fullNameRaw = "TRIM(CONCAT(
    COALESCE(first_name, ''), ' ',
    COALESCE(middle_name, ''), ' ',
    COALESCE(paternal_surname, ''), ' ',
    COALESCE(maternal_surname, '')
))";
```

### ✅ 2. Casting de Fechas Optimizado
**Ubicación:** `app/Models/Person.php`

**Estado:** ✅ Ya implementado (existente)

```php
protected $casts = [
    'birth_date' => 'date',
    'status' => 'integer',
];
```

### ✅ 3. Accessor para Nombre Inteligente
**Ubicación:** `app/Models/Person.php`

**Estado:** ✅ Simplificado el 2026-02-01

Eliminada la lógica condicional para Persona Jurídica, ahora solo maneja nombres de personas naturales.

### ✅ 4. Limpieza de Disco en Controlador
**Ubicación:** `app/Http/Controllers/PersonController.php`

**Estado:** ✅ Implementado (existente)

```php
private function storeImage($file, $old = null)
{
    if ($old) {
        Storage::disk('public')->delete($old);
    }
    return $file ? $file->store('people', 'public') : null;
}
```

### ✅ 5. Corrección del Debug dd($th)
**Ubicación:** `app/Http/Controllers/PersonController.php` (método `update`)

**Estado:** ✅ Corregido el 2026-02-01

Eliminado el `dd($th)` que exponía información sensible y reemplazado por manejo profesional de errores:

```php
catch (\Throwable $th) {
    DB::rollback();
    \Illuminate\Support\Facades\Log::error('Error actualizando persona: ' . $th->getMessage());
    return redirect()->route('admin.people.index')->with([
        'message' => 'Ocurrió un error al actualizar el registro: ' . $th->getMessage(),
        'alert-type' => 'error'
    ]);
}
```

### ✅ 6. Paginación AJAX con Delegación de Eventos
**Ubicación:** `resources/views/admin/people/list.blade.php`

**Estado:** ✅ Corregido el 2026-02-01

Reemplazado el handler directo por delegación de eventos para que funcione con contenido recargado dinámicamente:

```javascript
// ❌ Antes (no funcionaba tras recarga AJAX):
$('.page-link').click(function(e){...});

// ✅ Ahora (funciona siempre):
$(document).off('click', '.page-link').on('click', '.page-link', function(e){...});
```

### ✅ 7. Estado Vacío Mejorado
**Ubicación:** `resources/views/admin/people/list.blade.php`

**Estado:** ✅ Implementado el 2026-02-01

```blade
@empty
    <tr>
        <td colspan="7" class="text-center" style="padding: 40px;">
            <div class="text-muted">
                <i class="voyager-search" style="font-size: 50px; margin-bottom: 10px; display: block;"></i>
                <p>No se encontraron personas con esos criterios.</p>
                <button class="btn btn-sm btn-info" onclick="$('#input-search').val('').trigger('input')">
                    <i class="voyager-refresh"></i> Limpiar filtros
                </button>
            </div>
        </td>
    </tr>
@endforelse
```

### ✅ 8. Migración de Base de Datos (Eliminación Persona Jurídica)
**Ubicación:** `database/migrations/2026_02_01_131459_remove_juridica_fields_from_people_table.php`

**Estado:** ✅ Creada el 2026-02-01

Elimina permanentemente los campos de persona jurídica de la base de datos:

```php
Schema::table('people', function (Blueprint $table) {
    // Eliminar campos de persona jurídica
    $table->dropColumn(['person_type', 'nit', 'legal_name']);
    
    // Actualizar índice único
    $table->dropUnique(['tipo_doc', 'ci', 'ci_complemento']);
    $table->unique(['ci', 'ci_complemento'], 'people_ci_unique');
});
```

### ✅ 9. Validación de CI Boliviano (Algoritmo Módulo 11)
**Ubicación:** `app/Models/Person.php`

**Estado:** ✅ Implementado el 2026-02-01

Validación matemática del Carnet de Identidad boliviano usando el algoritmo oficial de módulo 11:

```php
public static function validateBolivianCI(string $ci, ?string $complemento = null): bool
{
    // Algoritmo de módulo 11
    $sum = 0;
    $multipliers = [2, 3, 4, 5, 6, 7, 8, 9];
    // ... cálculo del dígito verificador
    return $calculatedCheckDigit == intval($checkDigit);
}
```

### ✅ 10. Detección de Duplicados Inteligente
**Ubicación:** `app/Models/Person.php` (scope) y `app/Http/Controllers/PersonController.php`

**Estado:** ✅ Implementado el 2026-02-01

Detecta posibles registros duplicados antes de guardar, comparando por CI exacto o combinación similar de nombres:

```php
// En el modelo
public function scopePotentialDuplicates($query, $ci, $firstName, $paternalSurname, $maternalSurname, $excludeId = null)
{
    return $query->where(function ($q) use ($ci, $firstName, $paternalSurname, $maternalSurname) {
        if ($ci) {
            $q->orWhere('ci', $ci);
        }
        // ... búsqueda por similitud de nombres
    })->limit(5);
}

// En el controlador - prevención antes de guardar
$duplicates = Person::potentialDuplicates($request->ci, ...)->get();
if ($duplicates->count() > 0) {
    return back()->with([
        'message' => 'Posibles duplicados: ' . $duplicateNames,
        'alert-type' => 'warning'
    ]);
}
```

### ✅ 11. Validación de Imágenes Mejorada
**Ubicación:** `app/Http/Controllers/PersonController.php`

**Estado:** ✅ Implementado el 2026-02-01

Validación completa de imágenes antes de almacenar:
- Tamaño máximo: 2MB
- Formatos: JPG, PNG
- Dimensiones: mínimo 100x100, máximo 2000x2000
- Lazy loading en el listado

```php
private function validateImage($file)
{
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file->getSize() > $maxSize) {
        return 'La imagen no debe superar los 2MB.';
    }
    
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($file->getMimeType(), $allowedTypes)) {
        return 'Formato no válido. Use JPG o PNG.';
    }
    
    // Validación de dimensiones...
    return true;
}
```

### ✅ 12. Accessors Adicionales
**Ubicación:** `app/Models/Person.php`

**Estado:** ✅ Implementado el 2026-02-01

```php
// CI formateado con complemento
public function getCiFormattedAttribute(): string
{
    return $this->ci . ($this->ci_complemento ? '-' . $this->ci_complemento : '');
}

// Edad calculada automáticamente
public function getAgeAttribute(): ?int
{
    return $this->birth_date ? $this->birth_date->age : null;
}
```

---

## 📊 Resumen de Implementaciones

| Acción | Impacto | Nivel de Prioridad | Estado |
| :--- | :--- | :--- | :--- |
| **Eliminar Persona Jurídica** | Seguridad Electoral | Crítica | ✅ Completado |
| **Corregir dd($th)** | Seguridad/UX | Alta | ✅ Completado |
| **Delegación de Eventos** | Funcionalidad AJAX | Alta | ✅ Completado |
| **Estado Vacío Mejorado** | UX | Media | ✅ Completado |
| **Simplificar Formulario** | UX | Media | ✅ Completado |
| **Casting de Fechas** | Integridad de Datos | Baja | ✅ Ya existente |
| **Limpieza de Disco** | Mantenimiento | Baja | ✅ Ya existente |
| **Migración DB** | Eliminación campos | Alta | ✅ Completado |
| **Validación CI** | Integridad de datos | Alta | ✅ Completado |
| **Detección Duplicados** | Calidad de datos | Alta | ✅ Completado |
| **Validación Imágenes** | Seguridad/UX | Media | ✅ Completado |
| **Lazy Loading Images** | Performance | Baja | ✅ Completado |

---

## 🔮 Mejoras Futuras Sugeridas (Backlog)

Basado en el análisis del código, quedan las siguientes mejoras para futuras iteraciones:

### 12. Caché de Imágenes con Thumbnails
**Impacto:** Rendimiento  
**Descripción:** Generar thumbnails automáticos de 100x100 píxeles para el listado usando Intervention Image, manteniendo la original para el detalle.

### 13. Búsqueda Avanzada con Filtros
**Impacto:** UX  
**Descripción:** Añadir filtros por rango de edad, género, estado y fecha de registro en el panel de búsqueda.

### 14. Importación Masiva desde Excel
**Impacto:** Productividad  
**Descripción:** Permitir carga masiva de personas desde archivo Excel con validación de CI y detección de duplicados en batch.

### 15. API REST para Consultas Externas
**Impacto:** Integración  
**Descripción:** Exponer endpoint seguro para consultar personas por CI desde otros sistemas institucionales.  
**Descripción:** Alertar al registrar si ya existe una persona con el mismo CI o combinación similar de nombres/apellidos.

### 12. Soft Deletes Mejorado
**Impacto:** Auditoría  
**Descripción:** Incluir información del usuario que eliminó y fecha en el listado de eliminados.

---

> **Última actualización:** 2026-02-01 - Eliminación de Persona Jurídica completada. Sistema ahora 100% enfocado en gestión de ciudadanos para proceso electoral.

---

## ⚠️ Instrucciones de Despliegue

### Ejecutar Migración
Después de actualizar el código, es **OBLIGATORIO** ejecutar la migración para eliminar los campos de persona jurídica de la base de datos:

```bash
php artisan migrate --path=database/migrations/2026_02_01_131459_remove_juridica_fields_from_people_table.php
```

O simplemente:

```bash
php artisan migrate
```

### Notas Importantes
- **Backup:** Realizar backup de la base de datos antes de ejecutar la migración
- **Datos existentes:** Si existen registros con `person_type = 'Jurídica'`, se recomienda migrarlos o eliminarlos antes de ejecutar
- **Índices:** La migración actualiza el índice único de CI para optimizar búsquedas

---

**Código 3026 - Sistema Electoral Optimizado para "1 Humano = 1 Voto"**
