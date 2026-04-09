# Test01 API

A RESTful API built with **Laravel 13** and a **React SPA** frontend. Implements full authentication with Sanctum (email verification + password reset), CRUD for 5 entities, Swagger documentation, and Gitflow branching strategy.

---

## Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13, PHP 8.4+ |
| Auth | Laravel Sanctum (Bearer tokens) |
| Frontend | React 19, React Router 7, Tailwind CSS 4 |
| Build | Vite 8 |
| DB (local) | MySQL |
| DB (production) | MySQL / PostgreSQL (Laravel Cloud) |
| Docs | Swagger / OpenAPI 3 (l5-swagger) |
| Tests | PHPUnit 12 — 69 tests, 174 assertions |

---

## API Endpoints

**Auth** (public)
```
POST /api/v1/register                     Register (sends email verification)
POST /api/v1/login                        Login → Bearer token
POST /api/v1/forgot-password              Send password reset link
POST /api/v1/reset-password               Reset password with token
GET  /api/v1/email/verify/{id}/{hash}     Verify email (signed URL)
```

**Auth** (Bearer token required)
```
POST /api/v1/logout
POST /api/v1/email/verification-notification    Resend verification email
```

**Resources** (Bearer token required — all support index / store / show / update / destroy)
```
/api/v1/clients
/api/v1/suppliers
/api/v1/products
/api/v1/orders          status: 1=Pending, 2=Processing, 3=Completed
/api/v1/order-details
```

**Swagger UI:** `/api/documentation`

---

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed       # admin@example.com / password + demo data
npm run build             # or: npm run dev (hot reload)
```

---

## Running Tests

```bash
php artisan test --compact
```

---

## Deploy on Laravel Cloud

Laravel Cloud manages everything from its dashboard — no special config files needed.

### 1. Create the application

1. Go to [cloud.laravel.com](https://cloud.laravel.com) → **New Application**
2. Connect your GitHub repo and select the `main` branch
3. Choose a region and click **Create Application**

### 2. Add a Database resource

From the environment canvas, add a **MySQL** or **PostgreSQL** database resource.  
Laravel Cloud will automatically inject all `DB_*` environment variables.

### 3. Set Build Commands

In **Environment → Deployments → Build Commands**, paste exactly:

```
composer install --no-dev --optimize-autoloader --no-interaction
npm install -g pnpm
pnpm install
pnpm run build
```

### 4. Set Deploy Commands

In **Environment → Deployments → Deploy Commands**, paste:

```
php artisan migrate --force
php artisan optimize
```

> **Important:** `php artisan optimize` must be in **Deploy Commands** (not Build Commands).  
> Build Commands run without your env vars, so caching config there would bake in empty `APP_KEY` → every request returns 500.

> Run `php artisan db:seed` once manually from the **Commands** tab after the first successful deploy if you want demo data.

### 5. Set Environment Variables

In **Environment → Settings → Environment Variables**, add:

| Key | Value |
|---|---|
| `APP_KEY` | Run `php artisan key:generate --show` locally and paste the result |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | Your Cloud URL (e.g. `https://test01-api-production.laravel.cloud`) |
| `QUEUE_CONNECTION` | `sync` |
| `SESSION_DRIVER` | `database` |
| `CACHE_STORE` | `database` |
| `MAIL_MAILER` | `log` |
| `LOG_CHANNEL` | `stderr` |

> `DB_*` variables are auto-injected by Cloud when you attach the database resource.  
> `APP_KEY` is the **most common cause of 500 errors** — make sure it is set.

### 6. Deploy

Click **Deploy**. First deploy takes ~2 minutes. Subsequent deploys are automatic on every push to the connected branch.

---

## React Frontend

The SPA is served by Laravel at `/`. After building, it handles all routing client-side.

| Path | Description |
|---|---|
| `/login` | Sign in |
| `/register` | Create account |
| `/dashboard` | Stats overview + recent orders |
| `/clients` | Full CRUD with modals |
| `/suppliers` | Full CRUD with modals |
| `/products` | Full CRUD with modals |
| `/orders` | Full CRUD with modals |
| `/order-details` | Full CRUD with modals |
| Sidebar link | `/api/documentation` (Swagger UI) |

---

## Gitflow

```
main        → production-ready
develop     → integration branch
feature/*   → individual features
release/*   → pre-production
hotfix/*    → emergency patches
```
