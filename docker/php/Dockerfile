# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Stage 1: build de assets (Vite / Tailwind) — welcome.blade.php usa @vite
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 2: dependencias PHP (sin dev, autoloader optimizado)
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

# ---------------------------------------------------------------------------
# Stage 3: runtime php-fpm
# ---------------------------------------------------------------------------
FROM php:8.5-fpm-alpine AS runtime

# Librerías runtime: se quedan en la imagen final.
RUN apk add --no-cache \
        bash \
        icu-libs \
        libzip \
        libpng \
        libjpeg-turbo \
        libwebp \
        freetype

# Headers de compilación: virtual package que se elimina tras compilar,
# así no arrastramos los -dev ni dejamos huérfanas las libs de arriba.
RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        bcmath \
        intl \
        zip \
        gd \
        pcntl \
    && apk del --no-network .build-deps

# OPcache NO se instala como extension: PHP 8.5 lo trae compilado estaticamente
# en el binario (php -m ya lo lista). Solo se configura, y eso vive en php.ini.

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-app.ini

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint

# public-dist es la copia pristina de public/. El entrypoint la vuelca sobre el
# volumen compartido con nginx en cada arranque, para que los assets de Vite no
# queden desfasados respecto a la imagen tras un rebuild.
#
# public/storage -> storage/app/public: ahi viven los adjuntos de tickets
# (disco `public` de ImageStorageService). Se crea como symlink absoluto para
# que sobreviva a la copia y resuelva igual en nginx (que monta el mismo
# volumen de storage).
RUN chmod +x /usr/local/bin/entrypoint \
    && mkdir -p storage/app/public \
                storage/framework/cache/data \
                storage/framework/sessions \
                storage/framework/views \
                storage/logs \
                bootstrap/cache \
    && ln -sfn /var/www/html/storage/app/public public/storage \
    && cp -a public /var/www/html/public-dist \
    && chown -R www-data:www-data storage bootstrap/cache public public-dist \
    && chmod -R 775 storage bootstrap/cache

USER www-data

# Regenera el manifest de paquetes con el vendor sin dev-deps.
RUN php artisan package:discover --ansi --no-interaction

EXPOSE 9000

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

# ---------------------------------------------------------------------------
# Stage 4: all-in-one — mariadb + nginx + php-fpm + queue + scheduler en un
# solo contenedor (Alpine no empaqueta MySQL de Oracle; MariaDB es compatible
# con el driver `mysql` de Laravel). `docker run -p 8000:80 imagen` y listo, sin compose.
# ---------------------------------------------------------------------------
FROM runtime AS standalone

USER root

RUN apk add --no-cache \
        mariadb \
        mariadb-client \
        nginx \
        supervisor \
        su-exec

COPY docker/standalone/nginx.conf /etc/nginx/nginx.conf
COPY docker/standalone/my.cnf /etc/my.cnf.d/mariadb-server.cnf
COPY docker/standalone/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/standalone/entrypoint.sh /usr/local/bin/standalone-entrypoint
COPY docker/standalone/wait-for-db.sh /usr/local/bin/wait-for-db
COPY docker/standalone/wait-for-app.sh /usr/local/bin/wait-for-app
COPY docker/standalone/app-start.sh /usr/local/bin/app-start

RUN chmod +x /usr/local/bin/standalone-entrypoint \
              /usr/local/bin/wait-for-db \
              /usr/local/bin/wait-for-app \
              /usr/local/bin/app-start \
    && mkdir -p /run/mysqld /run/nginx /var/lib/nginx/tmp /var/lib/mysql/data \
    && chown -R mysql:mysql /run/mysqld /var/lib/mysql \
    && chown -R www-data:www-data /var/lib/nginx \
    && ln -sf /etc/supervisor/conf.d/supervisord.conf /etc/supervisord.conf

# Valores por defecto: sin esto el contenedor no sabria a que base conectarse.
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost:8000 \
    LOG_CHANNEL=stack \
    LOG_STACK=single \
    LOG_LEVEL=info \
    DB_CONNECTION=mysql \
    DB_HOST=127.0.0.1 \
    DB_PORT=3306 \
    DB_DATABASE=legumexapps_tickets_db \
    DB_USERNAME=legumex \
    DB_PASSWORD=mysecretpassword \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    FILESYSTEM_DISK=local \
    MYSQL_DATADIR=/var/lib/mysql/data

# Datos de mariadb + APP_KEY/JWT_SECRET generados: montar un volumen aqui para
# no perderlos al recrear el contenedor. Los adjuntos de tickets viven en
# /var/www/html/storage: montar otro volumen ahi si se quieren conservar.
VOLUME ["/var/lib/mysql"]

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=90s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/up") === false ? 1 : 0);'

ENTRYPOINT ["standalone-entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
