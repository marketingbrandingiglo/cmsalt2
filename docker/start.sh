#!/bin/sh
# Start container: siapkan storage, instalasi idempoten, lalu jalankan web server.
set -e
cd /app

# storage/ bisa berupa volume persisten yang awalnya kosong.
mkdir -p storage/app/public storage/app/private \
         storage/framework/cache/data storage/framework/sessions storage/framework/views \
         storage/logs bootstrap/cache

# APP_KEY: pakai env bila ada; kalau tidak, buat sekali & simpan di volume
# supaya tetap sama di setiap deploy (session/login tidak hilang).
if [ -z "$APP_KEY" ]; then
  KEY_FILE=storage/app/private/app_key
  [ -s "$KEY_FILE" ] || echo "base64:$(head -c 32 /dev/urandom | base64)" > "$KEY_FILE"
  export APP_KEY="$(cat "$KEY_FILE")"
fi

php artisan cms:install --no-interaction
php artisan optimize
php artisan filament:optimize

exec frankenphp php-server --root public/ --listen ":${PORT:-8080}"
