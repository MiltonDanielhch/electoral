# 📑 Documentación Técnica: Módulo de Mesas (v1.0)

**Proyecto:** Sistema de Gestión Electoral - Beni 2026
**Estado:** Sintonía Completa (Producción)
**Dependencias:** Módulo de Recintos, Módulo de Geografía.

---

## 1. Arquitectura de Datos (Capa DB)

La mesa representa la unidad mínima de escrutinio. Su integridad está protegida tanto a nivel de aplicación como de motor de base de datos.

### 🗄️ Esquema de Tabla `mesas`
- **ID Primario:** `id_mesa` (BigInt)
- **Código TSE:** `codigo_tse` (Char 11, Único). Identificador oficial nacional.
- **Relaciones:** `id_recinto` (Foreign Key -> `recintos`).
- **Estados (Enum):** `Habilitada`, `Escrutada`, `Anulada`, `Observada`.
- **Seguridad:** Implementa `SoftDeletes` para trazabilidad forense.

### 🛡️ Disparadores (Triggers SQL)
Para asegurar que el `codigo_tse` cumpla estrictamente con la Master Formula, se ha implementado el trigger:

- **`trg_mesas_validacion`:** Bloquea cualquier inserción (`BEFORE INSERT`) que no contenga exactamente 11 dígitos numéricos, devolviendo el error SQLSTATE '45000'.

---

## 2. Lógica de Negocio (Eloquent & Controladores)

### 🧠 Modelo `Mesa.php`
- **Relación Inversa:** `belongsTo(Recinto::class)`.
- **Relación Descendente:** `hasMany(ActaEscrutinio::class)`.
- **Scopes de Sintonía:** `scopeHabilitadas()` permite filtrar rápidamente las mesas aptas para recibir votos.
- **Casts:** Garantiza que el `numero_mesa` sea siempre un entero para cálculos matemáticos precisos.

### 🕹️ Controlador `MesaController.php`
- **Sintonía AJAX:** Implementa el Trait `ManagesCrud` para cargas de listas ultra rápidas sin recarga de página.
- **Protección de Eliminación:** El método `destroy` valida si existen actas vinculadas antes de permitir el borrado, evitando la pérdida de integridad electoral.
- **Búsqueda Multidimensional:** Permite buscar mesas por su número, por el código oficial o por el nombre del recinto asociado.

---

## 3. Seguridad y Acceso (Policies & Routes)

### 🔐 Política de Acceso (`MesaPolicy.php`)
El acceso está vinculado a la matriz de permisos de Voyager:
- **`browse_mesas` / `read_mesas`:** Visualización y reportes.
- **`add_mesas` / `edit_mesas`:** Gestión operativa.
- **`delete_mesas`:** Permiso crítico restringido a administradores de sistema.

### 🛣️ Gestión de Rutas (`web.php` & `api.php`)
- **Rutas Web:** Protegidas por los middlewares `loggin` y `system` para auditoría de cada clic.
- **Rutas API:** Implementan Throttling (60 rpm) para proteger el servidor de peticiones masivas durante la noche del escrutinio.

---

## 4. Interfaz de Usuario (UI)

La interfaz se ha diseñado para la eficiencia bajo presión:

- **Browse:** Tabla dinámica con etiquetas de colores (Labels) para identificar el estado de la mesa al instante.
- **Formularios:** Implementación de Select2 para la búsqueda rápida entre cientos de recintos del departamento.
- **Read:** Vista de auditoría que alerta si existe una incoherencia de sintonía (ej. mesa escrutada sin actas).

---

## 5. Reporte de Bugs y Mejoras Técnicas (Código 3026)

### 🛠️ Reporte de Bugs
#### 1. Inconsistencia de Validación (Backend vs DB)
- **Ubicación:** `app/Http/Requests/StoreMesaRequest.php` y `UpdateMesaRequest.php`.
- **El Problema:** El Request permite `max:20` caracteres, pero la Migración y el Trigger SQL solo aceptan exactamente 11.
- **Riesgo:** Si un usuario mete 15 dígitos, Laravel dirá que está "OK", pero la Base de Datos lanzará un error 500 (SQLState 45000), lo que se ve muy mal en la interfaz de usuario.
- **Sugerencia:** Cambiar `'codigo_tse' => 'required|string|max:20|unique:mesas'` por `'required|regex:/^[0-9]{11}$/|unique:mesas'`.

#### 2. El "Fantasma" de los Electores
- **Ubicación:** `database/migrations/[fecha]_create_mesas_table.php` y `app/Models/Mesa.php`.
- **El Problema:** No existe el campo `cantidad_electores` o padrón.
- **Riesgo:** Sin este dato, no podemos calcular el Porcentaje de Participación ni el Ausentismo. Tampoco podemos validar si los votos totales superan a los votantes (fraude o error de dedo).
- **Sugerencia:** Añadir `$table->integer('cantidad_electores')->default(0);` en la migración y en el `$fillable` del modelo.

#### 3. Falta de Restricción Lógica en el Seeder
- **Ubicación:** `database/seeders/MesaSeeder.php`.
- **El Problema:** El seeder usa `updateOrInsert` basado solo en `codigo_tse`.
- **Riesgo:** Si cambias un recinto de nombre o código, el seeder podría crear duplicados lógicos o dejar mesas huérfanas con códigos antiguos.
- **Sugerencia:** Limpiar la tabla o usar una lógica de truncado controlado antes de sembrar, para asegurar que la sintonía de los datos sea fresca.

#### 4. Vulnerabilidad de Carga en la API (N+1)
- **Ubicación:** `app/Http/Controllers/Api/MesaController.php`.
- **El Problema:** Aunque usas `with()`, si el volumen de actas por mesa crece mucho (Gobernador, Alcalde, Concejales, Asambleístas...), el JSON se volverá pesado.
- **Riesgo:** Lentitud en la App móvil en zonas con mala señal.
- **Sugerencia:** Implementar un Resource de API (`MesaResource`) para tener un control total de la transformación del JSON y paginar las actas si es necesario.

---

## 6. Sugerencias de Expansión (Sintonía Futura)

### 🚀 Mejoras Propuestas
#### 1. Registro de "Fotos de Actas"
- **Ubicación:** `app/Models/Mesa.php` (Relación futura).
- **Idea:** Cada mesa debería poder vincularse no solo a datos numéricos, sino a una imagen del acta física para transparencia total.
- **Acción:** Preparar un campo `foto_acta_path` en la tabla de actas que se vinculará a la mesa.

#### 2. Dashboard de "Sintonía de Transmisión"
- **Ubicación:** `resources/views/admin/mesas/browse.blade.php`.
- **Idea:** Un contador en tiempo real arriba de la tabla que diga: "Total Mesas: 1000 | Escrutadas: 450 (45%) | Faltantes: 550".
- **Acción:** Crear un componente de estadísticas en el `MesaController@index`.
