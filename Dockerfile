# IGLO CMS — image produksi (FrankenPHP: PHP 8.3 + web server Caddy).
# Dipakai Railway (lihat railway.json) dan bisa juga di Render / Fly.io / VPS.
FROM dunglas/frankenphp:1-php8.3

RUN install-php-extensions pdo_mysql pdo_sqlite intl gd zip bcmath opcache exif

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-iglo-cms.ini

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --no-progress

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && chmod +x docker/start.sh

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SERVER_NAME=:8080 \
    PORT=8080

EXPOSE 8080

CMD ["/app/docker/start.sh"]
