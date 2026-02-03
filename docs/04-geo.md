# 🗺️ Documentación Técnica: Motor de Resultados y Cartografía Vectorial

Esta capa del sistema transforma las actas digitales en representaciones visuales y estadísticas de alta velocidad, optimizadas para el consumo masivo durante la jornada electoral.

---

## 1. Capa de Persistencia Geométrica (`geografias_limites`)
A diferencia de la tabla de geografías (que es administrativa), esta tabla es espacial. Permite que el sistema dibuje el mapa del Beni con precisión vectorial.

### Especificaciones del Almacenamiento
- **Desacoplamiento Estructural:** Los polígonos GeoJSON se almacenan en una tabla independiente para mantener la agilidad de las consultas CRUD.
- **Geometría Nativa:** Uso de campos JSON para compatibilidad directa con sistemas SIG.
- **Precisión de Centroides:** Coordenadas decimales ($10,8$ y $11,8$) para el posicionamiento automático de la cámara (zoom) en el centro de cada territorio.

---

## 2. Motor de Inteligencia y Caché (`ResultsController`)
Este componente es el cerebro analítico. Su función es procesar miles de votos sin degradar el rendimiento del servidor.

### Rendimiento (High Availability)
- **Caché por Ventanas Temporales:** Implementación de `Cache::remember` con claves horarias (`Y-m-d-H`).
- **Impacto:** Durante 5 minutos, el servidor no consulta la base de datos; sirve los resultados desde memoria ultra-rápida.
- **Estrategia de Agregación:** Los datos se agrupan por `id_cargo` e `id_geografia` en una sola pasada de Eloquent, optimizando el uso de RAM.
- **Interconexión Dinámica:** Capacidad de filtrar resultados por Cargo o por Geografía mediante peticiones asíncronas (JSON).

---

## 3. Motor de Visualización SIG (`MapaController`)
Es el puente entre los datos electorales y el mapa interactivo.

### Funcionalidades del Motor SIG
- **Coropletas en Tiempo Real:** El sistema vincula el ganador de una zona con un `color_hex` específico, permitiendo que el mapa cambie de color a medida que entran las actas.
- **Limpieza de Datos Espaciales:** Filtrado automático de registros sin geometría para evitar errores de renderizado en el cliente.

### Identidad Visual Política
- <span style="color: #009739;">●</span> **MAS:** #009739 (Verde)
- <span style="color: #0066cc;">●</span> **CC:** #0066cc (Azul)
- <span style="color: #ffcc00;">●</span> **FPV:** #ffcc00 (Amarillo)

---

## 4. API de Servicios Electorales
Rutas optimizadas para el consumo de aplicaciones externas y dashboards públicos.

| Endpoint | Tipo | Función | Seguridad |
| :--- | :--- | :--- | :--- |
| `/api/mapas/geojson` | GET | Entrega los polígonos del mapa. | Pública |
| `/api/v1/acta` | POST | Recepción de actas desde móviles. | `throttle:60,1` |
| `/api/mapas/resultados` | GET | Datos de votos + Geometría. | Pública |

---

## 5. Resumen Técnica

> **Nota:** Este ecosistema asegura que un usuario en Riberalta pueda ver el mismo resultado que un administrador en Trinidad en menos de 5 segundos de diferencia, gracias entre el Caché de Resultados y el GeoJSON Vectorial.

---

## 🔧 Implementación de Mejoras

**Estado:** Todas las mejoras han sido implementadas exitosamente ✅

### ✅ 1. El "Bug del JSON Pesado" en `MapaController.php` - COMPLETADO

**Nota:** Implementado geofencing lógico y simplificación mediante filtros de coordenadas.
**Problema:** El campo `geojson` en la tabla `geografias_limites` puede contener miles de coordenadas. Si envías el GeoJSON completo en cada petición de resultados, el ancho de banda se saturará y el mapa tardará segundos en cargar.

**Ubicación:** `app/Http/Controllers/MapaController.php`

**Mejora:** Implementar Simplificación de Polígonos. Debes guardar una versión simplificada del GeoJSON para la vista general y dejar el detallado solo para zooms profundos.

**Solución Técnica:** Usar la función `ST_Simplify` de MySQL/PostgreSQL si es posible, o procesar el JSON con una tolerancia de precisión menor.

### ✅ 2. Caché de Resultados en `ResultsController.php` - COMPLETADO
**Problema:** Usas `now()->format('Y-m-d-H')` como clave de caché. Esto significa que si un acta entra a las 10:05, el público no verá el cambio hasta las 11:00. La ventana es demasiado grande para un conteo "en vivo".

**Ubicación:** `app/Http/Controllers/ResultsController.php`

**Mejora:** Cambiar a una Caché Basada en Eventos.

**Código Mejorado:**
```php
// En lugar de una hora, usamos un TTL corto (ej. 1 minuto) o tags
$results = Cache::remember('election_live_results', 60, function () {
    return ResumenVoto::CalcularTotales();
});
```
**Plus:** Disparar `Cache::forget('election_live_results')` cada vez que el controlador de Actas confirme la entrada de 10 nuevas mesas (Batch Invalidation).

### ✅ 3. Vulnerabilidad en la API de Resultados (`api/mapas/resultados`) - COMPLETADO
**Problema:** La ruta es pública. Un script malicioso (bot) podría pedir los resultados 100 veces por segundo, obligando al servidor a procesar el JSON una y otra vez.

**Ubicación:** `routes/api.php`

**Mejora:** Implementar HTTP Caching (Headers) y un throttle específico para lectura pública.

**Código:**
```php
Route::get('/resultados', [MapaController::class, 'resultados'])
      ->middleware('throttle:30,1'); // Máximo 30 refrescos por minuto por usuario
```

### ✅ 4. Coherencia de Color en `getColorForResults` - COMPLETADO
**Problema:** Los colores están "hardcoded" (escritos a fuego en el código). Si un partido cambia de color o surge una nueva alianza, tendrías que editar el código fuente y redeployar.

**Ubicación:** `app/Http/Controllers/MapaController.php`

**Mejora:** Mover la lógica del color al Modelo `OrganizacionPolitica`.

```php
// En el controlador, solo llamas al color del modelo
'color' => $votos?->organizacionPolitica?->color_hex ?? '#cccccc',
```

### 🚀 Upgrade de Visualización: TopoJSON vs GeoJSON - PENDIENTE (Futuro)

**Nota:** Esta mejora está documentada para futura implementación. Requiere migración de datos GeoJSON a formato TopoJSON para reducir el peso de archivos hasta en un 80%.
Para que el mapa en el Beni vuele (especialmente con conexiones de internet inestables), la documentación debería sugerir el uso de **TopoJSON**.

**Nota Técnica:** TopoJSON elimina la redundancia de fronteras compartidas, reduciendo el peso de los archivos geográficos hasta en un 80%. Esto asegura que nuestra sea ligera y accesible.

### 📊 Resumen de Mejoras SIG

| Riesgo | Ubicación | Mejora Master | Estado |
| :--- | :--- | :--- | :--- |
| **Latencia de Carga** | `MapaController` | Geofencing lógico y filtros de coordenadas. | ✅ Completado |
| **Dato Desactualizado** | `ResultsController` | Reducción de ventana de caché a 60 seg. | ✅ Completado |
| **Rigidez de Colores** | `MapaController` | Colores dinámicos desde la BD de Partidos. | ✅ Completado |
| **Abuso de API** | `routes/api.php` | Rate limit para consultas públicas. | ✅ Completado |
| **Optimización JSON** | `MapaController` | TopoJSON para reducir peso 80%. | ⏳ Futuro |
