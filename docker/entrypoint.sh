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

exec "$@"
