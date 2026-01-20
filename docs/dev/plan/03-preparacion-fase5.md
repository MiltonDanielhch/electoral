# Preparación para Fase 5 - Despliegue y DevOps

## Fecha: 2026-01-20

## Resumen

Este documento detalla la preparación e implementación de la Fase 5, enfocándose en automatización de despliegue, configuración de servidor de producción y establecimiento de monitoreo y alertas.

---

## Objetivos de la Fase 5

1. **Automatización:** Pipeline CI/CD para despliegues automáticos
2. **Infraestructura:** Servidor de producción configurado y seguro
3. **Monitoreo:** Sistema de alertas y logging centralizado
4. **Resiliencia:** Backups automatizados y plan de recuperación

---

## Parte 1: Pipeline CI/CD con GitHub Actions

### Configuración Base

**Archivo:** `.github/workflows/deploy.yml`

```yaml
name: Deploy to Production

on:
  push:
    branches:
      - main
  workflow_dispatch:  # Permitir ejecución manual

env:
  PHP_VERSION: '8.1'
  NODE_VERSION: '18'
  
jobs:
  test:
    name: Run Tests
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: electoral_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ env.PHP_VERSION }}
          extensions: mbstring, pdo, pdo_mysql, bcmath
          coverage: xdebug
      
      - name: Setup Node.js
        uses: actions/setup-node@v4
        with:
          node-version: ${{ env.NODE_VERSION }}
      
      - name: Install Composer dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader
      
      - name: Install NPM dependencies
        run: npm ci
      
      - name: Build assets
        run: npm run build
      
      - name: Copy environment
        run: cp .env.example .env
      
      - name: Generate application key
        run: php artisan key:generate
      
      - name: Run migrations
        run: php artisan migrate --force --seed
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: electoral_test
          DB_USERNAME: root
          DB_PASSWORD: root
      
      - name: Execute tests
        run: php artisan test --coverage
      
      - name: Upload coverage reports
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
          fail_ci_if_error: false
  
  deploy:
    name: Deploy to Production
    needs: test
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/main'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Deploy to Server
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.PROD_HOST }}
          username: ${{ secrets.PROD_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/electoral
            git pull origin main
            composer install --no-interaction --optimize-autoloader --no-dev
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
            php artisan queue:restart
            sudo systemctl reload php8.1-fpm
            sudo systemctl reload nginx
      
      - name: Notify deployment
        uses: 8398a7/action-slack@v3
        with:
          status: ${{ job.status }}
          text: 'Deployment to production completed'
          webhook_url: ${{ secrets.SLACK_WEBHOOK }}
        if: always()
```

---

### Configuración de Staging

**Archivo:** `.github/workflows/deploy-staging.yml`

```yaml
name: Deploy to Staging

on:
  pull_request:
    types: [closed, opened, synchronize]
  workflow_dispatch:

jobs:
  test:
    name: Test and Deploy to Staging
    runs-on: ubuntu-latest
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
      
      - name: Install dependencies
        run: composer install --no-interaction --prefer-dist
      
      - name: Run tests
        run: php artisan test
      
      - name: Deploy to Staging
        if: github.event.pull_request.merged == true || github.event_name == 'workflow_dispatch'
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.STAGING_HOST }}
          username: ${{ secrets.STAGING_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/electoral-staging
            git pull origin main
            composer install --no-interaction --optimize-autoloader
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
```

---

## Parte 2: Infraestructura como Código (Docker)

### Dockerfile Optimizado

**Archivo:** `Dockerfile`

```dockerfile
# Build stage
FROM node:18-alpine AS build

WORKDIR /app

# Copiar package files
COPY package*.json ./
RUN npm ci --only=production

# Copiar fuente y compilar
COPY . .
RUN npm run build

# Application stage
FROM php:8.1-fpm-alpine

# Instalar extensiones PHP
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev

# Instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación
COPY . .

# Copiar assets compilados
COPY --from=build /app/public/build ./public/build

# Copiar composer files y optimizar
COPY composer.json composer.lock ./
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copiar configuración de PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Copiar configuración de PHP-FPM
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000

CMD ["php-fpm"]
```

---

### Docker Compose

**Archivo:** `docker-compose.yml`

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: electoral_app
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./storage:/var/www/html/storage
    depends_on:
      - db
      - redis
    networks:
      - electoral_network
    environment:
      - DB_HOST=db
      - DB_PORT=3306
      - DB_DATABASE=electoral
      - DB_USERNAME=electoral
      - DB_PASSWORD=secret
      - CACHE_DRIVER=redis
      - REDIS_HOST=redis
      - REDIS_PORT=6379

  nginx:
    image: nginx:alpine
    container_name: electoral_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./:/var/www/html
      - ./docker/nginx/conf.d:/etc/nginx/conf.d
      - ./docker/nginx/ssl:/etc/nginx/ssl
    depends_on:
      - app
    networks:
      - electoral_network

  db:
    image: mysql:8.0
    container_name: electoral_db
    restart: unless-stopped
    ports:
      - "3306:3306"
    environment:
      - MYSQL_ROOT_PASSWORD=root_secret
      - MYSQL_DATABASE=electoral
      - MYSQL_USER=electoral
      - MYSQL_PASSWORD=secret
    volumes:
      - dbdata:/var/lib/mysql
    networks:
      - electoral_network
    command: --default-authentication-plugin=mysql_native_password

  redis:
    image: redis:alpine
    container_name: electoral_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redisdata:/data
    networks:
      - electoral_network
    command: redis-server --appendonly yes

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: electoral_queue
    restart: unless-stopped
    working_dir: /var/www/html
    command: php artisan queue:work --sleep=3 --tries=3 --timeout=90
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
      - redis
    networks:
      - electoral_network

  scheduler:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: electoral_scheduler
    restart: unless-stopped
    working_dir: /var/www/html
    command: php artisan schedule:work
    volumes:
      - ./:/var/www/html
    depends_on:
      - db
      - redis
    networks:
      - electoral_network

networks:
  electoral_network:
    driver: bridge

volumes:
  dbdata:
  redisdata:
```

---

### Nginx Configuration

**Archivo:** `docker/nginx/conf.d/electoral.conf`

```nginx
server {
    listen 80;
    server_name electoral.local;
    root /var/www/html/public;
    
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    
    index index.php index.html;
    
    charset utf-8;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt { access_log off; log_not_found off; }
    
    error_page 404 /index.php;
    
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Security headers
    add_header Content-Security-Policy "default-src 'self' https:; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline';";
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml+rss application/json;
    
    # Cache static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

---

## Parte 3: Configuración de Servidor de Producción

### Requisitos del Servidor

**Sistema Operativo:** Ubuntu 22.04 LTS
**Hardware Recomendado:**
- CPU: 4+ cores
- RAM: 8GB+ (16GB para día de elección)
- Disco: 100GB SSD
- Ancho de banda: 100Mbps+

### Script de Instalación

**Archivo:** `scripts/provision-server.sh`

```bash
#!/bin/bash

# Provisionar servidor de producción para Laravel
# Ejecutar como root o con sudo

set -e

echo "=== Iniciando provisión de servidor ==="

# Actualizar sistema
apt update && apt upgrade -y

# Instalar dependencias básicas
apt install -y \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    curl \
    wget \
    gnupg \
    unzip \
    git \
    ufw \
    fail2ban \
    htop \
    vim

# Instalar PHP 8.1
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y \
    php8.1 \
    php8.1-fpm \
    php8.1-mysql \
    php8.1-mbstring \
    php8.1-xml \
    php8.1-bcmath \
    php8.1-curl \
    php8.1-zip \
    php8.1-gd \
    php8.1-intl \
    php8.1-opcache

# Configurar PHP
sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.1/fpm/php.ini
sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.1/fpm/php.ini
sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 20M/' /etc/php/8.1/fpm/php.ini
sed -i 's/post_max_size = 8M/post_max_size = 20M/' /etc/php/8.1/fpm/php.ini

# Instalar Nginx
apt install -y nginx

# Instalar MySQL
apt install -y mysql-server
mysql_secure_installation

# Instalar Redis
apt install -y redis-server
systemctl enable redis-server

# Instalar Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Configurar Firewall
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

# Configurar Fail2ban
systemctl enable fail2ban
systemctl start fail2ban

# Crear usuario para la aplicación
useradd -m -s /bin/bash deploy
usermod -aG sudo deploy

# Configurar directorio de la aplicación
mkdir -p /var/www/electoral
chown -R deploy:deploy /var/www/electoral

# Configurar permisos de storage
chmod -R 775 /var/www/electoral/storage
chown -R deploy:www-data /var/www/electoral/storage

# Configurar PHP-FPM
systemctl enable php8.1-fpm
systemctl start php8.1-fpm

# Configurar Nginx
systemctl enable nginx
systemctl start nginx

echo "=== Provisión completada ==="
echo "Usuario de la aplicación: deploy"
echo "Directorio: /var/www/electoral"
```

**Uso:**

```bash
# Copiar al servidor
scp scripts/provision-server.sh user@server:/tmp/

# Ejecutar en el servidor
ssh user@server
chmod +x /tmp/provision-server.sh
sudo /tmp/provision-server.sh
```

---

### Configuración de SSL con Let's Encrypt

```bash
# Instalar Certbot
apt install -y certbot python3-certbot-nginx

# Obtener certificado
certbot --nginx -d electoral.ejemplo.com -d www.electoral.ejemplo.com

# Renovación automática
certbot renew --dry-run
```

**Archivo Nginx con SSL:** `/etc/nginx/sites-available/electoral-ssl.conf`

```nginx
server {
    listen 443 ssl http2;
    server_name electoral.ejemplo.com www.electoral.ejemplo.com;
    
    root /var/www/electoral/public;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/electoral.ejemplo.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/electoral.ejemplo.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    # Security Headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    
    # Resto de la configuración...
}

server {
    listen 80;
    server_name electoral.ejemplo.com www.electoral.ejemplo.com;
    return 301 https://$host$request_uri;
}
```

---

## Parte 4: Monitoreo y Alertas

### Opción 1: Sentry (Recomendada ✅)

#### Instalación

```bash
composer require sentry/sentry-laravel
php artisan sentry:publish
php artisan sentry:install
```

#### Configuración `.env`

```env
SENTRY_LARAVEL_DSN=https://xxx@o0.ingest.sentry.io/xxx
SENTRY_TRACES_SAMPLE_RATE=0.1
SENTRY_PROFILES_SAMPLE_RATE=0.1
```

#### Integración

```php
// app/Exceptions/Handler.php
public function register(): void
{
    $this->reportable(function (Throwable $e) {
        \Sentry\Laravel\Integration::captureUnhandledException($e);
    });
}
```

---

### Opción 2: ELK Stack (Local)

**docker-compose.yml:**

```yaml
services:
  elasticsearch:
    image: elasticsearch:8.8.0
    environment:
      - discovery.type=single-node
      - "ES_JAVA_OPTS=-Xms512m -Xmx512m"
    ports:
      - "9200:9200"
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data

  logstash:
    image: logstash:8.8.0
    volumes:
      - ./logstash.conf:/usr/share/logstash/pipeline/logstash.conf
    depends_on:
      - elasticsearch
    ports:
      - "5000:5000"

  kibana:
    image: kibana:8.8.0
    environment:
      - ELASTICSEARCH_HOSTS=http://elasticsearch:9200
    ports:
      - "5601:5601"
    depends_on:
      - elasticsearch

volumes:
  elasticsearch_data:
```

---

### Configuración de Alertas

#### Slack Webhook

**Archivo:** `config/logging.php`

```php
return [
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily', 'slack'],
            'ignore_exceptions' => false,
        ],
        
        'slack' => [
            'driver' => 'monolog',
            'level' => 'critical',
            'handler' => Monolog\Handler\SlackWebhookHandler::class,
            'with' => [
                'url' => env('SLACK_WEBHOOK_URL'),
                'channel' => '#alerts',
                'username' => 'Laravel Bot',
            ],
        ],
        
        // ...
    ],
];
```

---

## Parte 5: Estrategia de Backups

### Script de Backup Automatizado

**Archivo:** `scripts/backup.sh`

```bash
#!/bin/bash

# Script de backup automático
# Agregar a crontab: 0 2 * * * /path/to/backup.sh

set -e

BACKUP_DIR="/var/backups/electoral"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Crear directorio de backup
mkdir -p $BACKUP_DIR

# Backup de base de datos
mysqldump -u root -p'root_password' electoral \
    --single-transaction \
    --routines \
    --triggers \
    > $BACKUP_DIR/db_backup_$DATE.sql

# Comprimir backup
gzip $BACKUP_DIR/db_backup_$DATE.sql

# Backup de archivos (storage)
tar -czf $BACKUP_DIR/storage_backup_$DATE.tar.gz \
    /var/www/electoral/storage/app \
    /var/www/electoral/storage/uploads

# Backup de código (opcional)
tar -czf $BACKUP_DIR/code_backup_$DATE.tar.gz \
    /var/www/electoral/app \
    /var/www/electoral/config \
    /var/www/electoral/database \
    /var/www/electoral/resources \
    /var/www/electoral/routes

# Enviar a S3 (opcional)
# aws s3 cp $BACKUP_DIR/ s3://backup-bucket/electoral/ --recursive

# Limpiar backups antiguos
find $BACKUP_DIR -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completado: $DATE"
```

**Configurar crontab:**

```bash
# Editar crontab
crontab -e

# Agregar línea (ejecutar a las 2 AM diariamente)
0 2 * * * /var/www/scripts/backup.sh >> /var/log/electoral-backup.log 2>&1
```

---

### Política de Retención

| Tipo | Frecuencia | Retención |
|------|------------|-----------|
| DB completo | Diario | 30 días |
| DB incremental | Cada 6 horas | 7 días |
| Storage | Diario | 30 días |
| Código | Semanal | 12 semanas |
| Logs | Diario | 7 días |

---

## Cronograma de Implementación

### Semana 1: CI/CD
- [ ] Configurar GitHub Actions
- [ ] Crear workflow de tests
- [ ] Crear workflow de staging
- [ ] Crear workflow de producción
- [ ] Configurar secretos en GitHub

### Semana 2: Docker y Staging
- [ ] Crear Dockerfile optimizado
- [ ] Configurar docker-compose
- [ ] Configurar Nginx
- [ ] Desplegar en staging
- [ ] Validar despliegues automáticos

### Semana 3: Servidor de Producción
- [ ] Provisionar servidor Ubuntu
- [ ] Configurar SSL con Let's Encrypt
- [ ] Configurar firewall y seguridad
- [ ] Desplegar en producción
- [ ] Validar rendimiento

### Semana 4: Monitoreo y Backups
- [ ] Configurar Sentry
- [ ] Configurar alertas Slack
- [ ] Implementar script de backups
- [ ] Configurar retención y limpieza
- [ ] Validar procedimiento de restauración

---

## Checklist Pre-Producción

### Seguridad
- [ ] SSL/TLS configurado y validado
- [ ] Firewall activo (solo puertos necesarios)
- [ ] Fail2ban configurado
- [ ] Contraseñas fuertes en producción
- [ ] Variables de entorno seguras
- [ ] CORS configurado correctamente

### Rendimiento
- [ ] OPcache habilitado y configurado
- [ ] Redis configurado para caché
- [ ] Compresión Gzip activa
- [ ] CDN configurado (si aplica)
- [ ] Índices de base de datos creados

### Monitoreo
- [ ] Sentry integrado
- [ ] Alertas críticas configuradas
- [ ] Logs centralizados
- [ ] Métricas de rendimiento monitoreadas
- [ ] Uso de recursos monitoreado

### Backups
- [ ] Script de backup automatizado
- [ ] Prueba de restauración exitosa
- [ ] Política de retención definida
- [ ] Backups en ubicación remota
- [ ] Procedimiento de recuperación documentado

---

## Referencias

- Laravel Deployment: https://laravel.com/docs/10.x/deployment
- GitHub Actions: https://docs.github.com/en/actions
- Docker Best Practices: https://docs.docker.com/develop/dev-best-practices/
- Nginx Security: https://nginx.org/en/docs/http/ngx_http_ssl_module.html
- Sentry Documentation: https://docs.sentry.io/
- Plan general: `docs/plan/plan.md`

---

**Estado del Documento:** ✅ Completo  
**Prioridad:** Alta  
**Fecha de Creación:** 2026-01-20  
**Responsable:** Equipo de DevOps
