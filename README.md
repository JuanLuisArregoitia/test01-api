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
| DB (Render) | PostgreSQL |
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

## Deployment on Render

1. Create a new **Web Service** on Render, connect your GitHub repository.
2. Render will auto-detect PHP via Nixpacks.
3. Set **Build Command**:
   ```
   composer install --no-dev --optimize-autoloader && npm install && npm run build && php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
4. Set **Start Command**:
   ```
   php artisan serve --host 0.0.0.0 --port $PORT
   ```
5. Run migrations after first deploy via Render Shell:
   ```
   php artisan migrate --force && php artisan db:seed
   ```
6. Required environment variables (set in Render dashboard):
   - `APP_KEY` — Run `php artisan key:generate --show` locally
   - `APP_URL` — Your Render service URL (e.g. `https://test01-api.onrender.com`)
   - `DB_CONNECTION=pgsql` + `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (from Render PostgreSQL)
   - `MAIL_MAILER=log` (or configure a real mail service)

Use the included `render.yaml` for Infrastructure as Code — Render will provision the web service + PostgreSQL automatically.

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
