#!/usr/bin/env bash
set -e

echo "========================================================"
echo "  MAX GYM - Integrated Management System (Laravel 12)"
echo "========================================================"

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

mkdir -p database storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/testing storage/framework/views storage/logs bootstrap/cache

if [ ! -f "vendor/autoload.php" ]; then
    echo "[1/4] Installing Composer dependencies..."
    composer install --no-interaction
else
    echo "[1/4] Composer dependencies already installed."
fi

if grep -q '^APP_KEY=$' .env; then
    echo "[2/4] Generating application key..."
    php artisan key:generate --ansi
else
    echo "[2/4] Application key already configured."
fi

echo "[3/4] Running non-destructive migrations and seeding MAX GYM database..."
php artisan migrate --seed --force

echo "[4/4] Starting server at http://127.0.0.1:8000 ..."
php artisan serve
