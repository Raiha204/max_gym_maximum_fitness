@echo off
echo ========================================================
echo   MAX GYM - Integrated Management System (Laravel 12)
echo ========================================================
echo.

if not exist ".env" (
    copy .env.example .env
)

if not exist "vendor\autoload.php" (
    echo [1/4] Installing Composer dependencies...
    call composer install --no-interaction
) else (
    echo [1/4] Composer dependencies already installed.
)

findstr /r /c:"^APP_KEY=$" .env >nul
if not errorlevel 1 (
    echo [2/4] Generating application key...
    php artisan key:generate --ansi
) else (
    echo [2/4] Application key already configured.
)

echo [3/4] Running non-destructive migrations and seeding MAX GYM database...
php artisan migrate --seed --force

echo [4/4] Starting server at http://127.0.0.1:8000 ...
php artisan serve
pause
