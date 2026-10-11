# MAX Gym Integrated Management System — Full Laravel 12 Project

This folder contains the complete, ready-to-run **MAX Gym Integrated Management System** (Laravel 12) including the MAX GYM SVG icons, Models, Controllers, Migrations, Seeders, Routes, and Blade Views.

## Quick Start (One-Click)

- **Windows**: Double-click `START_MAX_GYM.bat`
- **Mac / Linux**: Run `bash start-max-gym.sh`

## Manual Quick Start

Create a MySQL database named `max_gym_maximum_fitness` (for example, in
phpMyAdmin) and set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and
`DB_PASSWORD` in `.env` to match your MySQL server. The supplied defaults are
for a local XAMPP MySQL installation.

```powershell
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Then open **http://127.0.0.1:8000** in your browser. Migrations do not copy
records from the old SQLite database. The startup scripts use non-destructive
migrations; do not use `migrate:fresh` on a database containing data you need.
Member photos are served through an authenticated application route, so a
public storage symlink is not required.

### Default Admin Account
| Role | Email | Password |
|---|---|---|
| Admin | `admin@maxgym.test` | `password` |
