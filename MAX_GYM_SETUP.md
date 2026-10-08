# MAX Gym Integrated Management System — Full Laravel 12 Project

This folder contains the complete, ready-to-run **MAX Gym Integrated Management System** (Laravel 12) including the MAX GYM SVG icons, Models, Controllers, Migrations, Seeders, Routes, and Blade Views.

## Quick Start (One-Click)

- **Windows**: Double-click `START_MAX_GYM.bat`
- **Mac / Linux**: Run `bash start-max-gym.sh`

## Manual Quick Start

```bash
cd max-gym-maximum-fitness
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan serve
```

Then open **http://127.0.0.1:8000** in your browser.
Member photos are served through an authenticated application route, so a
public storage symlink is not required.

### Default Admin Account
| Role | Email | Password |
|---|---|---|
| Admin | `admin@maxgym.test` | `password` |
