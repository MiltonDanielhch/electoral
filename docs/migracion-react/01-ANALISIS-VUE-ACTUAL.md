# 📊 Análisis del Código Vue Actual (Rama Electoral)

## 📁 Archivos Vue Existentes

### 1. Aplicación de Campo (Base)

#### Pages

##### `Home.vue` - Página de Búsqueda
**Ubicación:** `resources/js/Pages/Home.vue`  
**Tamaño:** ~180 líneas  
**Complejidad:** Media

**Funcionalidad:**
- Input para código de mesa (4 dígitos)
- Búsqueda asíncrona con API
- Validación de código
- Visualización de resultados de mesa
- Navegación a formulario de acta
- Manejo de errores

**Props:** Ninguna (usa Inertia props si las hay)
**Estado:**
- `codigoMesa` (ref): Código ingresado
- `loading` (ref): Estado de carga
- `mesa` (ref): Datos de mesa encontrada
- `error` (ref): Mensaje de error

**Métodos:**
- `buscarMesa()`: Busca mesa por código
- `formatDate()`: Formatea fecha

**Dependencias:**
- `@inertiajs/vue3` (Link)
- `axios`
- Composables: useNotification

---

##### `Acta.vue` - Página de Carga de Actas
**Ubicación:** `resources/js/Pages/Acta.vue`  
**Tamaño:** ~150 líneas  
**Complejidad:** Media

**Funcionalidad:**
- Muestra información de la mesa
- Renderiza formularios por cargo (dinámico)
- Indicador de conexión online/offline
- Manejo de múltiples actas por mesa (diferentes cargos)
- Notificaciones de éxito/error

**Props de Inertia:**
- `mesa`: Object - Datos de la mesa
- `cargos`: Array - Lista de cargos disponibles
- `partidos`: Array - Lista de partidos políticos

**Estado:**
- `actasCompletadas` (ref): Set de actas ya enviadas
- `isOnline` (ref): Estado de conexión

**Métodos:**
- `handleActaEnviada(cargoId)`: Marca acta como completada
- `verificarActasCompletadas()`: Consulta API por actas existentes
- `volver()`: Navega a Home

**Dependencias:**
- `@inertiajs/vue3` (Link, Head, router)
- `ActaForm` component
- `axios`
- Eventos: online/offline

---

#### Components

##### `ActaForm.vue` - Formulario de Acta
**Ubicación:** `resources/js/Components/ActaForm.vue`  
**Tamaño:** ~650 líneas  
**Complejidad:** Alta

**Funcionalidad:**
- Carga de imágenes (frente y reverso)
- Drag & drop de imágenes
- Vista previa de imágenes
- Compresión automática de imágenes
- Formulario de votos por partido
- Validación de votos (no negativos, suma correcta)
- Guardar borrador local (localStorage)
- Indicadores de compresión y carga
- Manejo de errores del API
- Notificaciones de éxito/error

**Props:**
```javascript
{
  mesa: Object,        // Datos de la mesa
  cargo: Object,       // Cargo electoral
  partidos: Array,     // Lista de partidos
  onEnviada: Function  // Callback cuando se envía
}
```

**Estado:**
- `form` (reactive): Datos del formulario
  - `votos`: Array de votos por partido
  - `observaciones`: String
  - `imagenFrente`: File
  - `imagenReverso`: File
- `imagenFrentePreview` / `imagenReversoPreview`: URLs de preview
- `loading`, `compressing`: Estados de carga
- `errors`: Errores de validación

**Métodos:**
- `handleImageUpload()`: Maneja carga de imágenes
- `compressImage()`: Comprime imagen
- `validarFormulario()`: Valida datos
- `guardarBorrador()`: Guarda en localStorage
- `cargarBorrador()`: Recupera de localStorage
- `enviarActa()`: Envía a API

**Dependencias:**
- `axios`
- `useImageCompression` composable
- `useNotification` composable
- FileReader API
- Canvas API (compresión)

---

##### `SyncStatus.vue` - Indicador de Sincronización
**Ubicación:** `resources/js/Components/SyncStatus.vue`  
**Tamaño:** ~150 líneas  
**Complejidad:** Media

**Funcionalidad:**
- Muestra estado de conexión (online/offline)
- Indica cantidad de actas pendientes
- Botón para sincronizar manualmente
- Lista de actas fallidas
- Opciones para reintentar o limpiar actas fallidas

**Props:** Ninguna

**Estado:**
- `isOnline`, `pendingCount`, `failedActas`, `lastSync`
- Usa composable `useOfflineSync`

**Dependencias:**
- `useOfflineSync` composable
- Eventos: online/offline

---

##### `ThemeToggle.vue` - Cambio de Tema
**Ubicación:** `resources/js/Components/ThemeToggle.vue`  
**Tamaño:** ~40 líneas  
**Complejidad:** Baja

**Funcionalidad:**
- Toggle entre modo oscuro/claro
- Persistencia en localStorage
- Iconos de sol/luna

**Props:** Ninguna

**Estado:**
- Usa composable `useTheme`

**Dependencias:**
- `useTheme` composable

---

#### Layouts

##### `AppLayout.vue` - Layout Principal
**Ubicación:** `resources/js/Layouts/AppLayout.vue`  
**Tamaño:** ~180 líneas  
**Complejidad:** Media

**Funcionalidad:**
- Navbar con branding y navegación
- ThemeToggle integrado
- SyncStatus integrado
- Slot para contenido principal
- Footer opcional
- Soporte para notificaciones toast
- Responsive design

**Props:**
- `title`: String - Título de la página

**Slots:**
- Default: Contenido principal

**Dependencias:**
- `ThemeToggle` component
- `SyncStatus` component
- `@inertiajs/vue3` (Link, Head)

---

### 2. Composables (Lógica Reutilizable)

#### `useImageCompression.js`
**Propósito:** Comprimir imágenes en navegador  
**Exporta:** `compressImage(file, maxWidth, quality)`

**Parámetros:**
- `file`: File - Imagen original
- `maxWidth`: Number (default: 1920) - Ancho máximo
- `quality`: Number (default: 0.75) - Calidad 0-1

**Retorna:** Promise<File> - Imagen comprimida

**Implementación:**
- Canvas API para redimensionar
- FileReader para leer archivo
- toBlob para generar archivo comprimido

---

#### `useNotification.js`
**Propósito:** Sistema de notificaciones toast  
**Exporta:** `showSuccess()`, `showError()`, `showInfo()`, `clear()`

**Características:**
- Eventos personalizados
- Auto-dismiss después de 5 segundos
- Tipos: success (verde), error (rojo), info (azul)
- Múltiples notificaciones simultáneas

**Implementación:**
- CustomEvent API
- Event listeners

---

#### `useOfflineSync.js`
**Propósito:** Sincronización offline  
**Exporta:** Objeto con estado y métodos

**Estado:**
- `isOnline`: Boolean
- `pendingCount`: Number
- `failedActas`: Array
- `lastSync`: Date/String
- `syncing`: Boolean

**Métodos:**
- `sync()`: Sincroniza actas pendientes
- `addPendingActa(acta)`: Agrega acta a cola
- `getPendingActas()`: Obtiene actas pendientes
- `clearFailedActas()`: Limpia actas fallidas
- `retryFailedActa(id)`: Reintenta acta fallida

**Dependencias:**
- `storage` service (IndexedDB)
- `api` service
- Eventos: online/offline

---

#### `useTheme.js`
**Propósito:** Gestión de tema oscuro/claro  
**Exporta:** Objeto con estado y métodos

**Estado:**
- `isDark`: Boolean - true = dark mode
- `theme`: String - 'dark' | 'light'

**Métodos:**
- `toggle()`: Cambia tema
- `setTheme(theme)`: Establece tema específico
- `init()`: Inicializa desde localStorage/system preference

**Implementación:**
- localStorage para persistencia
- matchMedia para detectar preferencia del sistema
- Clases Tailwind 'dark'

---

### 3. Servicios

#### `api.js`
**Propósito:** Cliente HTTP para API  
**Exporta:** Objeto con métodos

**Configuración:**
- Base URL: `/api/v1`
- Timeout: 10 segundos
- Headers: Content-Type, Accept

**Métodos:**
- `getMesa(codigo)`: GET /mesas/{codigo}
- `getCatalogos(params)`: GET /catalogos
- `sendActa(formData)`: POST /actas (multipart/form-data)
- `checkActaStatus(mesaCodigo, cargoId)`: GET /actas/status
- `getResults(params)`: GET /results/api/live

**Interceptores:**
- Request: Agrega auth token si existe
- Response: Manejo de errores 401, 403, 500

---

#### `storage.js`
**Propósito:** Almacenamiento local con IndexedDB  
**Exporta:** Objeto con métodos

**Database:**
- Nombre: 'electoral_db'
- Versión: 1
- Stores: 'pending_actas', 'settings'

**Métodos:**
- `saveActa(acta)`: Guarda acta pendiente
- `getPendingActas()`: Obtiene todas las actas pendientes
- `deleteActa(id)`: Elimina acta específica
- `clearAll()`: Limpia todas las actas
- `saveSetting(key, value)`: Guarda configuración
- `getSetting(key)`: Obtiene configuración

---

### 4. Dashboard de Resultados Electorales (Plan 05)

#### Pages/Results

##### `Index.vue` - Dashboard Principal
**Ubicación:** `resources/js/Pages/Results/Index.vue`  
**Tamaño:** ~200 líneas  
**Complejidad:** Alta

**Funcionalidad:**
- Página principal del dashboard
- Integra todos los componentes de resultados
- Maneja estado de filtros
- Auto-refresh de datos
- Manejo de errores

**Props de Inertia:**
- `cargos`: Array - Lista de cargos
- `geografias`: Array - Lista de geografías
- `cargoId`: String/Number (opcional)
- `geografiaId`: String/Number (opcional)

**Estado:**
- Usa `useElectionResults` composable
- `selectedCargo`, `selectedGeografia`, `refreshInterval`
- `results`, `resultsByGeografia`, `stats`
- `loading`, `error`

**Dependencias:**
- ResultsLayout
- ResultsFilters, ResultsStats, ResultsTable
- ResultsChart, ResultsGeoTable
- AlertError, LoadingBar
- useElectionResults

---

#### Components/Results

##### `LiveClock.vue`
**Funcionalidad:** Reloj en tiempo real  
**Estado:** `currentTime` (ref)  
**Interval:** 1 segundo

##### `ConnectionIndicator.vue`
**Funcionalidad:** Indicador online/offline  
**Estado:** `isOnline` (ref)  
**Eventos:** online/offline

##### `ResultsStats.vue`
**Funcionalidad:** Grid de estadísticas  
**Props:** `stats` (Object)  
**Sub-componente:** StatCard

##### `StatCard.vue`
**Funcionalidad:** Tarjeta individual de stat  
**Props:** `title`, `value`, `subtitle`, `color`, `icon`

##### `ResultsFilters.vue`
**Funcionalidad:** Filtros de búsqueda  
**Props:** `modelValue`, `cargos`, `geografias`, `loading`  
**Emits:** `update:cargo`, `update:geografia`, `update:interval`, `refresh`

##### `ResultsTable.vue`
**Funcionalidad:** Tabla de resultados por partido  
**Props:** `results` (Array)  
**Features:** Gráfico de barras inline

##### `ResultsChart.vue`
**Funcionalidad:** Gráfico Chart.js  
**Props:** `results` (Array)  
**Dependencias:** Chart.js

##### `ResultsGeoTable.vue`
**Funcionalidad:** Tabla por geografía  
**Props:** `resultsByGeografia` (Array)  
**Features:** Muestra top 5 partidos por geografía

##### `AlertError.vue`
**Funcionalidad:** Alerta de error  
**Props:** `message` (String)

##### `LoadingBar.vue`
**Funcionalidad:** Barra de carga superior  
**Props:** `loading` (Boolean)

---

#### Composables

##### `useElectionResults.js`
**Propósito:** Lógica de resultados electorales  
**Exporta:** Estado y métodos

**Estado:**
- `results`: Array de resultados por partido
- `resultsByGeografia`: Array por geografía
- `stats`: Object con estadísticas
- `loading`, `error`: Estados
- `selectedCargo`, `selectedGeografia`: Filtros
- `refreshInterval`: Intervalo de actualización

**Métodos:**
- `loadResults()`: Carga datos desde API
- `startAutoRefresh()`: Inicia intervalo
- `stopAutoRefresh()`: Detiene intervalo
- `restartAutoRefresh()`: Reinicia con nuevo intervalo

**API Endpoint:** `GET /results/api/live`

##### `useChartData.js`
**Propósito:** Lógica de gráficos Chart.js  
**Exporta:** Estado y métodos

**Estado:**
- `chart`: Instancia de Chart.js
- `chartInstance`: Referencia

**Métodos:**
- `initChart(canvasId)`: Inicializa gráfico
- `updateChart(results)`: Actualiza con nuevos datos
- `destroyChart()`: Limpia instancia

---

#### Layouts

##### `ResultsLayout.vue`
**Funcionalidad:** Layout específico para dashboard  
**Features:**
- Navbar con branding
- LiveClock
- ConnectionIndicator
- Responsive

---

## 🔌 API Endpoints Utilizados

### Aplicación de Campo
```
GET    /api/v1/mesas/{codigo}
GET    /api/v1/catalogos
POST   /api/v1/actas
GET    /api/v1/actas/status?mesa={codigo}&cargo={id}
```

### Dashboard de Resultados
```
GET    /results/api/live
GET    /results/api/health
GET    /results/ (Inertia)
GET    /results/cargo/{id} (Inertia)
GET    /results/geografia/{id} (Inertia)
```

---

## 📦 Dependencias NPM Actuales (Vue)

### Production
```json
{
  "@inertiajs/vue3": "^2.3.11",
  "vue": "^3.5.27",
  "pinia": "^3.0.4",
  "axios": "^1.13.2",
  "chart.js": "^4.x",
  "idb": "^7.1.1"
}
```

### Development
```json
{
  "@vitejs/plugin-vue": "^4.0.0",
  "vite-plugin-pwa": "^0.16.0",
  "vitest": "^1.0.0",
  "@vue/test-utils": "^2.0.0"
}
```

---

## 🎯 Puntos de Atención para Migración

### 1. Diferencias Vue → React

#### Template vs JSX
- Vue: `<template>` con directivas (`v-if`, `v-for`, `@click`)
- React: JSX con expresiones JavaScript (`{condition && <div>}`, `.map()`)

#### Reactividad
- Vue: `ref()` y `reactive()` automáticamente reactivos
- React: `useState()` re-rendeja componente al cambiar

#### Ciclo de Vida
- Vue: `onMounted`, `onUnmounted`, `onUpdated`
- React: `useEffect(() => {}, [deps])` - cleanup function

#### Props
- Vue: `defineProps({ prop: Type })` - tipado en runtime
- React: `function Component({ prop })` - destructuring

#### Eventos
- Vue: `defineEmits(['event'])` + `$emit('event')`
- React: Props como callbacks `onEvent={handler}`

### 2. Consideraciones Especiales

#### Inertia.js
- Vue: `@inertiajs/vue3` con componentes `Link`, `Head`
- React: `@inertiajs/react` con componentes equivalentes

#### PWA
- Vite plugin funciona igual con React
- Service worker se mantiene

#### IndexedDB
- API nativa, funciona igual en React
- Solo cambiar sintaxis de llamadas

### 3. Testing

#### Unit Tests
- Vue: Vitest + Vue Test Utils
- React: Jest/Vitest + React Testing Library

#### E2E Tests
- Playwright es agnóstico al framework
- Tests se mantienen casi igual

---

## ✅ Lista de Verificación Pre-Migración

- [x] Analizar todos los componentes Vue
- [x] Documentar API endpoints
- [x] Listar dependencias
- [x] Identificar hooks personalizados
- [x] Documentar estado global (si existe)
- [x] Listar servicios
- [x] Identificar componentes complejos
- [x] Documentar interacciones con DOM
- [x] Listar integraciones de terceros
- [x] Preparar plan de rollback

---

**Próximo paso:** Leer [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md)
