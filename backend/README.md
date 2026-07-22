# Backend API

Laravel 13 REST API for the news portal.

**Base URL:** http://localhost:8000

---

## Quick Start

```bash
cd backend
docker compose up -d              # start MySQL + Adminer
composer install
cp .env.development .env          # Windows: copy .env.development .env
php artisan key:generate
php artisan storage:link          # required for /storage/news/ images
php artisan migrate --seed        # tables + 6 categories + 100 articles + images
php artisan serve                 # start server → http://localhost:8000
```

---

## How the Code Works

Each API request follows this path:

```
HTTP Request
    ↓
FormRequest       ← validate query params / route id (where applicable)
    ↓
Controller        ← HTTP response + Cache-Control header
    ↓
Service           ← business flow (slug resolve, 404)
    ↓
*Cache            ← domain cache rules + Resource → array
    ↓
Support helpers   ← shared cache + HTTP helper traits
    ↓
Repository        ← DB queries + eager loading
    ↓
JSON Response
```

**Cross-module rule:** `NewsService` uses `CategoryRepository` to resolve category slugs — News module depends on Category data access, not Category HTTP layer.

---

## App Structure

```
app/
├── Modules/
│   ├── Category/
│   │   ├── Http/
│   │   │   ├── Controllers/CategoryController.php
│   │   │   ├── Requests/CategoryNewsRequest.php
│   │   │   └── Resources/CategoryResource.php
│   │   ├── Services/CategoryService.php
│   │   ├── Cache/CategoryCache.php
│   │   ├── Repositories/CategoryRepository.php
│   │   └── Models/Category.php
│   └── News/
│       ├── Http/
│       │   ├── Controllers/NewsController.php
│       │   ├── Requests/NewsRequest.php
│       │   └── Resources/NewsResource.php
│       ├── Services/NewsService.php
│       ├── Cache/NewsCache.php
│       ├── Repositories/NewsRepository.php
│       └── Models/News.php
├── Support/
│   ├── Cache/
│   │   ├── CacheKey.php        # keys + TTL constants
│   │   ├── CacheService.php    # getOrStore() + jsonWithCacheHeader()
│   │   └── Concerns/RemembersResourcePayload.php
│   └── Http/
│       ├── ResourcePayload.php
│       └── Concerns/
│           ├── PaginatesRequests.php
│           ├── MergesRouteId.php
│           └── RespondsWithCachedJson.php
└── Providers/
    └── AppServiceProvider.php
```

| Layer         | What it does                                               |
| ------------- | ---------------------------------------------------------- |
| Controllers   | Entry point — call service, return JSON + headers          |
| FormRequests  | Input validation (`NewsRequest`, `CategoryNewsRequest`)    |
| Services      | Business flow (slug resolve, 404 checks)                   |
| \*Cache       | Domain cache rules, Resource → array on cache miss         |
| Repositories  | Database queries, filters, eager loading                   |
| Support/Cache | Generic cache wrapper + key management (shared by modules) |
| Support/Http  | Shared Resource → array + request/controller traits        |
| Resources     | Which fields appear in the API JSON                        |
| Models        | Eloquent models + relationships                            |

Routes are defined in `routes/api.php`.

---

## Support Layer

`app/Support/` holds **shared infrastructure** used by more than one module.

| Class          | Role                                                                  |
| -------------- | --------------------------------------------------------------------- |
| `CacheKey`     | Central cache key builder + TTL constants                             |
| `CacheService` | `getOrStore()` wrapper + `jsonWithCacheHeader()` with `Cache-Control` |

Modules keep domain-specific cache logic in `CategoryCache` and `NewsCache`. Support stays generic — no Category or News business rules.

---

## Caching

| Layer      | Mechanism                          | TTL                                                                           |
| ---------- | ---------------------------------- | ----------------------------------------------------------------------------- |
| App cache  | `getOrStore` via `CacheService`    | Categories: 300s · News list: 30s · **falls back to DB if cache store fails** |
| HTTP cache | `Cache-Control: public, max-age=…` | Set per endpoint in controllers                                               |
| Store      | `CACHE_STORE=file` in `.env`       | Not Redis                                                                     |

**Cache keys** (from `CacheKey`):

| Key pattern                           | Endpoint                        |
| ------------------------------------- | ------------------------------- |
| `categories:all`                      | `GET /api/categories`           |
| `categories:menu`                     | `GET /api/menu`                 |
| `news:list:{slug}:{page}:{perPage}`   | `GET /api/news`                 |
| `news:category:{id}:{page}:{perPage}` | `GET /api/categories/{id}/news` |
| `news:show:{id}`                      | `GET /api/news/{id}`            |

---

## Docker

Run from `backend/` folder.

| Action           | Command                  |
| ---------------- | ------------------------ |
| Start            | `docker compose up -d`   |
| Stop             | `docker compose down`    |
| Stop + wipe data | `docker compose down -v` |
| Check status     | `docker compose ps`      |

| Service | URL                   | Login                                         |
| ------- | --------------------- | --------------------------------------------- |
| MySQL   | `localhost:3307`      | `news_portal` / `news_user` / `news_password` |
| Adminer | http://localhost:8080 | Server: `mysql`, same credentials             |

---

## Environment

Copy template to active file:

```bash
cp .env.development .env          # Windows: copy .env.development .env
php artisan key:generate
```

Key local settings:

```env
DB_PORT=3307
DB_DATABASE=news_portal
DB_USERNAME=news_user
DB_PASSWORD=news_password
APP_URL=http://localhost:8000
CACHE_STORE=file
```

---

## Database

### Migrations

| Task              | Command                      |
| ----------------- | ---------------------------- |
| Run migrations    | `php artisan migrate`        |
| Full reset + seed | `composer db:refresh`        |
| Check status      | `php artisan migrate:status` |

### Tables

| Table                    | Purpose          |
| ------------------------ | ---------------- |
| `categories`             | News categories  |
| `news`                   | News articles    |
| `users`, `cache`, `jobs` | Laravel defaults |

---

## Seeders

Run in order:

```
CategorySeeder  →  NewsSeeder  →  ImageSeeder
  6 categories      100 articles    download .jpg files
```

| Task                          | Command                                               |
| ----------------------------- | ----------------------------------------------------- |
| Seed everything               | `composer db:refresh` or `php artisan migrate --seed` |
| Seed images only              | `composer seed:images`                                |
| Remove all seeded data        | `composer seed:rollback`                              |
| Reset seed data (keep tables) | `composer seed:refresh`                               |

Image download logic is in `database/seeders/ImageSeeder.php`.

---

## Images

```bash
php artisan storage:link    # run once
```

| File on disk                    | Public URL                                 |
| ------------------------------- | ------------------------------------------ |
| `storage/app/public/news/1.jpg` | `http://localhost:8000/storage/news/1.jpg` |

Images are downloaded by `ImageSeeder` and served via Laravel storage link.

---

## API Reference

| Method | Endpoint                    | Description                             |
| ------ | --------------------------- | --------------------------------------- |
| GET    | `/api/menu`                 | Menu categories (`show_in_menu = true`) |
| GET    | `/api/categories`           | All categories                          |
| GET    | `/api/categories/{id}/news` | Paginated news for one category         |
| GET    | `/api/news`                 | Paginated news list                     |
| GET    | `/api/news/{id}`            | Single article with full content        |

**`/api/news` query params:**

| Param      | Example | Default       |
| ---------- | ------- | ------------- |
| `category` | `world` | all           |
| `page`     | `2`     | `1`           |
| `per_page` | `20`    | `12` (max 50) |

**Behaviour notes:**

- Unknown `category` slug on `GET /api/news` → empty paginated list (not 404)
- Invalid category id on `GET /api/categories/{id}/news` → 404
- List responses omit `content`; detail includes full `content`
- `NewsResource` uses `whenLoaded('category')` — category eager-loaded in repository

**Try it:**

```bash
curl http://localhost:8000/api/menu
curl http://localhost:8000/api/categories
curl http://localhost:8000/api/categories/1/news
curl http://localhost:8000/api/news
curl http://localhost:8000/api/news?category=world
curl http://localhost:8000/api/news/1
curl http://localhost:8000/storage/news/1.jpg
```

---

## Tests & Quality

```bash
composer test          # 24 tests
composer check         # format + analyse + test
composer fix           # auto-format with Pint
composer analyse       # PHPStan
```

### Test structure

```
tests/
├── TestCase.php
├── Unit/
│   ├── Modules/
│   │   ├── Category/
│   │   │   ├── Models/CategoryTest.php
│   │   │   └── Resources/CategoryResourceTest.php
│   │   └── News/
│   │       ├── Models/NewsTest.php
│   │       └── Resources/NewsResourceTest.php
│   ├── Support/
│   │   ├── Cache/CacheServiceTest.php
│   │   └── Http/ResourcePayloadTest.php
└── Feature/
    ├── Modules/
    │   ├── Category/CategoryApiTest.php   # integration — full HTTP stack
    │   └── News/NewsApiTest.php
    └── Seeders/ImageSeederTest.php
```

| Suite           | What it tests                                        |
| --------------- | ---------------------------------------------------- |
| Unit/Modules    | Model casts, relationships, Resource JSON shape      |
| Unit/Support    | CacheService fallback and ResourcePayload conversion |
| Feature/Modules | API endpoints through routing stack                  |
| Feature/Seeders | ImageSeeder download/skip/failure                    |

Tests use SQLite in memory — Docker not required.

---

## Troubleshooting

| Problem                      | Fix                                                                     |
| ---------------------------- | ----------------------------------------------------------------------- |
| DB connection refused        | `docker compose up -d`                                                  |
| DB access denied             | Check `.env` matches Docker credentials                                 |
| Images 404                   | `php artisan storage:link` then `composer seed:images`                  |
| Slow first request           | Normal — cache builds on first hit                                      |
| `storage` symlink wrong path | Re-run `php artisan storage:link`                                       |
| Full reset                   | `docker compose down -v && docker compose up -d && composer db:refresh` |

---

## License

MIT — built on [Laravel](https://laravel.com).
