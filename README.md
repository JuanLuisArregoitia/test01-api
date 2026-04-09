# Test01 API

A RESTful API built with **Laravel 13** and a **React SPA** frontend. Implements full authentication with Sanctum (email verification + password reset), CRUD for 5 entities, Swagger documentation, and Gitflow branching strategy.

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.5 |
| Auth | Laravel Sanctum (Bearer tokens) |
| Frontend | React 19, React Router 7, Tailwind CSS 4 |
| Build | Vite 8 |
| DB (local) | MySQL |
| DB (production) | PostgreSQL (Railway) |
| Docs | Swagger / OpenAPI 3 (l5-swagger) |
| Tests | PHPUnit 12 — 69 tests, 174 assertions |

## API Endpoints

**Auth** (public)
- `POST /api/v1/register` — Register + sends email verification
- `POST /api/v1/login` — Login → returns Bearer token
- `POST /api/v1/forgot-password` — Send password reset link
- `POST /api/v1/reset-password` — Reset password with token
- `GET  /api/v1/email/verify/{id}/{hash}` — Verify email (signed URL)

**Auth** (requires Bearer token)
- `POST /api/v1/logout`
- `POST /api/v1/email/verification-notification` — Resend verification email

**Resources** (all require Bearer token)
- `/api/v1/clients` — CRUD clients
- `/api/v1/suppliers` — CRUD suppliers
- `/api/v1/products` — CRUD products
- `/api/v1/orders` — CRUD orders (status: 1=Pending, 2=Processing, 3=Completed)
- `/api/v1/order-details` — CRUD order line items

## API Documentation (Swagger)

Access at `/api/documentation` with the app running.

## Local Setup

```bash
# Clone and install
composer install
npm install

# Environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate
php artisan db:seed     # Creates admin@example.com / password + demo data

# Build frontend
npm run build           # Production
npm run dev             # Development (hot reload)
```

**Demo credentials:** `admin@example.com` / `password`

## Running Tests

```bash
php artisan test --compact
```

## Deployment on Railway

The included `railway.toml` + `nixpacks.toml` configure the build automatically. Railway uses Nixpacks and will install both PHP 8.4 and Node.js.

### Steps

1. Create a new project on [Railway](https://railway.app) → **Deploy from GitHub repo**
2. Add a **PostgreSQL** plugin from the Railway dashboard (it auto-sets `DATABASE_URL`)
3. Set the following environment variables in the Railway service dashboard:

| Variable | Value |
|---|---|
| `APP_NAME` | `Test01 API` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_KEY` | Run `php artisan key:generate --show` locally |
| `APP_URL` | Your Railway URL (e.g. `https://test01-api.up.railway.app`) |
| `DB_CONNECTION` | `pgsql` |
| `DB_HOST` | From Railway PostgreSQL plugin (`${{Postgres.PGHOST}}`) |
| `DB_PORT` | `${{Postgres.PGPORT}}` |
| `DB_DATABASE` | `${{Postgres.PGDATABASE}}` |
| `DB_USERNAME` | `${{Postgres.PGUSER}}` |
| `DB_PASSWORD` | `${{Postgres.PGPASSWORD}}` |
| `CACHE_STORE` | `database` |
| `SESSION_DRIVER` | `database` |
| `QUEUE_CONNECTION` | `sync` |
| `MAIL_MAILER` | `log` |
| `LOG_CHANNEL` | `stderr` |

4. After the first deploy succeeds, run via Railway CLI or the **Service → Shell** tab:
```bash
php artisan migrate --force
php artisan db:seed
```

That's it. Railway auto-deploys on every push to your connected branch.

## Gitflow Branching

```
main          → production-ready
develop       → integration
feature/*     → individual features
release/*     → pre-production
hotfix/*      → emergency fixes
```

## React Frontend Pages

- `/login` — Sign in
- `/register` — Create account
- `/dashboard` — Stats overview (clients, suppliers, products, orders counts + recent orders)
- `/clients`, `/suppliers`, `/products`, `/orders`, `/order-details` — Full CRUD with blue modals
- Sidebar link → `/api/documentation` (Swagger UI)
