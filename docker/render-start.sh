#!/usr/bin/env bash
set -e

echo "Caching config..."
php artisan config:cache

echo "Running migrations..."
php artisan migrate --force

PORT="${PORT:-8000}"
echo "Starting server on port $PORT..."
php artisan serve --host=0.0.0.0 --port="$PORT"
