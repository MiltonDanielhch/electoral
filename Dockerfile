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
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath \
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
# Permisos finales
# ----------------------------------------------------------------------------
# Asegurar permisos correctos en todos los directorios
# ----------------------------------------------------------------------------
RUN chown -R unit:unit /var/www/example/storage \
    /var/www/example/bootstrap/cache \
    && chmod -R 775 /var/www/example/storage \
    && chmod -R 775 /var/www/example/bootstrap/cache

# ----------------------------------------------------------------------------
# Configuración de NGINX Unit
# ----------------------------------------------------------------------------
# Copiar archivo de configuración
# ----------------------------------------------------------------------------
COPY --chown=unit:unit unit.json /docker-entrypoint.d/unit.json

# ----------------------------------------------------------------------------
# No crear .env en la imagen (seguridad)
# ----------------------------------------------------------------------------
# El archivo .env debe proporcionarse como volumen o variables de entorno
# Ejemplo: docker run -v $(pwd)/.env:/var/www/example/.env ...
# ----------------------------------------------------------------------------

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