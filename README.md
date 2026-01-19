<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://laravel.com/docs/10.x">
    <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel 10.x">
  </a>
  <a href="https://voyager.readme.io">
    <img src="https://img.shields.io/badge/Voyager-1.7+-00A9E0?style=flat-square" alt="TCG Voyager">
  </a>
  <a href="https://php.net">
    <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php" alt="PHP 8.2+">
  </a>
  <a href="https://docker.com">
    <img src="https://img.shields.io/badge/Docker-Supported-2496ED?style=flat-square&logo=docker" alt="Docker">
  </a>
</p>

<h1 align="center">Sistema Electoral</h1>
<p align="center">
  Sistema de gestión electoral basado en Laravel + Voyager
</p>

---

## 📋 Tabla de Contenido

- [Descripción](#descripción)
- [Características](#características)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Docker](#docker)
- [Documentación](#documentación)
- [Estado del Sistema](#estado-del-sistema)
- [Licencia](#licencia)

---

## 📖 Descripción

El Sistema Electoral es una aplicación web completa para la gestión electoral construida sobre Laravel 10.x con el panel de administración TCG Voyager. El sistema permite gestionar personas, usuarios, roles y permisos con funcionalidades avanzadas de registro, auditoría y eliminación de registros.

### Versión Actual
- **Versión:** 1.2.0
- **Estado:** ✅ Producción Listo
- **Última actualización:** 2026-01-18

---

## ✨ Características

### Gestión de Personas
- ✅ Registro completo de personas naturales y jurídicas
- ✅ Documentos de identidad (CI, NIT, Pasaporte)
- ✅ Información de contacto y domicilio
- ✅ Gestión de fotografías con múltiples tamaños (AVIF optimizado)
- ✅ Búsqueda avanzada con filtros
- ✅ Sistema de estados (Activo/Inactivo/Pendiente)

### Gestión de Usuarios
- ✅ Sistema de autenticación basado en Voyager
- ✅ Relación uno a uno con personas
- ✅ Roles y permisos personalizados
- ✅ Soft deletes (eliminación lógica)
- ✅ Registro de auditoría automática

### Sistema de Auditoría
- ✅ Registro automático de quién crea registros
- ✅ Registro de quién elimina registros con observaciones
- ✅ Logs de peticiones HTTP completos
- ✅ Tracking de roles de usuarios

### Sistema de Almacenamiento
- ✅ Gestión de imágenes con múltiples tamaños
- ✅ Formato AVIF para optimización
- ✅ Organización por fechas (mes/año)
- ✅ Integración con Intervention Image

### Seguridad y Control
- ✅ Middleware personalizados para autenticación y autorización
- ✅ Sistema de licencias integrado
- ✅ Modo mantenimiento
- ✅ Control de acceso por roles
- ✅ Verificación de permisos BREAD
- ✅ Sin vulnerabilidades de SQL Injection
- ✅ Validaciones robustas en todos los controladores

---

## 🛠️ Requisitos

### Servidor
- **PHP:** 8.2 o superior
- **Composer:** 2.x
- **MySQL/MariaDB:** 5.7+ / 10.3+
- **Nginx/Apache:** Web server compatible con Laravel
- **Node.js & NPM:** Para compilar assets frontend (opcional)

### Extensiones PHP
```
php-mbstring
php-intl
php-dom
php-gd
php-xml
php-zip
php-curl
php-pdo_mysql
php-bcmath
php-exif
```

### Opcionales
- **Docker:** Para contenerización
- **Redis:** Para caché y colas

---

## 🚀 Instalación

### Opción 1: Instalación Rápida ⚡

Para una instalación rápida usando el instalador automatizado de Laravel:

```bash
composer install
cp .env.example .env
php artisan example:install
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data storage bootstrap/cache
```

> **Nota:** Este método ejecuta el instalador de ejemplo que configura la base de datos, migra y ejecuta los seeders automáticamente.

---

### Opción 2: Instalación Paso a Paso (Recomendado) 📝

Para un control total sobre el proceso de instalación:

#### 1. Clonar el Repositorio
```bash
git clone https://github.com/tu-usuario/electoral.git
cd electoral
```

#### 2. Instalar Dependencias
```bash
composer install
npm install  # opcional
```

#### 3. Configurar Entorno
```bash
cp .env.example .env
php artisan key:generate
```

#### 4. Configurar Base de Datos
Editar `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=electoral
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

#### 5. Ejecutar Migraciones y Seeders
```bash
php artisan migrate
php artisan db:seed --class=VoyagerDatabaseSeeder
```

#### 6. Crear Enlace de Almacenamiento
```bash
php artisan storage:link
```

#### 7. Optimizar la Aplicación
```bash
php artisan optimize:clear
php artisan optimize
```

#### 8. Configurar Permisos (Linux/Mac)
```bash
chmod -R 775 storage bootstrap/cache
```

> **Nota:** En servidores Apache/Nginx, también necesitas:
> ```bash
> chown -R www-data storage bootstrap/cache
> ```

---

### Opción 3: Instalación con Docker 🐳

Ver la sección de [Docker](#docker) más abajo para la instalación completa con contenedores.

---

## ⚙️ Configuración

### URL de Acceso
- **Panel de Administración:** `/admin`
- **Login:** `/admin/login`
- **Usuario por defecto:** Ver documentación de seeders

### Configuraciones Importantes

#### Modo Mantenimiento
Desde el panel de administración:
1. Ir a `/admin/settings`
2. Buscar `configuracion.maintenance`
3. Establecer en `'1'` para activar

#### Modo Desarrollo
Desde el panel de administración:
1. Ir a `/admin/settings`
2. Buscar `system.development`
3. Marcar como `true` para activar

#### Licencias
El sistema puede integrarse con un sistema de licencias externo. Configurar la conexión a la base de datos externa en `config/database.php`.

---

## 🐳 Docker

### Archivos Requeridos
Crear en la raíz del proyecto:
- `Dockerfile`
- `unit.json`
- `.dockerignore`
- `docker-compose.yml` (opcional)

### Construir Imagen
```bash
docker build -t electoral-app .
```

### Ejecutar Contenedor
```bash
docker run -p 8000:8000 \
  -v $(pwd)/.env:/var/www/example/.env \
  electoral-app
```
o
```bash
docker run -p 8001:8000 -v "${PWD}/.env:/var/www/example/.env" electoral-app
```
tines que poner en tu .env
```bash
DB_HOST=host.docker.internal
```

### Ejecutar con Docker Compose
```bash
docker-compose up -d
```
```bashs
docker exec -it electoral-app php artisan example:install
```

### Variables de Entorno para Docker
```bash
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=electoral
DB_USERNAME=root
DB_PASSWORD=secret
```

Para más detalles, ver la [documentación completa de Docker](docs/documentar/13-docker.md).

---

## 📚 Documentación

La documentación completa del sistema está disponible en `docs/documentar/`:

### 📋 Resumen Rápido
- **[Resumen Ejecutivo](docs/documentar/16-resumen-ejecutivo.md)** - Vista rápida del estado actual del sistema

### 📖 Documentación Completa
1. **[README](docs/documentar/00-README.md)** - Documentación general del sistema
2. **[Modelos](docs/documentar/01-modelos.md)** - Modelos de datos (Person, User)
3. **[Controladores](docs/documentar/02-controladores.md)** - Controladores y lógica de negocio
4. **[Rutas](docs/documentar/03-rutas.md)** - Definición de rutas del sistema
5. **[Middleware](docs/documentar/04-middleware.md)** - Middleware personalizados (Loggin, System)
6. **[Vistas](docs/documentar/05-vistas.md)** - Vistas personalizadas del sistema
7. **[Migraciones](docs/documentar/06-migraciones.md)** - Estructura de base de datos
8. **[Configuración](docs/documentar/07-configuracion.md)** - Configuraciones del sistema
9. **[Traits](docs/documentar/08-traits.md)** - Traits reutilizables (RegistersUserEvents)
10. **[BREAD](docs/documentar/09-bread.md)** - Sistema BREAD de Voyager
11. **[Logs](docs/documentar/10-logs.md)** - Sistema de logging

### 📊 Diagramas y Análisis
12. **[Índice](docs/documentar/11-indice.md)** - Índice completo de documentación
13. **[Diagramas](docs/documentar/12-diagramas.md)** - Diagramas de arquitectura
14. **[Docker](docs/documentar/13-docker.md)** - Configuración de Docker optimizada

### 🎯 Estado del Sistema
15. **[Análisis de Bugs y Mejoras](docs/documentar/14-analisis-bugs-mejoras.md)** - Análisis completo del sistema
16. **[Plan de Ejecución](docs/documentar/15-plan-ejecucion.md)** - Plan de ejecución y resumen final
17. **[Historial de Cambios](docs/documentar/17-historial-cambios.md)** - Registro de modificaciones

---

## 📊 Estado del Sistema

### ✅ Versión 1.1.0 (2026-01-18)

#### Bugs y Vulnerabilidades
- ✅ **Bugs Críticos:** 0 resueltos (eran 6)
- ✅ **Vulnerabilidades SQL:** 0 corregidas (eran 3)
- ✅ **Errores de Sintaxis:** 0

#### Seguridad
- ✅ Sin SQL Injection
- ✅ Validación de contraseñas (mínimo 8 caracteres)
- ✅ Validaciones robustas en todos los controladores
- ✅ Auditoría automática de acciones
- ✅ Logs HTTP completos

#### Funcionalidad
- ✅ Validaciones completas con regex para CI y teléfono
- ✅ Manejo de errores con try-catch
- ✅ Transacciones de base de datos
- ✅ Soft deletes con observaciones obligatorias

#### Rendimiento
- ✅ Caché de consultas frecuentes (5 minutos)
- ✅ Imágenes optimizadas en múltiples formatos AVIF
- ✅ Consultas optimizadas con Eloquent

#### Auditoría
- ✅ Método hasRole() en modelo User
- ✅ Método hasPermission() en modelo User
- ✅ Trait RegistersUserEvents para auditoría automática
- ✅ Logs de peticiones HTTP en canal separado

### 📈 Métricas de Calidad

| Métrica | Antes | Después | Mejora |
|---------|-------|---------|--------|
| Bugs Críticos | 6 | 0 | ✅ 100% |
| Vulnerabilidades SQL | 3 | 0 | ✅ 100% |
| Validaciones de Datos | 0 | 8 | ✅ Nueva |
| Caché de Consultas | 0 | 1 | ✅ Nueva |
| Métodos de Auditoría | 0 | 2 | ✅ Nueva |

---

## 🔧 Comandos Útiles

### Verificar Sintaxis PHP
```bash
find app -name "*.php" -exec php -l {} \;
```

### Limpiar Caché
```bash
php artisan optimize:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Ver Logs
```bash
# Logs de peticiones HTTP
tail -f storage/logs/requests-$(date +%Y-%m-%d).log

# Logs generales
tail -f storage/logs/laravel.log
```

### Verificar Migraciones
```bash
php artisan migrate:status
```

### Ejecutar Tests
```bash
php artisan test
```

---

## 🤝 Contribuyendo

Las contribuciones son bienvenidas. Por favor, sigue estos pasos:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

---

## 📝 Notas Importantes

1. **Soft Deletes:** El sistema usa soft deletes para mantener integridad de datos.
2. **Auditoría:** Se registra automáticamente el usuario y rol que crea/elimina registros.
3. **Imágenes:** Las imágenes se almacenan en formato AVIF optimizado.
4. **Licencias:** El sistema tiene integración con un sistema de licencias externo (opcional).
5. **Logs de HTTP:** Se almacenan en un canal separado (`requests`) para auditoría.
6. **Docker:** El Dockerfile está completamente optimizado con NGINX Unit.

---

## 🐛 Reportando Issues

Si encuentras un bug o tienes una sugerencia, por favor:

1. Revisa la [documentación](docs/documentar/)
2. Busca issues existentes
3. Crea un nuevo issue con:
   - Descripción detallada del problema
   - Pasos para reproducir
   - Versión de PHP y Laravel
   - Mensaje de error completo
   - Capturas de pantalla si es aplicable

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

---
