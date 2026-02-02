# 📚 API del Sistema Electoral - Documentación Completa

## 🎯 Introducción

Este documento explica en detalle toda la API del Sistema Electoral, cómo funciona, cómo probarla y cómo React la consumirá.

---

### 6. Herramientas de Depuración (DevTools)

#### GET `/api/debug/resumen`

**Descripción:** Endpoint temporal para verificar el contenido crudo de la tabla `resumen_votos` y confirmar que el Observer está sumando correctamente.

**Uso:**
```bash
curl -X GET "http://localhost:8000/api/debug/resumen"
```
*Nota: Eliminar esta ruta antes de pasar a producción.*


## 📖 Índice

1. [Arquitectura General](#arquitectura-general)
2. [Autenticación](#autenticación)
3. [Endpoints Principales](#endpoints-principales)
4. [Flujo de Trabajo](#flujo-de-trabajo)
5. [Modelos de Datos](#modelos-de-datos)
6. [Ejemplos Prácticos](#ejemplos-prácticos)
7. [Testing con cURL/Postman](#testing-con-curlpostman)
8. [Integración con React](#integración-con-react)

---

## 🏗️ Arquitectura General

### Stack Tecnológico

```
Backend: Laravel 10 + PHP 8.2
Base de Datos: MySQL 8.0
Caché: Redis
API: RESTful con Sanctum (Auth)
Frontend Actual: Blade + Livewire + Voyager
Frontend Nuevo: React + Inertia.js
```

### Estructura de la API

```
Base URL: http://localhost:8000

Rutas API (con auth):
├── /api/v1/          - API privada (requiere Sanctum)
│   ├── /catalogos    - Catálogos de partidos y cargos
│   ├── /mesas        - Información de mesas
│   └── /acta         - Registro de actas
│
├── /api/mapas/       - API de mapas (mixta)
│   ├── /geojson      - Datos geográficos
│   ├── /resultados   - Resultados para mapas
│   └── /geografias/  - Recintos por geografía
│
└── /api/public/mapas/ - API pública (throttle 30 req/min)
    ├── /geojson      - GeoJSON público
    └── /resultados   - Resultados públicos

Rutas Web:
├── /results/         - Dashboard de resultados
├── /admin/           - Panel Voyager (Blade)
└── /mapa-resultados  - Mapa público
```

---

## 🔐 Autenticación

### Sanctum (Token-based)

La API usa **Laravel Sanctum** para autenticación.

#### Obtener Token

```http
POST /login
Content-Type: application/json

{
  "email": "usuario@ejemplo.com",
  "password": "password123"
}
```

#### Usar Token

```http
GET /api/v1/catalogos
Authorization: Bearer {token}
```

#### Middleware de Protección

- `auth:sanctum` - Requiere token válido
- `throttle:120,1` - 120 peticiones/minuto por usuario

---

## 🌐 Endpoints Principales

### 1. Catálogos API

#### GET `/api/v1/catalogos`

**Descripción:** Obtiene catálogos de partidos políticos y cargos (con caché).

**Autenticación:** Requerida (Sanctum)

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "partidos_politicos": [
      {
        "id_partido": 1,
        "codigo_tse": "MAS",
        "nombre": "Movimiento al Socialismo",
        "sigla": "MAS",
        "color_hex": "#0F4B91"
      },
      {
        "id_partido": 2,
        "codigo_tse": "CC",
        "nombre": "Comunidad Ciudadana",
        "sigla": "CC",
        "color_hex": "#FFA500"
      }
    ],
    "cargos": [
      {
        "id_cargo": 1,
        "descripcion": "Presidente y Vicepresidente",
        "nivel": "Nacional",
        "tipo_acta": "Unica",
        "acta_unica": true
      },
      {
        "id_cargo": 2,
        "descripcion": "Diputado Nacional",
        "nivel": "Nacional",
        "tipo_acta": "Multi",
        "acta_unica": false
      }
    ]
  }
}
```

**Uso en React:**
```javascript
// Obtener catálogos al cargar formulario de acta
const cargarCatalogos = async () => {
  const response = await api.get('/catalogos');
  setPartidos(response.data.data.partidos_politicos);
  setCargos(response.data.data.cargos);
};
```

---

### 2. Mesas API

#### GET `/api/v1/mesas/estadisticas`

**Descripción:** Dashboard de sintonía - estadísticas en tiempo real de mesas.

**Autenticación:** Requerida

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_mesas": 5000,
    "escrutadas": 3500,
    "habilitadas": 1400,
    "anuladas": 50,
    "observadas": 50,
    "total_electores": 2500000,
    "porcentaje_escrutadas": 70.00,
    "faltantes": 1500,
    "porcentaje_faltantes": 30.00
  }
}
```

**Uso:** Monitor de transmisión en tiempo real.

---

#### GET `/api/v1/mesa/{codigo}`

**Descripción:** Obtiene información detallada de una mesa por su código TSE.

**Parámetros URL:**
- `codigo` (string): Código de 4 dígitos de la mesa (ej: "0001")

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id_mesa": 1,
    "id_recinto": 10,
    "codigo_tse": "0001",
    "estado": "Habilitada",
    "cantidad_electores": 300,
    "numero_mesa": 1,
    "recinto": {
      "id_recinto": 10,
      "nombre": "Escuela Juan Pueblo",
      "direccion": "Calle Principal 123",
      "id_geografia": 5,
      "geografia": {
        "id_geografia": 5,
        "nombre": "La Paz",
        "tipo": "Municipio"
      }
    },
    "actas_escrutinio": [
      {
        "id_acta": 1,
        "id_mesa": 1,
        "id_cargo": 1,
        "codigo_acta": "ACTA-001",
        "estado": "Digitada",
        "foto_frontal": "actas/fotos/frente_001.jpg",
        "foto_reverso": "actas/fotos/reverso_001.jpg",
        "total_sobres": 280,
        "total_votantes": 280,
        "votos_validos": 270,
        "votos_blancos": 5,
        "votos_nulos": 5,
        "cargo": {
          "id_cargo": 1,
          "descripcion": "Presidente y Vicepresidente"
        }
      }
    ]
  }
}
```

**Respuesta Error (404):**
```json
{
  "success": false,
  "message": "Mesa no encontrada"
}
```

**Uso en React:**
```javascript
// Buscar mesa por código
const buscarMesa = async (codigo) => {
  try {
    const response = await api.get(`/mesa/${codigo}`);
    return response.data.data;
  } catch (error) {
    if (error.response?.status === 404) {
      showError('Mesa no encontrada');
    }
  }
};
```

---

### 3. Actas API

#### POST `/api/v1/acta`

**Descripción:** Registra una nueva acta de escrutinio.

**Autenticación:** Requerida

**Content-Type:** `multipart/form-data` (por las fotos)

**Campos del Formulario:**

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `codigo_mesa` | string | Sí | Código de la mesa (4 dígitos) |
| `id_cargo` | integer | Sí | ID del cargo electoral |
| `codigo_acta` | string | Sí | Código único del acta |
| `foto_frontal` | file | Sí | Imagen frontal del acta (jpg/png) |
| `foto_reverso` | file | Sí | Imagen reverso del acta (jpg/png) |
| `total_sobres` | integer | Sí | Total de sobres en la urna |
| `total_votantes` | integer | Sí | Total de votantes |
| `votos_validos` | integer | Sí | Votos válidos totales |
| `votos_blancos` | integer | Sí | Votos en blanco |
| `votos_nulos` | integer | Sí | Votos nulos |
| `votos_impugnados` | integer | No | Votos impugnados |
| `votos_partido` | array | Sí | Array de votos por partido |
| `digitador` | string | Sí | Nombre del digitador |

**Estructura de votos_partido:**
```json
[
  {
    "id_partido": 1,
    "votos": 150
  },
  {
    "id_partido": 2,
    "votos": 120
  }
]
```

**Ejemplo de Request:**
```javascript
const formData = new FormData();
formData.append('codigo_mesa', '0001');
formData.append('id_cargo', '1');
formData.append('codigo_acta', 'ACTA-0001-001');
formData.append('foto_frontal', archivoFrente);
formData.append('foto_reverso', archivoReverso);
formData.append('total_sobres', '280');
formData.append('total_votantes', '280');
formData.append('votos_validos', '270');
formData.append('votos_blancos', '5');
formData.append('votos_nulos', '5');
formData.append('votos_impugnados', '0');
formData.append('votos_partido', JSON.stringify([
  { id_partido: 1, votos: 150 },
  { id_partido: 2, votos: 120 }
]));
formData.append('digitador', 'Juan Perez');
```

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Acta registrada exitosamente",
  "data": {
    "id_acta": 1,
    "codigo_acta": "ACTA-0001-001",
    "estado": "Digitada"
  }
}
```

**Respuesta Error (500):**
```json
{
  "success": false,
  "message": "Error al registrar el acta",
  "error": "Detalles del error..."
}
```

**Uso en React:**
```javascript
// Enviar acta
const enviarActa = async (formData) => {
  try {
    const response = await api.post('/acta', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    showSuccess('Acta registrada exitosamente');
    return response.data;
  } catch (error) {
    showError(error.response?.data?.message || 'Error al enviar acta');
  }
};
```

---

### 4. Mapas API (Pública)

#### GET `/api/public/mapas/geojson`

**Descripción:** Obtiene datos GeoJSON para mapas (público, limitado a 30 req/min).

**Autenticación:** No requerida

**Throttle:** 30 peticiones/minuto

**Respuesta:** GeoJSON con geometrías de geografías.

---

#### GET `/api/public/mapas/resultados`

**Descripción:** Obtiene resultados para visualización en mapas.

**Query Parameters:**
- `geografia_id` (opcional): Filtrar por geografía específica

**Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id_geografia": 1,
      "nombre": "La Paz",
      "total_votos": 50000,
      "ganador": "MAS",
      "color_hex": "#0F4B91"
    }
  ]
}
```

---

### 5. Dashboard de Resultados (Web)

#### GET `/results`

**Descripción:** Página principal del dashboard de resultados.

**Middleware:** `auth` (requiere login en Voyager)

**Respuesta:** Vista Blade con datos de resultados agrupados.

---

#### GET `/results/{cargoId}`

**Descripción:** Resultados filtrados por cargo específico.

**Respuesta (JSON):**
```json
{
  "success": true,
  "data": {
    "cargo": {
      "id_cargo": 1,
      "descripcion": "Presidente y Vicepresidente"
    },
    "results": [
      {
        "id_cargo": 1,
        "id_geografia": 5,
        "id_partido": 1,
        "total_votos": 15000,
        "total_mesas_escrutadas": 100,
        "porcentaje_votos": 55.5,
        "geografia": {
          "id_geografia": 5,
          "nombre": "La Paz"
        },
        "organizacion_politica": {
          "id_partido": 1,
          "nombre": "Movimiento al Socialismo",
          "sigla": "MAS",
          "color_hex": "#0F4B91"
        }
      }
    ]
  }
}
```

---

## 🔄 Flujo de Trabajo

### Flujo de Digitación de Actas

```
1. Usuario (digitador) inicia sesión en el sistema
   ↓
2. Busca mesa por código (4 dígitos)
   GET /api/v1/mesa/{codigo}
   ↓
3. Sistema retorna información de la mesa y sus actas existentes
   ↓
4. Usuario selecciona cargo a registrar
   ↓
5. Sistema carga catálogos (partidos, cargos)
   GET /api/v1/catalogos
   ↓
6. Usuario completa formulario:
   - Sube fotos frontal y reverso
   - Ingresa totales (sobres, votantes, etc.)
   - Ingresa votos por partido
   ↓
7. Sistema valida datos y calcula totales
   ↓
8. Usuario envía acta
   POST /api/v1/acta (multipart/form-data)
   ↓
9. Sistema:
   - Guarda fotos en storage
   - Crea registro en actas_escrutinio
   - Crea registros en votos_x_partido
   - Crea auditoría
   - Actualiza estado de mesa
   - **Observer:** Actualiza `resumen_votos` y recalcula porcentajes automáticamente
   - Invalida caché de resultados
   ↓
10. Respuesta de éxito al usuario
```

### Flujo de Visualización de Resultados

```
1. Usuario visita /results
   ↓
2. Sistema consulta resultados (con caché 60 segundos)
   SELECT de resumen_votos
   ↓
3. Sistema retorna vista con datos agrupados
   ↓
4. JavaScript/Charts renderiza gráficos
   ↓
5. Auto-refresh cada 60 segundos (para "tiempo real")
   ↓
6. Usuario puede filtrar por cargo o geografía
```

---

## 📊 Modelos de Datos

### Mesa
```php
{
  id_mesa: integer (PK)
  id_recinto: integer (FK)
  codigo_tse: string (4 dígitos)
  estado: enum ['Habilitada', 'Escrutada', 'Anulada', 'Observada']
  cantidad_electores: integer
  numero_mesa: integer
  created_at: timestamp
  updated_at: timestamp
}
```

### ActaEscrutinio
```php
{
  id_acta: integer (PK)
  id_mesa: integer (FK)
  id_cargo: integer (FK)
  codigo_acta: string (único)
  foto_frontal: string (path)
  foto_reverso: string (path)
  total_sobres: integer
  total_votantes: integer
  votos_validos: integer
  votos_blancos: integer
  votos_nulos: integer
  votos_impugnados: integer
  digitador: string
  estado: enum ['Digitada', 'Verificada', 'Publicada']
  created_at: timestamp
  updated_at: timestamp
}
```

### VotoXPartido
```php
{
  id_voto: integer (PK)
  id_acta: integer (FK)
  id_partido: integer (FK)
  votos: integer
}
```

### ResumenVoto (Vista/Materializada)
```php
{
  id_resumen: integer (PK)
  id_cargo: integer (FK)
  id_geografia: integer (FK)
  id_partido: integer (FK)
  total_votos: integer
  total_mesas_escrutadas: integer
  porcentaje_votos: decimal
  ultima_actualizacion: timestamp
}
```

---

## 🧪 Ejemplos Prácticos

### Ejemplo 1: Buscar una Mesa

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/mesa/0001" \
  -H "Authorization: Bearer {tu_token}" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "codigo_tse": "0001",
    "estado": "Habilitada",
    "cantidad_electores": 300,
    "recinto": {
      "nombre": "Escuela Juan Pueblo",
      "geografia": {
        "nombre": "La Paz"
      }
    }
  }
}
```

---

### Ejemplo 2: Enviar un Acta

**Request:**
```bash
curl -X POST "http://localhost:8000/api/v1/acta" \
  -H "Authorization: Bearer {tu_token}" \
  -H "Content-Type: multipart/form-data" \
  -F "codigo_mesa=0001" \
  -F "id_cargo=1" \
  -F "codigo_acta=ACTA-0001-001" \
  -F "foto_frontal=@frente.jpg" \
  -F "foto_reverso=@reverso.jpg" \
  -F "total_sobres=280" \
  -F "total_votantes=280" \
  -F "votos_validos=270" \
  -F "votos_blancos=5" \
  -F "votos_nulos=5" \
  -F "votos_partido=[{\"id_partido\":1,\"votos\":150},{\"id_partido\":2,\"votos\":120}]" \
  -F "digitador=Juan Perez"
```

---

### Ejemplo 3: Obtener Estadísticas

**Request:**
```bash
curl -X GET "http://localhost:8000/api/v1/mesas/estadisticas" \
  -H "Authorization: Bearer {tu_token}"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_mesas": 5000,
    "escrutadas": 3500,
    "porcentaje_escrutadas": 70.00
  }
}
```

---

## 🧪 Testing con cURL/Postman

### Colección Postman Recomendada

Crea una colección con estas variables:
- `base_url`: `http://localhost:8000`
- `token`: (se obtiene tras login)

### Scripts de Prueba

#### 1. Login (obtener token)
```bash
# Primero necesitas hacer login en el sistema web
# o usar un token personal access creado en la BD
```

#### 2. Prueba de Throttling
```bash
# Hacer 130 peticiones en 1 minuto
for i in {1..130}; do
  curl -s "http://localhost:8000/api/v1/catalogos" \
    -H "Authorization: Bearer {token}" > /dev/null
  echo "Request $i"
done
# Debería empezar a rechazar tras 120 peticiones
```

#### 3. Prueba de Caché
```bash
# Hacer dos peticiones seguidas al mismo endpoint
# La segunda debería ser más rápida (caché)
time curl -s "http://localhost:8000/api/v1/catalogos" \
  -H "Authorization: Bearer {token}"

time curl -s "http://localhost:8000/api/v1/catalogos" \
  -H "Authorization: Bearer {token}"
```

---

## ⚛️ Integración con React

### Configuración de Axios

```javascript
// resources/js/Services/api.js
import axios from 'axios';

const api = axios.create({
  baseURL: '/api/v1',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Interceptor para agregar token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor para manejar errores
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado, redirigir a login
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;
```

### Hook para Mesas

```javascript
// resources/js/Hooks/useMesa.js
import { useState, useCallback } from 'react';
import api from '@/Services/api';

export function useMesa() {
  const [mesa, setMesa] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const buscarMesa = useCallback(async (codigo) => {
    setLoading(true);
    setError(null);
    
    try {
      const response = await api.get(`/mesa/${codigo}`);
      setMesa(response.data.data);
      return response.data.data;
    } catch (err) {
      setError(err.response?.data?.message || 'Error al buscar mesa');
      return null;
    } finally {
      setLoading(false);
    }
  }, []);

  return { mesa, loading, error, buscarMesa };
}
```

### Hook para Catálogos

```javascript
// resources/js/Hooks/useCatalogos.js
import { useState, useEffect } from 'react';
import api from '@/Services/api';

export function useCatalogos() {
  const [catalogos, setCatalogos] = useState({
    partidos: [],
    cargos: [],
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const fetchCatalogos = async () => {
      try {
        const response = await api.get('/catalogos');
        setCatalogos({
          partidos: response.data.data.partidos_politicos,
          cargos: response.data.data.cargos,
        });
      } catch (error) {
        console.error('Error cargando catálogos:', error);
      } finally {
        setLoading(false);
      }
    };

    fetchCatalogos();
  }, []);

  return { ...catalogos, loading };
}
```

### Componente de Envío de Acta

```javascript
// resources/js/Components/ActaForm.jsx
import React, { useState } from 'react';
import { useCatalogos } from '@/Hooks/useCatalogos';
import api from '@/Services/api';

export default function ActaForm({ mesa }) {
  const { partidos, cargos, loading } = useCatalogos();
  const [formData, setFormData] = useState({
    id_cargo: '',
    codigo_acta: '',
    total_sobres: '',
    total_votantes: '',
    votos_blancos: '',
    votos_nulos: '',
    votos_validos: '',
  });
  const [votosPartido, setVotosPartido] = useState({});
  const [fotos, setFotos] = useState({
    frontal: null,
    reverso: null,
  });

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    const data = new FormData();
    data.append('codigo_mesa', mesa.codigo_tse);
    data.append('id_cargo', formData.id_cargo);
    data.append('codigo_acta', formData.codigo_acta);
    data.append('foto_frontal', fotos.frontal);
    data.append('foto_reverso', fotos.reverso);
    data.append('total_sobres', formData.total_sobres);
    data.append('total_votantes', formData.total_votantes);
    data.append('votos_validos', formData.votos_validos);
    data.append('votos_blancos', formData.votos_blancos);
    data.append('votos_nulos', formData.votos_nulos);
    data.append('digitador', 'Usuario Actual');
    
    // Convertir votos por partido a JSON
    const votosArray = Object.keys(votosPartido).map(id => ({
      id_partido: parseInt(id),
      votos: parseInt(votosPartido[id]) || 0,
    }));
    data.append('votos_partido', JSON.stringify(votosArray));

    try {
      await api.post('/acta', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      });
      alert('Acta registrada exitosamente');
    } catch (error) {
      alert('Error: ' + error.response?.data?.message);
    }
  };

  if (loading) return <div>Cargando...</div>;

  return (
    <form onSubmit={handleSubmit}>
      {/* Campos del formulario */}
    </form>
  );
}
```

---

## 🔍 Debugging y Troubleshooting

### Problemas Comunes

#### 1. Error 401 - Unauthorized
```
Causa: Token inválido o expirado
Solución: 
- Verificar que el token no haya expirado
- Rehacer login para obtener nuevo token
- Verificar que el header Authorization esté correcto
```

#### 2. Error 429 - Too Many Requests
```
Causa: Se excedió el límite de 120 peticiones/minuto
Solución:
- Implementar debouncing en búsquedas
- Usar caché en el frontend
- Optimizar número de peticiones
```

#### 3. Error 500 - Internal Server Error
```
Causa: Error en el servidor (BD, lógica, etc.)
Solución:
- Revisar logs en storage/logs/laravel.log
- Verificar que las fotos no excedan tamaño máximo
- Validar formato de JSON en votos_partido
```

#### 4. Fotos no se guardan
```
Causa: Permisos de storage o tamaño excedido
Solución:
- Verificar que storage/app/public/actas tenga permisos 755
- Verificar php.ini upload_max_filesize (recomendado 10M)
- Verificar que el Content-Type sea multipart/form-data
```

---

## 📈 Performance

### Optimizaciones Implementadas

1. **Caché Redis:**
   - Catálogos: Cache forever
   - Estadísticas: 60 segundos
   - Resultados por cargo/geografía: 60 segundos

2. **Throttling:**
   - API privada: 120 req/min por usuario
   - API pública: 30 req/min por IP

3. **Eager Loading:**
   - Consultas con `with()` para evitar N+1

4. **Base de Datos:**
   - Índices en campos de búsqueda frecuente
   - ResumenVoto es una tabla/materializada optimizada

---

## 🚀 Próximos Pasos

Ahora que entiendes la API completa, puedes:

1. **Probar los endpoints** con Postman/cURL
2. **Crear la estructura React** siguiendo los hooks de ejemplo
3. **Implementar componentes** paso a paso
4. **Conectar con el backend** real

---

## 📚 Referencias

- [Laravel Sanctum Docs](https://laravel.com/docs/10.x/sanctum)
- [Laravel Throttle](https://laravel.com/docs/10.x/routing#rate-limiting)
- [Axios Docs](https://axios-http.com/)
- [React Query](https://tanstack.com/query/latest) (opcional para caché en React)

---

**Versión:** 1.0  
**Fecha:** 2026-02-01  
**Estado:** Lista para implementación React

---

¿Listo para empezar la implementación de React? 🚀
