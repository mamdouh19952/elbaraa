# El Baraa Arabians — مربط البراء

Bilingual (Arabic / English) showcase website for a private Egyptian Arabian horse stud, plus an
admin dashboard the owner uses to manage the horses himself.

Two applications in one repository:

| Folder | What it is | Runs on |
|---|---|---|
| [`back/`](back/README.md) | Laravel 12 REST API (JSON only, no Blade UI) | `http://localhost:8000` |
| [`front/`](front/README.md) | Vue 3 SPA — public site **and** admin dashboard | `http://localhost:5173` |

---

## Quick start

You need **PHP 8.2+**, **Composer**, **Node 20+**, and a running **MySQL**.

```bash
# 1. Backend
cd back
composer install
cp .env.example .env          # then set your DB_* credentials
php artisan key:generate
php artisan migrate --seed    # creates tables + demo horses + the admin user
php artisan storage:link      # makes uploaded images publicly reachable
php artisan serve             # http://localhost:8000
```

```bash
# 2. Frontend (in a second terminal)
cd front
npm install
cp .env.example .env          # VITE_API_BASE_URL=http://localhost:8000/api
npm run dev                   # http://localhost:5173
```

Then open <http://localhost:5173>.

**Admin login:** `admin@elbaraa-arabians.com` / `password` — change this before deploying anywhere.
The dashboard is at `/admin` and is deliberately **not linked** from the public site.

---

## How the two apps fit together

```
Browser
   │
   ├── http://localhost:5173  ── Vue SPA
   │        │                     • public pages (Home, Horses, About, Contact)
   │        │                     • /admin dashboard behind a route guard
   │        │
   │        └── axios ──► http://localhost:8000/api  ── Laravel API
   │                             • public GET endpoints (no auth)
   │                             • admin endpoints behind auth:sanctum
   │                             • serves uploaded images from /storage
   └─
```

The frontend never talks to the database. It only knows the API, and only through
`front/src/services/api.js`.

### The response contract

**Every** endpoint returns the same envelope. The frontend relies on this everywhere, so don't
change it on one endpoint without changing both sides:

```jsonc
// success
{ "status": true,  "message": "...", "data": ... }
// error
{ "status": false, "message": "..." }
// validation (HTTP 422)
{ "status": false, "message": "Validation error", "errors": { "field": ["..."] } }
```

Framework errors are folded into the same shape in `back/bootstrap/app.php`, so a 401 or 404 looks
like every other response rather than Laravel's default HTML/JSON.

### Auth

Sanctum **token** auth (not cookie/SPA session), because the two apps are separate origins:

1. `POST /api/login` → returns `data.token`
2. The SPA stores it in `localStorage` + the Pinia auth store
3. Every request sends `Authorization: Bearer <token>`
4. `POST /api/logout` revokes just that token

There is one admin account and no public registration.

---

## Testing the API

A ready-to-import **Postman collection** lives at
[`back/El-Baraa-Arabians.postman_collection.json`](back/El-Baraa-Arabians.postman_collection.json).

Import it, run **Auth → Login** first (it saves the token automatically), and everything else works.
Running the whole collection top-to-bottom also works — Logout is deliberately last.

---

## Documentation map

| File | Covers |
|---|---|
| `README.md` (this file) | How the pieces fit together, setup, the shared contract |
| [`back/README.md`](back/README.md) | Full API reference, data model, media handling, seeding |
| [`front/README.md`](front/README.md) | SPA structure, i18n/RTL, design tokens, how to add a page |
| `CLAUDE.md` | Cross-cutting engineering rules (the contract, auth, CORS, git) |
| `back/CLAUDE.md` | Laravel conventions for this codebase |
| `front/CLAUDE.md` | Vue conventions for this codebase |
| `specs/001-horse-website-mvp/spec.md` | What the product is meant to do (requirements, user stories) |

> `CLAUDE.md` files are *how to build*. The spec is *what to build*. If they disagree on a business
> rule, the spec wins.

---

## Things worth knowing before you change anything

- **A horse belongs to many categories at once** (Sale + Mating + Breeding), via a pivot table —
  never a single `type` column. Adding a new category later needs no schema change.
- **`price` is nullable and independent of category.** A horse listed For Sale with no price is
  valid and renders as "price on request". Don't make it required.
- **`video` is optional and can be either** an uploaded file *or* a YouTube/Vimeo link.
- **Sold horses stay visible** on the public site — they are part of the stud's record.
- **The photographs in `front/public/images/` are the owner's real, watermarked photos.** Reuse
  them; never regenerate, replace, or delete them. The seeder copies them into the media library
  with `preservingOriginal()` precisely so the originals stay put.
- **Arabic is the default language** and RTL is a real layout mirror, not just translated strings.

---

## Known gaps

- **Jetstream/Livewire starter scaffolding is still installed** in `back/` but completely unused —
  this project is API-only. It is dead weight worth removing (see `back/README.md`).
- **No draft/unpublished state.** Every horse saved in the dashboard is immediately public.
- **Some seeded horse names are placeholders.** Names were read from the watermarks in the photos;
  where a photo had no readable name, the record says so and needs the owner to correct it.
- **No automated test suite committed yet.** The API was verified with an end-to-end script during
  development, but there are no PHPUnit feature tests in the repo.
