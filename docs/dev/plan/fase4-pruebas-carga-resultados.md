# Resultados de Pruebas de Carga - Fase 4

## Fecha: 2026-01-20

## Resumen

Los scripts de prueba de carga k6 se han creado en `tests/load/k6/`:
- `api-escrutinio.js` - Prueba para API de escrutinio con ramp-up hasta 100 usuarios
- `admin-panel.js` - Prueba para panel de administración con 20 administradores concurrentes
- `election-day.js` - Simulación del día de elección con 50 peticiones/segundo

## Estado de Ejecución

**PENDIENTE:** k6 no está instalado en el entorno actual.

## Pasos para Ejecutar

### 1. Instalar k6

**Windows (Chocolatey):**
```bash
choco install k6
```

**Linux/Mac:**
```bash
brew install k6
```

### 2. Configurar Token de API

Crear un token de Sanctum para pruebas:
```bash
php artisan tinker
>>> \App\Models\User::first()->createToken('k6-test-token')->plainTextToken
```

### 3. Ejecutar Pruebas

**Prueba básica de API:**
```bash
k6 run --env BASE_URL=http://localhost:8000 --env API_TOKEN=your-token tests/load/k6/api-escrutinio.js
```

**Prueba de panel de administración:**
```bash
k6 run --env BASE_URL=http://localhost:8000 --env ADMIN_EMAIL=admin@example.com --env ADMIN_PASSWORD=password tests/load/k6/admin-panel.js
```

**Prueba de día de elección:**
```bash
k6 run --env BASE_URL=http://localhost:8000 --env API_TOKEN=your-token tests/load/k6/election-day.js
```

### 4. Guardar Resultados

```bash
# Guardar en formato JSON
k6 run --out json=results.json tests/load/k6/api-escrutinio.js

# Generar reporte HTML
k6 run --out json=results.json --summary-export=summary.json tests/load/k6/api-escrutinio.js
```

## Métricas Esperadas

| Métrica | Objetivo | Actual | Estado |
|---------|----------|--------|--------|
| API Response Time (P95) | < 2s | ⏳ | Por medir |
| API Response Time (P99) | < 5s | ⏳ | Por medir |
| Admin Panel Response (P95) | < 1.5s | ⏳ | Por medir |
| Error Rate | < 1% | ⏳ | Por medir |
| Throughput (req/s) | > 100 | ⏳ | Por medir |
| Concurrent Users | > 100 | ⏳ | Por medir |

## Recomendaciones

1. **Primera ejecución:** Ejecutar con pocos usuarios para verificar que el sistema funcione correctamente
2. **Monitoreo de base de datos:** Revisar `SHOW STATUS LIKE 'Threads_connected'` durante la prueba
3. **Logs de consultas lentas:** Revisar `storage/logs/laravel.log` para consultas > 100ms
4. **Redis:** Verificar que Redis esté corriendo: `redis-cli ping`

## Configuración Actual

- **Cache Driver:** Redis
- **Índices agregados:** Sí (migración 2026_01_20_205947)
- **Query logging:** Habilitado en AppServiceProvider (> 100ms)

## Próximos Pasos

1. ✅ Scripts k6 creados
2. ⏳ Instalar k6
3. ⏳ Ejecutar pruebas de carga
4. ⏳ Documentar resultados reales
5. ⏳ Ajustar configuración según resultados

---

**Documentación relacionada:**
- `tests/load/k6/README.md` - Documentación de scripts
- `docs/dev/plan/02-preparacion-fase4.md` - Guía completa de implementación
