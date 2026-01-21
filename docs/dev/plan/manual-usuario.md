# Manual de Usuario - Sistema Electoral

## Fecha: 2026-01-21
## Versión: 1.0

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Panel de Administración](#panel-de-administración)
4. [Gestión de Personas y Usuarios](#gestión-de-personas-y-usuarios)
5. [Configuración Electoral](#configuración-electoral)
6. [API de Escrutinio](#api-de-escrutinio)
7. [Resultados en Tiempo Real](#resultados-en-tiempo-real)
8. [Seguridad y Roles](#seguridad-y-roles)
9. [Flujo Completo de una Elección](#flujo-completo-de-una-elección)
10. [Referencia Rápida](#referencia-rápida)

---

## 1. Introducción

### ¿Qué es el Sistema Electoral?

El Sistema Electoral es una plataforma web completa diseñada para gestionar elecciones democráticas, desde la configuración inicial hasta el escrutinio y publicación de resultados en tiempo real.

### Características Principales

- **Gestión Centralizada:** Panel de administración para configurar todos los aspectos de la elección
- **Escalabilidad:** Soporta miles de mesas electorales concurrentes
- **Seguridad:** Autenticación robusta, auditoría de todas las acciones y protección contra ataques
- **Tiempo Real:** Resultados actualizados en tiempo real a medida que se reciben las actas
- **API Móvil:** API REST para aplicaciones móviles de escrutinio en condiciones de baja conectividad

### Roles de Usuario

| Rol | Descripción | Permisos |
|-----|-------------|-----------|
| **Administrador** | Acceso total al sistema | Gestión de usuarios, configuración electoral, resultados |
| **Operador** | Gestión de datos maestros | CRUD de personas, mesas, recintos, candidatos |
| **Delegado** | Envío de actas | Solo acceso a API móvil para su mesa asignada |
| **Observador** | Consulta de resultados | Solo lectura de resultados |

---

## 2. Arquitectura del Sistema

### Componentes Principales

```
┌─────────────────────────────────────────────────────────────┐
│                   Panel Web (Voyager)                    │
│  - Gestión de usuarios y roles                           │
│  - Configuración electoral                              │
│  - Visualización de resultados                           │
└──────────────────┬──────────────────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
┌───────▼────────┐  ┌──────▼────────────┐
│  Backend API   │  │  Base de Datos   │
│  Laravel/PHP   │  │  MySQL + Redis   │
│  - REST API    │  │                 │
│  - Sanctum     │  └─────────────────┘
└───────┬────────┘
        │
┌───────▼──────────────────────────────────────────────────┐
│           Aplicaciones Móviles (PWA)                    │
│  - Delegados de mesa                                    │
│  - Captura de actas                                     │
│  - Envío offline cuando hay conexión                      │
└────────────────────────────────────────────────────────────┘
```

### Módulos del Sistema

1. **Usuarios y Personas:** Gestión de credenciales y datos personales separados
2. **Geografía:** Jerarquía administrativa (departamento, provincia, municipio)
3. **Organizaciones Políticas:** Partidos, siglas, colores
4. **Recintos y Mesas:** Lugares de votación y mesas electorales
5. **Cargos:** Puestos electivos (Gobernador, Alcalde, etc.)
6. **Candidatos:** Candidatos por cargo y partido
7. **Escritunio:** Recepción y procesamiento de actas
8. **Resultados:** Agregación y visualización de votos

---

## 3. Panel de Administración

### Acceso al Panel

**URL:** `/admin`

**Credenciales por defecto:**
- Usuario: `admin@example.com`
- Contraseña: `password`

> ⚠️ **Importante:** Cambiar las credenciales por defecto antes de producción.

### Dashboard Principal

El dashboard muestra:
- Estadísticas generales de la elección
- Mesas escrutadas vs totales
- Últimas actas recibidas
- Alertas del sistema

### Navegación

Menú principal en el panel:
- **Personas:** Gestión de personas físicas
- **Usuarios:** Gestión de credenciales de acceso
- **Roles:** Configuración de permisos
- **Medios:** Biblioteca de imágenes
- **Configuración Electoral:**
  - Geografías
  - Recintos
  - Mesas
  - Cargos
  - Organizaciones Políticas
  - Candidatos

---

## 4. Gestión de Personas y Usuarios

### 4.1 Gestión de Personas

#### Crear una Persona

**Ruta:** `/admin/people`

**Pasos:**
1. Hacer clic en "Nueva Persona"
2. Completar el formulario:
   - **CI:** Cédula de identidad (7-10 dígitos)
   - **Nombre(s):** Nombres de la persona
   - **Apellido Paterno:** Apellido paterno
   - **Apellido Materno:** Apellido materno (opcional)
   - **Fecha de Nacimiento:** Formato AAAA-MM-DD
   - **Género:** Masculino/Femenino
   - **Email:** Correo electrónico (opcional)
   - **Teléfono:** Número de contacto (opcional)
   - **Dirección:** Domicilio (opcional)
   - **Foto:** Imagen en formato JPEG, PNG, BMP o WebP (máx 10MB)
3. Hacer clic en "Guardar"

#### Búsqueda de Personas

El sistema permite búsqueda en tiempo real:
- Por CI, nombres, apellidos o email
- Los resultados se actualizan automáticamente mientras escribes

#### Ver y Editar Persona

1. Ir a la lista de personas
2. Hacer clic en el botón "Ver" o "Editar" junto a la persona
3. Modificar los campos necesarios
4. Guardar cambios

### 4.2 Gestión de Usuarios

#### Relación Persona-Usuario

Cada usuario está vinculado a una persona física:
- **Persona:** Datos personales (CI, nombres, dirección)
- **Usuario:** Credenciales de acceso (email, contraseña, rol)

#### Crear un Usuario

**Ruta:** `/admin/users`

**Pasos:**
1. Hacer clic en "Nuevo Usuario"
2. Seleccionar o crear una persona:
   - Seleccionar persona existente de la lista
   - O crear una nueva persona directamente
3. Configurar credenciales:
   - **Email:** Correo electrónico (único)
   - **Contraseña:** Mínimo 8 caracteres
   - **Confirmar Contraseña:** Repetir contraseña
4. Asignar rol:
   - **Administrador:** Acceso total
   - **Operador:** Gestión de datos maestros
   - **Observador:** Solo lectura
5. Definir estado: Activo/Inactivo
6. Guardar

#### Restablecer Contraseña

1. Ir a la lista de usuarios
2. Hacer clic en "Editar" el usuario
3. Generar nueva contraseña o dejar vacío para mantener la actual
4. Guardar

#### Desactivar Usuario

1. Editar usuario
2. Cambiar estado a "Inactivo"
3. Guardar

---

## 5. Configuración Electoral

### 5.1 Jerarquía Geográfica

#### Concepto

El sistema organiza la geografía en una jerarquía:
```
Departamento
  └─ Provincia
      └─ Municipio
          └─ Recinto
              └─ Mesa
```

#### Crear Geografías

**Ruta:** `/admin/geografias`

**Campos:**
- **Nombre:** Nombre de la división administrativa
- **Tipo:** departamento, provincia, municipio
- **Código TSE:** Código oficial del Tribunal Supremo Electoral
- **Padre:** Geografía padre en la jerarquía (para provincias y municipios)
- **Nivel Jerárquico:** Nivel en el árbol (automático)

#### Ejemplo

1. Crear Departamento "Beni"
2. Crear Provincias de Beni (padre: Beni)
3. Crear Municipios de cada provincia (padre: Provincia)

### 5.2 Recintos de Votación

**Ruta:** `/admin/recintos`

#### Crear Recinto

1. Hacer clic en "Nuevo Recinto"
2. Completar:
   - **Nombre:** Nombre del recinto (ej. "Unidad Educativa Santa María")
   - **Geografía:** Municipio donde se ubica
   - **Dirección:** Dirección física detallada
   - **Código TSE:** Código oficial
3. Guardar

#### Ver Recintos

La lista muestra:
- Nombre del recinto
- Ubicación geográfica
- Número de mesas asignadas

### 5.3 Mesas Electorales

**Ruta:** `/admin/mesas`

#### Crear Mesa

1. Hacer clic en "Nueva Mesa"
2. Completar:
   - **Código TSE:** Código único de mesa (ej. "BEN001001001")
   - **Recinto:** Recinto al que pertenece
   - **Electores Habilitados:** Número de votantes en esa mesa
   - **Estado:** Activa/Inactiva
3. Guardar

#### Asignación Automática

Las mesas se asignan a un recinto específico. El código sigue el formato:
```
[PROV][MUN][REC][MESA]
Ejemplo: BEN001001001 = Beni, Municipio 1, Recinto 1, Mesa 1
```

### 5.4 Cargos Electivos

**Ruta:** `/admin/cargos`

#### Cargos Disponibles

- Gobernador
- Asambleísta Departamental
- Alcalde Municipal
- Concejal Municipal

#### Crear Cargo

1. Hacer clic en "Nuevo Cargo"
2. Completar:
   - **Nombre:** Descripción del cargo
   - **Nivel:** Departamental o Municipal
3. Guardar

### 5.5 Organizaciones Políticas

**Ruta:** `/admin/organizaciones-politicas`

#### Crear Partido

1. Hacer clic en "Nueva Organización"
2. Completar:
   - **Nombre:** Nombre completo del partido
   - **Sigla:** Abreviatura (ej. "MAS")
   - **Color Hex:** Color en formato hexadecimal (ej. "#009640" para verde)
   - **Código TSE:** Código oficial
   - **Estado:** Activo/Inactivo
3. Guardar

#### Ejemplo de Partidos Configurados

- MAS (Movimiento al Socialismo) - Verde (#009640)
- CC (Comunidad Ciudadana) - Azul (#0055A4)
- FPV (Frente Para la Victoria) - Azul claro (#0077C0)

### 5.6 Candidatos

**Ruta:** `/admin/candidatos`

#### Registrar Candidato

1. Hacer clic en "Nuevo Candidato"
2. Completar:
   - **Persona:** Seleccionar persona de la lista
   - **Cargo:** Cargo al que postula
   - **Organización Política:** Partido que postula
   - **Número de Lista:** Número en la papeleta
   - **Foto:** Imagen del candidato
3. Guardar

---

## 6. API de Escrutinio

### 6.1 Concepto

La API permite que aplicaciones móviles envíen actas de escrutinio desde las mesas al servidor central.

### 6.2 Autenticación

Uso de **Laravel Sanctum** con tokens Bearer.

#### Obtener Token de API

**Método 1: Token personal**

```bash
curl -X POST http://localhost:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "delegado@example.com",
    "password": "password"
  }'
```

**Método 2: Token de aplicación (para producción)**

```bash
php artisan tinker
>>> User::find(1)->createToken('mobile-app-token')->plainTextToken
```

### 6.3 Endpoints

#### GET /api/v1/catalogos

Obtener catálogos para la aplicación móvil.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "organizaciones": [
      {
        "id": 1,
        "nombre": "Movimiento al Socialismo",
        "sigla": "MAS",
        "color_hex": "#009640",
        "codigo_tse": "1"
      }
    ],
    "cargos": [
      {
        "id": 1,
        "descripcion": "Gobernador",
        "nivel": "departamental"
      }
    ],
    "geografias": [
      {
        "id": 1,
        "nombre": "Beni",
        "tipo": "departamento"
      }
    ]
  }
}
```

#### GET /api/v1/mesa/{codigo}

Obtener datos de una mesa específica.

**Ejemplo:**
```bash
GET /api/v1/mesa/BEN001001001
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "codigo_tse": "BEN001001001",
    "recinto": {
      "nombre": "Unidad Educativa Santa María",
      "geografia": {
        "nombre": "Trinidad"
      }
    },
    "electores_habilitados": 350,
    "estado": "activa"
  }
}
```

#### POST /api/v1/acta

Enviar acta de escrutinio.

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {token}
```

**Body:**
```json
{
  "mesa_codigo": "BEN001001001",
  "cargo_id": 1,
  "imagen": "data:image/jpeg;base64,/9j/4AAQSkZJRgABA...",
  "votos": {
    "MAS": 150,
    "CC": 95,
    "FPV": 45,
    "VOTOS_NULOS": 8,
    "VOTOS_BLANCOS": 5
  }
}
```

**Campos requeridos:**
- `mesa_codigo`: Código TSE de la mesa
- `cargo_id`: ID del cargo (1=Gobernador, 2=Alcalde, etc.)
- `imagen`: Imagen del acta en Base64 (JPEG/PNG)
- `votos`: Objeto con votos por partido + nulos + blancos

**Validaciones:**
- La mesa debe existir y estar activa
- La suma de votos debe coincidir con electores habilitados
- La imagen no puede estar vacía
- Solo se puede enviar un acta por mesa y cargo

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Acta procesada exitosamente",
  "data": {
    "acta_id": 1234,
    "procesada_at": "2026-01-21 14:30:00"
  }
}
```

**Respuesta con Error (422):**
```json
{
  "success": false,
  "message": "Error de validación",
  "errors": {
    "mesa_codigo": ["La mesa no existe"],
    "votos.MAS": ["El número de votos excede los electores habilitados"]
  }
}
```

### 6.4 Ejemplos de Uso

#### Ejemplo en JavaScript (Fetch)

```javascript
const API_URL = 'http://localhost:8000/api/v1';
const API_TOKEN = 'your-token-here';

// Obtener catálogos
async function getCatalogos() {
  const response = await fetch(`${API_URL}/catalogos`, {
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Accept': 'application/json'
    }
  });
  return await response.json();
}

// Enviar acta
async function enviarActa(mesaCodigo, imagenBase64, votos) {
  const response = await fetch(`${API_URL}/acta`, {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${API_TOKEN}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      mesa_codigo: mesaCodigo,
      cargo_id: 1,
      imagen: imagenBase64,
      votos: votos
    })
  });
  return await response.json();
}

// Uso
const acta = await enviarActa(
  'BEN001001001',
  imagenBase64,
  {
    'MAS': 150,
    'CC': 95,
    'FPV': 45,
    'VOTOS_NULOS': 8,
    'VOTOS_BLANCOS': 5
  }
);
```

#### Ejemplo en PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client(['base_uri' => 'http://localhost:8000']);

// Enviar acta
$response = $client->post('/api/v1/acta', [
    'headers' => [
        'Authorization' => 'Bearer ' . $apiToken,
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ],
    'json' => [
        'mesa_codigo' => 'BEN001001001',
        'cargo_id' => 1,
        'imagen' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABA...',
        'votos' => [
            'MAS' => 150,
            'CC' => 95,
            'VOTOS_BLANCOS' => 5,
            'VOTOS_NULOS' => 8
        ]
    ]
]);

$result = json_decode($response->getBody(), true);
```

---

## 7. Resultados en Tiempo Real

### 7.1 Dashboard de Resultados

**Ruta:** `/results`

El dashboard muestra:
- **Resultados Generales:** Totales por partido
- **Resultados por Cargo:** Gobernador, Alcalde, etc.
- **Resultados por Geografía:** Departamento, provincia, municipio
- **Gráficos:** Barras y torta para visualización

### 7.2 API de Resultados

#### GET /results

Obtener resultados generales.

**Respuesta:**
```json
{
  "success": true,
  "data": {
    "total_mesas": 1000,
    "mesas_escrutadas": 750,
    "porcentaje_escrutado": "75%",
    "resultados": [
      {
        "partido": "MAS",
        "sigla": "MAS",
        "color": "#009640",
        "votos": 45000,
        "porcentaje": "60%"
      },
      {
        "partido": "CC",
        "sigla": "CC",
        "color": "#0055A4",
        "votos": 30000,
        "porcentaje": "40%"
      }
    ]
  }
}
```

#### GET /results/cargo/{id}

Resultados por cargo específico.

#### GET /results/geografia/{id}

Resultados por geografía específica.

### 7.3 Actualización en Tiempo Real

Los resultados se actualizan automáticamente:
- Cada 5 segundos: Actualización de totales
- Cada nuevo acta: Actualización inmediata
- Usando caché Redis para rendimiento

### 7.4 Invalidación de Caché

Cuando se actualiza o elimina un acta, el caché de resultados se invalida automáticamente.

---

## 8. Seguridad y Roles

### 8.1 Sistema de Autenticación

#### Inicio de Sesión

**URL:** `/login`

1. Ingresar email y contraseña
2. Sistema valida credenciales
3. Redirección al panel según rol

#### Cierre de Sesión

**URL:** `/logout`

1. Hacer clic en nombre de usuario
2. Seleccionar "Cerrar sesión"
3. Token invalidado

### 8.2 Roles y Permisos

#### Administrador

Permisos completos:
- Gestión de usuarios y roles
- Configuración electoral completa
- Visualización de resultados
- Auditoría de acciones

#### Operador

Permisos limitados:
- CRUD de personas, mesas, recintos, candidatos
- Visualización de resultados (solo lectura)
- Sin acceso a configuración de usuarios

#### Delegado

Permisos mínimos:
- Solo acceso a API móvil
- Solo envío de actas de su mesa asignada
- Sin acceso al panel web

#### Observador

Solo lectura:
- Visualización de resultados
- Sin acceso a datos maestros
- Sin acceso a configuración

### 8.3 Auditoría

Todas las acciones críticas son auditadas:

- **Creación:** Quién creó, cuándo, desde qué IP
- **Actualización:** Qué campos cambiaron
- **Eliminación:** Soft delete con información del eliminador
- **Actas de Escrutinio:** Registro completo de cada acta recibida

**Logs guardados en:** `storage/logs/laravel.log`

---

## 9. Flujo Completo de una Elección

### Fase 1: Preparación (Semanas Antes)

1. **Configuración de Geografía**
   - Crear departamentos, provincias, municipios
   - Asignar códigos TSE

2. **Registro de Recintos**
   - Crear recintos de votación
   - Asignar a geografías

3. **Configuración de Mesas**
   - Crear mesas con códigos TSE
   - Asignar a recintos
   - Registrar electores habilitados

4. **Registro de Partidos**
   - Crear organizaciones políticas
   - Configurar siglas y colores

5. **Candidatos**
   - Registrar candidatos por cargo y partido
   - Asignar número de lista

6. **Usuarios y Delegados**
   - Crear cuentas de delegados
   - Asignar mesas a delegados
   - Entregar tokens de API

### Fase 2: Día de Elección

1. **Apertura de Mesas**
   - Delegados reciben credenciales
   - Verifican datos de mesa por API

2. **Votación**
   - Proceso físico de votación
   - No interactúa con el sistema

3. **Cierre y Escrutinio**
   - Delegados toman foto del acta
   - Contabilizan votos en la app móvil
   - Envían acta por API

### Fase 3: Procesamiento (En Tiempo Real)

1. **Recepción de Actas**
   - API recibe y valida actas
   - Almacena imagen en formato AVIF
   - Procesa votos en base de datos

2. **Cálculo de Resultados**
   - Sistema agrega votos por partido
   - Calcula porcentajes
   - Actualiza dashboard en tiempo real

3. **Invalidación de Caché**
   - Redis se actualiza con cada acta
   - Resultados disponibles inmediatamente

### Fase 4: Post-Elección

1. **Verificación**
   - Admins revisan actas recibidas
   - Verifican imágenes y datos
   - Corrigen errores si es necesario

2. **Reportes**
   - Generan reportes oficiales
   - Exportan resultados a PDF/Excel
   - Documentan incidencias

3. **Archivo**
   - Actas y resultados archivados
   - Logs de auditoría conservados
   - Sistema preparado para nueva elección

---

## 10. Referencia Rápida

### Rutas Principales

| Ruta | Descripción | Autenticación |
|------|-------------|---------------|
| `/login` | Inicio de sesión | Pública |
| `/logout` | Cierre de sesión | Requerida |
| `/admin` | Panel de administración | Admin/Operador |
| `/admin/people` | Gestión de personas | Admin/Operador |
| `/admin/users` | Gestión de usuarios | Admin |
| `/admin/cargos` | Gestión de cargos | Admin/Operador |
| `/admin/organizaciones-politicas` | Gestión de partidos | Admin/Operador |
| `/admin/geografias` | Gestión de geografías | Admin/Operador |
| `/admin/recintos` | Gestión de recintos | Admin/Operador |
| `/admin/mesas` | Gestión de mesas | Admin/Operador |
| `/admin/candidatos` | Gestión de candidatos | Admin/Operador |
| `/results` | Dashboard de resultados | Admin/Operador/Observador |
| `/api/v1/catalogos` | API: Catálogos | Token |
| `/api/v1/mesa/{codigo}` | API: Datos de mesa | Token |
| `/api/v1/acta` | API: Enviar acta | Token |

### Códigos de Estado HTTP

| Código | Significado | Descripción |
|--------|-------------|-------------|
| 200 | OK | Petición exitosa |
| 201 | Created | Recurso creado exitosamente |
| 401 | Unauthorized | No autenticado o token inválido |
| 403 | Forbidden | No tiene permisos para el recurso |
| 404 | Not Found | Recurso no encontrado |
| 422 | Unprocessable Entity | Error de validación en datos enviados |
| 500 | Internal Server Error | Error del servidor |

### Formatos de Datos

#### Fecha
- Formato: `AAAA-MM-DD`
- Ejemplo: `2026-01-21`

#### Fecha-Hora
- Formato: `AAAA-MM-DD HH:MM:SS`
- Ejemplo: `2026-01-21 14:30:00`

#### Color Hexadecimal
- Formato: `#RRGGBB`
- Ejemplo: `#009640` (verde)

#### Imagen Base64
- Formato: `data:image/{tipo};base64,{datos}`
- Ejemplo: `data:image/jpeg;base64,/9j/4AAQSkZJRgABA...`

### Consultas Útiles

#### Obtener resumen del sistema
```bash
php artisan tinker
>>> DB::table('actas_escrutinio')->count()
>>> DB::table('mesas')->count()
>>> DB::table('people')->count()
```

#### Limpiar caché de resultados
```bash
php artisan cache:clear
```

#### Ver logs recientes
```bash
tail -100 storage/logs/laravel.log
```

---

## 📞 Soporte

### Documentación Técnica

- **Plan de Desarrollo:** `docs/plan/plan.md`
- **API OpenAPI:** `docs/dev/plan/04-api-documentacion-openapi.md`
- **Índice de Documentación:** `docs/dev/plan/00-INDICE.md`

### Comandos Útiles

```bash
# Instalar dependencias
composer install
npm install

# Ejecutar migraciones
php artisan migrate

# Crear usuario administrador
php artisan db:seed --class=AdminSeeder

# Ejecutar pruebas
php artisan test

# Iniciar servidor local
php artisan serve

# Optimizar para producción
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 Notas

- El sistema usa **Redis** para caché. Asegúrese que Redis esté corriendo.
- Las imágenes se convierten automáticamente a **AVIF** para optimizar espacio.
- Todas las consultas que tardan más de **100ms** se registran en logs (modo debug).
- Los actas se validan automáticamente contra el número de electores habilitados.

---

**Versión del Documento:** 1.0
**Última Actualización:** 2026-01-21
**Estado:** Completo
