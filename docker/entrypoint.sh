#!/usr/bin/env sh
set -eu

cd /app

if [ -z "${APP_KEY:-}" ]; then
  echo "APP_KEY is required in production." >&2
  exit 1
fi

php artisan config:clear || true
php artisan migrate --force --no-interaction
php artisan storage:link || true

# Single-container mode for Railway free tier: web + queue + scheduler
if [ "${JM_PROCESS_MODE:-web}" = "all" ]; then
  php artisan queue:work redis --queue=charges,whatsapp,default --tries=3 --sleep=1 &
  php artisan schedule:work &
fi

exec "$@"
