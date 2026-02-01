# 🚀 Migración Vue → React - Sistema Electoral

## 📋 Índice de Documentación

Bienvenido al plan completo de migración del frontend de **Vue 3 + Inertia.js** a **React + Inertia.js** para el Sistema Electoral (rama electoral).

---

## 📚 Documentos del Plan

### 📖 Documentos Principales

| Documento | Descripción | Prioridad | Tiempo Est. |
|-----------|-------------|-----------|-------------|
| [00-PLAN-MAESTRO.md](./00-PLAN-MAESTRO.md) | 🎯 Visión general, cronograma y objetivos | 🔴 Alta | 30 min |
| [01-ANALISIS-VUE-ACTUAL.md](./01-ANALISIS-VUE-ACTUAL.md) | 📊 Análisis completo del código Vue existente | 🔴 Alta | 45 min |
| [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md) | ⚙️ Configuración inicial de React + Inertia | 🔴 Alta | 2 horas |
| [03-MIGRACION-COMPONENTES-CAMPO.md](./03-MIGRACION-COMPONENTES-CAMPO.md) | 🎨 Migración de app de campo (12 componentes) | 🔴 Alta | 4 horas |
| [04-MIGRACION-DASHBOARD.md](./04-MIGRACION-DASHBOARD.md) | 📊 **Migración del Dashboard 05** (14 componentes) | 🔴 Alta | 6 horas |

### 🔧 Documentos Adicionales

| Documento | Descripción | Prioridad | Tiempo Est. |
|-----------|-------------|-----------|-------------|
| [05-MIGRACION-COMPOSABLES.md](./05-MIGRACION-COMPOSABLES.md) | 🔄 Conversión de composables a hooks | 🟡 Media | 3 horas |
| [06-MIGRACION-SERVICIOS.md](./06-MIGRACION-SERVICIOS.md) | 🔌 Servicios API y utilidades | 🟡 Media | 1 hora |
| [07-TESTING-REACT.md](./07-TESTING-REACT.md) | 🧪 Configuración de testing | 🟡 Media | 3 horas |
| [08-OPTIMIZACIONES.md](./08-OPTIMIZACIONES.md) | ⚡ Optimizaciones de rendimiento | 🟢 Baja | 2 horas |
| [09-DEPLOY-PRODUCCION.md](./09-DEPLOY-PRODUCCION.md) | 🚀 Checklist de producción | 🟢 Baja | 1 hora |

---

## 🗺️ Flujo de Trabajo Recomendado

```
1. LECTURA INICIAL
   └── 00-PLAN-MAESTRO.md (Visión general)
   └── 01-ANALISIS-VUE-ACTUAL.md (Entender qué tenemos)

2. SETUP INICIAL
   └── 02-SETUP-REACT-INERTIA.md
   └── Crear rama: feature/frontend-react-inertia
   └── Instalar dependencias
   └── Verificar que React funciona

3. MIGRACIÓN COMPONENTES
   └── 03-MIGRACION-COMPONENTES-CAMPO.md
   └── Migrar en orden: ThemeToggle → AppLayout → Home → Acta → ActaForm
   └── Probar cada componente antes de continuar

4. MIGRACIÓN DASHBOARD
   └── 04-MIGRACION-DASHBOARD.md ⭐ IMPORTANTE
   └── Instalar Recharts
   └── Migrar componentes del dashboard 05
   └── Probar API endpoints

5. TESTING (Opcional)
   └── 07-TESTING-REACT.md
   └── Configurar Jest/React Testing Library
   └── Migrar tests existentes

6. OPTIMIZACIÓN Y DEPLOY
   └── 08-OPTIMIZACIONES.md
   └── 09-DEPLOY-PRODUCCION.md
   └── Build de producción
   └── Deploy
```

---

## 📊 Resumen de Componentes a Migrar

### App de Campo (Base)
1. ✅ `ThemeToggle.jsx` - Simple
2. ✅ `useTheme.js` - Hook
3. ✅ `AppLayout.jsx` - Layout
4. ✅ `useNotification.js` - Hook
5. ✅ `SyncStatus.jsx` - Componente
6. ✅ `useOfflineSync.js` - Hook complejo
7. ✅ `useImageCompression.js` - Hook
8. ✅ `api.js` - Servicio
9. ✅ `storage.js` - Servicio IndexedDB
10. ✅ `Home.jsx` - Página
11. ✅ `Acta.jsx` - Página
12. ✅ `ActaForm.jsx` - Componente complejo

### Dashboard de Resultados Electorales (05)
13. ✅ `ResultsLayout.jsx`
14. ✅ `LiveClock.jsx`
15. ✅ `ConnectionIndicator.jsx`
16. ✅ `useElectionResults.js` - Hook
17. ✅ `useChartData.js` - Hook
18. ✅ `ResultsStats.jsx` + `StatCard.jsx`
19. ✅ `ResultsFilters.jsx`
20. ✅ `ResultsTable.jsx`
21. ✅ `ResultsChart.jsx` (con Recharts)
22. ✅ `ResultsGeoTable.jsx`
23. ✅ `AlertError.jsx`
24. ✅ `LoadingBar.jsx`
25. ✅ `Index.jsx` - Página principal
26. ✅ `ResultsController.php` - Backend

**Total: 26 archivos + backend**

---

## ⏱️ Cronograma Total

| Fase | Documento | Duración | Estado |
|------|-----------|----------|--------|
| **FASE 1** | Setup React | 2 horas | 📋 Pendiente |
| **FASE 2** | Componentes Campo | 4 horas | 📋 Pendiente |
| **FASE 3** | Hooks | 3 horas | 📋 Pendiente |
| **FASE 4** | Dashboard | 6 horas | 📋 Pendiente |
| **FASE 5** | Testing | 4 horas | 📋 Pendiente |
| **FASE 6** | Optimización | 2 horas | 📋 Pendiente |
| **FASE 7** | Deploy | 1 hora | 📋 Pendiente |
| **TOTAL** | | **21 horas** | 📋 0% |

---

## 🎯 Punto de Inicio

**¿Por dónde empezar?**

1. **Si quieres ver el panorama completo:**
   → Lee [00-PLAN-MAESTRO.md](./00-PLAN-MAESTRO.md)

2. **Si ya entiendes el plan y quieres empezar:**
   → Ve directo a [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md)

3. **Si solo necesitas el dashboard 05:**
   → Lee [04-MIGRACION-DASHBOARD.md](./04-MIGRACION-DASHBOARD.md)
   (asume que ya tienes React configurado)

---

## 🚀 Comandos Rápidos

```bash
# 1. Crear rama de trabajo
git checkout electoral
git pull origin electoral
git checkout -b feature/frontend-react-inertia

# 2. Instalar dependencias React
npm uninstall vue @inertiajs/vue3 @vitejs/plugin-vue pinia
npm install react@18 react-dom@18 @inertiajs/react
npm install -D @vitejs/plugin-react
npm install recharts chart.js idb

# 3. Iniciar desarrollo
php artisan serve
npm run dev

# 4. Verificar instalación
open http://localhost:8000/test-react
```

---

## 📝 Notas Importantes

### ⚠️ Consideraciones para Rama Electoral

1. **Estado actual:** La rama electoral tiene Vue configurado y funcionando
2. **Cambios recientes:** Incluye dashboard 05 y optimizaciones
3. **Base de datos:** Asegúrate de tener los modelos ResumenVoto, Mesa, etc.
4. **Redis:** Configurado para caché

### 🔄 Estrategia de Migración

1. **Enfoque gradual:** Migrar componente por componente
2. **Paralelismo:** Mantener Vue temporalmente mientras migramos
3. **Testing:** Probar cada componente inmediatamente después de migrar
4. **Rollback:** Guardar backups antes de cada fase

### 📦 Dependencias Clave

```json
{
  "dependencies": {
    "@inertiajs/react": "^1.0.0",
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "axios": "^1.13.2",
    "chart.js": "^4.4.0",
    "recharts": "^2.10.0",
    "idb": "^7.1.1"
  },
  "devDependencies": {
    "@vitejs/plugin-react": "^4.2.0",
    "vite-plugin-pwa": "^0.16.0",
    "vitest": "^1.0.0"
  }
}
```

---

## ✅ Checklist General

### Pre-Migración
- [ ] Revisar 00-PLAN-MAESTRO.md
- [ ] Revisar 01-ANALISIS-VUE-ACTUAL.md
- [ ] Crear rama feature/frontend-react-inertia
- [ ] Backup de rama electoral

### Setup
- [ ] Seguir 02-SETUP-REACT-INERTIA.md
- [ ] Verificar React funciona (/test-react)

### Migración
- [ ] Migrar componentes de campo (03)
- [ ] Migrar dashboard (04)
- [ ] Migrar hooks (05)
- [ ] Configurar servicios (06)

### Testing
- [ ] Configurar testing (07)
- [ ] Tests pasan

### Producción
- [ ] Optimizaciones (08)
- [ ] Deploy checklist (09)
- [ ] Build exitoso
- [ ] Deploy a producción

---

## 🆘 Soporte

### Si tienes problemas:

1. **Error en setup:** Revisa [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md) sección "Solución de Problemas"

2. **Componente no funciona:** Compara con el código Vue original en [01-ANALISIS-VUE-ACTUAL.md](./01-ANALISIS-VUE-ACTUAL.md)

3. **API no responde:** Verifica rutas en backend y ResultsController

4. **Dudas de conversión:** Revisa tabla Vue vs React en [00-PLAN-MAESTRO.md](./00-PLAN-MAESTRO.md)

---

## 📞 Contacto y Recursos

### Documentación Oficial
- [React Docs](https://react.dev)
- [Inertia.js React](https://inertiajs.com/)
- [Recharts](https://recharts.org/)
- [Tailwind CSS](https://tailwindcss.com/)

### Documentación Interna
- Plan original Vue: `docs/dev/plan/05-dashboard-resultados-eleccion.md`
- Implementación Vue: `docs/dev/frontend-vue-inertia.md`
- Plan desarrollo Vue: `docs/dev/plan-desarrollo-vue.md`

---

## 🎉 Estado del Plan

- **Total de documentos:** 10
- **Archivos de código:** 26+ componentes
- **Tiempo estimado:** 21 horas
- **Dificultad:** Media-Alta
- **Prioridad:** Alta (para elecciones)

**Estado:** ✅ Planificación completa - Listo para iniciar migración

---

**Fecha de creación:** 2026-02-01  
**Versión:** 1.0  
**Rama base:** electoral  
**Rama trabajo:** feature/frontend-react-inertia (por crear)

---

## 🚀 ¡Empecemos!

👉 **Primer paso:** Abre [00-PLAN-MAESTRO.md](./00-PLAN-MAESTRO.md) para ver el panorama completo.

👉 **O si prefieres empezar ya:** Ve a [02-SETUP-REACT-INERTIA.md](./02-SETUP-REACT-INERTIA.md)

👉 **Si solo necesitas el dashboard:** Ve a [04-MIGRACION-DASHBOARD.md](./04-MIGRACION-DASHBOARD.md)
