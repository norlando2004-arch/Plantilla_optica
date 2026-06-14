# Dockerfile para Render (Laravel + Vite)
# - Sirve la app desde /public
# - Compila assets con Vite en build (porque /public/build está en .gitignore)
# - Escucha en el puerto que Render provee via $PORT

FROM node:20-alpine AS vite-build

WORKDIR /app

COPY package.json package-lock.json vite.config.js /app/
COPY resources /app/resources
COPY public /app/public

RUN npm ci \
    && npm run build

FROM php:8.4-apache

# Render asigna $PORT; por defecto usamos 10000 para local/convención Render
ENV PORT=10000

# Dependencias comunes
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libpq-dev \
        libzip-dev \
        tesseract-ocr \
        tesseract-ocr-spa \
        tesseract-ocr-eng \
        poppler-utils \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        dom \
        gd \
        intl \
        mbstring \
        pdo \
        pdo_pgsql \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Ajustes de subida para fotos desde celular en Render
RUN printf '%s\n' \
    'upload_max_filesize=25M' \
    'post_max_size=30M' \
    'memory_limit=512M' \
    'max_file_uploads=20' \
    > /usr/local/etc/php/conf.d/uploads.ini

# DocumentRoot -> /public (Laravel)
RUN sed -ri 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Permitir que Laravel use public/.htaccess (rewrite) en producción
RUN printf '%s\n' \
    '<Directory /var/www/html/public>' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Entry point: ajusta Apache para escuchar en $PORT (Render)
RUN printf '%s\n' \
    '#!/bin/sh' \
    'set -e' \
        'PORT_VALUE="${PORT:-10000}"' \
        'echo "[entrypoint] Using PORT=${PORT_VALUE}"' \
        'sed -ri "s/^Listen 80$/Listen ${PORT_VALUE}/" /etc/apache2/ports.conf' \
        'sed -ri "s/^Listen 443$/#Listen 443/" /etc/apache2/ports.conf || true' \
    'sed -ri "s#<VirtualHost \\*:[0-9]+>#<VirtualHost *:${PORT_VALUE}>#" /etc/apache2/sites-available/000-default.conf' \
    '' \
    '# Laravel: asegurar permisos de escritura' \
    'if [ -d /var/www/html/storage ] && [ -d /var/www/html/bootstrap/cache ]; then' \
    '  chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache' \
    '  chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache' \
    'fi' \
    'php artisan storage:link || true' \
    '' \
    '# Laravel: tareas opcionales de arranque (activar en Render con variables)' \
    'if [ "${RUN_MIGRATIONS:-}" = "1" ] || [ "${RUN_MIGRATIONS:-}" = "true" ]; then php artisan migrate --force; fi' \
    'if [ "${RUN_SEEDERS:-}" = "1" ] || [ "${RUN_SEEDERS:-}" = "true" ]; then php artisan db:seed --force; fi' \
    'exec apache2-foreground' \
    > /usr/local/bin/render-entrypoint \
    && chmod +x /usr/local/bin/render-entrypoint

# Composer (útil cuando agregues el proyecto)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Por ahora el proyecto puede estar vacío; cuando agregues código, se copiará aquí
COPY . /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN if [ -f .env.example ] && [ ! -f .env ]; then cp .env.example .env; fi

# Si luego agregas un proyecto PHP (Laravel, etc.), esto instalará dependencias en build.
# Si aún no existe composer.json, el build no fallará.
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress; fi

# Copiar assets compilados (manifest.json + CSS/JS) al contenedor final
COPY --from=vite-build /app/public/build /var/www/html/public/build

EXPOSE 10000

CMD ["/usr/local/bin/render-entrypoint"]
