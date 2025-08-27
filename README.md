<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel Template Voyager Example

## Instalación
```
composer install
cp .env.example .env
php artisan example:install
sudo chmod -R 775 storage bootstrap/cache
chown -R www-data storage bootstrap/cache
```

## Versión de Laravel
Laravel Framework 10.0.0

## Requisistos
- php >= 8.1
- Extenciones **php-mbstring php-intl php-dom php-gd php-xml php-zip php-curl php-fpm php-mysql**


## Dockerfile
Crear en la Raiz del proyecto los siguientes archivos:
Dockerfile
unit.json

Ejecutar.
```
docker build -t example .
docker run -e DB_DATABASE=example -e DB_HOST=host.docker.internal -p 8000:8000 -t example
```
Ejemplo
```
docker run  -e DB_CONNECTION=mysql -e DB_HOST=host.docker.internal -e DB_PORT=3306 -e DB_DATABASE=example -e DB_USERNAME=root -e DB_CONNECTION_SOLUCION_DIGITAL=mysql -e DB_HOST_SOLUCION_DIGITAL=host.docker.internal -e DB_PORT_SOLUCION_DIGITAL=3306 -e DB_DATABASE_SOLUCION_DIGITAL=soluciondigital -e DB_USERNAME_SOLUCION_DIGITAL=root -p 8000:8000 -t example
```