@echo off
echo ========================================================
echo   MAX GYM - Integrated Management System (Laravel 12)
echo ========================================================
echo.

if not exist ".env" (
    copy .env.example .env
)

if not exist "database\database.sqlite" (
    type nul > database\database.sqlite
)

if not exist "vendor\autoload.php" (
    echo [1/4] Installing Composer dependencies...
    call composer install --no-interaction
) else (
    echo [1/4] Composer dependencies already installed.
)

echo [2/4] Generating application key...
php artisan key:generate --ansi

echo [3/4] Running migrations and seeding MAX GYM database...
php artisan migrate:fresh --seed --force

echo [4/4] Starting server at http://127.0.0.1:8000 ...
php artisan serve
pause
