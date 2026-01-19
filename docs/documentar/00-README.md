# Panel Administrativo - Documentación General

## Información del Proyecto

**Proyecto:** Panel Administrativo genérico basado en Laravel + Voyager  
**Versión Laravel:** 10.x  
**Versión PHP:** 8.2+  
**Framework Admin:** TCG Voyager 1.7+  
**Versión del Sistema:** 1.2.0  
**Última Actualización:** 2026-01-18  

## Descripción General

Este es un panel de administración genérico construido sobre Laravel con el panel de administración Voyager. El sistema permite gestionar personas, usuarios, roles y permisos con funcionalidades avanzadas de registro y eliminación de registros.

## Estructura del Sistema

```
app/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Controladores personalizados
│   │   └── Middleware/        # Middleware personalizados
│   ├── Models/                # Modelos Eloquent
│   └── Traits/                # Traits reutilizables
├── database/
│   ├── migrations/            # Migraciones de base de datos
│   └── seeders/               # Seeders de datos
├── resources/views/
│   ├── administrations/       # Vistas de administración
│   ├── partials/              # Componentes reutilizables
│   └── vendor/voyager/        # Vistas sobrescritas de Voyager
├── routes/
│   ├── web.php               # Rutas web
│   ├── api.php               # Rutas API
│   └── ...                   # Otras rutas
└── config/
    └── voyager.php           # Configuración de Voyager
```

## Dependencias Principales

```json
{
  "php": "^8.2",
  "laravel/framework": "^10.0",
  "laravel/sanctum": "^3.0",
  "tcg/voyager": "^1.7"
}
```

## Características Principales

### 1. Gestión de Personas
- Registro completo de personas naturales y jurídicas
- Documentos de identidad (CI, NIT, Pasaporte)
- Información de contacto y domicilio
- Gestión de fotografías con múltiples tamaños
- Búsqueda avanzada con filtros
- Sistema de estados (Activo/Inactivo/Pendiente)

### 2. Gestión de Usuarios
- Sistema de autenticación basado en Voyager
- Relación con personas (uno a uno)
- Roles y permisos personalizados
- Soft deletes (eliminación lógica)
- Registro de auditoría

### 3. Sistema de Auditoría
- Registro automático de quién crea registros
- Registro de quién elimina registros con observaciones
- Logs de peticiones HTTP
- Tracking de roles de usuarios

### 4. Sistema de Almacenamiento
- Gestión de imágenes con múltiples tamaños (original, banner, medium, small, cropped)
- Formato AVIF para optimización
- Organización por fechas (mes/año)
- Integración con Intervention Image

### 5. Seguridad y Control
- Middleware personalizados para autenticación y autorización
- Sistema de configuración flexible
- Modo mantenimiento
- Control de acceso por roles
- Verificación de permisos BREAD

## Configuración Inicial

### Requisitos Previos
- PHP 8.2+
- Composer
- MySQL/MariaDB
- Extensiones PHP: GD, PDO, etc.

### Instalación
```bash
# Instalar dependencias
composer install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Migraciones y seeders
php artisan migrate
php artisan db:seed --class=VoyagerDatabaseSeeder

# Enlace de almacenamiento
php artisan storage:link

# Optimizar
php artisan optimize:clear
```

## Acceso al Sistema

- **URL Administración:** `/admin`
- **URL Login:** `/admin/login`
- **Rol Admin:** Acceso total al sistema

## Documentación Detallada

### 📋 Resumen Rápido
**Nuevo:** Consulte el `16-resumen-ejecutivo.md` para un resumen del estado actual del sistema.

### 📚 Documentación Completa
Consulte los archivos individuales en `docs/documentar/` para más detalles:

- `01-modelos.md` - Modelos de datos
- `02-controladores.md` - Controladores y lógica de negocio
- `03-rutas.md` - Definición de rutas
- `04-middleware.md` - Middleware personalizados
- `05-vistas.md` - Vistas personalizadas
- `06-migraciones.md` - Estructura de base de datos
- `07-configuracion.md` - Configuraciones del sistema
- `08-traits.md` - Traits reutilizables
- `09-bread.md` - BREAD de Voyager
- `10-logs.md` - Sistema de logging

### 🎯 Estado del Sistema
- `14-analisis-bugs-mejoras.md` - Análisis completo del sistema (actualizado)
- `15-plan-ejecucion.md` - Plan de ejecución (actualizado)

### 🔧 Documentación Adicional
- `11-indice.md` - Índice completo de documentación
- `12-diagramas.md` - Diagramas de arquitectura
- `13-docker.md` - Configuración de Docker (optimizado)

## Convenciones de Código

- **Nombre de tablas:** Plural en inglés (ej: `people`, `users`)
- **Nombre de modelos:** Singular en inglés (ej: `Person`, `User`)
- **Controladores:** Nombre del recurso + `Controller` (ej: `PersonController`)
- **Vistas:** Ruta relativa desde `resources/views/`
- **Rutas:** Prefijo `/admin` para rutas administrativas

## Notas Importantes

1. El sistema usa soft deletes en múltiples modelos
2. Se registra automáticamente el usuario y rol que crea/elimina registros
3. Las imágenes se almacenan en formato AVIF optimizado
4. El sistema tiene configuración flexible para mantenimiento y desarrollo
5. Los logs de HTTP se almacenan en un canal separado

## Soporte y Contacto

Para más información, consulte la documentación específica de cada módulo.
