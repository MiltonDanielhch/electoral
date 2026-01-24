FROM unit:1.33.0-php8.2

# Instalación de dependencias
RUN apt-get update && apt-get install -y --no-install-recommends \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libssl-dev libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath mbstring \
        tokenizer xml ctype fileinfo json openssl pcre session \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Configuración PHP para Producción
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.enable_cli=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.jit=tracing"; \
    echo "opcache.jit_buffer_size=128M"; \
    echo "opcache.save_comments=1"; \
    echo "memory_limit=512M"; \
    echo "upload_max_filesize=64M"; \
    echo "post_max_size=64M"; \
    echo "max_execution_time=30"; \
    echo "max_input_vars=3000"; \
    } > /usr/local/etc/php/conf.d/custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/electoral

# Copiar dependencias primero (cacheo de capas)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copiar código fuente
COPY . .

# Generar autoloader y optimizar
RUN composer dump-autoload --optimize \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

# Permisos y storage link
RUN mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/testing storage/framework/views storage/logs \
    && chown -R unit:unit /var/www/electoral \
    && chmod -R 775 storage bootstrap/cache \
    && php artisan storage:link

# Configuración de Unit
COPY unit.json /docker-entrypoint.d/config.json

EXPOSE 8000

CMD ["unitd", "--no-daemon", "--control", "unix:/var/run/control.unit.sock"]