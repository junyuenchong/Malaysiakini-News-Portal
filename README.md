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

## Backend Architecture

Module-based layout — each domain owns its HTTP layer, services, cache, repository, and model.

```
backend/app/
├── Modules/
│   ├── Category/
│   │   ├── Http/           # Controllers, Requests, Resources
│   │   ├── Services/      # Business rules + orchestration
│   │   ├── Cache/         # Category cache rules + Resource mapping
│   │   ├── Repositories/ # DB queries + eager loading (N+1 prevention)
│   │   └── Models/        # Eloquent model + relationships
│   └── News/
│       ├── Http/           # Controllers, Requests, Resources
│       ├── Services/       # Business rules + orchestration
│       ├── Cache/          # News cache rules + Resource mapping
│       ├── Repositories/  # DB queries + eager loading (N+1 prevention)
│       └── Models/         # Eloquent model + relationships
├── Support/
│   ├── Cache/
│   │   ├── CacheKey.php               # Cache key builder + TTL constants
│   │   ├── CacheService.php           # getOrStore() + jsonWithCacheHeader()
│   │   └── Concerns/RemembersResourcePayload.php # Shared Resource → array cache helper
│   └── Http/
│       ├── ResourcePayload.php        # Converts Resources into plain arrays
│       └── Concerns/
│           ├── PaginatesRequests.php        # Shared page/perPage accessors
│           ├── MergesRouteId.php            # Copy route id into request payload
│           └── RespondsWithCachedJson.php  # Shared cached JSON response helper
└── Providers/
    └── AppServiceProvider.php          # Laravel service provider
```

**Request flow:**

```
HTTP Request
    ↓
Controller      ← JSON response + Cache-Control header
    ↓
Service         ← business rules (slug resolve, 404)
    ↓
*Cache          ← domain cache rules + Resource mapping
    ↓
Support layer   ← shared cache + HTTP helper traits
    ↓
Repository      ← DB queries + eager loading (N+1 prevention)
    ↓
JSON Response
```

**Why `Support/`?** Shared infrastructure used by multiple modules. Keeps cache mechanics out of domain modules while `CategoryCache` / `NewsCache` own domain-specific rules.

---

## Frontend Architecture

```
src/app/
├── layout/navbar/           # navigation
├── components/news-card/    # article card
├── pages/
│   ├── news-list/           # home + /category/:slug
│   └── news-detail/         # /news/:id
├── services/                # HTTP clients (shareReplay cache)
├── models/                  # TypeScript API types
└── config/env.config.ts     # API base URL (create locally — gitignored)
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
composer test          # 24 tests — unit + integration
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
