#!/bin/sh
# Start container di Vercel. Web server HARUS langsung listen di $PORT (start
# yang lambat dianggap gagal → FUNCTION_INVOCATION_FAILED), jadi migrasi, data
# awal & akun admin (cms:bootstrap) dijalankan di background; output-nya masuk
# ke Logs Vercel.
cd /app
php artisan cms:bootstrap &

exec frankenphp run --config /etc/frankenphp/Caddyfile
