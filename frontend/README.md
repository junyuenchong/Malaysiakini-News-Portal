# Frontend App

Angular 19 news portal — displays data from the Laravel API.

**App URL:** http://localhost:4200

---

## Quick Start

```bash
cd frontend
npm install                       # install dependencies
# Set API URL in: src/app/config/env.config.ts → env.apiUrl
npm start                         # start dev server → http://localhost:4200
```

> Start the backend first: `php artisan serve` in the `backend/` folder.

**API URL to set:** `http://localhost:8000/api`

---

## What the App Loads

| Page | API call | What it shows |
| ---- | -------- | --------------- |
| Navbar menu | `GET /api/categories` | Navigation categories |
| News list (home) | `GET /api/news` | Latest articles |
| News list (category) | `GET /api/categories/{id}/news` | Filtered articles |
| News detail | `GET /api/news/{id}` | Full article + content |
| Images | from `image_url` field | e.g. `/storage/news/1.jpg` |

---

## Environment

| File | Purpose |
| ---- | ------- |
| `src/app/config/env.config.ts` | Set `env.apiUrl` — the Laravel API base URL |
| `.env.development` | Template (optional) |
| `.env` | Active env file (optional) |

```typescript
// src/app/config/env.config.ts
apiUrl: 'http://localhost:8000/api'
```

Restart `npm start` after changing the API URL.

---

## Commands

| Task | Command |
| ---- | ------- |
| Start dev server | `npm start` |
| Production build | `npm run build` |
| Lint check | `npm run lint` |
| Lint fix | `npm run lint:fix` |
| Format code | `npm run format` |
| Fix lint + format | `npm run fix` |
| Check all (no changes) | `npm run check` |
| Run all tests | `npm test` |
| Unit tests only | `npm run test:unit` |
| Integration tests only | `npm run test:integration` |

---

## Project Structure

```
src/app/
├── layout/navbar/           # NavbarComponent — GET /api/categories
├── components/news-card/    # NewsCardComponent — one article card
├── pages/
│   ├── news-list/           # NewsListComponent — home + category pages
│   └── news-detail/         # article page — GET /api/news/{id}
├── services/            # HTTP calls to Laravel API
├── models/              # TypeScript types for API data
└── config/env.config.ts # API base URL
```

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

| Problem | Fix |
| ------- | --- |
| Blank page | Check backend is running on port 8000 |
| API errors | Verify `env.apiUrl` in `env.config.ts`, restart `npm start` |
| Images not loading | In backend: `php artisan storage:link` + `composer seed:images` |
| Lint errors | `npm run fix` |

---

## More Info

- [Root README](../README.md) — full project setup
- [Backend README](../backend/README.md) — API, seeders, Docker
