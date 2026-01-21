# Pruebas de Carga con k6

Esta carpeta contiene los scripts de pruebas de carga para el Sistema Electoral usando k6.

## Instalación de k6

### Windows (Chocolatey - requiere Admin)

```powershell
choco install k6 -y
```

### Windows (Manual)

Descargar el ejecutable desde: https://k6.io/docs/getting-started/installation/

### Linux/Mac

```bash
brew install k6
```

Verificar instalación:

```bash
k6 version
```

## Scripts Disponibles

### 1. API de Escrutinio (`api-escrutinio.js`)

Prueba la API de escrutinio con alta concurrencia, simulando el envío de actas y consulta de mesas.

```bash
k6 run tests/load/k6/api-escrutinio.js
```

Con variables de entorno:

```bash
k6 run --env BASE_URL=http://localhost:8000 --env API_TOKEN=your-token tests/load/k6/api-escrutinio.js
```

### 2. Panel de Administración (`admin-panel.js`)

Prueba el panel de administración con múltiples usuarios concurrentes.

```bash
k6 run tests/load/k6/admin-panel.js
```

### 3. Día de Elección (`election-day.js`)

Simula el pico de carga del día de elección con envío masivo de actas.

```bash
k6 run tests/load/k6/election-day.js
```

## Exportar Resultados

```bash
# Exportar a JSON
k6 run --out json=results.json tests/load/k6/api-escrutinio.js

# Exportar a CSV
k6 run --out csv=results.csv tests/load/k6/api-escrutinio.js
```

## Métricas Objetivo

| Métrica | Objetivo |
|---------|----------|
| API Response Time (P95) | < 2s |
| API Response Time (P99) | < 5s |
| Admin Panel Response (P95) | < 1.5s |
| Error Rate | < 1% |
| Throughput (req/s) | > 100 |
| Concurrent Users | > 100 |

## Referencias

- [Documentación de k6](https://k6.io/docs/)
- [Preparación Fase 4](../../../dev/plan/02-preparacion-fase4.md)
