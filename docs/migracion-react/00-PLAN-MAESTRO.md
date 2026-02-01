# 🚀 Plan Completo de Migración: Vue → React (Rama Electoral)

## 📋 Resumen Ejecutivo

Este plan documenta la migración completa del frontend de **Vue 3 + Inertia.js** a **React + Inertia.js** para el Sistema Electoral, adaptado específicamente para la rama `electoral`.

**Estado del Arte (Vue):**
- ✅ 6 componentes principales (Home, Acta, ActaForm, AppLayout, ThemeToggle, SyncStatus)
- ✅ 4 composables (useImageCompression, useNotification, useOfflineSync, useTheme)
- ✅ 1 servicio API completo
- ✅ Dashboard de resultados electorales completo (8+ componentes adicionales)
- ✅ Sistema PWA con Service Worker
- ✅ Testing con Vitest + Playwright (73% cobertura)
- ✅ Sincronización offline con IndexedDB

**Objetivo (React):**
- 🎯 Misma funcionalidad 100% en React
- 🎯 Mejor rendimiento con React 18+ Concurrent Features
- 🎯 Hooks nativos de React (useState, useEffect, useContext, custom hooks)
- 🎯 TypeScript opcional pero recomendado
- 🎯 Testing con Jest + React Testing Library

---

## 🗂️ Estructura de Archivos de Migración

```
docs/migracion-react/
├── 00-PLAN-MAESTRO.md                    # Este documento - Visión general
├── 01-ANALISIS-VUE-ACTUAL.md             # Análisis de código Vue existente
├── 02-SETUP-REACT-INERTIA.md             # Configuración inicial de React
├── 03-MIGRACION-COMPONENTES-CAMPO.md     # Migración de app de campo
├── 04-MIGRACION-DASHBOARD.md             # Migración del dashboard 05
├── 05-MIGRACION-COMPOSABLES.md           # Conversión a hooks de React
├── 06-MIGRACION-SERVICIOS.md             # Servicios y API
├── 07-TESTING-REACT.md                   # Configuración de testing
├── 08-OPTIMIZACIONES.md                  # Optimizaciones específicas
├── 09-DEPLOY-PRODUCCION.md               # Checklist de producción
└── codigo/                               # Código React generado
    ├── components/
    ├── hooks/
    ├── pages/
    └── services/
```

---

## 📊 Alcance de la Migración

### Componentes Vue a Migrar (rama electoral)

#### Aplicación de Campo (Base)
1. **Pages/**
   - `Home.vue` → `Home.jsx` - Buscador de mesas
   - `Acta.vue` → `Acta.jsx` - Formulario de carga de actas

2. **Components/**
   - `ActaForm.vue` → `ActaForm.jsx` - Formulario reutilizable
   - `SyncStatus.vue` → `SyncStatus.jsx` - Indicador de sincronización
   - `ThemeToggle.vue` → `ThemeToggle.jsx` - Cambio de tema

3. **Layouts/**
   - `AppLayout.vue` → `AppLayout.jsx` - Layout principal

4. **Composables/** (Convertir a Hooks)
   - `useImageCompression.js` → `useImageCompression.js`
   - `useNotification.js` → `useNotification.js`
   - `useOfflineSync.js` → `useOfflineSync.js`
   - `useTheme.js` → `useTheme.js`

5. **Services/**
   - `api.js` → `api.js` (adaptado para React)

#### Dashboard de Resultados Electorales (05-dashboard)
6. **Pages/Results/**
   - `Index.vue` → `Index.jsx` - Página principal de resultados

7. **Components/Results/**
   - `LiveClock.vue` → `LiveClock.jsx`
   - `ConnectionIndicator.vue` → `ConnectionIndicator.jsx`
   - `ResultsStats.vue` → `ResultsStats.jsx`
   - `StatCard.vue` → `StatCard.jsx`
   - `ResultsFilters.vue` → `ResultsFilters.jsx`
   - `ResultsTable.vue` → `ResultsTable.jsx`
   - `ResultsChart.vue` → `ResultsChart.jsx` (con Recharts)
   - `ResultsGeoTable.vue` → `ResultsGeoTable.jsx`
   - `AlertError.vue` → `AlertError.jsx`
   - `LoadingBar.vue` → `LoadingBar.jsx`

8. **Layouts/**
   - `ResultsLayout.vue` → `ResultsLayout.jsx`

9. **Hooks/**
   - `useElectionResults.js` - Hook de resultados electorales
   - `useChartData.js` - Hook de gráficos (adaptado a Recharts)

### Archivos de Configuración
- `vite.config.js` - Agregar plugin de React
- `package.json` - Reemplazar Vue por React
- `resources/js/app.js` - Punto de entrada React
- `tailwind.config.js` - Ajustar content paths

---

## 🎯 Fases de Migración

### **FASE 1: Setup y Configuración (2 horas)**

#### 1.1 Instalar Dependencias React
```bash
# Remover dependencias Vue
npm uninstall vue @inertiajs/vue3 @vitejs/plugin-vue pinia

# Instalar React + Inertia
npm install react@18 react-dom@18 @inertiajs/react

# Instalar herramientas de build
npm install -D @vitejs/plugin-react

# Opcional: TypeScript
npm install -D typescript @types/react @types/react-dom
```

#### 1.2 Configurar Vite para React
```javascript
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
    ],
    server: {
        hmr: {
            overlay: false,
        },
    },
});
```

#### 1.3 Actualizar package.json
```json
{
  "dependencies": {
    "@inertiajs/react": "^1.0.0",
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "axios": "^1.6.0",
    "chart.js": "^4.4.0",
    "recharts": "^2.10.0",
    "idb": "^7.1.1"
  },
  "devDependencies": {
    "@vitejs/plugin-react": "^4.2.0",
    "vitest": "^1.0.0",
    "@testing-library/react": "^14.0.0",
    "@testing-library/jest-dom": "^6.0.0",
    "jsdom": "^23.0.0"
  }
}
```

#### 1.4 Punto de Entrada React
```javascript
// resources/js/app.jsx
import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Sistema Electoral';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
```

### **FASE 2: Migración de Componentes Base (4 horas)**

Orden de migración recomendado:
1. **AppLayout.jsx** - Layout principal
2. **ThemeToggle.jsx** - Componente simple
3. **Home.jsx** - Página de búsqueda
4. **Acta.jsx** - Página de acta
5. **ActaForm.jsx** - Formulario complejo
6. **SyncStatus.jsx** - Indicador de sync

### **FASE 3: Migración de Hooks (3 horas)**

Convertir composables Vue a hooks React:
1. **useTheme.js** - Tema oscuro/claro
2. **useNotification.js** - Notificaciones
3. **useImageCompression.js** - Compresión de imágenes
4. **useOfflineSync.js** - Sincronización offline

### **FASE 4: Migración del Dashboard (6 horas)**

Migrar el dashboard completo de resultados:
1. **ResultsLayout.jsx**
2. **LiveClock.jsx**
3. **ConnectionIndicator.jsx**
4. **ResultsStats.jsx** + **StatCard.jsx**
5. **ResultsFilters.jsx**
6. **ResultsTable.jsx**
7. **ResultsChart.jsx** (con Recharts en lugar de Chart.js)
8. **ResultsGeoTable.jsx**
9. **AlertError.jsx**
10. **LoadingBar.jsx**
11. **Index.jsx** - Página principal
12. **useElectionResults.js** + **useChartData.js**

### **FASE 5: Testing y Optimización (4 horas)**

1. Configurar Jest + React Testing Library
2. Migrar tests de Vitest a Jest
3. Tests E2E con Playwright (reutilizar existentes)
4. Optimizaciones de rendimiento
5. Code splitting

### **FASE 6: Validación y Deploy (2 horas)**

1. Probar todas las funcionalidades
2. Verificar build de producción
3. Documentar cambios
4. Checklist de deploy

---

## ⏱️ Cronograma Total

| Fase | Duración | Componentes | Prioridad |
|------|----------|-------------|-----------|
| FASE 1: Setup | 2 horas | Configuración | 🔴 Alta |
| FASE 2: Componentes Base | 4 horas | Home, Acta, Layouts | 🔴 Alta |
| FASE 3: Hooks | 3 horas | 4 hooks | 🔴 Alta |
| FASE 4: Dashboard | 6 horas | 12 componentes | 🟡 Media |
| FASE 5: Testing | 4 horas | Tests + optimización | 🟡 Media |
| FASE 6: Deploy | 2 horas | Validación | 🟢 Baja |
| **TOTAL** | **21 horas** | **30+ archivos** | - |

---

## 📚 Documentación por Archivo

### Archivos de Configuración
- [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md) - Setup completo

### Migración de Componentes
- [03-MIGRACION-COMPONENTES-CAMPO.md](./03-MIGRACION-COMPONENTES-CAMPO.md) - App de campo
- [04-MIGRACION-DASHBOARD.md](./04-MIGRACION-DASHBOARD.md) - Dashboard 05

### Lógica y Servicios
- [05-MIGRACION-COMPOSABLES.md](./05-MIGRACION-COMPOSABLES.md) - Hooks
- [06-MIGRACION-SERVICIOS.md](./06-MIGRACION-SERVICIOS.md) - API y servicios

### Testing y Optimización
- [07-TESTING-REACT.md](./07-TESTING-REACT.md) - Testing
- [08-OPTIMIZACIONES.md](./08-OPTIMIZACIONES.md) - Performance
- [09-DEPLOY-PRODUCCION.md](./09-DEPLOY-PRODUCCION.md) - Producción

---

## 🎨 Guía Rápida: Vue vs React

### Diferencias Clave

| Aspecto | Vue 3 | React 18 |
|---------|-------|----------|
| **Template** | `<template>` | JSX |
| **Estado** | `ref()` / `reactive()` | `useState()` |
| **Ciclo de Vida** | `onMounted()` | `useEffect()` |
| **Props** | `defineProps()` | Parámetros de función |
| **Eventos** | `defineEmits()` | Callbacks como props |
| **Computed** | `computed()` | `useMemo()` |
| **Watch** | `watch()` | `useEffect()` con deps |
| **Refs DOM** | `ref` | `useRef()` |
| **Provide/Inject** | `provide()` / `inject()` | `Context API` |
| **Slots** | `<slot>` | `children` prop |

### Ejemplo de Conversión

**Vue:**
```vue
<template>
  <div class="p-4">
    <h1>{{ title }}</h1>
    <button @click="increment">Count: {{ count }}</button>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  title: String
})

const count = ref(0)
const doubled = computed(() => count.value * 2)

const increment = () => {
  count.value++
}
</script>
```

**React:**
```jsx
import { useState, useMemo } from 'react'

function Counter({ title }) {
  const [count, setCount] = useState(0)
  
  const doubled = useMemo(() => count * 2, [count])
  
  const increment = () => {
    setCount(prev => prev + 1)
  }
  
  return (
    <div className="p-4">
      <h1>{title}</h1>
      <button onClick={increment}>Count: {count}</button>
    </div>
  )
}

export default Counter
```

---

## ✅ Checklist de Migración

### Pre-Migración
- [ ] Hacer backup de rama electoral
- [ ] Crear rama feature/frontend-react-inertia desde electoral
- [ ] Revisar todos los archivos Vue actuales
- [ ] Documentar API endpoints utilizados
- [ ] Listar dependencias actuales

### Durante Migración
- [ ] Instalar dependencias React
- [ ] Configurar Vite para React
- [ ] Migrar componentes uno por uno
- [ ] Probar cada componente después de migrar
- [ ] Mantener Vue temporalmente como respaldo
- [ ] Actualizar tests

### Post-Migración
- [ ] Eliminar dependencias Vue
- [ ] Limpiar archivos temporales
- [ ] Actualizar documentación
- [ ] Deploy a staging
- [ ] Tests E2E completos
- [ ] Deploy a producción

---

## 🚀 Próximos Pasos

1. **Empezar con FASE 1:** Leer [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md)
2. **Migrar componentes:** Seguir [03-MIGRACION-COMPONENTES-CAMPO.md](./03-MIGRACION-COMPONENTES-CAMPO.md)
3. **Dashboard:** Revisar [04-MIGRACION-DASHBOARD.md](./04-MIGRACION-DASHBOARD.md)
4. **Hooks:** Ver [05-MIGRACION-COMPOSABLES.md](./05-MIGRACION-COMPOSABLES.md)

---

**Versión:** 1.0  
**Fecha:** 2026-02-01  
**Rama base:** electoral  
**Tiempo estimado total:** 21 horas  
**Estado:** Planificación completa - Listo para iniciar
