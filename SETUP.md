# AeroTAXI — Local Development Setup Guide

Complete step-by-step instructions for running this project locally on any machine.

---

admin@aerotaxi.com
admin123

## Architecture Overview

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13 (PHP 8.3+) |
| Frontend | Blade + Tailwind CSS 4 via Vite |
| Local DB | SQLite (`database/database.sqlite`) |
| Cloud DB | Turso (libsql) |
| Payments | Stripe (test mode) |
| Email | Gmail SMTP |

**Dual-database strategy:**
- The app reads and writes to **local SQLite** during development.
- Run `php artisan turso:sync` to **push** local data up to Turso cloud.
- Run `php artisan turso:pull` to **pull** Turso cloud data down to local SQLite.

---

## Prerequisites

Install these before starting:

| Tool | Minimum version | Download |
|------|----------------|---------|
| PHP | 8.3 | https://www.php.net/downloads |
| Composer | 2.x | https://getcomposer.org/download/ |
| Node.js | 18+ | https://nodejs.org |
| Git | any | https://git-scm.com |

Verify installations:

```bash
php --version        # PHP 8.3.x
composer --version   # Composer 2.x.x
node --version       # v18.x or v22.x
npm --version        # 9.x or 11.x
git --version        # git version x.x.x
```

**Required PHP extensions** (enabled by default in most PHP 8.3 installs):

```
pdo_sqlite, sqlite3, curl, mbstring, openssl, zip, fileinfo
```

Verify:

```bash
php -m | grep -E "pdo_sqlite|sqlite3|curl|mbstring|openssl"
```

---

## Step 1 — Clone the Repository

```bash
git clone <repository-url> aerotaxi
cd aerotaxi
```

Or if already cloned and pulling the latest:

```bash
git pull origin master
```

---

## Step 2 — Install PHP Dependencies

```bash
composer install
```

This installs all packages from `composer.lock` into the `vendor/` folder.

---

## Step 3 — Create and Configure the Environment File

```bash
cp .env.example .env
```

Then open `.env` and fill in the required values:

```env
APP_NAME=AeroTAXI
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# ── Local SQLite database ──────────────────────────────────────────────────
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

# ── Turso cloud sync (get these from https://app.turso.tech) ──────────────
# The HTTP pipeline URL for your Turso database:
TURSO_HTTP_URL=https://YOUR-DB-NAME.turso.io/v2/pipeline
# Auth token (Dashboard → your database → Generate Token):
TURSO_AUTH_TOKEN=your_turso_auth_token_here

# ── Mail (Gmail SMTP) ─────────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_char_app_password   # Google Account → App Passwords
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="supportaerotaxi@gmail.com"
MAIL_FROM_NAME="Aero Taxi"

# ── Stripe (test keys from https://dashboard.stripe.com/test/apikeys) ─────
STRIPE_PUBLISHABLE_KEY=pk_test_YOUR_KEY
STRIPE_SECRET_KEY=sk_test_YOUR_KEY

# ── Admin email addresses (comma-separated) ────────────────────────────────
ADMIN_EMAILS="admin@example.com"
```

> **Gmail App Password:** Go to myaccount.google.com → Security → 2-Step Verification → App passwords.
> Create a password for "Mail" and paste the 16-character code as `MAIL_PASSWORD`.

---

## Step 4 — Generate Application Key

```bash
php artisan key:generate
```

---

## Step 5 — Create the Local SQLite File

```bash
# Windows (PowerShell)
New-Item -ItemType File -Path "database\database.sqlite" -Force

# macOS / Linux
touch database/database.sqlite
```

---

## Step 6 — Run Database Migrations

This creates all tables in your local SQLite file:

```bash
php artisan migrate
```

Expected output: all 11 migration files listed as "DONE".

---

## Step 7 — Install Node.js Dependencies

```bash
npm install
```

---

## Step 8 — Build Frontend Assets

```bash
npm run build
```

This compiles Tailwind CSS and JavaScript into `public/build/`.

---

## Step 9 — Start the Development Server

### Option A — Single server only (simplest)

```bash
php artisan serve
```

Visit http://localhost:8000

### Option B — All services at once (recommended)

Runs PHP server + queue worker + Vite dev server + log tailing concurrently:

```bash
composer run dev
```

This starts:
- `php artisan serve` — web server on port 8000
- `php artisan queue:listen` — processes queued jobs (emails, etc.)
- `npm run dev` — Vite HMR for live CSS/JS reloading
- `php artisan pail` — real-time log viewer

Press `Ctrl+C` to stop all processes.

---

## Step 10 — Pull Latest Data from Turso (Optional)

If another developer has pushed data to Turso and you want it locally:

```bash
php artisan turso:pull
```

This will:
1. Query Turso's HTTP API for all business tables
2. Clear the corresponding local SQLite tables
3. Re-insert all rows from Turso

---

## Database Sync Commands

### Push local → Turso cloud

```bash
# Sync all business tables
php artisan turso:sync

# Sync specific tables only
php artisan turso:sync --tables=bookings,airports,vehicles

# Verbose output (shows HTTP responses on error)
php artisan turso:sync -v
```

### Pull Turso cloud → local SQLite

```bash
# Pull all business tables
php artisan turso:pull

# Pull specific tables only
php artisan turso:pull --tables=bookings,admins

# Verbose output
php artisan turso:pull -v
```

**Tables synced/pulled** (business data only — infrastructure tables are skipped):

| Table | Description |
|-------|------------|
| `users` | Customer accounts |
| `admins` | Admin panel accounts |
| `airports` | Airport locations |
| `vehicles` | Fleet with pricing |
| `faqs` | FAQ entries |
| `bookings` | Transfer bookings |
| `contact_messages` | Contact form submissions |
| `subscribers` | Email subscribers |
| `admin_notifications` | Admin notification log |

**Skipped** (local-only infrastructure): `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`

---

## Admin Panel

Visit: http://localhost:8000/admin/login

Default admin credentials are stored in the `admins` table. If you pulled from Turso, the admin already exists. Otherwise seed one:

```bash
php artisan tinker
```

```php
// Inside tinker
App\Models\Admin::create([
    'name'     => 'Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('your_password'),
]);
```

---

## Common Commands Reference

```bash
# Clear all caches (run after editing .env or config files)
php artisan config:clear && php artisan cache:clear && php artisan route:clear

# Run tests
composer test

# Check routes
php artisan route:list

# Run queue worker manually
php artisan queue:work --tries=1

# View real-time logs
php artisan pail
```

---

## PHP SSL Fix for Windows (one-time setup)

If you get SSL certificate errors when running PHP commands that make HTTP requests (e.g., during `turso:sync`), run these commands once:

```powershell
# Download Mozilla CA bundle
$phpDir = (Get-Command php).Source | Split-Path
Invoke-WebRequest -Uri "https://curl.se/ca/cacert.pem" -OutFile "$phpDir\cacert.pem" -UseBasicParsing

# Find your php.ini
php --ini

# Open php.ini and find the [curl] section, then set:
# curl.cainfo = "C:\path\to\your\php\cacert.pem"
```

---

## Troubleshooting

### "Could not open input file: artisan"
You're not in the project root. Run `cd aerotaxi` first.

### "No application encryption key has been specified"
Run `php artisan key:generate`.

### "SQLSTATE[HY000]: unable to open database file"
The SQLite file doesn't exist. Run:
```bash
# Windows
New-Item -ItemType File -Path "database\database.sqlite" -Force
# macOS/Linux
touch database/database.sqlite
```
Then run `php artisan migrate`.

### "Turso sync returns HTTP 400"
- Verify `TURSO_HTTP_URL` ends with `/v2/pipeline`
- Verify `TURSO_AUTH_TOKEN` is a valid JWT (starts with `eyJ`)

### "Class LibSQL not found" error
The libsql PHP native extension is not supported on Windows with PHP 8.3 yet.
This project runs on local SQLite + HTTP sync instead — that is the correct setup.

### Pages return 500 error
```bash
php artisan config:clear
php artisan cache:clear
# Then check storage/logs/laravel.log for details
```

### Stripe webhook not working locally
Use the Stripe CLI to forward webhooks:
```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

---

## Deployment to Vercel

This project is pre-configured for Vercel. The production deployment uses Turso as the primary database (not SQLite).

```bash
# Deploy
vercel --prod
```

Environment variables must be set in the Vercel dashboard — copy all values from your local `.env`.

---

## Project Structure Quick Reference

```
aerotaxi/
├── app/
│   ├── Console/Commands/
│   │   ├── TursoSync.php    # push local → Turso
│   │   └── TursoPull.php    # pull Turso → local
│   ├── Http/Controllers/    # web + admin controllers
│   ├── Models/              # Eloquent models
│   └── Mail/                # email mailables
├── database/
│   ├── database.sqlite      # local offline database
│   └── migrations/          # schema definitions
├── resources/
│   ├── views/               # Blade templates
│   └── css/ js/             # frontend source
├── public/build/            # compiled assets (git-ignored)
├── .env                     # local config (git-ignored)
├── .env.example             # config template (committed)
└── SETUP.md                 # this file
```
