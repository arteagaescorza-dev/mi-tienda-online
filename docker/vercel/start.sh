#!/usr/bin/env bash
set -euo pipefail

# Ajustar puerto si Vercel inyecta $PORT distinto de 80
PORT="${PORT:-80}"
if [ "$PORT" != "80" ]; then
    sed -i "s/listen 80 /listen ${PORT} /g" /etc/nginx/http.d/default.conf
    sed -i "s/listen \[::\]:80 /listen [::]:${PORT} /g" /etc/nginx/http.d/default.conf
fi

# Cachear configuración y rutas en arranque (si APP_KEY está presente)
if [ -n "${APP_KEY:-}" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

php-fpm -D
exec nginx -g "daemon off;"
