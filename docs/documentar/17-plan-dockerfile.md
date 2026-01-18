# Plan de Ejecución - Optimizaciones del Dockerfile

## Cronograma General

Este documento es una guía paso a paso para ejecutar todas las optimizaciones identificadas en el análisis del Dockerfile.

---

## 📋 Índice del Plan

### FASE 1: Bugs Críticos (HOY - Día 1)
### FASE 2: Optimizaciones de Rendimiento (Día 2-3)
### FASE 3: Optimizaciones de Seguridad (Día 4)
### FASE 4: Optimizaciones de Mantenibilidad (Día 5)
### FASE 5: Optimizaciones Avanzadas (Semana 2)

---

## 📅 FASE 1: Bugs Críticos (HOY - Día 1)

**Tiempo estimado:** 30-45 minutos
**Prioridad:** 🔴 CRÍTICA - El Dockerfile no funciona correctamente

### Tarea 1.1: Corregir Configuración de PHP

**Archivo:** `Dockerfile` (líneas 10-15)

**Problema:** Línea 13 usa `>` (sobrescribe) en lugar de `>>` (agrega)

**Paso 1:** Abrir el Dockerfile

**Paso 2:** Reemplazar las líneas 10-15

**Código ANTES:**
```dockerfile
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini
```

**Código DESPUÉS:**
```dockerfile
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini
```

---

### Tarea 1.2: Mejorar Permisos de Directorios

**Archivo:** `Dockerfile` (líneas 21-27)

**Problema:** Permisos incompletos y se ejecuta `chown` dos veces

**Paso 1:** Reemplazar las líneas 21-27

**Código ANTES:**
```dockerfile
RUN mkdir -p /var/www/example/storage /var/www/example/bootstrap/cache

RUN chown -R unit:unit /var/www/example/storage bootstrap/cache && chmod -R 775 /var/www/example/storage

COPY . .

RUN chown -R unit:unit storage bootstrap/cache && chmod -R 775 storage bootstrap/cache
```

**Código DESPUÉS:**
```dockerfile
RUN mkdir -p /var/www/example/storage \
    /var/www/example/storage/app \
    /var/www/example/storage/framework \
    /var/www/example/storage/logs \
    /var/www/example/bootstrap/cache \
    && chown -R unit:unit /var/www/example \
    && chmod -R 775 /var/www/example/storage \
    && chmod -R 775 /var/www/example/bootstrap/cache
```

---

### Tarea 1.3: Mejorar Manejo de Archivo .env

**Archivo:** `Dockerfile` (líneas 33-34)

**Problema:** El archivo .env se crea en la imagen (problema de seguridad)

**Paso 1:** Eliminar las líneas 33-34

**Código ANTES:**
```dockerfile
COPY .env.example .env
RUN php artisan key:generate
RUN php artisan storage:link
```

**Código DESPUÉS:**
```dockerfile
# No crear .env en la imagen
# Se debe pasar como variable de entorno o volumen
```

**Nota:** El usuario debe proporcionar el archivo `.env` como volumen o variables de entorno al ejecutar el contenedor.

---

### Verificación de FASE 1

**Paso 1:** Probar construcción del Dockerfile
```bash
docker build -t electoral-app:fixed .
```

**Paso 2:** Verificar que la imagen se construya sin errores

**Paso 3:** Probar ejecución del contenedor
```bash
docker run -p 8000:8000 \
  -v $(pwd)/.env:/var/www/example/.env \
  electoral-app:fixed
```

**Paso 4:** Verificar que las configuraciones de PHP estén correctas
```bash
docker exec -it <container-id> php -i | grep -E "opcache|memory_limit|upload_max_filesize"
```

---

## 📅 FASE 2: Optimizaciones de Rendimiento (Día 2-3)

**Tiempo estimado:** 1-2 horas
**Prioridad:** 🟡 ALTA - Mejora significativa el rendimiento

### Tarea 2.1: Crear Archivo .dockerignore

**Archivo:** `.dockerignore` (NUEVO)

**Paso 1:** Crear el archivo
```bash
nano .dockerignore
```

**Contenido:**
```dockerignore
.git
.gitignore
.gitattributes
Dockerfile
docker-compose*.yml
.dockerignore
.env
.env.*
node_modules
npm-debug.log
yarn-error.log
tests
storage/*.key
storage/*.log
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
vendor
docs
README.md
*.md
.editorconfig
.phpunit.xml
```

**Beneficios:**
- Reducción del tiempo de construcción (~60%)
- Imagen más pequeña
- Menor uso de ancho de banda

---

### Tarea 2.2: Crear Docker Compose

**Archivo:** `docker-compose.yml` (NUEVO)

**Paso 1:** Crear el archivo
```bash
nano docker-compose.yml
```

**Contenido:**
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: electoral-app
    restart: unless-stopped
    ports:
      - "8000:8000"
    volumes:
      - ./storage:/var/www/example/storage
      - ./bootstrap/cache:/var/www/example/bootstrap/cache
      - ./.env:/var/www/example/.env
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=mysql
      - DB_DATABASE=electoral
      - DB_USERNAME=electoral
      - DB_PASSWORD=secret
      - CACHE_DRIVER=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - mysql
      - redis
    networks:
      - electoral-net

  mysql:
    image: mysql:8.0
    container_name: electoral-mysql
    restart: unless-stopped
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=electoral
      - MYSQL_USER=electoral
      - MYSQL_PASSWORD=secret
    volumes:
      - mysql-data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - electoral-net

  redis:
    image: redis:alpine
    container_name: electoral-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - electoral-net

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: electoral-phpmyadmin
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      - PMA_HOST=mysql
      - PMA_USER=root
      - PMA_PASSWORD=root
    depends_on:
      - mysql
    networks:
      - electoral-net

volumes:
  mysql-data:
  redis-data:

networks:
  electoral-net:
    driver: bridge
```

**Beneficios:**
- Orquestación fácil de múltiples contenedores
- Configuración centralizada
- Fácil manejo de volúmenes y redes
- Incluye base de datos, Redis y phpMyAdmin

---

### Tarea 2.3: Optimizar Copia de Archivos (Caché de Layers)

**Archivo:** `Dockerfile`

**Paso 1:** Reorganizar el Dockerfile para aprovechar el caché de capas

**Código ANTES:**
```dockerfile
COPY . .
RUN composer install --prefer-dist --optimize-autoloader --no-interaction
```

**Código DESPUÉS:**
```dockerfile
# Copiar composer.json primero (cambia menos frecuentemente)
COPY composer.json composer.lock ./

# Instalar dependencias (usa caché si no cambian)
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-dev

# Copiar el resto de archivos
COPY . .
```

**Beneficios:**
- Reducción del tiempo de construcción (~70%)
- Aprovecha el caché de Docker para dependencias de Composer

---

### Verificación de FASE 2

**Paso 1:** Limpiar imágenes anteriores
```bash
docker system prune -a
```

**Paso 2:** Construir con docker-compose
```bash
docker-compose build
```

**Paso 3:** Verificar que se construya correctamente
```bash
docker-compose ps
```

**Paso 4:** Iniciar servicios
```bash
docker-compose up -d
```

**Paso 5:** Verificar tamaño de la imagen
```bash
docker images electoral-app
```

---

## 📅 FASE 3: Optimizaciones de Seguridad (Día 4)

**Tiempo estimado:** 1-2 horas
**Prioridad:** 🟡 MEDIA - Mejora la seguridad del contenedor

### Tarea 3.1: Ejecutar como Usuario No-Root

**Archivo:** `Dockerfile`

**Paso 1:** Agregar antes de CMD

**Código ANTES:**
```dockerfile
EXPOSE 8000

CMD ["unitd", "--no-daemon"]
```

**Código DESPUÉS:**
```dockerfile
EXPOSE 8000

# Ejecutar como usuario no-root (unit ya existe en la imagen base)
USER unit

CMD ["unitd", "--no-daemon"]
```

**Beneficios:**
- Menor superficie de ataque
- Cumple con mejores prácticas de seguridad

---

### Tarea 3.2: Agregar Etiquetas de Versión

**Archivo:** `Dockerfile`

**Paso 1:** Agregar al inicio del Dockerfile

**Código ANTES:**
```dockerfile
FROM unit:1.33.0-php8.2
```

**Código DESPUÉS:**
```dockerfile
# ============================================================================
# Imagen para Sistema Electoral
# ============================================================================
ARG VERSION=1.0.0
ARG BUILD_DATE
ARG VCS_REF

LABEL org.opencontainers.image.created=$BUILD_DATE \
      org.opencontainers.image.revision=$VCS_REF \
      org.opencontainers.image.version=$VERSION \
      org.opencontainers.image.title="Sistema Electoral" \
      org.opencontainers.image.description="Panel administrativo con Laravel + Voyager" \
      org.opencontainers.image.vendor="Electoral"

FROM unit:1.33.0-php8.2
```

**Uso:**
```bash
docker build \
  --build-arg VERSION=1.0.0 \
  --build-arg BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ') \
  --build-arg VCS_REF=$(git rev-parse --short HEAD) \
  -t electoral:1.0.0 .
```

**Beneficios:**
- Metadatos de versión en la imagen
- Rastreabilidad de cambios
- Cumple con OCI Image Spec

---

### Tarea 3.3: Mejorar Seguridad de Contraseñas

**Archivo:** `Dockerfile` y `.env`

**Paso 1:** Asegurar que las contraseñas no estén en la imagen

**Práctica recomendada:**
- No incluir `.env` en la imagen (ya hecho en Tarea 1.3)
- Usar variables de entorno o secretos de Docker
- Usar Docker Secrets para contraseñas sensibles

**Ejemplo en docker-compose.yml:**
```yaml
environment:
  - DB_PASSWORD_FILE=/run/secrets/db_password
secrets:
  db_password:
    file: ./secrets/db_password.txt
```

---

### Verificación de FASE 3

**Paso 1:** Construir imagen
```bash
docker build --build-arg VERSION=1.0.0 -t electoral:1.0.0 .
```

**Paso 2:** Verificar usuario de ejecución
```bash
docker run electoral:1.0.0 whoami
# Debe mostrar: unit
```

**Paso 3:** Verificar etiquetas
```bash
docker inspect electoral:1.0.0 | grep -A 10 Labels
```

**Paso 4:** Verificar que no hay secrets en la imagen
```bash
docker history electoral:1.0.0
# Buscar capas que puedan contener .env
```

---

## 📅 FASE 4: Optimizaciones de Mantenibilidad (Día 5)

**Tiempo estimado:** 1 hora
**Prioridad:** 🟢 BAJA - Mejora la mantenibilidad

### Tarea 4.1: Agregar Documentación al Dockerfile

**Archivo:** `Dockerfile`

**Paso 1:** Agregar comentarios explicativos

**Código completo con documentación:**
```dockerfile
# ============================================================================
# Imagen para Sistema Electoral
# ============================================================================
# PHP 8.2 + NGINX Unit 1.33.0
#
# Uso:
#   docker build -t electoral-app .
#   docker run -p 8000:8000 electoral-app
#
# Variables de entorno:
#   - DB_HOST: Host de la base de datos (default: mysql)
#   - DB_DATABASE: Nombre de la base de datos (default: electoral)
#   - DB_USERNAME: Usuario de la base de datos (default: electoral)
#   - DB_PASSWORD: Contraseña de la base de datos
#   - APP_ENV: Entorno (development|staging|production)
# ============================================================================

# ----------------------------------------------------------------------------
# Imagen base
# ----------------------------------------------------------------------------
FROM unit:1.33.0-php8.2

# ----------------------------------------------------------------------------
# Instalar dependencias del sistema y extensiones de PHP
# ----------------------------------------------------------------------------
# Paquetes:
#   - libicu-dev: Internacionalización
#   - libzip-dev: Manejo de archivos ZIP
#   - libpng-dev, libjpeg-dev, libfreetype6-dev: Imágenes (GD)
#   - libssl-dev: SSL/TLS
#
# Extensiones de PHP:
#   - pcntl: Control de procesos (para queues/commands)
#   - opcache: Caché de OPcode (optimización)
#   - pdo, pdo_mysql: Base de datos MySQL
#   - intl: Internacionalización
#   - zip: Archivos ZIP
#   - gd: Procesamiento de imágenes
#   - exif: Metadatos de imágenes
#   - ftp: Protocolo FTP
#   - bcmath: Matemáticas de precisión
#   - redis: Sistema de caché (vía PECL)
# ----------------------------------------------------------------------------
RUN apt update && apt install -y \
    curl unzip git libicu-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* \
    && apt-get clean

# ----------------------------------------------------------------------------
# Configuración de PHP
# ----------------------------------------------------------------------------
# OPcache: Habilitado con JIT compilación
# JIT: Tracing (optimización dinámica)
# JIT Buffer: 256M
# Memory Limit: 512MB
# Upload Max Filesize: 64MB
# Post Max Size: 64MB
# ----------------------------------------------------------------------------
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_execution_time=300" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_input_vars=10000" >> /usr/local/etc/php/conf.d/custom.ini

# ----------------------------------------------------------------------------
# Instalar Composer
# ----------------------------------------------------------------------------
# Composer se usa para gestionar dependencias de PHP
# Se copia desde la imagen oficial de Composer
# ----------------------------------------------------------------------------
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# ----------------------------------------------------------------------------
# Directorio de trabajo
# ----------------------------------------------------------------------------
WORKDIR /var/www/example

# ----------------------------------------------------------------------------
# Preparar directorios de Laravel
# ----------------------------------------------------------------------------
# Crear directorios necesarios antes de copiar archivos
# Dar permisos adecuados al usuario 'unit'
# ----------------------------------------------------------------------------
RUN mkdir -p /var/www/example/storage \
    /var/www/example/storage/app \
    /var/www/example/storage/framework \
    /var/www/example/storage/logs \
    /var/www/example/bootstrap/cache \
    && chown -R unit:unit /var/www/example \
    && chmod -R 775 /var/www/example/storage \
    && chmod -R 775 /var/www/example/bootstrap/cache

# ----------------------------------------------------------------------------
# Copiar archivos de la aplicación
# ----------------------------------------------------------------------------
# Orden importante para aprovechar caché de capas:
# 1. composer.json y composer.lock primero
# 2. composer install
# 3. Resto de archivos
# ----------------------------------------------------------------------------
COPY composer.json composer.lock ./

# ----------------------------------------------------------------------------
# Instalar dependencias de Composer
# ----------------------------------------------------------------------------
# --prefer-dist: Usa versiones descargadas (más rápido)
# --optimize-autoloader: Optimiza el autoloader
# --no-interaction: No pregunta nada
# --no-dev: No instala dependencias de desarrollo
# ----------------------------------------------------------------------------
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-dev

# ----------------------------------------------------------------------------
# Copiar el resto de archivos de la aplicación
# ----------------------------------------------------------------------------
COPY --chown=unit:unit . .

# ----------------------------------------------------------------------------
# Configuración de NGINX Unit
# ----------------------------------------------------------------------------
# Copiar archivo de configuración
# ----------------------------------------------------------------------------
COPY --chown=unit:unit unit.json /docker-entrypoint.d/unit.json

# ----------------------------------------------------------------------------
# Permisos finales
# ----------------------------------------------------------------------------
# Asegurar permisos correctos en todos los directorios
# ----------------------------------------------------------------------------
RUN chown -R unit:unit /var/www/example/storage \
    /var/www/example/bootstrap/cache \
    && chmod -R 775 /var/www/example/storage \
    && chmod -R 775 /var/www/example/bootstrap/cache

# ----------------------------------------------------------------------------
# Puerto expuesto
# ----------------------------------------------------------------------------
# NGINX Unit escuchará en el puerto 8000
# ----------------------------------------------------------------------------
EXPOSE 8000

# ----------------------------------------------------------------------------
# Usuario de ejecución
# ----------------------------------------------------------------------------
# Ejecutar como usuario no-root por seguridad
# ----------------------------------------------------------------------------
USER unit

# ----------------------------------------------------------------------------
# Comando de inicio
# ----------------------------------------------------------------------------
# Iniciar NGINX Unit en foreground (necesario para Docker)
# ----------------------------------------------------------------------------
CMD ["unitd", "--no-daemon"]
```

---

### Verificación de FASE 4

**Paso 1:** Construir imagen
```bash
docker build -t electoral:documented .
```

**Paso 2:** Verificar que se construya correctamente

**Paso 3:** Probar ejecución
```bash
docker run -p 8000:8000 electoral:documented
```

---

## 📅 FASE 5: Optimizaciones Avanzadas (Semana 2)

**Tiempo estimado:** 2-3 horas
**Prioridad:** 🟢 BAJA - Optimizaciones avanzadas pero no urgentes

### Tarea 5.1: Implementar Multi-Stage Build

**Archivo:** `Dockerfile`

**Beneficio:** Reducción de tamaño ~50%

**Código de ejemplo (ver documento 16 para código completo):**
```dockerfile
# STAGE 1: Builder con herramientas de compilación
FROM unit:1.33.0-php8.2 AS builder
# ... instalar dependencias y composer ...

# STAGE 2: Imagen final optimizada
FROM unit:1.33.0-php8.2
# ... copiar solo lo necesario del builder ...
```

---

### Tarea 5.2: Implementar Docker BuildKit

**Paso 1:** Habilitar BuildKit
```bash
export DOCKER_BUILDKIT=1
```

**Paso 2:** Usar caché de montajes
```dockerfile
# syntax=docker/dockerfile:1.4

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --prefer-dist --optimize-autoloader --no-dev
```

**Beneficios:**
- Construcción más rápida
- Mejor caché de capas

---

### Tarea 5.3: Implementar Escaneo de Vulnerabilidades

**Paso 1:** Instalar security-checker
```dockerfile
# En stage de builder
RUN composer require enlightn/security-checker --dev
RUN composer security-checker security:check
```

**Paso 2:** Crear GitHub Actions para escaneo (ver documento 16)

**Beneficios:**
- Detección automática de vulnerabilidades
- CI/CD integrado

---

## 📊 Resumen del Plan

| Fase | Tareas | Tiempo Estimado | Prioridad | Status |
|------|--------|-----------------|-----------|--------|
| FASE 1 | Bugs Críticos | 30-45 min | 🔴 CRÍTICA | ⬜ Pendiente |
| FASE 2 | Optimizaciones Rendimiento | 1-2 horas | 🟡 ALTA | ⬜ Pendiente |
| FASE 3 | Optimizaciones Seguridad | 1-2 horas | 🟢 MEDIA | ⬜ Pendiente |
| FASE 4 | Optimizaciones Mantenibilidad | 1 hora | 🟢 BAJA | ⬜ Pendiente |
| FASE 5 | Optimizaciones Avanzadas | 2-3 horas | 🟢 BAJA | ⬜ Pendiente |

---

## 🎯 Checklist de Ejecución

### FASE 1 - Bugs Críticos
- [ ] Tarea 1.1: Corregir configuración de PHP (línea 13)
- [ ] Tarea 1.2: Mejorar permisos de directorios
- [ ] Tarea 1.3: Mejorar manejo de archivo .env
- [ ] Verificación de FASE 1

### FASE 2 - Optimizaciones de Rendimiento
- [ ] Tarea 2.1: Crear archivo .dockerignore
- [ ] Tarea 2.2: Crear archivo docker-compose.yml
- [ ] Tarea 2.3: Optimizar copia de archivos (caché de layers)
- [ ] Verificación de FASE 2

### FASE 3 - Optimizaciones de Seguridad
- [ ] Tarea 3.1: Ejecutar como usuario no-root
- [ ] Tarea 3.2: Agregar etiquetas de versión
- [ ] Tarea 3.3: Mejorar seguridad de contraseñas
- [ ] Verificación de FASE 3

### FASE 4 - Optimizaciones de Mantenibilidad
- [ ] Tarea 4.1: Agregar documentación al Dockerfile
- [ ] Verificación de FASE 4

### FASE 5 - Optimizaciones Avanzadas
- [ ] Tarea 5.1: Implementar multi-stage build
- [ ] Tarea 5.2: Implementar Docker BuildKit
- [ ] Tarea 5.3: Implementar escaneo de vulnerabilidades
- [ ] Verificación de FASE 5

---

## 🔄 Comandos de Rollback

Si algo sale mal, puedes revertir cambios usando Git:

```bash
# Ver cambios
git status
git diff Dockerfile

# Revertir Dockerfile
git checkout -- Dockerfile

# Revertir archivos nuevos
rm .dockerignore docker-compose.yml

# Crear nuevo commit con cambios
git add .
git commit -m "FASE 1 completada: Bugs críticos de Dockerfile solucionados"
```

---

## 📝 Notas Importantes

1. **Siempre probar cambios** en un entorno de desarrollo
2. **Usar etiquetas de versión** para controlar cambios
3. **Documentar cada cambio** en el Dockerfile
4. **Usar .dockerignore** para reducir tamaño
5. **No incluir secrets** en la imagen Docker
6. **Usar volúmenes** para datos persistentes
7. **Ejecutar como usuario no-root** para seguridad
8. **Limpiar caché regularmente**: `docker system prune -a`

---

## 🚀 Comandos Rápidos de Verificación

```bash
# Construir imagen
docker build -t electoral-app .

# Construir con versión
docker build --build-arg VERSION=1.0.0 -t electoral:1.0.0 .

# Ver tamaño de imagen
docker images electoral-app

# Ejecutar con docker-compose
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down

# Limpiar todo
docker system prune -a --volumes

# Verificar configuraciones PHP en contenedor
docker exec -it <container-id> php -i | grep -E "opcache|memory_limit|upload_max_filesize"

# Verificar usuario
docker run electoral-app whoami
```

---

## 📁 Archivos Nuevos a Crear

### 1. .dockerignore
```dockerignore
.git
.gitignore
.gitattributes
Dockerfile
docker-compose*.yml
.dockerignore
.env
.env.*
node_modules
npm-debug.log
yarn-error.log
tests
storage/*.key
storage/*.log
storage/framework/cache/*
storage/framework/sessions/*
storage/framework/views/*
vendor
docs
README.md
*.md
.editorconfig
.phpunit.xml
```

### 2. docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: electoral-app
    restart: unless-stopped
    ports:
      - "8000:8000"
    volumes:
      - ./storage:/var/www/example/storage
      - ./bootstrap/cache:/var/www/example/bootstrap/cache
      - ./.env:/var/www/example/.env
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - DB_HOST=mysql
      - DB_DATABASE=electoral
      - DB_USERNAME=electoral
      - DB_PASSWORD=secret
      - CACHE_DRIVER=redis
      - QUEUE_CONNECTION=redis
    depends_on:
      - mysql
      - redis
    networks:
      - electoral-net

  mysql:
    image: mysql:8.0
    container_name: electoral-mysql
    restart: unless-stopped
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=electoral
      - MYSQL_USER=electoral
      - MYSQL_PASSWORD=secret
    volumes:
      - mysql-data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - electoral-net

  redis:
    image: redis:alpine
    container_name: electoral-redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis-data:/data
    networks:
      - electoral-net

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: electoral-phpmyadmin
    restart: unless-stopped
    ports:
      - "8080:80"
    environment:
      - PMA_HOST=mysql
      - PMA_USER=root
      - PMA_PASSWORD=root
    depends_on:
      - mysql
    networks:
      - electoral-net

volumes:
  mysql-data:
  redis-data:

networks:
  electoral-net:
    driver: bridge
```

---

**Última actualización:** 2026-01-18
**Versión del plan:** 1.0.0
**Autor:** AI Assistant
**Estado:** Listo para ejecución
