# Documentación de API de Escrutinio - OpenAPI 3.0

## Fecha: 2026-01-20

## Resumen

Este documento proporciona la especificación completa de la API de Escrutinio utilizando el estándar OpenAPI 3.0 (Swagger), para facilitar la integración con aplicaciones clientes y facilitar el desarrollo.

---

## Información General

### Base URL

```
Producción: https://electoral.ejemplo.com/api/v1
Staging: https://staging.electoral.ejemplo.com/api/v1
Local: http://localhost:8000/api/v1
```

### Autenticación

**Tipo:** Bearer Token (Laravel Sanctum)

```http
Authorization: Bearer {token}
```

### Rate Limiting

- **Límite:** 60 peticiones por minuto por token
- **Headers incluidos en respuestas:**
  - `X-RateLimit-Limit`: Límite de peticiones
  - `X-RateLimit-Remaining`: Peticiones restantes
  - `X-RateLimit-Reset`: Timestamp de reset

---

## OpenAPI Specification

```yaml
openapi: 3.0.0
info:
  title: Sistema Electoral API
  description: API para el sistema de escrutinio electoral
  version: 1.0.0
  contact:
    name: Soporte Técnico
    email: soporte@electoral.ejemplo.com
  license:
    name: Propietario
  
servers:
  - url: https://electoral.ejemplo.com/api/v1
    description: Producción
  - url: https://staging.electoral.ejemplo.com/api/v1
    description: Staging
  - url: http://localhost:8000/api/v1
    description: Local

security:
  - BearerAuth: []

paths:
  # Endpoints definidos abajo...

components:
  securitySchemes:
    BearerAuth:
      type: http
      scheme: bearer
      bearerFormat: JWT
```

---

## Endpoints

### 1. Obtener Datos de Mesa

**Endpoint:** `GET /mesa/{codigo}`

**Descripción:** Obtiene los datos de una mesa específica para que el delegado verifique su asignación.

**Parámetros:**

| Nombre | Tipo | Ubicación | Requerido | Descripción |
|--------|------|-----------|-----------|-------------|
| codigo | string | path | Sí | Código de la mesa (ej: BEN001001001) |

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "codigo": "BEN001001001",
    "numero": 1,
    "recinto": {
      "id": 1,
      "nombre": "Colegio La Salle",
      "codigo": "REC001",
      "geografia": {
        "id": 15,
        "nombre": "Trinidad",
        "padre": {
          "id": 1,
          "nombre": "Cercado",
          "padre": {
            "id": 8,
            "nombre": "Beni",
            "padre": null
          }
        }
      }
    },
    "electores": 350,
    "mesas": 3,
    "activo": true,
    "created_at": "2026-01-18T10:00:00.000000Z",
    "updated_at": "2026-01-18T10:00:00.000000Z"
  }
}
```

**Respuesta No Encontrada (404):**

```json
{
  "success": false,
  "message": "Mesa no encontrada"
}
```

**Ejemplo cURL:**

```bash
curl -X GET "https://electoral.ejemplo.com/api/v1/mesa/BEN001001001" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

### 2. Obtener Catálogos

**Endpoint:** `GET /catalogos`

**Descripción:** Obtiene los catálogos necesarios para la aplicación cliente (partidos, cargos, etc.).

**Parámetros de Query (Opcionales):**

| Nombre | Tipo | Descripción |
|--------|------|-------------|
| tipo | string | Filtrar por tipo (opciones: partidos, cargos, geografias, todo) |
| parent_id | integer | ID de geografía padre (para geografías) |

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "data": {
    "organizaciones_politicas": [
      {
        "id": 1,
        "sigla": "MAS",
        "nombre": "Movimiento al Socialismo",
        "color": "#1E3A8A",
        "codigo_tse": "01"
      },
      {
        "id": 2,
        "sigla": "CC",
        "nombre": "Comunidad Ciudadana",
        "color": "#F59E0B",
        "codigo_tse": "02"
      }
    ],
    "cargos": [
      {
        "id": 1,
        "nombre": "Gobernador",
        "descripcion": "Gobernador del Departamento"
      },
      {
        "id": 2,
        "nombre": "Alcalde Municipal",
        "descripcion": "Alcalde del Municipio"
      }
    ],
    "geografias": [
      {
        "id": 8,
        "nombre": "Beni",
        "nivel": "departamento",
        "padre_id": null
      },
      {
        "id": 1,
        "nombre": "Cercado",
        "nivel": "provincia",
        "padre_id": 8
      }
    ]
  }
}
```

**Ejemplo cURL:**

```bash
curl -X GET "https://electoral.ejemplo.com/api/v1/catalogos?tipo=partidos" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

### 3. Enviar Acta de Escrutinio

**Endpoint:** `POST /acta`

**Descripción:** Recibe la imagen del acta y los datos de votos de una mesa específica.

**Headers:**

| Nombre | Valor | Requerido |
|--------|-------|-----------|
| Content-Type | multipart/form-data | Sí |
| Authorization | Bearer {token} | Sí |

**Body (Multipart Form Data):**

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| mesa_codigo | string | Sí | Código de la mesa |
| imagen | file | Sí | Imagen del acta escaneada (JPEG/PNG, máx 10MB) |
| cargo_id | integer | Sí | ID del cargo |
| votos | object | Sí | JSON string con votos por partido |

**Formato de votos (JSON):**

```json
{
  "MAS": 150,
  "CC": 120,
  "FPV": 80,
  "VOTOS_NULOS": 15,
  "VOTOS_BLANCOS": 8
}
```

**Respuesta Exitosa (200):**

```json
{
  "success": true,
  "message": "Acta procesada exitosamente",
  "data": {
    "acta_id": 12345,
    "mesa_codigo": "BEN001001001",
    "cargo_id": 1,
    "imagen_url": "https://electoral.ejemplo.com/storage/actas/BEN001001001_20260120_123456.jpg",
    "votos_registrados": {
      "MAS": 150,
      "CC": 120,
      "FPV": 80,
      "VOTOS_NULOS": 15,
      "VOTOS_BLANCOS": 8
    },
    "procesado_at": "2026-01-20T12:34:56.000000Z"
  }
}
```

**Respuesta de Validación (422):**

```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "mesa_codigo": ["El código de mesa es requerido"],
    "imagen": ["La imagen es requerida", "El archivo debe ser JPEG o PNG"],
    "votos": ["El total de votos debe coincidir con el número de electores"]
  }
}
```

**Ejemplo cURL:**

```bash
curl -X POST "https://electoral.ejemplo.com/api/v1/acta" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "mesa_codigo=BEN001001001" \
  -F "cargo_id=1" \
  -F 'votos={"MAS":150,"CC":120,"FPV":80,"VOTOS_NULOS":15,"VOTOS_BLANCOS":8}' \
  -F "imagen=@/path/to/acta.jpg" \
  -H "Accept: application/json"
```

---

### 4. Verificar Estado de Acta

**Endpoint:** `GET /acta/{mesa_codigo}`

**Descripción:** Verifica si ya se ha enviado un acta para una mesa específica.

**Parámetros:**

| Nombre | Tipo | Ubicación | Requerido | Descripción |
|--------|------|-----------|-----------|-------------|
| mesa_codigo | string | path | Sí | Código de la mesa |
| cargo_id | integer | query | Sí | ID del cargo |

**Respuesta Exitosa (200) - Acta Encontrada:**

```json
{
  "success": true,
  "data": {
    "id": 12345,
    "mesa_codigo": "BEN001001001",
    "cargo_id": 1,
    "procesado": true,
    "procesado_at": "2026-01-20T12:34:56.000000Z",
    "imagen_url": "https://electoral.ejemplo.com/storage/actas/BEN001001001_20260120_123456.jpg",
    "votos": {
      "MAS": 150,
      "CC": 120,
      "FPV": 80,
      "VOTOS_NULOS": 15,
      "VOTOS_BLANCOS": 8
    }
  }
}
```

**Respuesta Exitosa (200) - Acta No Encontrada:**

```json
{
  "success": true,
  "data": {
    "procesado": false,
    "message": "No se ha enviado acta para esta mesa"
  }
}
```

**Ejemplo cURL:**

```bash
curl -X GET "https://electoral.ejemplo.com/api/v1/acta/BEN001001001?cargo_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## Códigos de Respuesta

| Código | Descripción |
|--------|-------------|
| 200 | OK - Petición exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Parámetros inválidos |
| 401 | Unauthorized - Token de autenticación inválido |
| 403 | Forbidden - Sin permisos para el recurso |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 429 | Too Many Requests - Límite de rate limiting excedido |
| 500 | Internal Server Error - Error del servidor |

---

## Códigos de Error

El API puede retornar los siguientes códigos de error:

| Código | Mensaje | Descripción |
|--------|---------|-------------|
| INVALID_TOKEN | Token de autenticación inválido o expirado |
| MESA_NOT_FOUND | La mesa especificada no existe |
| ACTA_ALREADY_PROCESSED | Ya se ha enviado un acta para esta mesa |
| INVALID_IMAGE | El formato de imagen no es válido |
| IMAGE_TOO_LARGE | La imagen excede el tamaño máximo (10MB) |
| INVALID_VOTES | Los votos no cumplen con las reglas de validación |
| INVALID_CARGO | El cargo especificado no existe |
| INVALID_PARTIDO | Uno de los partidos especificados no existe |

---

## Reglas de Validación

### Validación de Votos

1. **Suma de Votos:** La suma de todos los votos debe ser igual o menor al número de electores de la mesa.

2. **Votos No Negativos:** Todos los valores de votos deben ser números positivos o cero.

3. **Partidos Válidos:** Todos los partidos especificados deben existir en la base de datos.

4. **Votos Nulos/Blancos:** Los votos nulos y blancos son obligatorios.

```javascript
// Ejemplo de validación en frontend
function validarVotos(votos, electores) {
  const total = Object.values(votos).reduce((sum, val) => sum + val, 0);
  
  if (total > electores) {
    return { valid: false, message: 'El total de votos excede el número de electores' };
  }
  
  if (!votos.hasOwnProperty('VOTOS_NULOS')) {
    return { valid: false, message: 'Faltan los votos nulos' };
  }
  
  if (!votos.hasOwnProperty('VOTOS_BLANCOS')) {
    return { valid: false, message: 'Faltan los votos en blanco' };
  }
  
  return { valid: true };
}
```

---

## Ejemplos de Integración

### Ejemplo en JavaScript (Fetch)

```javascript
const API_BASE_URL = 'https://electoral.ejemplo.com/api/v1';
const API_TOKEN = 'YOUR_TOKEN';

// Obtener datos de mesa
async function obtenerMesa(codigo) {
  const response = await fetch(`${API_BASE_URL}/mesa/${codigo}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Accept': 'application/json',
    },
  });
  
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error(data.message || 'Error al obtener mesa');
  }
  
  return data.data;
}

// Obtener catálogos
async function obtenerCatalogos(tipo = 'todo') {
  const response = await fetch(`${API_BASE_URL}/catalogos?tipo=${tipo}`, {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Accept': 'application/json',
    },
  });
  
  const data = await response.json();
  return data.data;
}

// Enviar acta
async function enviarActa(mesaCodigo, cargoId, imagenFile, votos) {
  const formData = new FormData();
  formData.append('mesa_codigo', mesaCodigo);
  formData.append('cargo_id', cargoId);
  formData.append('imagen', imagenFile);
  formData.append('votos', JSON.stringify(votos));
  
  const response = await fetch(`${API_BASE_URL}/acta`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Accept': 'application/json',
    },
    body: formData,
  });
  
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error(data.message || 'Error al enviar acta');
  }
  
  return data.data;
}

// Uso
obtenerMesa('BEN001001001')
  .then(mesa => console.log('Mesa:', mesa))
  .catch(error => console.error('Error:', error));
```

---

### Ejemplo en PHP (Guzzle)

```php
<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ElectoralApiClient
{
    private Client $client;
    private string $token;
    
    public function __construct(string $baseUrl, string $token)
    {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);
        $this->token = $token;
    }
    
    public function obtenerMesa(string $codigo): array
    {
        $response = $this->client->get("/mesa/{$codigo}");
        
        return json_decode($response->getBody(), true)['data'];
    }
    
    public function obtenerCatalogos(string $tipo = 'todo'): array
    {
        $response = $this->client->get('/catalogos', [
            'query' => ['tipo' => $tipo],
        ]);
        
        return json_decode($response->getBody(), true)['data'];
    }
    
    public function enviarActa(
        string $mesaCodigo,
        int $cargoId,
        string $imagenPath,
        array $votos
    ): array {
        $response = $this->client->post('/acta', [
            'multipart' => [
                [
                    'name' => 'mesa_codigo',
                    'contents' => $mesaCodigo,
                ],
                [
                    'name' => 'cargo_id',
                    'contents' => $cargoId,
                ],
                [
                    'name' => 'imagen',
                    'contents' => fopen($imagenPath, 'r'),
                    'filename' => basename($imagenPath),
                ],
                [
                    'name' => 'votos',
                    'contents' => json_encode($votos),
                ],
            ],
        ]);
        
        return json_decode($response->getBody(), true)['data'];
    }
}

// Uso
$client = new ElectoralApiClient(
    'https://electoral.ejemplo.com/api/v1',
    'YOUR_TOKEN'
);

try {
    $mesa = $client->obtenerMesa('BEN001001001');
    echo "Mesa: {$mesa['recinto']['nombre']}\n";
    
    $catalogos = $client->obtenerCatalogos('partidos');
    echo "Partidos: " . count($catalogos['organizaciones_politicas']) . "\n";
} catch (RequestException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Testing de la API

### Ejemplos con Postman

**Colección de Postman:** Disponible en `/api-tests/postman/` (a crear)

### Ejemplos con cURL

```bash
# Test: Obtener mesa
curl -X GET "http://localhost:8000/api/v1/mesa/BEN001001001" \
  -H "Authorization: Bearer YOUR_TEST_TOKEN" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\nTime: %{time_total}s\n"

# Test: Enviar acta
curl -X POST "http://localhost:8000/api/v1/acta" \
  -H "Authorization: Bearer YOUR_TEST_TOKEN" \
  -F "mesa_codigo=BEN001001001" \
  -F "cargo_id=1" \
  -F 'votos={"MAS":150,"CC":120,"FPV":80,"VOTOS_NULOS":15,"VOTOS_BLANCOS":8}' \
  -F "imagen=@test-image.jpg" \
  -H "Accept: application/json" \
  -w "\nHTTP Status: %{http_code}\nTime: %{time_total}s\n"
```

---

## Swagger UI

Para visualizar la documentación de forma interactiva:

1. Instalar l5-swagger:

```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

2. Generar documentación:

```bash
php artisan l5-swagger:generate
```

3. Acceder a:
   - UI: `http://localhost:8000/api/documentation`
   - JSON: `http://localhost:8000/api/documentation.json`

---

## Referencias

- Laravel API Documentation: https://laravel.com/docs/10.x/api-resources
- OpenAPI Specification: https://swagger.io/specification/
- Laravel Sanctum: https://laravel.com/docs/10.x/sanctum
- Plan general: `docs/plan/plan.md`
- Controladores API: `app/Http/Controllers/Api/`

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-20  
**Versión API:** 1.0.0
