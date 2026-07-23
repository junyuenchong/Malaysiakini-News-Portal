# News Portal Assessment

Laravel 13 API + Angular 19 frontend, inspired by [Malaysiakini](https://www.malaysiakini.com/my/latest/news).

---

## What This Project Does

| Part         | Role                                                        |
| ------------ | ----------------------------------------------------------- |
| **Backend**  | REST API — categories, menu, news list, news detail, images |
| **Frontend** | Angular app — navbar, news list, article detail             |
| **Docker**   | MySQL database + Adminer UI for local dev                   |

---

## Quick Start

```bash
# 1. Database (Docker)
cd backend
docker compose up -d              # start MySQL + Adminer

# 2. Backend API
composer install
cp .env.development .env          # Windows: copy .env.development .env
php artisan key:generate
php artisan storage:link          # serve images from /storage/news/
php artisan migrate --seed        # tables + 6 categories + 100 articles + images
php artisan serve                 # start API → http://localhost:8000

# 3. Frontend (new terminal)
cd frontend
npm install
# Create src/app/config/env.config.ts (see frontend/README.md)
npm start                         # start app → http://localhost:4200
```

---

## URLs

| What            | URL                                      |
| --------------- | ---------------------------------------- |
| Frontend app    | http://localhost:4200                    |
| Backend API     | http://localhost:8000                    |
| News images     | http://localhost:8000/storage/news/1.jpg |
| MySQL           | `localhost:3307`                         |
| Adminer (DB UI) | http://localhost:8080                    |

**MySQL login:** database `news_portal` / user `news_user` / password `news_password`

---

## API Endpoints

| Method | URL                         | What it returns                                 |
| ------ | --------------------------- | ----------------------------------------------- |
| GET    | `/api/menu`                 | Menu-visible categories (`show_in_menu = true`) |
| GET    | `/api/categories`           | All categories                                  |
| GET    | `/api/categories/{id}/news` | Paginated news for one category                 |
| GET    | `/api/news`                 | Paginated news list                             |
| GET    | `/api/news/{id}`            | One full article                                |
| GET    | `/storage/news/{id}.jpg`    | Article image                                   |

**News list filters:** `?category=world&page=1&per_page=12`

---

## Project Structure

```
News Portal Assessment/
├── backend/                    # Laravel 13 REST API
├── frontend/                   # Angular 19 SPA
└── README.md                   # This file — project overview
```

---

## Backend Architecture

Module-based layout — each domain owns its HTTP layer, services, cache, repository, and model.

```
backend/
├── app/
│   ├── Modules/                # Domain modules (Category, News)
│   │   ├── Category/
│   │   │   ├── Http/           # API entry — routes hit here first
│   │   │   │   ├── Controllers/  # Return JSON + Cache-Control headers
│   │   │   │   ├── Requests/     # Validate query params and route ids
│   │   │   │   └── Resources/    # Shape API JSON output
│   │   │   ├── Services/       # Business rules and orchestration
│   │   │   ├── Cache/          # Category cache keys, TTL, Resource mapping
│   │   │   ├── Repositories/   # DB queries + eager loading
│   │   │   └── Models/         # Eloquent model + relationships
│   │   └── News/
│   │       ├── Http/           # API entry — routes hit here first
│   │       │   ├── Controllers/  # Return JSON + Cache-Control headers
│   │       │   ├── Requests/     # Validate query params and route ids
│   │       │   └── Resources/    # Shape API JSON output
│   │       ├── Services/       # Business rules and orchestration
│   │       ├── Cache/          # News cache keys, TTL, Resource mapping
│   │       ├── Repositories/   # DB queries + eager loading
│   │       └── Models/         # Eloquent model + relationships
│   ├── Support/
│   │   └── Cache/              # Shared cache helpers used by all modules
│   │       ├── CacheKey.php      # Cache key builder + TTL constants
│   │       └── CacheService.php  # getOrStore() + jsonWithCacheHeader()
│   └── Providers/
│       └── AppServiceProvider.php  # Shared bindings + app startup config
├── database/
│   ├── migrations/             # Table schema (categories, news, …)
│   ├── seeders/                # Sample data + image download
│   └── factories/              # Test data factories
├── routes/
│   ├── api.php                 # API route definitions
│   └── console.php             # Artisan commands (seed shortcuts)
├── tests/                      # PHPUnit — unit + feature tests
└── public/                     # Web root (index.php, storage symlink)
```

**Request flow:**

```
HTTP Request
    ↓
FormRequest     ← validate query params / route id
    ↓
Controller      ← JSON response + Cache-Control header
    ↓
Service         ← business rules (slug resolve, 404)
    ↓
*Cache          ← domain cache rules + Resource → array
    ↓
CacheService    ← shared getOrStore() wrapper
    ↓
Repository      ← DB queries + eager loading
    ↓
JSON Response
```

**Why `Support/`?** Shared infrastructure used by multiple modules. Right now it only holds the generic cache wrapper; each module keeps its own cache rules in `CategoryCache` / `NewsCache`.

---

## Frontend Architecture

```
frontend/
├── src/
│   ├── app/
│   │   ├── layout/
│   │   │   └── navbar/         # Top navigation bar
│   │   ├── components/
│   │   │   └── news-card/      # Reusable article card
│   │   ├── pages/
│   │   │   ├── news-list/      # Home + /category/:slug
│   │   │   └── news-detail/    # /news/:id article page
│   │   ├── services/           # HTTP clients (shareReplay cache)
│   │   ├── models/             # TypeScript API types
│   │   ├── config/             # env.config.ts — API base URL (gitignored)
│   │   ├── app.routes.ts       # Route definitions
│   │   └── app.config.ts       # Angular providers
│   ├── index.html              # HTML shell
│   ├── main.ts                 # App bootstrap
│   └── styles.css              # Global styles
└── scripts/
    └── generate-env-config.mjs # Reads .env → generates env.config.ts
```

**Routes:** `/` · `/category/:slug` · `/news/:id`

---

## Caching

| Layer        | Where                  | What                                                                   |
| ------------ | ---------------------- | ---------------------------------------------------------------------- |
| App cache    | Laravel `getOrStore`   | API payload cached in file store — **falls back to DB if cache fails** |
| HTTP cache   | `Cache-Control` header | Browser caches JSON responses                                          |
| Client cache | Angular `shareReplay`  | Avoids duplicate HTTP calls in the SPA                                 |

---

## Tests

### Backend (PHPUnit)

```bash
cd backend
composer test          # 23 tests — unit + integration
composer check         # format + analyse + test
```

| Suite             | What it tests                |
| ----------------- | ---------------------------- |
| `Unit/Modules`    | Models, Resources per module |
| `Feature/Modules` | Full HTTP API per module     |
| `Feature/Seeders` | ImageSeeder                  |

### Frontend (Jasmine/Karma)

```bash
cd frontend
npm test               # unit + integration
npm run test:unit
npm run test:integration
```

| Pattern                 | What it tests                     |
| ----------------------- | --------------------------------- |
| `*.unit.spec.ts`        | Services, components in isolation |
| `*.integration.spec.ts` | Page-level behaviour              |

Tests use SQLite in memory on the backend — Docker not required for `composer test`.

---

## Common Commands

### Backend

```bash
cd backend
composer check              # format + analyse + test
composer test               # run PHPUnit
composer db:refresh         # reset DB + reseed everything
composer seed:images          # download article images
php artisan serve             # start API server
```

### Frontend

```bash
cd frontend
npm run check               # lint + format check
npm run fix                 # auto-fix lint + format
npm start                   # start dev server
npm run build               # production build
npm test                    # all tests
```

### Docker

```bash
cd backend
docker compose up -d          # start MySQL + Adminer
docker compose down           # stop containers
docker compose down -v        # stop + delete all DB data
```

---

## Env Files

| Folder      | Active file                                     | Config            |
| ----------- | ----------------------------------------------- | ----------------- |
| `backend/`  | `.env` (copy from `.env.development`)           | Laravel `config/` |
| `frontend/` | `src/app/config/env.config.ts` (create locally) | `env.apiUrl`      |

**Frontend API URL example:** `http://localhost:8000/api`

---

## More Documentation

| File                                     | Contents                            |
| ---------------------------------------- | ----------------------------------- |
| [backend/README.md](backend/README.md)   | Docker, seeders, API details, tests |
| [frontend/README.md](frontend/README.md) | Angular setup, routes, tests        |

---

## Stop Everything

```bash
cd backend
docker compose down               # stop MySQL + Adminer
docker compose down -v            # stop + delete all DB data
```
