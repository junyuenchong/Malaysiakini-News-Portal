# Backend API

Laravel 13 REST API for the news portal.

**Base URL:** http://localhost:8000

---

## Quick Start

```bash
cd backend
docker compose up -d              # start MySQL + Adminer
composer install                  # install PHP dependencies
cp .env.development .env          # Windows: copy .env.development .env
php artisan key:generate          # generate APP_KEY
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
Controller      ← thin, calls helper
    ↓
Helper          ← query DB + cache
    ↓
Projection      ← build JSON fields
    ↓
JSON Response
```

**News routes also use `NewsRequest`** to validate query params or route id before the controller runs.

---

## App Structure

```
app/Http/
├── Controllers/
│   ├── CategoryController.php    # GET /api/categories, GET /api/menu
│   └── NewsController.php        # GET /api/news, GET /api/news/{id}
├── Helpers/
│   ├── JsonCacheHelper.php       # shared cache + JSON response
│   ├── CategoryHelper.php        # category list + cache
│   └── NewsHelper.php            # news list/show + cache
├── Projections/
│   ├── CategoryProjection.php    # category JSON fields
│   └── NewsProjection.php        # news JSON fields
└── Requests/
    └── NewsRequest.php           # validates news list params + article id
```

| Layer | What it does |
| ----- | ------------ |
| Controllers | Entry point — receive request, call helper |
| Helpers | Database queries, filters, caching |
| Projections | Which fields appear in the API JSON |
| Requests | Input validation before controller runs |

Category routes have no Form Request (no input to validate).

---

## Docker

Run from `backend/` folder.

| Action | Command |
| ------ | ------- |
| Start | `docker compose up -d` |
| Stop | `docker compose down` |
| Stop + wipe data | `docker compose down -v` |
| Check status | `docker compose ps` |

| Service | URL | Login |
| ------- | --- | ----- |
| MySQL | `localhost:3307` | `news_portal` / `news_user` / `news_password` |
| Adminer | http://localhost:8080 | Server: `mysql`, same credentials |

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

| Task | Command |
| ---- | ------- |
| Run migrations | `php artisan migrate` |
| Full reset + seed | `composer db:refresh` |
| Check status | `php artisan migrate:status` |

### Tables

| Table | Purpose |
| ----- | ------- |
| `categories` | News categories |
| `news` | News articles |
| `users`, `cache`, `jobs` | Laravel defaults |

---

## Seeders

Run in order:

```
CategorySeeder  →  NewsSeeder  →  ImageSeeder
  6 categories      100 articles    download .jpg files
```

| Task | Command |
| ---- | ------- |
| Seed everything | `composer db:refresh` or `php artisan migrate --seed` |
| Seed images only | `composer seed:images` |
| Remove all seeded data | `composer seed:rollback` |
| Reset seed data (keep tables) | `composer seed:refresh` |

Image download logic is in `database/seeders/ImageSeeder.php`.

---

## Images

```bash
php artisan storage:link    # run once
```

| File on disk | Public URL |
| ------------ | ---------- |
| `storage/app/public/news/1.jpg` | `http://localhost:8000/storage/news/1.jpg` |

Images are downloaded by `ImageSeeder` and served via Laravel storage link.

---

## API Reference

| Method | Endpoint | Description |
| ------ | -------- | ----------- |
| GET | `/api/menu` | Menu categories (`show_in_menu = true`) |
| GET | `/api/categories` | All categories |
| GET | `/api/news` | Paginated news list |
| GET | `/api/news/{id}` | Single article with full content |

**`/api/news` query params:**

| Param | Example | Default |
| ----- | ------- | ------- |
| `category` | `politics` | all |
| `page` | `2` | `1` |
| `per_page` | `20` | `12` (max 50) |

**Try it:**

```bash
curl http://localhost:8000/api/menu
curl http://localhost:8000/api/news
curl http://localhost:8000/api/news?category=politics
curl http://localhost:8000/api/news/1
curl http://localhost:8000/storage/news/1.jpg
```

---

## Tests & Quality

```bash
composer test          # 18 tests
composer check         # format + analyse + test
composer fix           # auto-format with Pint
```

| Suite | What it tests |
| ----- | ------------- |
| Unit | Models, Projections |
| Feature | API endpoints, ImageSeeder |

Tests use SQLite in memory — Docker not required.

---

## Troubleshooting

| Problem | Fix |
| ------- | --- |
| DB connection refused | `docker compose up -d` |
| DB access denied | Check `.env` matches Docker credentials |
| Images 404 | `php artisan storage:link` then `composer seed:images` |
| Slow first request | Normal — cache builds on first hit |
| Full reset | `docker compose down -v && docker compose up -d && composer db:refresh` |

---

## License

MIT — built on [Laravel](https://laravel.com).
