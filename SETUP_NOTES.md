# MAX Gym Integrated Management System — Setup Notes

This is your `laravel/` project (Laravel 12, dependencies already installed
via `vendor/`) with the MAX Gym module added on top: models, controllers,
migrations, routes, and the black/red/white Blade views.

## To run it

From inside the `laravel/` folder:

```bash
# 1. Generate the app key (APP_KEY is currently empty in .env)
php artisan key:generate

# 2. Create the SQLite database file (this project uses sqlite by default)
touch database/database.sqlite

# 3. Run all migrations (existing Laravel tables + the MAX Gym tables) and seed accounts
php artisan migrate --seed

# 4. Serve it
php artisan serve
```

Visit `http://localhost:8000` — it redirects straight to the login page.

## Default logins (created by the seeder)

| Role    | Email                | Password  |
|---------|-----------------------|-----------|
| Admin   | admin@maxgym.test     | password  |
| Cashier | cashier@maxgym.test   | password  |

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

## ⚠️ If you already ran `migrate` before this update

The database schema changed (Members and Memberships were merged into one
table, and payments/attendance now point to memberships instead of members).
Run this to rebuild the database from scratch:

```bash
php artisan migrate:fresh --seed
```

This drops all tables and recreates them — fine to do now since there's no
real data yet, but keep in mind it wipes anything you'd already entered
through the app.

## Feature coverage (same ~50% milestone as before)

Working: login, dashboard stats, member registration/search/edit, membership
creation with **partial-payment balance tracking** (status only becomes
"Active" once fully paid), attendance check-in, walk-in payment recording,
equipment status tracking, and equipment maintenance reporting/resolution.

Not yet built: role-based UI restrictions (the `role` column exists but
isn't enforced yet), exportable/printable reports, receipt printing, and the
System Evaluation questionnaire.
