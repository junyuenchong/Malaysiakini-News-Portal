# News Portal Assessment

Laravel 13 API + Angular 19 frontend, inspired by [Malaysiakini](https://www.malaysiakini.com/my/latest/news).

---

## What This Project Does

| Part | Role |
| ---- | ---- |
| **Backend** | REST API — categories, news list, news detail, images |
| **Frontend** | Angular app — menu, news list, article detail |
| **Docker** | MySQL database + Adminer UI for local dev |

---

## Quick Start

```bash
# 1. Database (Docker)
cd backend                        # enter backend folder
docker compose up -d              # start MySQL + Adminer

# 2. Backend API
composer install                  # install PHP dependencies
cp .env.development .env          # copy dev env (Windows: copy .env.development .env)
php artisan key:generate          # generate APP_KEY
php artisan storage:link          # serve images from /storage/news/
php artisan migrate --seed        # create tables + sample data + images
php artisan serve                 # start API → http://localhost:8000

# 3. Frontend (new terminal)
cd frontend                       # enter frontend folder
npm install                       # install Node dependencies
# Set API URL in: src/app/config/env.config.ts → env.apiUrl
npm start                         # start app → http://localhost:4200
```

---

## URLs

| What | URL |
| ---- | --- |
| Frontend app | http://localhost:4200 |
| Backend API | http://localhost:8000 |
| News images | http://localhost:8000/storage/news/1.jpg |
| MySQL | `localhost:3307` |
| Adminer (DB UI) | http://localhost:8080 |

**MySQL login:** database `news_portal` / user `news_user` / password `news_password`

---

## API Endpoints

| Method | URL | What it returns |
| ------ | --- | --------------- |
| GET | `/api/categories` | All categories |
| GET | `/api/categories/{id}/news` | News for one category |
| GET | `/api/news` | Paginated news list |
| GET | `/api/news/{id}` | One full article |
| GET | `/storage/news/{id}.jpg` | Article image |

**News list filters:** `?category=world&page=1&per_page=12`

---

## Backend Code Layout

```
backend/app/Http/
├── Controllers/    # receive HTTP request, call helper
├── Helpers/        # query database + cache results
├── Resources/      # API Resource JSON shaping
└── Requests/       # validate input
```

**Flow:** Request → Controller → Helper → Resource → JSON

---

## Common Commands

### Backend

```bash
cd backend
composer check              # format + analyse + test
composer db:refresh         # reset DB + reseed everything
composer seed:rollback      # remove seeded data only
php artisan serve           # start API server
composer serve              # same as php artisan serve
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

| Folder | Active file | Config |
| ------ | ----------- | ------ |
| `backend/` | `.env` (copy from `.env.development`) | Laravel `config/` |
| `frontend/` | `.env` (optional) | `src/app/config/env.config.ts` → `env.apiUrl` |

**Frontend API URL example:** `http://localhost:8000/api`

---

## More Documentation

| File | Contents |
| ---- | -------- |
| [backend/README.md](backend/README.md) | Docker, seeders, API details, tests |
| [frontend/README.md](frontend/README.md) | Angular setup, lint, build |

---

## Stop Everything

```bash
cd backend
docker compose down               # stop MySQL + Adminer
docker compose down -v            # stop + delete all DB data
```
