# Docker en el Sistema

## Configuración de Docker

El sistema usa Docker para crear un entorno de desarrollo y producción reproducible.

---

## Archivos de Docker

### 1. Dockerfile

**Archivo:** `Dockerfile`

**Imagen Base:** `unit:1.33.0-php8.2`

**Puerto Expuesto:** `8000`

**Usuario:** `unit:unit`

**Directorio de Trabajo:** `/var/www/example`

---

## Análisis del Dockerfile

### Línea por Línea

#### 1. Imagen Base
```dockerfile
FROM unit:1.33.0-php8.2
```
- **Imágenes:** NGINX Unit 1.33.0 con PHP 8.2
- **NGINX Unit:** Servidor web moderno y de alto rendimiento
- **PHP 8.2:** Última versión estable de PHP
- **Ventajas:**
  - Más eficiente que Apache/Nginx + PHP-FPM
  - Configuración dinámica sin reinicios
  - Menor consumo de memoria
  - Soporte nativo para PHP

#### 2. Instalación de Dependencias del Sistema
```dockerfile
RUN apt update && apt install -y \
    curl unzip git libicu-dev libzip-dev libpng-dev libjpeg-dev \
    libfreetype6-dev libssl-dev
```
**Paquetes instalados:**
- `curl` - Cliente HTTP para descargas
- `unzip` - Descompresor de archivos ZIP
- `git` - Sistema de control de versiones
- `libicu-dev` - Biblioteca de internacionalización (para Laravel)
- `libzip-dev` - Biblioteca para trabajar con archivos ZIP
- `libpng-dev` - Biblioteca para imágenes PNG
- `libjpeg-dev` - Biblioteca para imágenes JPEG
- `libfreetype6-dev` - Biblioteca para fuentes
- `libssl-dev` - Biblioteca SSL/TLS

#### 3. Configuración de GD (Grafics Draw)
```dockerfile
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
```
- Configura la extensión GD de PHP
- Habilita soporte para:
  - Imágenes PNG (libpng)
  - Imágenes JPEG (libjpeg)
  - Fuentes TrueType (freetype)

**Uso en el sistema:**
- Procesamiento de imágenes en `StorageController`
- Conversión de imágenes a AVIF
- Redimensionamiento de imágenes

#### 4. Instalación de Extensiones de PHP
```dockerfile
RUN docker-php-ext-install -j$(nproc) \
    pcntl opcache pdo pdo_mysql intl zip gd exif ftp bcmath
```
**Extensiones instaladas:**
- `pcntl` - Control de procesos (para queues/commands)
- `opcache` - Caché de OPcode (optimización de rendimiento)
- `pdo` - PHP Data Objects (abstracción de BD)
- `pdo_mysql` - Driver MySQL para PDO
- `intl` - Internacionalización (para Laravel)
- `zip` - Creación/manipulación de archivos ZIP
- `gd` - Procesamiento de imágenes
- `exif` - Metadatos de imágenes
- `ftp` - Protocolo FTP
- `bcmath` - Matemáticas de precisión arbitraria

**Parámetro `-j$(nproc)`:**
- Usa todos los núcleos del CPU para compilación paralela
- Reduce significativamente el tiempo de construcción

#### 5. Instalación de Redis
```dockerfile
RUN pecl install redis \
    && docker-php-ext-enable redis
```
**Redis:**
- Sistema de caché y colas
- Instalado vía PECL (PHP Extension Community Library)
- Habilitado en PHP

**Uso en el sistema:**
- Caché de configuración
- Colas de jobs
- Sesiones (opcional)

#### 6. Configuración de PHP
```dockerfile
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit=tracing" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "opcache.jit_buffer_size=256M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit=512M" > /usr/local/etc/php/conf.d/custom.ini \
    && echo "upload_max_filesize=64M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "post_max_size=64M" >> /usr/local/etc/php/conf.d/custom.ini
```
**Configuraciones:**

| Configuración | Valor | Propósito |
|---------------|-------|-----------|
| `opcache.enable` | 1 | Habilita OPcache |
| `opcache.jit` | tracing | Habilita compilación JIT (Just-In-Time) |
| `opcache.jit_buffer_size` | 256M | Tamaño del buffer JIT |
| `memory_limit` | 512M | Límite de memoria PHP |
| `upload_max_filesize` | 64M | Tamaño máximo de subida |
| `post_max_size` | 64M | Tamaño máximo de POST |

**Detalles:**
- **OPcache:** Almacena el código PHP compilado en memoria para mayor velocidad
- **JIT (Just-In-Time):** Compila código PHP a código máquina en tiempo de ejecución
- **Memory Limit:** 512MB para procesamiento de imágenes grandes
- **Upload Limits:** 64MB para permitir subida de imágenes grandes

#### 7. Instalación de Composer
```dockerfile
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
```
- Copia Composer desde la imagen oficial `composer:latest`
- Permite ejecutar `composer install` dentro del contenedor

#### 8. Directorio de Trabajo
```dockerfile
WORKDIR /var/www/example
```
- Establece el directorio de trabajo principal
- Equivalente a `cd /var/www/example`

#### 9. Creación de Directorios
```dockerfile
RUN mkdir -p /var/www/example/storage /var/www/example/bootstrap/cache
```
- Crea directorios necesarios de Laravel
- `storage/` - Logs, caché, uploads
- `bootstrap/cache/` - Caché de Laravel

#### 10. Permisos Iniciales
```dockerfile
RUN chown -R unit:unit /var/www/example/storage bootstrap/cache \
    && chmod -R 775 /var/www/example/storage
```
- Cambia el propietario a `unit:unit` (usuario de NGINX Unit)
- Permisos `775` (rwxrwxr-x):
  - Propietario: lectura, escritura, ejecución
  - Grupo: lectura, escritura, ejecución
  - Otros: lectura, ejecución

#### 11. Copia de Archivos del Proyecto
```dockerfile
COPY . .
```
- Copia todos los archivos del proyecto al directorio de trabajo
- Incluye: código PHP, composer.json, vistas, etc.

#### 12. Permisos Finales
```dockerfile
RUN chown -R unit:unit storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
```
- Asegura permisos correctos después de copiar archivos
- Laravel necesita escribir en estos directorios

#### 13. Instalación de Dependencias de Composer
```dockerfile
RUN composer install --prefer-dist --optimize-autoloader --no-interaction
```
**Parámetros:**
- `--prefer-dist` - Usa versiones descargadas (más rápido que clonar)
- `--optimize-autoloader` - Optimiza el autoloader de Composer
- `--no-interaction` - No pregunta en modo no interactivo

**Resultado:**
- Instala todas las dependencias de `composer.json`
- Crea `vendor/` con librerías

#### 14. Configuración de NGINX Unit
```dockerfile
COPY unit.json /docker-entrypoint.d/unit.json
```
- Copia archivo de configuración de NGINX Unit
- Se carga automáticamente al iniciar el contenedor

#### 15. Configuración de Entorno
```dockerfile
COPY .env.example .env
RUN php artisan key:generate
RUN php artisan storage:link
```
- Copia `.env.example` a `.env`
- Genera `APP_KEY` única
- Crea enlace simbólico de almacenamiento

#### 16. Exposición de Puerto
```dockerfile
EXPOSE 8000
```
- Expone el puerto 8000
- El servidor NGINX Unit escuchará en este puerto

#### 17. Comando de Inicio
```dockerfile
CMD ["unitd", "--no-daemon"]
```
- Inicia NGINX Unit
- `--no-daemon` - Ejecuta en foreground (necesario para Docker)

---

## Configuración de NGINX Unit

**Archivo:** `unit.json`

### Estructura del Archivo

```json
{
    "listeners": {
        "*:8000": {
            "pass": "routes"
        }
    },

    "routes": [
        {
            "match": {
                "uri": "!/index.php"
            },
            "action": {
                "share": "/var/www/example/public$uri",
                "fallback": {
                    "pass": "applications/laravel"
                }
            }
        }
    ],

    "applications": {
        "laravel": {
            "type": "php",
            "root": "/var/www/example/public/",
            "script": "index.php"
        }
    }
}
```

### Componentes

#### 1. Listeners
```json
"listeners": {
    "*:8000": {
        "pass": "routes"
    }
}
```
- Escucha en todas las interfaces (`*`) en el puerto `8000`
- Pasa las peticiones al router

#### 2. Routes
```json
"routes": [
    {
        "match": {
            "uri": "!/index.php"
        },
        "action": {
            "share": "/var/www/example/public$uri",
            "fallback": {
                "pass": "applications/laravel"
            }
        }
    }
]
```
**Lógica:**
- **Si la URI NO es `/index.php`:**
  1. Intenta servir archivos estáticos desde `/var/www/example/public`
  2. Si no encuentra el archivo, pasa a la aplicación Laravel
- **Si la URI ES `/index.php`:**
  1. Pasa directamente a la aplicación Laravel

**Archivos servidos directamente:**
- CSS, JS, imágenes
- Archivos en `storage/` (via `storage:link`)
- Otros assets

**Rutas de Laravel:**
- `/admin`
- `/admin/people`
- `/api/*`
- etc.

#### 3. Applications
```json
"applications": {
    "laravel": {
        "type": "php",
        "root": "/var/www/example/public/",
        "script": "index.php"
    }
}
```
- **Tipo:** PHP
- **Root:** Directorio público de Laravel
- **Script:** Punto de entrada (`index.php`)

---

## Comandos de Docker

### Construir la Imagen
```bash
docker build -t electoral-sistema .
```

### Ejecutar el Contenedor
```bash
docker run -p 8000:8000 electoral-sistema
```

### Ejecutar en Modo Interactivo
```bash
docker run -it -p 8000:8000 electoral-sistema bash
```

### Ver Logs del Contenedor
```bash
docker logs <container-id>
docker logs -f <container-id>  # Seguir logs en tiempo real
```

### Entrar al Contenedor en Ejecución
```bash
docker exec -it <container-id> bash
```

### Ejecutar Comandos en el Contenedor
```bash
docker exec <container-id> php artisan migrate
docker exec <container-id> php artisan storage:link
docker exec <container-id> composer install
```

---

## Variables de Entorno

El Dockerfile espera variables de entorno que pueden pasarse al ejecutar:

```bash
docker run -e DB_HOST=localhost \
           -e DB_DATABASE=electoral \
           -e DB_USERNAME=root \
           -e DB_PASSWORD=secret \
           -p 8000:8000 electoral-sistema
```

O usar un archivo `.env` (incluido en el Dockerfile):

```bash
# El Dockerfile copia .env.example a .env
# Genera APP_KEY automáticamente
```

---

## Optimizaciones del Dockerfile

### 1. Compilación Paralela
```dockerfile
RUN docker-php-ext-install -j$(nproc) ...
```
- Usa todos los núcleos del CPU
- Reduce tiempo de construcción ~50%

### 2. OPcache + JIT
```dockerfile
opcache.enable=1
opcache.jit=tracing
opcache.jit_buffer_size=256M
```
- Mejora rendimiento ~2-3x en producción

### 3. Composer Optimizado
```dockerfile
RUN composer install --prefer-dist --optimize-autoloader ...
```
- Usa versiones pre-descargadas
- Optimiza el autoloader

### 4. Imágenes Base Livianas
```dockerfile
FROM unit:1.33.0-php8.2
```
- NGINX Unit es más ligero que Apache/Nginx + PHP-FPM
- Menor consumo de memoria

### 5. Minimización de Capas
- Comandos `RUN` combinados para reducir capas
- `COPY . .` al final para mejor caché

---

## Requisitos del Sistema

### Requisitos de Hardware

| Recurso | Mínimo | Recomendado |
|---------|--------|-------------|
| CPU | 1 núcleo | 2+ núcleos |
| RAM | 512 MB | 1 GB+ |
| Disco | 1 GB | 5 GB+ |

### Requisitos de Software

- Docker 20.10+
- Docker Compose (opcional)
- Git (para clonar el proyecto)

---

## Troubleshooting

### Error de Permisos
**Problema:** Laravel no puede escribir en `storage/`

**Solución:**
```bash
docker exec -it <container-id> bash
chown -R unit:unit /var/www/example/storage
chmod -R 775 /var/www/example/storage
```

### Error de Conexión a BD
**Problema:** No se puede conectar a MySQL

**Solución:**
```bash
# Asegúrate de pasar las variables de entorno
docker run -e DB_HOST=host.docker.internal \
           -e DB_DATABASE=electoral \
           -p 8000:8000 electoral-sistema
```

### Error de Imagen No Carga
**Problema:** Las imágenes no se muestran

**Solución:**
```bash
docker exec <container-id> php artisan storage:link
```

### Contenedor No Arranca
**Problema:** El contenedor se detiene inmediatamente

**Solución:**
```bash
# Ver logs para diagnóstico
docker logs <container-id>

# Ejecutar en modo interactivo para ver errores
docker run -it -p 8000:8000 electoral-sistema bash
```

---

## Ventajas de Usar NGINX Unit

1. **Alto Rendimiento:** Más rápido que Apache/Nginx + PHP-FPM
2. **Configuración Dinámica:** No requiere reiniciar para cambios
3. **Menor Consumo:** Menos memoria y CPU
4. **Single Binary:** Todo en un solo proceso
5. **Soporte Múltiple:** PHP, Python, Go, Java, Ruby, etc.
6. **Configuración JSON:** Fácil de versionar y automatizar

---

## Comparación con Alternativas

| Característica | NGINX Unit | Apache + mod_php | Nginx + PHP-FPM |
|---------------|------------|------------------|-----------------|
| Rendimiento | ★★★★★ | ★★★☆☆ | ★★★★☆ |
| Consumo de RAM | ★★★★★ | ★★☆☆☆ | ★★★☆☆ |
| Configuración Dinámica | ★★★★★ | ★☆☆☆☆ | ★★☆☆☆ |
| Facilidad de Uso | ★★★★☆ | ★★★★★ | ★★★☆☆ |
| Comunidad | ★★★☆☆ | ★★★★★ | ★★★★★ |

---

## Notas Importantes

1. **NGINX Unit:** El sistema usa NGINX Unit en lugar de Apache/Nginx tradicional.

2. **PHP 8.2:** Se usa la última versión de PHP para mejor rendimiento.

3. **JIT Compiler:** El JIT está habilitado para mejor rendimiento en producción.

4. **Memory Limit:** 512MB para procesamiento de imágenes grandes.

5. **Upload Limits:** 64MB para permitir subida de imágenes grandes.

6. **OPcache:** Habilitado para caché de código compilado.

7. **Redis:** Instalado pero requiere configuración adicional para usarlo.

8. **Permisos:** Los directorios `storage/` y `bootstrap/cache/` deben tener permisos de escritura.

9. **Enlace Simbólico:** `storage:link` se ejecuta automáticamente en la construcción.

10. **Puerto 8000:** El servidor escucha en el puerto 8000 (no 80 ni 443).
