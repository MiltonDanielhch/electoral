## ⚙️ Núcleo de Reutilización: Trait `ManagesCrud`

El sistema implementa un patrón de diseño basado en **Traits** para centralizar la lógica operativa, reduciendo la duplicidad de código en un 80%.

### Funcionalidades Core:
1. **Resolución de Modelos:** Instanciación dinámica del modelo definido en el controlador hijo mediante `$this->model`.
2. **Inyección de Filtros:** Implementa un "Hook" que busca el método `applySearch` en el controlador específico, permitiendo búsquedas personalizadas sin alterar el flujo base.
3. **Carga de Relaciones:** Soporta la propiedad protegida `$with` para optimizar consultas a la base de datos mediante Eager Loading.
4. **Paginación Adaptativa:** Gestiona automáticamente la paginación basada en la solicitud del cliente (AJAX), devolviendo únicamente la vista parcial necesaria (`listView`).

### Flujo de Datos en `list()`:
- Recibe parámetros `search` y `paginate`.
- Ordena automáticamente por la `primaryKey` del modelo en orden descendente.
- Renderiza la vista parcial configurada, inyectando la colección de `$items`.


# list-browse-script
## ⌨️ Capa de Interacción Dinámica (JavaScript)

El sistema utiliza un motor de renderizado asíncrono centralizado en `list-browse-script.blade.php`. Este script elimina la necesidad de recargas de página (F5) durante la gestión de datos.

### Características Principales:
- **Búsqueda Inteligente (Debouncing):** Implementa un retraso de 400ms en el filtrado para optimizar el rendimiento del servidor y mejorar la experiencia del usuario.
- **Navegación Fluida:** Captura los clics de la paginación de Laravel (`.pagination a`) para redirigirlos vía AJAX, manteniendo al usuario en el mismo contexto visual.
- **Feedback de Carga:** Gestiona estados visuales dinámicos mediante el icono `voyager-spin` mientras se procesan las peticiones al laboratorio de datos.
- **Gestión de Errores:** Incluye un bloque de "Failsafe" que notifica al usuario en pantalla si la conexión con el servidor falla, evitando que el sistema quede bloqueado en un estado de carga infinito.

### Funciones Clave:
- `list(page)`: Función maestra que sincroniza los filtros de búsqueda, cantidad de registros y número de página con el controlador.
- `deleteItem(url, name)`: Orquestador dinámico para la confirmación de eliminaciones, garantizando que el usuario siempre sepa exactamente qué registro está a punto de borrar.
