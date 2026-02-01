# 🗳️ Documentación Técnica: Módulo de Candidatos

**Componente:** Definición de Rutas y Endpoints (`web.php`)  
**Estado:** Sintonía Activada 3026

---

## 1. Infraestructura de Rutas

El enrutamiento se ha diseñado siguiendo los principios de RESTful Routing, encapsulado en un prefijo semántico para mantener la limpieza del archivo `web.php` y facilitar el mantenimiento.

### Matriz de Endpoints

| Método | URI | Nombre de Ruta | Función Técnica |
| :--- | :--- | :--- | :--- |
| GET | `/candidatos/ajax/list` | `admin.candidatos.ajax.list` | Punto de entrada para la carga asíncrona de la tabla. |
| GET | `/candidatos` | `admin.candidatos.index` | Vista principal (Browse). |
| POST | `/candidatos` | `admin.candidatos.store` | Persistencia de nuevos candidatos. |
| GET | `/candidatos/create` | `admin.candidatos.create` | Formulario de creación. |
| GET | `/candidatos/{candidato}` | `admin.candidatos.show` | Visualización detallada (Read). |
| GET | `/candidatos/{id}/edit` | `admin.candidatos.edit` | Formulario de edición. |
| PUT | `/candidatos/{candidato}` | `admin.candidatos.update` | Actualización de registro. |
| DELETE | `/candidatos/{candidato}` | `admin.candidatos.destroy` | Eliminación (Soft Delete). |

---

## 2. Ingeniería de Diseño: Rutas de Alta Disponibilidad

- **Inyección de Modelos (Route Model Binding):** El uso de `{candidato}` permite que Laravel resuelva automáticamente la instancia del modelo, inyectándola directamente en el controlador. Esto reduce líneas de código y centraliza el manejo de errores 404 Not Found.
- **Prioridad de Rutas:** La ruta `/create` se define estratégicamente antes de las rutas con parámetros variables para evitar colisiones con el resolvedor de modelos de Eloquent.
- **Separación de Tráfico AJAX:** Se ha dedicado un endpoint específico para el listado dinámico, lo que permite que el sistema escale sin interferir con las rutas de navegación estándar.

---

## 3. Seguridad y Protección de Endpoints

Aunque no se visualiza en el snippet, estas rutas heredan el middleware de grupo definido en el archivo maestro (usualmente `web` y `auth`), sumado a la protección intrínseca de las Policies analizadas anteriormente.

- **Protección de Métodos Críticos:** Las acciones de escritura (POST, PUT, DELETE) están blindadas contra ataques CSRF por defecto en el middleware de Laravel.
- **Semántica de Verbos:** Se respeta estrictamente el uso de PUT para actualizaciones y DELETE para bajas, alineándose con las mejores prácticas de arquitectura web moderna.

---

## 4. Análisis de Mejoras y Optimización (Master Formula)

Tras auditar el mapa de rutas, identificamos los siguientes puntos para fortalecer la arquitectura:

### A. Rate Limiting Electoral
Dada la sensibilidad del sistema, las rutas de escritura (`store`, `update`, `destroy`) deberían estar bajo un Rate Limiter específico para prevenir ataques de denegación de servicio (DoS) o intentos de fuerza bruta en la carga de datos.
**Propuesta:** Aplicar un middleware `throttle:15,1` a estas rutas específicas.

### B. Route Caching
Para optimizar el rendimiento en producción, se recomienda asegurar que no existan clausuras (Closures) en el archivo de rutas, permitiendo el uso de `php artisan route:cache`, lo cual reduce el tiempo de arranque de la aplicación.

### C. Parámetros de Seguridad
Se sugiere el uso de restricciones por expresión regular para los parámetros de ID, asegurando que el sistema no intente resolver modelos con caracteres no numéricos maliciosos:

```php
Route::put('/{candidato}', [CandidatoController::class, 'update'])
    ->name('admin.candidatos.update')
    ->where('candidato', '[0-9]+');
```

---

## 🛠️ Bugs Potenciales y Fixes Críticos

### A. El "Bug del ID Huérfano" en el Update
**Ubicación:** `UpdateCandidatoRequest.php`

**Problema:** Si por algún error de ruta el objeto `$this->route('candidato')` llega nulo, la línea `$this->route('candidato')->id_candidato` lanzará un error 500.

**Sugerencia:** Usa una validación defensiva:

```php
$candidatoId = $this->route('candidato') ? $this->route('candidato')->id_candidato : $this->id;
```

### B. Race Condition en el Borrado de Imágenes
**Ubicación:** `CandidatoController.php` (Método `update`)

**Problema:** Si el `Storage::delete` falla (por permisos de carpeta), el código sigue adelante y actualiza la base de datos con la nueva ruta, dejando un archivo "zombie" en el servidor o perdiendo la referencia si la transacción fallara después.

**Sugerencia:** Envolver el proceso en una Base de Datos Transaction para asegurar que si el archivo no se gestiona bien, el registro no se altere.

---

## 🚀 Mejoras de Lógica (Master Formula)

### A. Validación de Unicidad Electoral (Índice Compuesto)
**Ubicación:** `StoreCandidatoRequest.php` y `UpdateCandidatoRequest.php`

**Problema:** La base de datos tiene un índice único `uk_candidato_unico`. Si un usuario intenta registrar al mismo partido para el mismo cargo en la misma geografía, Laravel lanzará un error de SQL feo (23000) en lugar de un mensaje validado.

**Sugerencia:** Añade esta regla en el array de `rules()`:

```php
Rule::unique('candidatos')->where(fn ($q) => 
    $q->where('id_cargo', $this->id_cargo)
      ->where('id_geografia_postulacion', $this->id_geografia_postulacion)
      ->where('id_partido', $this->id_partido)
)->ignore($this->route('candidato')?->id_candidato, 'id_candidato')
```

### B. Implementación de "Slug" para la URL
**Ubicación:** `Candidato.php` (Modelo) y `web.php` (Rutas)

**Mejora:** Usar el ID en las URLs es funcional pero poco estético y revela el conteo de registros.

**Sugerencia:** Implementar `getRouteKeyName()` en el modelo para usar el CI o un Slug generado del nombre, aumentando la seguridad por oscurecimiento.

---

## 🎨 Optimización de UX y Frontend

### A. Previsualización de Imagen Dinámica
**Ubicación:** `edit-add.blade.php`

**Mejora:** El operador no sabe si la foto es correcta hasta que guarda.

**Sugerencia:** Insertar este pequeño script al final de la vista:

```javascript
document.getElementById('imagen').onchange = evt => {
    const [file] = evt.target.files;
    if (file) {
        // Crear un preview dinámico en un contenedor <img>
        document.getElementById('preview_img').src = URL.createObjectURL(file);
    }
}
```

### B. Filtro "Smart" en el Browse
**Ubicación:** `CandidatoController.php` (Método `applySearch`)

**Mejora:** La búsqueda actual es básica.

**Sugerencia:** Permitir buscar por Sigla de Partido aunque estemos en la tabla de Candidatos usando `orWhereHas`:

```php
$query->orWhereHas('partido', function($q) use ($search) {
    $q->where('sigla', 'like', "%$search%");
});
```

---

## 🛡️ Auditoría y Seguridad Avanzada

### A. Observer de Limpieza (Hard Delete)
**Ubicación:** Crear `app/Observers/CandidatoObserver.php`

**Mejora:** Si eliminas un candidato permanentemente, la imagen queda ocupando espacio.

**Sugerencia:** El Observer debe detectar el evento `forceDeleted` y ejecutar `Storage::disk('public')->delete($candidato->imagen)`.

### B. Middleware de "Modo Lectura"
**Ubicación:** `web.php`

**Mejora:** Si el proceso electoral entra en fase de "Votación", nadie debería poder editar candidatos.

**Sugerencia:** Crear un Middleware que bloquee las rutas `store`, `update` y `destroy` si una variable de configuración `ELECCIONES_BLOQUEADAS` es verdadera.
