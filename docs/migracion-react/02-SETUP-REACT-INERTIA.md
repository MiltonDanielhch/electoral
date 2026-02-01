# 02 - Setup React + Inertia.js

## 🚀 Instalación y Configuración Inicial

### Paso 1: Crear Rama de Trabajo

```bash
# Desde la rama electoral
git checkout electoral
git pull origin electoral

# Crear rama de migración
git checkout -b feature/frontend-react-inertia

# Verificar que estamos en la rama correcta
git branch
```

---

### Paso 2: Backup de Dependencias

```bash
# Guardar package.json actual
cp package.json package.json.vue.backup

# Guardar vite.config.js actual
cp vite.config.js vite.config.js.vue.backup

# Guardar app.js actual
cp resources/js/app.js resources/js/app.js.vue.backup
```

---

### Paso 3: Remover Dependencias Vue

```bash
# Remover Vue y plugins relacionados
npm uninstall vue @inertiajs/vue3 @vitejs/plugin-vue pinia

# Verificar que se removieron
cat package.json | grep -E "(vue|pinia)"
# Debería no mostrar resultados
```

---

### Paso 4: Instalar Dependencias React

```bash
# Instalar React 18 y React DOM
npm install react@18 react-dom@18

# Instalar Inertia para React
npm install @inertiajs/react

# Instalar plugin de Vite para React
npm install -D @vitejs/plugin-react

# Re-verificar axios está instalado
npm install axios

# Instalar dependencias adicionales necesarias
npm install chart.js idb
```

#### Opcional: TypeScript
```bash
# Si deseas usar TypeScript (recomendado)
npm install -D typescript @types/react @types/react-dom

# Crear tsconfig.json
npx tsc --init
```

---

### Paso 5: Actualizar package.json

Después de las instalaciones, tu `package.json` debería verse así:

```json
{
  "name": "electoral",
  "private": true,
  "version": "0.0.0",
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview",
    "test": "vitest",
    "test:ui": "vitest --ui",
    "test:coverage": "vitest --coverage"
  },
  "devDependencies": {
    "@tailwindcss/forms": "^0.5.11",
    "@tailwindcss/postcss": "^4.1.18",
    "@vitejs/plugin-react": "^4.2.0",
    "autoprefixer": "^10.4.23",
    "axios": "^1.13.2",
    "laravel-vite-plugin": "^0.7.2",
    "lodash": "^4.17.19",
    "postcss": "^8.5.6",
    "tailwindcss": "^4.1.18",
    "vite": "^4.0.0",
    "vite-plugin-pwa": "^0.16.0",
    "vitest": "^1.0.0"
  },
  "dependencies": {
    "@inertiajs/react": "^1.0.0",
    "chart.js": "^4.4.0",
    "idb": "^7.1.1",
    "react": "^18.2.0",
    "react-dom": "^18.2.0"
  }
}
```

---

### Paso 6: Configurar Vite para React

Reemplaza completamente `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import { VitePWA } from 'vite-plugin-pwa';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.jsx'],
            refresh: true,
        }),
        react(),
        VitePWA({
            registerType: 'autoUpdate',
            manifest: {
                name: 'Sistema Electoral - App de Campo',
                short_name: 'Electoral Campo',
                theme_color: '#2563eb',
                background_color: '#ffffff',
                display: 'standalone',
                orientation: 'portrait',
                icons: [
                    {
                        src: '/icon-192x192.png',
                        sizes: '192x192',
                        type: 'image/png'
                    },
                    {
                        src: '/icon-512x512.png',
                        sizes: '512x512',
                        type: 'image/png'
                    }
                ]
            },
            workbox: {
                runtimeCaching: [
                    {
                        urlPattern: /^https:\/\/localhost:8000\/api\//,
                        handler: 'NetworkFirst',
                        options: {
                            cacheName: 'api-cache',
                            expiration: {
                                maxEntries: 100,
                                maxAgeSeconds: 60 * 60 * 24 // 24 horas
                            }
                        }
                    }
                ]
            }
        }),
    ],
    server: {
        hmr: {
            overlay: false,
        },
    },
    resolve: {
        alias: {
            '@': '/resources/js',
        },
    },
});
```

**Cambios clave:**
- ✅ Reemplazado `@vitejs/plugin-vue` por `@vitejs/plugin-react`
- ✅ Actualizado entry point a `resources/js/app.jsx`
- ✅ Mantenido VitePWA para PWA
- ✅ Agregado alias `@` para imports

---

### Paso 7: Crear Punto de Entrada React

Crea `resources/js/app.jsx`:

```javascript
import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Sistema Electoral';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.jsx`, 
        import.meta.glob('./Pages/**/*.jsx')
    ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
```

**Diferencias con Vue:**
- ✅ Usa `createRoot` de `react-dom/client`
- ✅ Render con JSX: `<App {...props} />`
- ✅ `import.meta.glob` busca archivos `.jsx`
- ✅ Mismo sistema de resolución de páginas

---

### Paso 8: Actualizar tailwind.config.js

Asegúrate de que busque archivos `.jsx`:

```javascript
/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.{jsx,js}",
    "./resources/views/**/*.blade.php",
  ],
  darkMode: 'class', // Para soporte de modo oscuro
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#eff6ff',
          100: '#dbeafe',
          200: '#bfdbfe',
          300: '#93c5fd',
          400: '#60a5fa',
          500: '#3b82f6',
          600: '#2563eb',
          700: '#1d4ed8',
          800: '#1e40af',
          900: '#1e3a8a',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
```

---

### Paso 9: Crear Estructura de Carpetas React

```bash
# Crear estructura de carpetas
mkdir -p resources/js/Pages
mkdir -p resources/js/Components
mkdir -p resources/js/Layouts
mkdir -p resources/js/Hooks
mkdir -p resources/js/Services

# Crear subcarpeta para Results (Dashboard)
mkdir -p resources/js/Pages/Results
mkdir -p resources/js/Components/Results
```

Estructura resultante:
```
resources/js/
├── app.jsx                    # Punto de entrada
├── bootstrap.js               # Axios config
├── Pages/
│   ├── Home.jsx              # Página búsqueda mesas
│   ├── Acta.jsx              # Página carga actas
│   └── Results/
│       └── Index.jsx         # Dashboard resultados
├── Components/
│   ├── ActaForm.jsx          # Formulario acta
│   ├── SyncStatus.jsx        # Indicador sync
│   ├── ThemeToggle.jsx       # Toggle tema
│   └── Results/              # Componentes dashboard
│       ├── LiveClock.jsx
│       ├── ConnectionIndicator.jsx
│       ├── ResultsStats.jsx
│       ├── StatCard.jsx
│       ├── ResultsFilters.jsx
│       ├── ResultsTable.jsx
│       ├── ResultsChart.jsx
│       ├── ResultsGeoTable.jsx
│       ├── AlertError.jsx
│       └── LoadingBar.jsx
├── Layouts/
│   ├── AppLayout.jsx         # Layout principal
│   └── ResultsLayout.jsx     # Layout dashboard
├── Hooks/
│   ├── useTheme.js           # Hook tema
│   ├── useNotification.js    # Hook notificaciones
│   ├── useImageCompression.js # Hook compresión
│   ├── useOfflineSync.js     # Hook sincronización
│   ├── useElectionResults.js # Hook resultados
│   └── useChartData.js       # Hook gráficos
└── Services/
    ├── api.js                # Cliente API
    └── storage.js            # IndexedDB
```

---

### Paso 10: Crear Componente de Prueba

Crea un componente simple para verificar que todo funciona:

```javascript
// resources/js/Pages/Test.jsx
import React from 'react';

export default function Test() {
    return (
        <div className="min-h-screen bg-gray-100 flex items-center justify-center">
            <div className="bg-white p-8 rounded-lg shadow-md">
                <h1 className="text-2xl font-bold text-gray-800 mb-4">
                    ¡React + Inertia Funciona! 🎉
                </h1>
                <p className="text-gray-600">
                    Si ves esto, la configuración está correcta.
                </p>
            </div>
        </div>
    );
}
```

---

### Paso 11: Configurar Ruta de Prueba

Agrega temporalmente una ruta de prueba en `routes/web.php`:

```php
// Ruta temporal de prueba
Route::get('/test-react', function () {
    return inertia('Test');
});
```

---

### Paso 12: Verificar Instalación

```bash
# Limpiar caché de npm (opcional)
npm cache clean --force
rm -rf node_modules
npm install

# Iniciar servidor Laravel
php artisan serve

# En otra terminal, iniciar Vite
npm run dev

# Verificar que no hay errores en consola
```

Abre: http://localhost:8000/test-react

**Deberías ver:** "¡React + Inertia Funciona! 🎉"

---

### Paso 13: Solución de Problemas Comunes

#### Error: "Cannot find module 'react'"
```bash
# Reinstalar dependencias
rm -rf node_modules package-lock.json
npm install
```

#### Error: "Invalid hook call"
Asegúrate de no tener múltiples versiones de React:
```bash
npm ls react react-dom
# Debería mostrar solo una versión
```

#### Error: "Target container is not a DOM element"
Verifica que el elemento root existe en `resources/views/app.blade.php`:
```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body>
    @inertia
</body>
</html>
```

**Nota:** Debes agregar `@viteReactRefresh` para hot reload.

#### Error: "You are using the runtime-only build of Vue"
Este error es residual de Vue, verifica que:
1. No quedó ningún import de Vue
2. No hay archivos `.vue` siendo importados
3. `vite.config.js` no tiene plugin de Vue

---

### Paso 14: Configurar ESLint (Opcional pero Recomendado)

```bash
npm install -D eslint eslint-plugin-react eslint-plugin-react-hooks
```

Crea `.eslintrc.json`:
```json
{
  "env": {
    "browser": true,
    "es2021": true
  },
  "extends": [
    "eslint:recommended",
    "plugin:react/recommended",
    "plugin:react-hooks/recommended"
  ],
  "parserOptions": {
    "ecmaFeatures": {
      "jsx": true
    },
    "ecmaVersion": "latest",
    "sourceType": "module"
  },
  "plugins": ["react", "react-hooks"],
  "rules": {
    "react/react-in-jsx-scope": "off",
    "react/prop-types": "off"
  },
  "settings": {
    "react": {
      "version": "detect"
    }
  }
}
```

---

## ✅ Checklist de Setup Completo

- [ ] Rama feature/frontend-react-inertia creada
- [ ] Dependencias Vue removidas
- [ ] React 18 instalado
- [ ] @inertiajs/react instalado
- [ ] @vitejs/plugin-react instalado
- [ ] vite.config.js actualizado
- [ ] app.jsx creado
- [ ] tailwind.config.js actualizado para .jsx
- [ ] Estructura de carpetas creada
- [ ] Componente Test.jsx funciona
- [ ] Ruta /test-react responde
- [ ] No hay errores en consola
- [ ] Hot reload funciona (@viteReactRefresh)

---

## 🎯 Resultado Esperado

Después de completar este setup:

1. ✅ React 18 funcionando con Inertia.js
2. ✅ Vite configurado con plugin de React
3. ✅ Tailwind CSS procesando archivos .jsx
4. ✅ PWA configurado y funcionando
5. ✅ Hot Module Replacement (HMR) activo
6. ✅ Estructura de carpetas lista para migrar componentes

---

## 🚀 Próximo Paso

Una vez verificado que el setup funciona, procede a:

[03-MIGRACION-COMPONENTES-CAMPO.md](./03-MIGRACION-COMPONENTES-CAMPO.md) - Migrar componentes de la app de campo

---

**Tiempo estimado:** 2 horas  
**Dificultad:** Media  
**Bloqueante:** Sí - Debe completarse antes de migrar componentes
