#!/bin/sh
# Start container: siapkan storage, instalasi idempoten, lalu jalankan web server.
set -e
cd "${APP_DIR:-/app}"

echo "== IGLO CMS: start (APP_ENV=${APP_ENV:-?}, DB_CONNECTION=${DB_CONNECTION:-<kosong>}, DB_URL $( [ -n "$DB_URL" ] && echo terisi || echo KOSONG ), PORT=${PORT:-8080})"
[ -n "$APP_URL" ] || echo "!! APP_URL kosong — set APP_URL=https://\${{RAILWAY_PUBLIC_DOMAIN}}"
if command -v mountpoint >/dev/null 2>&1 && ! mountpoint -q storage; then
  echo "!! storage/ bukan volume — file upload akan hilang saat redeploy (pasang volume ke /app/storage)"
fi

# storage/ bisa berupa volume persisten yang awalnya kosong.
mkdir -p storage/app/public storage/app/private \
         storage/framework/cache/data storage/framework/sessions storage/framework/views \
         storage/logs bootstrap/cache

# APP_KEY: pakai env bila ada; kalau tidak, buat sekali & simpan di volume
# supaya tetap sama di setiap deploy (session/login tidak hilang).
if [ -z "$APP_KEY" ]; then
  KEY_FILE=storage/app/private/app_key
  [ -s "$KEY_FILE" ] || echo "base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')" > "$KEY_FILE"
  APP_KEY="$(cat "$KEY_FILE")"
  export APP_KEY
fi

if ! php artisan cms:install --no-interaction; then
  echo "!! Instalasi gagal — lihat pesan di atas. Container berhenti."
  exit 1
fi

php artisan optimize
php artisan filament:optimize

echo "== IGLO CMS siap di port ${PORT:-8080}"
exec frankenphp php-server --root public/ --listen ":${PORT:-8080}"
