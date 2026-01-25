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

### Opción 1: Desarrollo Local con Docker (Recomendado)

Sigue estos pasos para levantar el entorno completo en tu máquina local.

#### Requisitos
- Docker
- Docker Compose

#### Pasos de Instalación
1.  **Clonar el Repositorio**
    ```bash
    git clone https://github.com/MiltonDanielhch/electoral.git
    cd electoral
    ```

2.  **Configurar Entorno Local**
    Copia el archivo de ejemplo `.env.example` a `.env`. No necesitas modificarlo para el arranque inicial, ya que `docker-compose.yaml` provee los valores por defecto.
    ```bash
    cp .env.example .env
    ```

3.  **Levantar los Contenedores**
    Este comando construirá las imágenes y levantará todos los servicios (aplicación, base de datos, Redis, colas y planificador).
    ```bash
    docker-compose up -d --build
    ```

4.  **Ejecutar Migraciones y Seeders**
    Una vez los contenedores estén corriendo, ejecuta las migraciones de la base de datos y los datos iniciales.
    ```bash
    docker exec -it electoral-app php artisan migrate:fresh --seed
    ```

5.  **Crear Usuario Administrador**
    Crea tu usuario para acceder al panel de Voyager.
    ```bash
    docker exec -it electoral-app php artisan voyager:admin tu-email@ejemplo.com --create
    ```

6.  **Acceder a la Aplicación**
    ¡Listo! Puedes acceder a la aplicación en **http://localhost:8082**.

### Opción 2: Despliegue en Producción con Coolify

El proyecto está preparado para un despliegue "push-to-deploy" usando Coolify.

#### Requisitos
- Un repositorio en GitHub con el código del proyecto.
- Una instancia de Coolify configurada.

#### Pasos de Despliegue
1.  **Subir Cambios a GitHub**
    Asegúrate de que todos tus cambios estén en la rama que vas a desplegar (ej. `main`).
    ```bash
    git push origin main
    ```

2.  **Crear Recurso en Coolify**
    - En tu panel de Coolify, selecciona "Create New Resource".
    - Elige "From Git Repository" y selecciona tu repositorio y rama.
    - Coolify detectará automáticamente el `docker-compose.yaml` y configurará los servicios (`app`, `queue`, `scheduler`).

3.  **Configurar Variables de Entorno**
    - Ve a la pestaña "Environment Variables" de tu aplicación en Coolify.
    - Agrega las variables necesarias para producción. Coolify gestionará las de la base de datos y Redis si los creas como servicios dependientes.

    **Variables Clave para Producción:**
    ```env
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://tu-dominio-desplegado.com
    APP_KEY=base64:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx= # Genera una nueva con php artisan key:generate

    # Drivers de Rendimiento
    CACHE_DRIVER=redis
    SESSION_DRIVER=redis
    QUEUE_CONNECTION=redis
    ```

4.  **Desplegar**
    - Haz clic en el botón "Deploy". Coolify construirá la imagen y desplegará los contenedores.

5.  **Ejecutar Comandos Post-Despliegue**
    - Una vez el despliegue sea exitoso, ve a la pestaña "Execute Command" de tu servicio `app`.
    - Ejecuta los comandos para preparar la base de datos de producción:
      ```bash
      php artisan migrate --seed --force
      php artisan storage:link
      php artisan voyager:admin tu-admin@produccion.com --create
      php artisan optimize
      ```

---

Para más detalles, ver la [documentación completa de Docker](docs/documentar/13-docker.md).

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

---
