# MAX Gym Integrated Management System — Setup Notes

This is your `laravel/` project (Laravel 12, dependencies already installed
via `vendor/`) with the MAX Gym module added on top: models, controllers,
migrations, routes, and the black/red/white Blade views.

## MySQL setup with XAMPP

The project uses MySQL database `max_gym_maximum_fitness`. In XAMPP, start
MySQL. Start Apache too if you want to use phpMyAdmin, then create a database
named `max_gym_maximum_fitness` with the `utf8mb4_unicode_ci` collation. The
`.env` file is configured for the usual XAMPP defaults (`127.0.0.1:3306`, user
`root`, blank password); change those values if your MySQL credentials differ.

From the project root, run:

```powershell
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Visit `http://localhost:8000` — it redirects to the login page. Existing data
in `database/database.sqlite` is separate and is not copied into MySQL by the
migrations.

Member photos are served through an authenticated application route, so a
public storage symlink is not required.

Visit `http://localhost:8000` — it redirects straight to the login page.

## What's in this project

- `app/Models/` — Membership (holds the member's info *and* their plan/balance
  in one place), Attendance, Payment, Equipment, Maintenance (User.php was
  updated in place to add a `role` field)
- `app/Http/Controllers/` — one controller per module, plus
  `Auth/AuthController.php` for login/logout
- `database/migrations/` — a migration adding `role` to the existing users
  table, plus new tables for memberships, attendance, payments, equipment,
  and maintenance
- `database/seeders/DatabaseSeeder.php` — replaced to create the admin/
  cashier accounts above instead of the default test user
- `resources/views/` — full Blade UI (layout, login, dashboard, and CRUD
  screens for every module) in the black/red/white theme
- `routes/web.php` — replaced with all MAX Gym routes, behind `auth`
  middleware

There is no separate "Member" record anymore — registering someone under
Members & Memberships *is* their member record; walk-in / one-off payments
just take a typed name instead of requiring registration first.

Nothing in `vendor/`, `bootstrap/`, or `config/` was touched — this plugs
into the default Laravel 12 skeleton as-is.

## Existing database data

The SQLite file is left in place as a separate database. Switching the app to
MySQL does not transfer its records. Back up any data you need before running
schema changes, and avoid `php artisan migrate:fresh` on a database with data
you want to keep because it drops all tables.

## Feature coverage (same ~50% milestone as before)

Working: login, dashboard stats, member registration/search/edit, membership
creation with **partial-payment balance tracking** (status only becomes
"Active" once fully paid), attendance check-in, walk-in payment recording,
equipment status tracking, and equipment maintenance reporting/resolution.

Not yet built: role-based UI restrictions (the `role` column exists but
isn't enforced yet), exportable/printable reports, receipt printing, and the
System Evaluation questionnaire.
