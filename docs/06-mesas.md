# 📑 Documentación Técnica: Módulo de Mesas (v1.0)

**Proyecto:** Sistema de Gestión Electoral - Beni 2026
**Estado:** Completa (Producción)
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
Para asegurar que el `codigo_tse` cumpla estrictamente, se ha implementado el trigger:

- **`trg_mesas_validacion`:** Bloquea cualquier inserción (`BEFORE INSERT`) que no contenga exactamente 11 dígitos numéricos, devolviendo el error SQLSTATE '45000'.

---

## 2. Lógica de Negocio (Eloquent & Controladores)

### 🧠 Modelo `Mesa.php`
- **Relación Inversa:** `belongsTo(Recinto::class)`.
- **Relación Descendente:** `hasMany(ActaEscrutinio::class)`.
- **Scopes:** `scopeHabilitadas()` permite filtrar rápidamente las mesas aptas para recibir votos.
- **Casts:** Garantiza que el `numero_mesa` sea siempre un entero para cálculos matemáticos precisos.

### 🕹️ Controlador `MesaController.php`
- **AJAX:** Implementa el Trait `ManagesCrud` para cargas de listas ultra rápidas sin recarga de página.
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
- **Read:** Vista de auditoría que alerta si existe una incoherencia (ej. mesa escrutada sin actas).

---

## 5. Reporte de Bugs y Mejoras Técnicas  ✅ COMPLETADO

### 🛠️ Reporte de Bugs
#### 1. Inconsistencia de Validación (Backend vs DB) ✅ RESUELTO
- **Ubicación:** `app/Http/Requests/StoreMesaRequest.php` y `UpdateMesaRequest.php`.
- **Problema:** El Request permitía `max:20` caracteres, pero la Migración y el Trigger SQL solo aceptaban exactamente 11.
- **Solución:** Cambiado a `'required|regex:/^[0-9]{11}$/|unique:mesas'` para validación estricta de 11 dígitos numéricos exactos.
- **Estado:** ✅ Implementado y probado.

#### 2. El "Fantasma" de los Electores ✅ RESUELTO
- **Ubicación:** `database/migrations/2026_01_18_005_create_mesas_table.php` y `app/Models/Mesa.php`.
- **Problema:** No existía el campo `cantidad_electores` o padrón.
- **Solución:** Añadido `$table->integer('cantidad_electores')->unsigned()->default(0);` en la migración y en el `$fillable` y `$casts` del modelo.
- **Estado:** ✅ Implementado. Valor por defecto: 250 electores por mesa en el seeder.

#### 3. Falta de Restricción Lógica en el Seeder ✅ RESUELTO
- **Ubicación:** `database/seeders/MesaSeeder.php`.
- **Problema:** El seeder usaba `updateOrInsert` basado solo en `codigo_tse`.
- **Solución:** Implementado `DB::table('mesas')->truncate()` antes de sembrar para asegurar de datos fresca y evitar duplicados o mesas huérfanas.
- **Estado:** ✅ Implementado. Lógica cambiada a `insert()` puro tras truncado.

#### 4. Vulnerabilidad de Carga en la API (N+1) ✅ RESUELTO
- **Ubicación:** `app/Http/Controllers/Api/MesaController.php`.
- **Problema:** Aunque usaba `with()`, el JSON podía volverse pesado con muchas actas.
- **Solución:** Creado `App\Http\Resources\Api\MesaResource` para control total de transformación de JSON con carga condicional y optimización de campos.
- **Estado:** ✅ Implementado. Incluye información de fotos de actas y todos los campos relevantes.

---

## 6. Sugerencias de Expansión (Futura) ✅ IMPLEMENTADO

### 🚀 Mejoras Propuestas
#### 1. Registro de "Fotos de Actas" ✅ IMPLEMENTADO
- **Ubicación:** `database/migrations/2026_01_18_007_create_actas_escrutinio_table.php`.
- **Idea:** Cada mesa debe vincularse a imágenes del acta física para transparencia total.
- **Solución:** La tabla de actas ya incluye `foto_frontal` y `foto_reverso` (string 255, nullable). El Api/MesaResource incluye estas URLs en la respuesta.
- **Estado:** ✅ Implementado y funcional.

#### 2. Dashboard de "Transmisión" ✅ IMPLEMENTADO
- **Ubicación:** `app/Http/Controllers/Api/MesaController.php` y `routes/api.php`.
- **Idea:** Contador en tiempo real: "Total Mesas: 1000 | Escrutadas: 450 (45%) | Faltantes: 550".
- **Solución:** Creado endpoint `GET /api/v1/mesas/estadisticas` que retorna:
  - `total_mesas`: Total de mesas en el sistema
  - `escrutadas`: Mesas con estado "Escrutada"
  - `habilitadas`: Mesas con estado "Habilitada"
  - `anuladas`: Mesas con estado "Anulada"
  - `observadas`: Mesas con estado "Observada"
  - `total_electores`: Suma de electores en todas las mesas
  - `porcentaje_escrutadas`: Porcentaje de progreso
  - `faltantes`: Mesas pendientes de escrutinio
  - `porcentaje_faltantes`: Porcentaje restante
- **Estado:** ✅ Implementado y disponible vía API.

---

## 7. Mejoras Adicionales Implementadas

### 🔧 Optimizaciones Técnicas
#### 1. Validación Reforzada
- **Cambio:** Agregado campo `cantidad_electores` a las validaciones de Store y Update requests.
- **Validación:** `required|integer|min:0` con mensajes de error personalizados en español.

#### 2. API Resource Completo
- **Cambio:** MesaResource incluye:
  - Información básica de la mesa (id, código, estado, electores, número)
  - Datos del recinto con geografía anidada
  - Lista de actas con cargo, estado y fotos
  - Conteos de votos (sobres, válidos, blancos, nulos)
  - Timestamps de creación y actualización

#### 3. Protección de Datos en Seeder
- **Cambio:** El seeder ahora trunca la tabla antes de insertar, garantizando:
  - No duplicados lógicos
  - Datos frescos en cada ejecución
  - Sincronización perfecta con recintos actuales

#### 4. Endpoint de Estadísticas en Tiempo Real
- **Ruta:** `GET /api/v1/mesas/estadisticas`
- **Middleware:** `auth:sanctum` + `throttle:120,1`
- **Uso:** Ideal para dashboards y monitoreo en tiempo real del escrutinio

---

## 8. Mejoras de Interfaz de Usuario (UI) ✅ IMPLEMENTADO

### 🎨 Vistas Actualizadas

#### 1. Formulario de Edición/Creación (`edit-add.blade.php`)
**Ubicación:** `resources/views/admin/mesas/edit-add.blade.php`

**Mejoras implementadas:**
- ✅ Campo `cantidad_electores` agregado en el formulario con validación HTML5 (min="0")
- ✅ Validación `pattern="[0-9]{11}"` en el campo código TSE para forzar 11 dígitos
- ✅ Layout mejorado: distribución en 3 columnas (Código, Número, Electores)
- ✅ Placeholder descriptivo: "Ej: 250" para electores
- ✅ Mensaje de ayuda: "Total de electores en el padrón de esta mesa"

#### 2. Vista de Detalle (`read.blade.php`)
**Ubicación:** `resources/views/admin/mesas/read.blade.php`

**Mejoras implementadas:**
- ✅ Campo `cantidad_electores` mostrado con badge azul e ícono de personas
- ✅ Formato numérico con separador de miles: `number_format($mesa->cantidad_electores)`
- ✅ **Nueva Sección:** "Actas de Escrutinio Registradas" con:
  - Visualización en grid de 2 columnas
  - Información completa de cada acta: código, cargo, estado
  - Conteos de votos: sobres, votantes, válidos, blancos, nulos
  - Botones para ver fotos frontal y reverso (si existen)
  - Alertas si no hay fotos registradas
  - Estados de acta con colores: Pendiente (gris), Digitada (info), Observada (warning), Validada (success), Cerrada (primary)
- ✅ Alerta informativa cuando no hay actas registradas

#### 3. Lista de Mesas (`list.blade.php`)
**Ubicación:** `resources/views/admin/mesas/list.blade.php`

**Mejoras implementadas:**
- ✅ Nueva columna "Electores" con badge azul y formato numérico
- ✅ Corregido colspan en mensaje "No se encontraron mesas" (6 → 7)
- ✅ Mejorada accesibilidad del botón de borrar usando `data-*` attributes
- ✅ Separador de miles en la cantidad de electores
- ✅ Código TSE formateado con `<code>` para destacar
- ✅ Estados con colores consistentes: Habilitada (verde), Escrutada (azul), Anulada (rojo), Observada (amarillo)

#### 4. Dashboard de Estadísticas (`browse.blade.php`)
**Ubicación:** `resources/views/admin/mesas/browse.blade.php`

**Mejoras implementadas:**
- ✅ **Panel de Estadísticas en Tiempo Real** con:
  - 6 tarjetas informativas:
    - Total Mesas (gris)
    - Escrutadas (verde)
    - Habilitadas (amarillo)
    - Observadas (rojo)
    - Faltantes (gris)
    - Progreso % (azul)
  - Barra de progreso animada con porcentaje completado
  - Colores diferenciados por estado
  - Auto-refresh cada 30 segundos vía JavaScript
  - Consumo de API `/api/v1/mesas/estadisticas`
  - Formato numérico con `toLocaleString()` para miles
- ✅ Estilos CSS personalizados para las tarjetas de estadísticas
- ✅ Script JavaScript integrado para actualización en tiempo real
- ✅ Visualización responsive (2 columnas en móvil, 6 en desktop)

---

### 🔄 Flujo de Datos en Tiempo Real

```javascript
// Actualización automática cada 30 segundos
setInterval(loadEstadisticas, 30000);

// Endpoint consumido
GET /api/v1/mesas/estadisticas

// Datos mostrados:
- Total Mesas: 1,234
- Escrutadas: 567 (45.9%)
- Faltantes: 667
- Progreso: [███████████████░░░░░░░░░░] 45.9%
```

---

### 📱 UX/UI Mejorada

#### Accesibilidad:
- Validaciones HTML5 en formularios
- Mensajes de error descriptivos en español
- Atributos `aria-*` en barra de progreso
- Tooltips en botones de acción

#### Responsive:
- Grid adaptable en vista de detalle (actas)
- Tablas scrollables en móviles
- Tarjetas de estadísticas en columnas adaptativas

#### Feedback Visual:
- Colores semánticos consistentes en toda la interfaz
- Iconos representativos (Voyager icons)
- Estados destacados con badges y labels
- Barra de progreso animada
- Alertas contextuales (info, warning, danger, success)
