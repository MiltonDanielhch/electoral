FROM unit:1.33.0-php8.2

# Instalación de dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libssl-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath mbstring \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configuración optimizada de PHP para Producción
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.jit=tracing"; \
    echo "opcache.jit_buffer_size=128M"; \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
    } > /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/electoral

# Directorios necesarios con permisos correctos ANTES de copiar
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

# Copiar el código del proyecto
COPY . .

# Instalación de dependencias de Composer
RUN composer install --prefer-dist --optimize-autoloader --no-interaction --no-dev

# Asegurar permisos para el usuario 'unit'
RUN chown -R unit:unit /var/www/electoral \
    && chmod -R 775 /var/www/electoral/storage /var/www/electoral/bootstrap/cache

# Configuración de NGINX Unit (Copiamos directamente a la carpeta de auto-config)
COPY unit.json /docker-entrypoint.d/unit.json

# Preparación final
RUN php artisan storage:link || true

EXPOSE 8000

CMD ["unitd", "--no-daemon"]
