# Frontend App

Angular 19 news portal — displays data from the Laravel API.

**App URL:** http://localhost:4200

---

## Quick Start

```bash
cd frontend
npm install

# Create API config (file is gitignored — must be created locally)
# src/app/config/env.config.ts
```

```typescript
// src/app/config/env.config.ts
export const env = {
  apiUrl: 'http://localhost:8000/api',
};
```

```bash
npm start                         # start dev server → http://localhost:4200
```

> Start the backend first: `php artisan serve` in the `backend/` folder.

---

## Routes

| Path              | Component           | What it shows                  |
| ----------------- | ------------------- | ------------------------------ |
| `/`               | NewsListComponent   | Latest news (all categories)   |
| `/category/:slug` | NewsListComponent   | News filtered by category slug |
| `/news/:id`       | NewsDetailComponent | Full article with content      |

---

## What the App Loads

| Page          | API call                                                | What it shows                                |
| ------------- | ------------------------------------------------------- | -------------------------------------------- |
| Navbar        | `GET /api/categories`                                   | Navigation categories                        |
| Home (`/`)    | `GET /api/news`                                         | Latest articles                              |
| Category page | `GET /api/categories` + `GET /api/categories/{id}/news` | Resolves slug → id, then loads category news |
| News detail   | `GET /api/news/{id}`                                    | Full article + content                       |
| Images        | from `image_url` field                                  | e.g. `/storage/news/1.jpg`                   |

`CategoryService.getMenu()` (`GET /api/menu`) is available for menu-visible categories only — navbar currently uses `getCategories()`.

---

## Client-Side Caching

Services use RxJS `shareReplay` to avoid duplicate HTTP calls within a session:

| Service           | Cached calls                                          |
| ----------------- | ----------------------------------------------------- |
| `CategoryService` | `getCategories()`, `getMenu()`                        |
| `NewsService`     | `getNews()`, `getNewsByCategoryId()`, `getNewsById()` |

---

## Environment

| File                           | Purpose                                                      |
| ------------------------------ | ------------------------------------------------------------ |
| `src/app/config/env.config.ts` | Set `env.apiUrl` — the Laravel API base URL (create locally) |

```typescript
export const env = {
  apiUrl: 'http://localhost:8000/api',
};
```

Restart `npm start` after changing the API URL.

---

## Commands

| Task                   | Command                    |
| ---------------------- | -------------------------- |
| Start dev server       | `npm start`                |
| Production build       | `npm run build`            |
| Lint check             | `npm run lint`             |
| Lint fix               | `npm run lint:fix`         |
| Format code            | `npm run format`           |
| Fix lint + format      | `npm run fix`              |
| Check all (no changes) | `npm run check`            |
| Run all tests          | `npm test`                 |
| Unit tests only        | `npm run test:unit`        |
| Integration tests only | `npm run test:integration` |

---

## Project Structure

```
src/app/
├── layout/navbar/              # NavbarComponent
├── components/news-card/       # NewsCardComponent — one article card
├── pages/
│   ├── news-list/              # home + category filter
│   └── news-detail/            # single article page
├── services/
│   ├── category.service.ts     # GET /api/categories, /api/menu
│   └── news.service.ts         # GET /api/news, /api/news/{id}, /api/categories/{id}/news
├── models/news.model.ts        # TypeScript types for API data
├── config/env.config.ts        # API base URL (create locally)
├── app.routes.ts               # route definitions
└── app.config.ts               # Angular providers
```

---

## Tests

```bash
npm test                    # unit + integration
npm run test:unit           # *.unit.spec.ts
npm run test:integration    # *.integration.spec.ts
```

| File                                      | Type        | What it tests         |
| ----------------------------------------- | ----------- | --------------------- |
| `app.component.unit.spec.ts`              | Unit        | Root component        |
| `category.service.unit.spec.ts`           | Unit        | Category HTTP service |
| `news.service.unit.spec.ts`               | Unit        | News HTTP service     |
| `news-list.component.integration.spec.ts` | Integration | News list page        |

---

## Production Build

```bash
# 1. Set production API URL in src/app/config/env.config.ts
# 2. Build
npm run build
```

Output folder: `frontend/dist/frontend/`

---

## Troubleshooting

| Problem                 | Fix                                                             |
| ----------------------- | --------------------------------------------------------------- |
| Blank page              | Check backend is running on port 8000                           |
| `env.config.ts` missing | Create `src/app/config/env.config.ts` (see Quick Start)         |
| API errors              | Verify `env.apiUrl`, restart `npm start`                        |
| Images not loading      | In backend: `php artisan storage:link` + `composer seed:images` |
| Lint errors             | `npm run fix`                                                   |

---

## More Info

- [Root README](../README.md) — full project setup
- [Backend README](../backend/README.md) — API, seeders, Docker
