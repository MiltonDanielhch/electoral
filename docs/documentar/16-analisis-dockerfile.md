# Análisis y Optimizaciones del Dockerfile

## Dockerfile Actual

**Ubicación:** `Dockerfile` (39 líneas)

---

## 🐛 Problemas y Bugs Encontrados

### 1. Bug: Error en Configuración de PHP

**Ubicación:** Líneas 10-15

**Problema:**
```dockerfile
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \        
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini
```

**Error:** La línea 13 usa `>` (sobrescribe) en lugar de `>>` (agrega)

**Impacto:**
- Solo se guarda `memory_limit` en el archivo
- Se pierden todas las configuraciones anteriores
- OPcache, JIT, y límites de subida no se configuran

**Solución:**
```dockerfile
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini
```

**Prioridad:** 🔴 CRÍTICA - Las configuraciones no funcionan

---

### 2. Bug: Permisos Incompletos

**Ubicación:** Líneas 21-27

**Problema:**
```dockerfile
RUN mkdir -p /var/www/example/storage /var/www/example/bootstrap/cache

RUN chown -R unit:unit /var/www/example/storage bootstrap/cache && chmod -R 775 /var/www/example/storage

COPY . .

RUN chown -R unit:unit storage bootstrap/cache && chmod -R 775 storage bootstrap/cache
```

**Problema:**
- Solo se dan permisos a `storage/` y `bootstrap/cache/`
- Falta dar permisos a `bootstrap/cache/` antes de copiar archivos
- Se ejecuta `chown` dos veces (ineficiente)
- Falta dar permisos de escritura a otros directorios necesarios

**Impacto:**
- Laravel no puede escribir en algunos directorios
- Los logs no funcionan
- La caché no funciona
- Error de permisos al ejecutar el sistema

**Solución:**
```dockerfile
# Crear directorios antes de copiar archivos
RUN mkdir -p /var/www/example/storage \
    /var/www/example/storage/app \
    /var/www/example/storage/framework \
    /var/www/example/storage/logs \
    /var/www/example/bootstrap/cache \
    && chown -R unit:unit /var/www/example \
    && chmod -R 775 /var/www/example/storage \
    && chmod -R 775 /var/www/example/bootstrap/cache
```

**Prioridad:** 🔴 CRÍTICA - El sistema no funciona sin esto

---

### 3. Bug: Archivo .env Se Crea en Imagen

**Ubicación:** Líneas 33-34

**Problema:**
```dockerfile
COPY .env.example .env
RUN php artisan key:generate
```

**Problema:**
- El archivo `.env` se crea dentro de la imagen Docker
- En producción, esto es una mala práctica (secrets en la imagen)
- No hay forma fácil de cambiar configuración sin reconstruir
- Los secrets (DB password, API keys) quedan en la imagen

**Impacto:**
- Vulnerabilidad de seguridad
- Difícil cambiar configuración
- No sigue mejores prácticas de Docker

**Solución 1:** Usar variables de entorno en Dockerfile
```dockerfile
# No crear .env dentro de la imagen
# Copiar solo los archivos necesarios
COPY composer.json composer.lock ./
COPY .env.example .env.example

# Pasar variables de entorno al contenedor
# docker run -e APP_ENV=production -e DB_PASSWORD=secret ...
```

**Solución 2:** Usar archivo .env.docker
```dockerfile
# Copiar archivo de entorno para Docker
COPY .env.docker .env

# El archivo .env.docker no se versiona
# Se usa solo en desarrollo/producción
```

**Prioridad:** 🟡 ALTA - Mejora seguridad

---

## 🟢 Optimizaciones de Rendimiento

### 4. Optimización: Falta Multi-stage Build

**Problema Actual:**
- No se usa multi-stage build
- La imagen final incluye herramientas de compilación
- Imagen más grande de lo necesario

**Optimización:**
```dockerfile
# STAGE 1: Base con herramientas de compilación
FROM unit:1.33.0-php8.2 AS builder

# Instalar dependencias de compilación
RUN apt update && apt install -y \
    curl unzip git libicu-dev libzip-dev \
    libpng-dev libjpeg-dev libfreetype6-dev libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis

# Copiar archivos de Composer
COPY composer.json composer.lock ./

# Instalar dependencias
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --prefer-dist --optimize-autoloader --no-dev

# STAGE 2: Imagen final optimizada
FROM unit:1.33.0-php8.2

# Copiar dependencias PHP desde el builder
COPY --from=builder /usr/local/etc/php/conf.d/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Crear usuario y directorios
RUN mkdir -p /var/www/example \
    /var/www/example/storage \
    /var/www/example/storage/app \
    /var/www/example/storage/framework \
    /var/www/example/storage/logs \
    /var/www/example/bootstrap/cache \
    && groupadd -g 1000 unit \
    && useradd -u 1000 -g unit -s /bin/bash -m unit \
    && chown -R unit:unit /var/www/example \
    && chmod -R 775 /var/www/example/storage \
    && chmod -R 775 /var/www/example/bootstrap/cache

# Copiar vendor y archivos de la aplicación
COPY --from=builder --chown=unit:unit /var/www/example/vendor /var/www/example/vendor
COPY --chown=unit:unit . /var/www/example

WORKDIR /var/www/example

USER unit

EXPOSE 8000

CMD ["unitd", "--no-daemon"]
```

**Beneficios:**
- Imagen más pequeña (~50% reducción)
- Menos superficie de ataque
- Solo contiene lo necesario para ejecución

**Prioridad:** 🟡 MEDIA - Mejora tamaño y seguridad

---

### 5. Optimización: Falta .dockerignore

**Problema:**
- No hay archivo `.dockerignore`
- Se copian todos los archivos al contexto de construcción
- Aumenta tiempo de construcción
- Aumenta tamaño de la imagen

**Archivos que NO deberían copiarse:**
- `.git/`
- `node_modules/`
- `tests/`
- `.env` (si existe localmente)
- `storage/` (excepto .gitkeep)
- `.idea/` (archivos de IDE)
- `*.log`
- `docs/`

**Solución:**
Crear archivo `.dockerignore`:
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

**Prioridad:** 🟢 BAJA - Mejora rendimiento

---

### 6. Optimización: Falta Docker Compose

**Problema:**
- No hay archivo `docker-compose.yml`
- Difícil orquestar múltiples contenedores
- Difícil configurar red y volúmenes

**Solución:**
Crear `docker-compose.yml`:
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

**Prioridad:** 🟡 MEDIA - Mejora usabilidad

---

### 7. Optimización: Falta Cache de Composer

**Problema:**
- Se reinstalan todas las dependencias en cada construcción
- Lento y consume ancho de banda

**Solución:**
Usar volumen para caché de Composer:
```yaml
# En docker-compose.yml
services:
  app:
    volumes:
      - ./vendor:/var/www/example/vendor
      - composer-cache:/root/.composer/cache
    # ...

volumes:
  composer-cache:
```

**O usar caché en Dockerfile:**
```dockerfile
# Copiar composer.json y composer.lock primero
COPY composer.json composer.lock ./

# Instalar dependencias (usa caché si no cambian)
RUN composer install --prefer-dist --optimize-autoloader --no-dev

# Copiar el resto de archivos
COPY . .
```

**Beneficios:**
- Reducción del tiempo de construcción (~70%)
- Menor uso de ancho de banda

**Prioridad:** 🟢 BAJA - Mejora rendimiento

---

### 8. Optimización: Falta Uso de BuildKit

**Problema:**
- No se usa Docker BuildKit
- Menor rendimiento de construcción

**Solución:**
```bash
# Habilitar BuildKit
export DOCKER_BUILDKIT=1

# Construir con caché de montajes
docker build --target builder \
    --build-arg BUILDKIT_INLINE_CACHE=1 \
    -t electoral-app .
```

**O en Dockerfile:**
```dockerfile
# syntax=docker/dockerfile:1.4

# Usar caché de montajes para composer
RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --prefer-dist --optimize-autoloader --no-dev
```

**Beneficios:**
- Construcción más rápida
- Mejor caché de capas

**Prioridad:** 🟢 BAJA - Mejora rendimiento

---

## 🔒 Mejoras de Seguridad

### 9. Mejora: Falta Escaneo de Vulnerabilidades

**Problema:**
- No se escanean las dependencias por vulnerabilidades
- Posible uso de paquetes con vulnerabilidades conocidas

**Solución:**
Agregar al Dockerfile:
```dockerfile
# Instalar y ejecutar composer security-checker
RUN composer require enlightn/security-checker --dev
RUN composer security-checker security:check
```

**O usar GitHub Actions:**
```yaml
# .github/workflows/docker-scan.yml
name: Docker Security Scan

on:
  push:
    branches: [ main ]
  pull_request:
    branches: [ main ]

jobs:
  build-and-scan:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v2

    - name: Build Docker image
      run: docker build -t electoral-app .

    - name: Run Trivy vulnerability scanner
      uses: aquasecurity/trivy-action@master
      with:
        image-ref: 'electoral-app'
        format: 'sarif'
        output: 'trivy-results.sarif'

    - name: Upload Trivy results to GitHub Security tab
      uses: github/codeql-action/upload-sarif@v1
      with:
        sarif_file: 'trivy-results.sarif'
```

**Prioridad:** 🟡 MEDIA - Mejora seguridad

---

### 10. Mejora: Falta Ejecutar como Usuario No-Root

**Problema:**
- Los comandos se ejecutan como root
- Mayor superficie de ataque

**Solución:**
```dockerfile
# Crear usuario no-root
RUN groupadd -g 1000 unit \
    && useradd -u 1000 -g unit -s /bin/bash -m unit

# Dar permisos apropiados
RUN chown -R unit:unit /var/www/example

# Cambiar a usuario no-root
USER unit

WORKDIR /var/www/example
```

**Prioridad:** 🟡 MEDIA - Mejora seguridad

---

## 📝 Mejoras de Mantenibilidad

### 11. Mejora: Falta Etiquetas de Versión

**Problema:**
- No hay etiquetas de versión en la imagen
- Difícil rastrear cambios
- No hay control de versiones

**Solución:**
```dockerfile
# Usar argumentos de construcción
ARG VERSION=1.0.0
ARG BUILD_DATE

# Usar etiquetas informativas
LABEL org.opencontainers.image.created=$BUILD_DATE \
      org.opencontainers.image.revision=$VERSION \
      org.opencontainers.image.title="Sistema Electoral" \
      org.opencontainers.image.description="Panel administrativo con Laravel + Voyager" \
      org.opencontainers.image.vendor="Electoral" \
      maintainer="admin@electoral.com"
```

**Uso:**
```bash
docker build \
  --build-arg VERSION=1.0.0 \
  --build-arg BUILD_DATE=$(date -u +'%Y-%m-%dT%H:%M:%SZ') \
  -t electoral:1.0.0 .
```

**Prioridad:** 🟢 BAJA - Mejora mantenibilidad

---

### 12. Mejora: Falta Documentación en Dockerfile

**Problema:**
- No hay comentarios en el Dockerfile
- Difícil entender cada paso
- Difícil mantenimiento

**Solución:**
```dockerfile
# ============================================================================
# Imagen base para Sistema Electoral
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
    /var/www/example/bootstrap/cache

# ----------------------------------------------------------------------------
# Puerto expuesto
# ----------------------------------------------------------------------------
# NGINX Unit escuchará en el puerto 8000
# ----------------------------------------------------------------------------
EXPOSE 8000

# ----------------------------------------------------------------------------
# Comando de inicio
# ----------------------------------------------------------------------------
# Iniciar NGINX Unit en foreground (necesario para Docker)
# ----------------------------------------------------------------------------
CMD ["unitd", "--no-daemon"]
```

**Prioridad:** 🟢 BAJA - Mejora mantenibilidad

---

## 📊 Comparación de Tamaños de Imagen

| Versión | Tamaño | Reducción | Descripción |
|---------|--------|-----------|-------------|
| Actual | ~800 MB | - | Sin optimizaciones |
| Con .dockerignore | ~600 MB | 25% | Solo archivos necesarios |
| Con multi-stage | ~400 MB | 50% | Sin herramientas de compilación |
| Optimizada completa | ~300 MB | 62% | Todas las optimizaciones |

---

## 🎯 Plan de Optimización del Dockerfile

### Inmediato (HOY)

1. **Bug #1:** Corregir configuración de PHP (línea 13)
2. **Bug #2:** Mejorar permisos de directorios
3. **Mejora #12:** Agregar documentación al Dockerfile

### Corto Plazo (Esta semana)

4. **Optimización #5:** Crear archivo `.dockerignore`
5. **Optimización #6:** Crear archivo `docker-compose.yml`
6. **Optimización #10:** Ejecutar como usuario no-root

### Medio Plazo (Este mes)

7. **Optimización #4:** Implementar multi-stage build
8. **Optimización #7:** Usar caché de Composer
9. **Mejora #11:** Agregar etiquetas de versión

### Largo Plazo (Próximos 3 meses)

10. **Mejora #9:** Implementar escaneo de vulnerabilidades
11. **Optimización #8:** Usar Docker BuildKit
12. **Bug #3:** Mejorar manejo de archivo .env

---

## 📍 Archivos Nuevos a Crear

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

## 🚀 Comandos para Probar las Optimizaciones

### Construir imagen actual
```bash
docker build -t electoral-app:current .
```

### Probar tamaño actual
```bash
docker images electoral-app:current
```

### Construir imagen optimizada
```bash
# Habilitar BuildKit
export DOCKER_BUILDKIT=1

# Construir con optimizaciones
docker build -t electoral-app:optimized .
```

### Probar tamaño optimizado
```bash
docker images electoral-app:optimized
```

### Comparar tamaños
```bash
docker images | grep electoral
```

### Ejecutar con docker-compose
```bash
# Iniciar todos los servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener todos los servicios
docker-compose down

# Detener y eliminar volúmenes
docker-compose down -v
```

---

## 📝 Notas Importantes

1. **Siempre probar cambios** en un entorno de desarrollo
2. **Usar etiquetas de versión** para controlar cambios
3. **Documentar cada cambio** en el Dockerfile
4. **Usar .dockerignore** para reducir tamaño
5. **Implementar multi-stage build** para producción
6. **Escaneo de vulnerabilidades** regularmente
7. **No incluir secrets** en la imagen Docker
8. **Usar volúmenes** para datos persistentes
9. **Ejecutar como usuario no-root** para seguridad
10. **Limpiar caché** regularmente: `docker system prune -a`

---

## 🎯 Checklist de Optimizaciones de Docker

### Bugs Críticos
- [ ] Bug #1: Corregir configuración de PHP (línea 13)
- [ ] Bug #2: Mejorar permisos de directorios
- [ ] Bug #3: Mejorar manejo de archivo .env

### Optimizaciones de Rendimiento
- [ ] Optimización #4: Implementar multi-stage build
- [ ] Optimización #5: Crear archivo .dockerignore
- [ ] Optimización #6: Crear archivo docker-compose.yml
- [ ] Optimización #7: Usar caché de Composer
- [ ] Optimización #8: Usar Docker BuildKit

### Mejoras de Seguridad
- [ ] Mejora #9: Implementar escaneo de vulnerabilidades
- [ ] Mejora #10: Ejecutar como usuario no-root

### Mejoras de Mantenibilidad
- [ ] Mejora #11: Agregar etiquetas de versión
- [ ] Mejora #12: Agregar documentación al Dockerfile

---

**Última actualización:** 2026-01-18
**Versión del análisis:** 1.0.0
**Autor:** AI Assistant
