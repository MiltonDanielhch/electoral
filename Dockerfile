# ============================================================================
# PHP 8.2 + NGINX Unit 1.33.0 -
# ============================================================================
FROM nginx/unit:1.33.0-php8.2

# Instalar dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# Configuración de PHP optimizada
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.jit=tracing'; \
    echo 'opcache.jit_buffer_size=256M'; \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=64M'; \
    echo 'post_max_size=64M'; \
    echo 'max_execution_time=300'; \
} > /usr/local/etc/php/conf.d/custom.ini

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/example

# Copiar archivos de dependencias primero para cachear capas
COPY composer.json composer.lock ./

# Instalar dependencias (Sin scripts para evitar errores de clases no encontradas aún)
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-dev --no-scripts

# Copiar el resto de la aplicación con el usuario unit
COPY --chown=unit:unit . .

# Generar autoloader final
RUN composer dump-autoload --optimize

# Configuración de NGINX Unit
COPY --chown=unit:unit unit.json /docker-entrypoint.d/unit.json

# Permisos críticos para Laravel
RUN mkdir -p storage bootstrap/cache \
    && chown -R unit:unit /var/www/example \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

# El comando se define en el Docker Compose para mayor flexibilidad
CMD ["unitd", "--no-daemon", "--control", "unix:/var/run/control.unit.sock"]
